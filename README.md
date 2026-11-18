# Mini ERP

Mini ERP em Laravel + Blade + Bootstrap 5, com MySQL (Laragon).

**Módulos**
- **Apresentação**: quem abre o endereço sem estar logado vê uma página explicando o sistema (também em `/sobre`).
- **Login e Criar conta**: só usuários autenticados acessam as telas. Quem cria conta na página inicial
  só entra depois que um usuário do sistema aprova o pedido na tela **Usuários** (o menu mostra quantos aguardam).
- **Cadastros**: Categorias, Fornecedores, Clientes (CPF/CNPJ validado e único) e Produtos (com estoque mínimo).
- **Vendas**: vários produtos por venda, total calculado, baixa automática de estoque e cancelamento que devolve o estoque.
  O campo Cliente busca por nome ou CPF/CNPJ enquanto você digita, e o botão **+ Novo cliente** cadastra sem sair da venda.
  Para vender sem identificar o cliente, use **Consumidor final** (cliente especial criado pela migration, que não pode ser editado nem excluído).
  Cada venda tem **forma de pagamento** (Dinheiro, Pix, Cartão de débito ou de crédito) e **desconto em R$**; em Dinheiro,
  a tela calcula o troco. Os valores são recalculados no servidor.
- **Caixa do dia**: total por forma de pagamento, número de vendas, descontos e canceladas à parte, para conferir a gaveta. Pronto para imprimir.
- **Custo e lucro**: cada produto pode ter custo; a venda guarda o custo do momento. Dashboard e relatório mostram lucro bruto
  e margem, só com vendas que têm custo em todos os itens (as outras são contadas num aviso, sem valor inventado).
- **Devoluções**: a partir de uma venda, escolhe os produtos e quantos voltaram, o **motivo** e o **destino**:
  volta para o estoque, perda da loja ou devolver ao fornecedor (só o primeiro soma no estoque). O valor devolvido
  já sai com o desconto da venda, é descontado do faturamento e do lucro e aparece no Caixa do dia.
  A tela Devoluções lista tudo que voltou, com filtro por destino.
- **Estoque**: histórico de entradas e saídas com a **origem** de cada uma (venda, cancelamento, devolução,
  cadastro do produto ou ajuste manual com o motivo digitado) e movimentação manual (compra, perda, ajuste).
- **Relatórios**: vendas por período, produtos mais vendidos e exportação para Excel (CSV).
- **Dashboard**: faturamento do mês, gráficos e alerta de estoque baixo.

Funciona sem internet: Bootstrap, Chart.js e as fontes estão em `public/vendor`.

## Tema "Mata"

O visual (`public/css/tema.css`) é escuro, com as cores do Brasil: verde-mata, amarelo-sol e azul-céu.
- **Amarelo-sol** marca as ações principais e o item ativo do menu.
- **Menu lateral** com ícones e contador de produtos para repor; no celular ele abre pelo botão ☰.
- **Dashboard**: faturamento do mês comparado ao mês anterior, vendas, ticket médio, gráfico dos últimos 14 dias, estoque baixo e últimas vendas.
- **Medidor de estoque**: barra de nível com a marca do mínimo e a situação escrita (OK, Baixo, Esgotado).
- **Busca rápida**: aperte **Ctrl + K** e digite para ir a qualquer tela, criar algo ou buscar produtos, clientes e vendas.
- Cores dos gráficos e medidores validadas para contraste e daltonismo (skill dataviz).
- **Um amarelo por tela**: o amarelo-sol cheio fica só na ação principal; filtros, Tabela/Vitrine e a coluna ordenada escolhidos aparecem em verde-mata.
- **Formulários** com trilha (ex.: Produtos › Novo produto), campos lado a lado que ocupam a largura da tela
  (no celular ficam um embaixo do outro) e rodapé com Cancelar e a ação principal.
- **Página inicial** cabe inteira na tela do computador, sem rolar, com os botões Entrar e Criar conta.
- **Ajuda na própria tela**: cada tela tem uma dica curta abaixo do título (o botão "Entendi" esconde), os campos que confundem
  têm um "?" com explicação, e as listas vazias dizem qual é o próximo passo.
- **Acessibilidade**: anel amarelo no foco do teclado (Tab), contrastes de texto no padrão WCAG AA (mínimo 4,5:1)
  e animações desligadas para quem ativou "reduzir movimento" no sistema.
  Revisado contra a WCAG 2.2 AA:
  - link "Pular para o conteúdo" no primeiro Tab e menu do celular que abre/fecha pelo teclado (Esc fecha);
  - bordas dos campos com contraste 3:1, erros e campos obrigatórios anunciados pelo leitor de tela;
  - linhas que abrem têm um botão (›) próprio; busca, filtros e ordenação avisam o que mudou;
  - a busca rápida (Ctrl + K) funciona só pelo teclado e prende o foco enquanto está aberta.

Fontes: Outfit e Inter (licença SIL Open Font, em `public/vendor/fonts`).

## Como rodar (Laragon / MySQL)

1. Crie o banco `mini_erp` no MySQL (HeidiSQL ou Workbench).
2. Na pasta do projeto:
   ```bash
   composer install
   copy .env.example .env      # no Linux/macOS: cp .env.example .env
   php artisan key:generate
   php artisan migrate --seed  # cria as tabelas e os dados de exemplo
   php artisan serve
   ```
3. Acesse http://127.0.0.1:8000 e entre com **admin@minierp.com / admin123**.

**Outro jeito: importar o banco pronto.** O arquivo `database/banco.sql` já tem todas as tabelas e os dados de exemplo
(e cria o banco `mini_erp` sozinho). Em vez do `php artisan migrate --seed`, importe esse arquivo:
```bash
mysql -u root < database/banco.sql
```
ou, no MySQL Workbench, em **Server → Data Import → Import from Self-Contained File**.
O login é o mesmo: **admin@minierp.com / admin123**.

O `banco.sql` foi gerado antes das devoluções e do cadastro com aprovação: depois de importar, rode
`php artisan migrate` para criar o que falta.

Já tinha o projeto rodando? Depois do `git pull`, rode `php artisan migrate` para criar as tabelas novas,
ou `php artisan migrate:fresh --seed` para apagar tudo e recomeçar com os dados de exemplo.

## Estrutura

| Pasta | O que tem |
|---|---|
| `routes/web.php` | Todas as rotas (endereços), com os grupos `guest` e `auth` + `aprovado` |
| `app/Models` | Categoria, Fornecedor, Cliente, Produto, Venda, VendaItem, MovimentacaoEstoque, Devolucao, DevolucaoItem, User |
| `app/Http/Controllers` | Um controller por módulo + `Auth/LoginController` e `Auth/CadastroController` |
| `app/Http/Requests` | Validação de cada formulário (Form Requests) |
| `app/Http/Middleware` | `UsuarioAprovado`: barra quem tem cadastro aguardando aprovação |
| `app/Services` | Regras de negócio: `VendaService` (vender/cancelar), `EstoqueService` (entradas/saídas), `DevolucaoService` (devoluções) e `LucroService` (lucro bruto) |
| `app/Rules/CpfCnpj.php` | Regra que confere os dígitos do CPF/CNPJ |
| `resources/views` | Telas Blade (`layouts/app.blade.php` é o layout) |
| `database/migrations` | Criação e alteração das tabelas |
| `database/seeders` | Dados de exemplo |
| `database/banco.sql` | Banco pronto (tabelas + dados de exemplo) para importar no MySQL |
| `lang/pt_BR` | Mensagens de validação em português |
| `public/css` | Visual: `tema.css` (sistema), `apresentacao.css` (página inicial), `entrada.css` (Entrar/Criar conta) |
| `docs/APRESENTACAO.md` | Roteiro para apresentar o CRUD de Produtos |

**Como o MVC aparece:** a rota (`routes/web.php`) chama o método do controller; o controller valida com o
Form Request, usa os models (e, nas operações com várias tabelas, um Service dentro de uma transação) e devolve
uma view Blade. Os arquivos `vite.config.js`, `package.json`, `resources/css/app.css` e `resources/js/app.js` vieram
com o Laravel e **não são usados**: o visual é Bootstrap salvo em `public/vendor`, sem precisar de Node.

## Melhorias futuras

- **Perfis de usuário (dono e caixa)**: hoje todo usuário aprovado acessa tudo (inclusive aprovar outros). A ideia é o dono ver tudo e o caixa só registrar
  e consultar vendas. Exige um campo de perfil nos usuários e checagem nas rotas.

- **Venda a prazo (fiado)**: fica para a fase do financeiro. Entra como nova forma de pagamento (constante
  `Venda::FORMAS_PAGAMENTO` e regra CHECK do banco, numa migration própria, com vencimento e situação do pagamento)
  e não poderá usar o Consumidor final, porque a dívida precisa de um cliente identificado.
- **Produto "inativo"**: hoje um produto já vendido não pode ser excluído (o histórico precisa dele). Um campo "inativo"
  tiraria o produto da Nova venda sem apagar o histórico. Exige mudar o banco.

## Testes

```bash
php artisan test
```
Os testes também rodam sozinhos no GitHub a cada push (`.github/workflows/tests.yml`).
