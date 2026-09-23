# Mini ERP — Como explicar o CRUD de Produtos

O CRUD escolhido para a apresentação é o de **Produtos**, porque ele usa tudo o que os outros usam e ainda liga três tabelas entre si.

## 1. Visão geral

| Letra | Operação | No sistema | Método HTTP |
| --- | --- | --- | --- |
| C | Create (criar) | Botão "Novo Produto" → formulário → Salvar | GET /produtos/create + POST /produtos |
| R | Read (ler) | Tabela de produtos | GET /produtos |
| U | Update (atualizar) | Botão "Editar" → formulário preenchido → Atualizar | GET /produtos/{id}/edit + PUT /produtos/{id} |
| D | Delete (excluir) | Botão "Excluir" com confirmação | DELETE /produtos/{id} |

O que Produtos tem a mais que os outros cadastros:

- **Relacionamentos**: cada produto pertence a uma categoria (obrigatória) e a um fornecedor (opcional).
- **Campos de tipos diferentes**: texto, decimal (preço), inteiro (estoque) e listas de seleção (select).
- **Validação mais rica**: números mínimos e checagem de que a categoria e o fornecedor existem no banco.
- **Formatação**: o preço aparece como moeda brasileira (R$ 7,50).
- **Integração com o Dashboard**: produtos com estoque até 5 aparecem na página inicial.

## 2. Fluxo MVC

```
Navegador → Rota (routes/web.php) → Controller → Model → MySQL
                                        ↓
Navegador ←──────── View (Blade) ←──────┘
```

Exemplo: ao clicar em "Produtos", o navegador pede `GET /produtos`. A rota chama `ProdutoController@index`, que busca os produtos pelo model `Produto` e entrega a lista para a view `produtos/index.blade.php`.

| Camada | Arquivo | Responsabilidade |
| --- | --- | --- |
| Model | `app/Models/Produto.php` | Representa a tabela e os relacionamentos |
| View | `resources/views/produtos/` | O que o usuário vê (HTML + Blade) |
| Controller | `app/Http/Controllers/ProdutoController.php` | Recebe o pedido, valida, salva, redireciona |
| Rotas | `routes/web.php` | Liga cada URL a um método do controller |
| Banco | `database/migrations/..._create_produtos_table.php` | Cria a tabela no MySQL |

## 3. Banco de dados: a migration

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
- **Ordem**: a migration de produtos roda por último, porque depende das outras tabelas.

## 4. Model Produto

```php
protected $fillable = ['nome', 'descricao', 'preco', 'estoque', 'categoria_id', 'fornecedor_id'];

public function categoria(): BelongsTo
{
    return $this->belongsTo(Categoria::class);
}
```

- **`$fillable`**: campos liberados para `Produto::create($dados)`. Um campo fora da lista é ignorado (proteção).
- **`$casts`**: preço sempre com 2 casas, estoque sempre inteiro.
- **`belongsTo`**: permite escrever `$produto->categoria->nome` na view.
- **O outro lado**: `Categoria` e `Fornecedor` têm `hasMany(Produto::class)`.

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

Para mostrar na aula: `php artisan route:list`

## 6. Controller

- **index**: `Produto::with(['categoria', 'fornecedor'])->get()`. O `with` carrega tudo numa consulta só.
- **create**: busca categorias e fornecedores para montar os selects.
- **store**: valida, grava (`Produto::create`) e redireciona com `->with('success', ...)`.
- **edit / update**: `Produto $produto` é o *route model binding*: o Laravel busca o produto pelo id da URL (404 se não existir).
- **destroy**: `$produto->delete()`.

```php
private function validar(Request $request): array
{
    return $request->validate([
        'nome' => 'required|string|max:255',
        'descricao' => 'nullable|string',
        'preco' => 'required|numeric|min:0',
        'estoque' => 'required|integer|min:0',
        'categoria_id' => 'required|exists:categorias,id',
        'fornecedor_id' => 'nullable|exists:fornecedores,id',
    ]);
}
```

Se alguma regra falhar, o Laravel **volta sozinho para o formulário** com os erros e o que foi digitado. Nada é salvo.

## 7. Views Blade

| Diretiva | Para que serve |
| --- | --- |
| `@extends('layouts.app')` | Usa o layout (navbar, sidebar, alertas) |
| `@forelse / @empty` | Loop que mostra uma mensagem se a lista estiver vazia |
| `{{ }}` | Imprime o valor protegido contra XSS |
| `@csrf` | Token que prova que o formulário veio do nosso site (sem ele: erro 419) |
| `@method('PUT')` / `@method('DELETE')` | HTML só envia GET/POST; isso diz ao Laravel o método real |
| `@error('campo')` | Mostra o erro daquele campo |
| `old('campo', $produto->campo)` | Mantém o que foi digitado; senão, usa o valor do banco |
| `@selected(...)` | Deixa marcada a opção certa no select |

A única diferença entre `create` e `edit` é o `action` (store ou update), o `@method('PUT')` e o valor padrão do `old()`.

## 8. Roteiro da apresentação (~10 min)

**Antes da aula:** Laragon ligado, `php artisan serve` rodando, 2 categorias e 1 fornecedor cadastrados.

1. **Mostrar funcionando (3 min)**
    - Abra http://127.0.0.1:8000 e mostre o Dashboard e a sidebar.
    - Clique "Novo Produto" e salve vazio: aparecem os erros de validação.
    - Cadastre um produto com estoque 3: alerta verde e preço em R$ na tabela.
    - Volte ao Dashboard: o produto aparece em "estoque baixo".
    - Edite (troque o fornecedor para "Nenhum") e depois exclua.
    - Tente excluir uma categoria com produto: aparece a mensagem de erro.
2. **Banco (1 min)**: migration e chaves estrangeiras; se der, a tabela no HeidiSQL.
3. **Model (1 min)**: `$fillable` e os `belongsTo`.
4. **Rotas (1 min)**: `Route::resource` e `php artisan route:list`.
5. **Controller (3 min)**: siga um cadastro: `create` → formulário → `store` → `validar` → `redirect`.
6. **Views (1 min)**: `@forelse`, `@csrf`, `@method('PUT')`, `@error`, `old()`.

Para fechar: *"Os outros três cadastros seguem exatamente essa estrutura. Produtos só acrescenta os relacionamentos."*

## 9. Perguntas prováveis

| Pergunta | Resposta curta |
| --- | --- |
| O que é MVC? | Model (dados), View (tela) e Controller (lógica que liga os dois). |
| Para que serve o `@csrf`? | Impede que um site falso envie formulários em nome do usuário. |
| Por que `@method('PUT')`? | HTML só envia GET e POST; o Laravel lê esse campo e trata como PUT/DELETE. |
| O que é `$fillable`? | Lista de campos liberados para salvar em massa. |
| Onde está o SQL? | O Eloquent gera: `create()` vira INSERT, `delete()` vira DELETE. |
| E se a validação falhar? | Volta ao formulário com erros e dados digitados; nada é salvo. |
| E se excluir uma categoria com produtos? | O controller bloqueia com mensagem; o banco também bloquearia. |
| E se excluir um fornecedor? | Os produtos ficam sem fornecedor (`nullOnDelete`). |
| O que é migration? | Arquivo que cria as tabelas; `php artisan migrate` recria o banco. |
| Como foi testado? | `php artisan test` roda os testes de todos os CRUDs. |
