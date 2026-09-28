# Mini ERP

Mini ERP em Laravel + Blade + Bootstrap 5, com MySQL (Laragon).

**Módulos**
- **Login**: só usuários autenticados acessam o sistema.
- **Cadastros**: Categorias, Fornecedores, Clientes (CPF/CNPJ validado e único) e Produtos (com estoque mínimo).
- **Vendas**: vários produtos por venda, total calculado, baixa automática de estoque e cancelamento que devolve o estoque.
- **Estoque**: histórico de entradas e saídas e movimentação manual (compra, perda, ajuste).
- **Relatórios**: vendas por período, produtos mais vendidos e exportação para Excel (CSV).
- **Dashboard**: faturamento do mês, gráficos e alerta de estoque baixo.

Funciona sem internet: Bootstrap e Chart.js estão em `public/vendor`.

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

## Testes

```bash
php artisan test
```
Os testes também rodam sozinhos no GitHub a cada push (`.github/workflows/tests.yml`).
