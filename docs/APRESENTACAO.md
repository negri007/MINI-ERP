# Mini ERP — Como explicar o CRUD de Produtos

O CRUD escolhido para a apresentação é o de **Produtos**, porque ele usa tudo o que os outros usam e ainda liga várias tabelas entre si: categoria, fornecedor, estoque e vendas.

## 1. Visão geral

| Letra | Operação | No sistema | Método HTTP |
| --- | --- | --- | --- |
| C | Create (criar) | Botão "+ Novo produto" → formulário → Salvar | GET /produtos/create + POST /produtos |
| R | Read (ler) | Lista de produtos (Tabela ou Vitrine) | GET /produtos |
| U | Update (atualizar) | Ícone de lápis → formulário preenchido → Atualizar | GET /produtos/{id}/edit + PUT /produtos/{id} |
| D | Delete (excluir) | Ícone de lixeira com confirmação | DELETE /produtos/{id} |

O que Produtos tem a mais que os outros cadastros:

- **Relacionamentos**: cada produto pertence a uma categoria (obrigatória) e a um fornecedor (opcional).
- **Campos de tipos diferentes**: texto, decimal (preço e custo), inteiro (estoque) e listas de seleção (select).
- **Custo opcional e lucro**: o custo pode ficar vazio, que quer dizer "não informado". Quando existe, o Dashboard e o relatório calculam o lucro bruto e a margem.
- **Validação mais rica**: números mínimos e checagem de que a categoria e o fornecedor existem no banco.
- **Formatação**: preço e custo aparecem como moeda brasileira (R$ 7,50).
- **Integração com o Dashboard**: produtos no estoque mínimo ou abaixo aparecem em "Estoque baixo" e no selo vermelho do menu.
- **Ligação com o estoque**: o estoque inicial vira uma movimentação, e depois só muda por vendas ou pela tela de Estoque.

## 2. Fluxo MVC

```
Navegador → Rota (routes/web.php) → Controller → Model → MySQL
                                        ↓
Navegador ←──────── View (Blade) ←──────┘
```

Exemplo: ao clicar em "Produtos", o navegador pede `GET /produtos`. A rota chama `ProdutoController@index`, que busca os produtos pelo model `Produto` e entrega a lista para a view `produtos/index.blade.php`.

| Camada | Arquivo | Responsabilidade |
| --- | --- | --- |
| Model | `app/Models/Produto.php` | Representa a tabela, os relacionamentos e as regras do produto |
| View | `resources/views/produtos/` | O que o usuário vê (HTML + Blade) |
| Controller | `app/Http/Controllers/ProdutoController.php` | Recebe o pedido, salva, redireciona |
| Validação | `app/Http/Requests/ProdutoRequest.php` | Confere os dados antes de o controller rodar |
| Rotas | `routes/web.php` | Liga cada URL a um método do controller |
| Banco | `database/migrations/` | Cria e altera as tabelas no MySQL |

## 3. Banco de dados: as migrations

A tabela nasce numa migration e depois ganha colunas novas em outras migrations, sem apagar os dados que já existem.

**Criação da tabela** (`2026_01_01_000004_create_produtos_table.php`):

```php
Schema::create('produtos', function (Blueprint $table) {
    $table->id();
    $table->string('nome');
    $table->text('descricao')->nullable();
    $table->decimal('preco', 10, 2)->default(0);
    $table->integer('estoque')->default(0);
    $table->foreignId('categoria_id')
          ->constrained('categorias')->restrictOnDelete();
    $table->foreignId('fornecedor_id')->nullable()
          ->constrained('fornecedores')->nullOnDelete();
    $table->timestamps();
});
```

- **`foreignId(...)->constrained(...)`**: chave estrangeira; o banco só aceita um `categoria_id` que exista.
- **`restrictOnDelete()`**: impede excluir uma categoria que ainda tem produtos.
- **`nullOnDelete()`**: se o fornecedor for excluído, o produto fica sem fornecedor.
- **Ordem**: a migration de produtos roda depois de categorias e fornecedores, porque depende delas.

**Coluna nova numa tabela que já existe** (`2026_09_30_000001_add_custo_to_produtos.php`):

```php
Schema::table('produtos', function (Blueprint $table) {
    $table->decimal('custo', 10, 2)->nullable()->after('preco');
});

// Regra no próprio banco: custo não pode ser negativo
DB::statement('ALTER TABLE produtos ADD CONSTRAINT produtos_custo_nao_negativo CHECK (custo IS NULL OR custo >= 0)');
```

- **`Schema::table`** (e não `create`): altera a tabela sem perder os produtos cadastrados.
- **`nullable()`**: os produtos antigos ficam com custo vazio ("não informado"), em vez de um zero inventado.
- **`CHECK`**: mesmo que alguém passe por fora do sistema, o MySQL recusa custo negativo.
- Toda migration tem o método **`down()`**, que desfaz a mudança (`php artisan migrate:rollback`).

## 4. Model Produto

```php
protected $fillable = ['nome', 'descricao', 'preco', 'custo', 'estoque', 'estoque_minimo', 'categoria_id', 'fornecedor_id'];

protected $casts = [
    'preco' => 'decimal:2',
    'custo' => 'decimal:2', // null = custo não informado
    'estoque' => 'integer',
    'estoque_minimo' => 'integer',
];

public function categoria(): BelongsTo
{
    return $this->belongsTo(Categoria::class);
}

// Filtro reutilizável: Produto::estoqueBaixo()->get()
public function scopeEstoqueBaixo(Builder $query): Builder
{
    return $query->whereColumn('estoque', '<=', 'estoque_minimo');
}

// Um único texto para explicar por que não pode excluir (tela e servidor usam o mesmo)
public function motivoParaNaoExcluir(): ?string
{
    // Produto que já apareceu em alguma venda não pode ser excluído
}
```

- **`$fillable`**: campos liberados para `Produto::create($dados)`. Um campo fora da lista é ignorado (proteção).
- **`$casts`**: preço e custo sempre com 2 casas, estoque sempre inteiro.
- **`belongsTo`**: permite escrever `$produto->categoria->nome` na view.
- **`hasMany`**: `$produto->movimentacoes` traz o histórico de estoque; `$produto->itensVenda` traz as vendas em que ele apareceu.
- **Scopes** (`scopeEstoqueBaixo`, `scopeBusca`): filtros com nome, usados como `Produto::estoqueBaixo()`.
- **`motivoParaNaoExcluir()`**: a regra fica no model, então a janela de confirmação e o controller mostram a mesma frase.

## 5. Rotas

```php
Route::resource('produtos', ProdutoController::class)->except('show');
```

| Método | URL | Controller | Para quê |
| --- | --- | --- | --- |
| GET | /produtos | index | Listar |
| GET | /produtos/create | create | Formulário vazio |
| POST | /produtos | store | Salvar novo |
| GET | /produtos/{produto}/edit | edit | Formulário preenchido |
| PUT | /produtos/{produto} | update | Salvar alteração |
| DELETE | /produtos/{produto} | destroy | Excluir |

Todas ficam dentro do grupo `auth`: sem login, o Laravel manda para a tela de entrar.

Para mostrar na aula: `php artisan route:list`

## 6. Controller

- **index**: `Produto::with([...])->withCount('itensVenda')->busca(...)`. O `with` carrega categoria, fornecedor e as últimas movimentações em poucas consultas; o `withCount` já traz quantas vendas cada produto teve (para saber se pode excluir); a lista é paginada.
- **create**: busca categorias e fornecedores para montar os selects.
- **store**: salva o produto e registra o estoque inicial como movimentação, tudo numa transação.
- **edit / update**: `Produto $produto` é o *route model binding*: o Laravel busca o produto pelo id da URL (404 se não existir). O estoque **não** é alterado na edição.
- **destroy**: pergunta ao model `motivoParaNaoExcluir()`. Se o produto já foi vendido, não exclui e mostra o motivo.

```php
public function store(ProdutoRequest $request, EstoqueService $estoque)
{
    DB::transaction(function () use ($request, $estoque) {
        $dados = $request->validated();
        $estoqueInicial = (int) $dados['estoque'];

        // O produto nasce com estoque 0 e o saldo entra como movimentação
        $produto = Produto::create(array_merge($dados, ['estoque' => 0]));

        if ($estoqueInicial > 0) {
            $estoque->entrada($produto, $estoqueInicial, 'Estoque inicial');
        }
    });

    return redirect()->route('produtos.index')->with('success', 'Produto cadastrado com sucesso!');
}
```

### Validação: Form Request

A validação fica numa classe própria, `app/Http/Requests/ProdutoRequest.php` (criada com `php artisan make:request ProdutoRequest`). Basta colocar `ProdutoRequest $request` no método: o Laravel valida **antes** de o método rodar.

```php
public function rules(): array
{
    $regras = [
        'nome' => 'required|string|max:255',
        'descricao' => 'nullable|string',
        'preco' => 'required|numeric|min:0',
        'custo' => 'nullable|numeric|min:0|max:99999999.99', // opcional: vazio = não informado
        'estoque_minimo' => 'required|integer|min:0',
        'categoria_id' => 'required|exists:categorias,id',
        'fornecedor_id' => 'nullable|exists:fornecedores,id',
    ];

    // Estoque só no cadastro; depois muda por vendas e movimentações
    if ($this->isMethod('post')) {
        $regras['estoque'] = 'required|integer|min:0';
    }

    return $regras;
}
```

Se alguma regra falhar, o Laravel **volta sozinho para o formulário** com os erros (em português, vindos de `lang/pt_BR/validation.php`) e o que foi digitado. Nada é salvo.

## 7. Views Blade

| Diretiva | Para que serve |
| --- | --- |
| `@extends('layouts.app')` | Usa o layout (menu lateral, busca Ctrl+K, alertas) |
| `@include('partials.cabeca-form', [...])` | Trilha "Produtos › Novo produto", título e dica da tela |
| `@forelse / @empty` | Loop que mostra a tela vazia se a lista não tiver nada |
| `{{ }}` | Imprime o valor protegido contra XSS |
| `@csrf` | Token que prova que o formulário veio do nosso site (sem ele: erro 419) |
| `@method('PUT')` / `@method('DELETE')` | HTML só envia GET/POST; isso diz ao Laravel o método real |
| `@error('campo')` | Mostra o erro daquele campo (e o leitor de tela anuncia) |
| `old('campo', $produto->campo)` | Mantém o que foi digitado; senão, usa o valor do banco |
| `@selected(...)` | Deixa marcada a opção certa no select |

Os campos ficam em **`produtos/_form.blade.php`**, incluído no `create` e no `edit` com `@include('produtos._form')`. Assim o formulário é escrito uma vez só. A diferença entre as duas telas é o `action` (store ou update) e o `@method('PUT')`.

Os campos que costumam confundir (estoque mínimo e custo) têm um botão **"?"** com a explicação (`partials/ajuda-campo.blade.php`).

## 8. Roteiro da apresentação (~10 min)

**Antes da aula:** Laragon ligado, banco com os dados de exemplo (`php artisan migrate:fresh --seed` ou importar `database/banco.sql`) e `php artisan serve` rodando. Login: admin@minierp.com / admin123.

1. **Mostrar funcionando (4 min)**
    - Abra http://127.0.0.1:8000: sem login aparece a página de apresentação. Clique em "Entrar" e faça login.
    - Mostre o Dashboard: faturamento, lucro bruto com a margem, gráfico e "Estoque baixo".
    - Clique em "+ Novo produto" e salve vazio: aparecem os erros de validação.
    - Cadastre um produto com preço 10,00, custo 6,00, estoque 3 e mínimo 5: alerta verde e preço em R$ na lista.
    - Volte ao Dashboard: o produto aparece em "Estoque baixo".
    - Mostre a busca, o filtro "Estoque baixo" e a troca Tabela | Vitrine.
    - Edite o produto (troque o fornecedor para "Nenhum"): o estoque não é editável ali.
    - Faça uma venda desse produto pagando em Dinheiro, com desconto e troco; mostre que o estoque baixou.
    - Abra o "Caixa do dia": a venda aparece somada em Dinheiro.
    - Tente excluir o produto vendido: a janela explica por que não pode. Exclua um produto que nunca foi vendido.
2. **Banco (1 min)**: migration de criação, chaves estrangeiras e a migration que acrescentou o custo; se der, a tabela no Workbench.
3. **Model (1 min)**: `$fillable`, `$casts` e os `belongsTo`.
4. **Rotas (1 min)**: `Route::resource` e `php artisan route:list`.
5. **Controller (2 min)**: siga um cadastro: `create` → formulário → `ProdutoRequest` (validação) → `store` (transação) → `redirect`.
6. **Views (1 min)**: `_form` com `@include`, `@forelse`, `@csrf`, `@method('PUT')`, `@error`, `old()`.

Para fechar: *"Os outros três cadastros (Categorias, Fornecedores e Clientes) seguem exatamente essa estrutura. Produtos só acrescenta os relacionamentos, o custo e a ligação com estoque e vendas."*

## 9. Perguntas prováveis

| Pergunta | Resposta curta |
| --- | --- |
| O que é MVC? | Model (dados), View (tela) e Controller (lógica que liga os dois). |
| Para que serve o `@csrf`? | Impede que um site falso envie formulários em nome do usuário. |
| Por que `@method('PUT')`? | HTML só envia GET e POST; o Laravel lê esse campo e trata como PUT/DELETE. |
| O que é `$fillable`? | Lista de campos liberados para salvar em massa. |
| Onde está o SQL? | O Eloquent gera: `create()` vira INSERT, `delete()` vira DELETE. |
| E se a validação falhar? | Volta ao formulário com erros e dados digitados; nada é salvo. |
| E se excluir uma categoria com produtos? | A tela explica antes e o controller bloqueia; o banco também bloquearia (`restrictOnDelete`). |
| E se excluir um fornecedor? | A tela avisa quantos produtos ficam sem fornecedor (`nullOnDelete`). |
| O que é migration? | Arquivo que cria ou altera as tabelas; `php artisan migrate` aplica, `migrate:rollback` desfaz. |
| O que é Form Request? | Uma classe só para validação. O controller fica limpo e a regra é reaproveitada no store e no update. |
| O que é transação (`DB::transaction`)? | Um bloco "tudo ou nada": se algo falhar no meio (ex.: estoque insuficiente), o banco desfaz tudo. |
| Por que o estoque não é editável? | Para ter histórico: toda mudança vira uma movimentação (entrada/saída) com motivo e usuário. |
| Por que venda não se apaga? | Venda é documento. Se deu errado, ela é cancelada e o estoque volta, mas o registro fica. |
| Por que o custo fica guardado no item da venda? | Pelo mesmo motivo do preço: se o custo mudar depois, o lucro das vendas antigas continua certo. |
| Por que custo vazio e não zero? | Zero seria inventar um custo e mostrar um lucro falso. Vendas sem custo ficam fora do lucro, com aviso. |
| Como o desconto é calculado? | No servidor (`VendaService`), em centavos, para não ter erro de arredondamento. Desconto maior que o subtotal é recusado. |
| O que é o "Consumidor final"? | Um cliente especial, criado pela migration, para vender sem identificar quem comprou. Ele não pode ser editado nem excluído. |
| Como funciona a devolução? | Parte de uma venda: escolhe os itens, o motivo e o destino. Só "volta para o estoque" soma no estoque; perda e fornecedor não. O valor sai com o desconto da venda e é descontado do faturamento. |
| Por que venda com devolução não pode ser cancelada? | O cancelamento devolveria ao estoque também o que já voltou na devolução. Para devolver o resto, registra-se outra devolução. |
| O que é middleware? | Um filtro que roda antes do controller. `auth` exige login; `aprovado` (nosso, em `app/Http/Middleware`) barra quem ainda não foi aprovado. |
| Alguém pode se aprovar sozinho no cadastro? | Não: `aprovado_em` não está no `$fillable` do User, então o campo enviado no formulário é ignorado. |
| Como foi testado? | `php artisan test` roda os testes de todos os módulos; no GitHub rodam sozinhos a cada push. |
