# Mini ERP

Mini ERP em Laravel + Blade + Bootstrap 5, com MySQL (Laragon).

**Módulos**
- **Login**: só usuários autenticados acessam o sistema.
- **Cadastros**: Categorias, Fornecedores, Clientes (CPF/CNPJ validado e único) e Produtos (com estoque mínimo).
- **Vendas**: vários produtos por venda, total calculado, baixa automática de estoque e cancelamento que devolve o estoque.
  O campo Cliente busca por nome ou CPF/CNPJ enquanto você digita, e o botão **+ Novo cliente** cadastra sem sair da venda.
  Para vender sem identificar o cliente, use **Consumidor final** (cliente especial criado pela migration, que não pode ser editado nem excluído).
- **Estoque**: histórico de entradas e saídas e movimentação manual (compra, perda, ajuste).
- **Relatórios**: vendas por período, produtos mais vendidos e exportação para Excel (CSV).
- **Dashboard**: faturamento do mês, gráficos e alerta de estoque baixo.

Funciona sem internet: Bootstrap, Chart.js e as fontes estão em `public/vendor`.

## Tema "Mata"

O visual (`public/css/tema.css`) é escuro, com as cores do Brasil: verde-mata, amarelo-sol e azul-céu.
- **Amarelo-sol** marca as ações principais e o item ativo do menu.
- **Menu lateral** com ícones e contador de produtos para repor; no celular ele abre pelo botão ☰.
- **Dashboard**: faturamento do mês comparado ao mês anterior, vendas, ticket médio, gráfico dos últimos 14 dias, estoque baixo e últimas vendas.
- **Medidor de estoque**: barra de nível com a marca do mínimo e a situação escrita (OK, Baixo, Esgotado).
- **Busca rápida**: aperte **Ctrl + K** (ou **/**) e digite para ir a qualquer tela, criar algo ou buscar produtos, clientes e vendas.
- Cores dos gráficos e medidores validadas para contraste e daltonismo (skill dataviz).
- **Um amarelo por tela**: o amarelo-sol cheio fica só na ação principal; filtros, Tabela/Vitrine e a coluna ordenada escolhidos aparecem em verde-mata.
- **Formulários** com trilha (ex.: Produtos › Novo produto), largura de leitura e rodapé com Cancelar e a ação principal.
- **Ajuda na própria tela**: cada tela tem uma dica curta abaixo do título (o botão "Entendi" esconde), os campos que confundem
  têm um "?" com explicação, e as listas vazias dizem qual é o próximo passo.
- **Acessibilidade**: anel amarelo no foco do teclado (Tab), contrastes de texto no padrão WCAG AA (mínimo 4,5:1)
  e animações desligadas para quem ativou "reduzir movimento" no sistema.

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

Já tinha o projeto rodando? Depois do `git pull`, rode `php artisan migrate` para criar as tabelas novas,
ou `php artisan migrate:fresh --seed` para apagar tudo e recomeçar com os dados de exemplo.

## Estrutura

| Pasta | O que tem |
|---|---|
| `app/Models` | Categoria, Fornecedor, Cliente, Produto, Venda, VendaItem, MovimentacaoEstoque |
| `app/Http/Controllers` | Um controller por módulo + `Auth/LoginController` |
| `app/Http/Requests` | Validação de cada formulário (Form Requests) |
| `app/Services` | Regras de negócio: `VendaService` (vender/cancelar) e `EstoqueService` (entradas/saídas) |
| `app/Rules/CpfCnpj.php` | Regra que confere os dígitos do CPF/CNPJ |
| `resources/views` | Telas Blade (`layouts/app.blade.php` é o layout) |
| `database/migrations` | Criação das tabelas |
| `database/seeders` | Dados de exemplo |
| `lang/pt_BR` | Mensagens de validação em português |
| `docs/APRESENTACAO.md` | Roteiro para apresentar o CRUD de Produtos |

## Melhorias futuras

- **Venda a prazo (fiado)**: quando existir, não poderá usar o Consumidor final, porque a dívida precisa de um cliente identificado.
- **Produto "inativo"**: hoje um produto já vendido não pode ser excluído (o histórico precisa dele). Um campo "inativo"
  tiraria o produto da Nova venda sem apagar o histórico. Exige mudar o banco.

## Testes

```bash
php artisan test
```
Os testes também rodam sozinhos no GitHub a cada push (`.github/workflows/tests.yml`).
