# Mini ERP

Mini ERP em Laravel + Blade + Bootstrap 5 (CDN), com cadastros de **Categorias**, **Fornecedores**, **Clientes** e **Produtos**, além de um Dashboard com totais e produtos com estoque baixo.

## Como rodar (Laragon / MySQL)

1. Crie o banco `mini_erp` no MySQL do Laragon.
2. Na pasta do projeto:
   ```bash
   composer install
   copy .env.example .env      # no Linux/macOS: cp .env.example .env
   php artisan key:generate
   php artisan migrate
   php artisan serve
   ```
3. Acesse http://127.0.0.1:8000

## Estrutura

| Módulo       | Controller                 | Views                          |
|--------------|----------------------------|--------------------------------|
| Dashboard    | `DashboardController`      | `resources/views/dashboard`    |
| Categorias   | `CategoriaController`      | `resources/views/categorias/`  |
| Fornecedores | `FornecedorController`     | `resources/views/fornecedores/`|
| Clientes     | `ClienteController`        | `resources/views/clientes/`    |
| Produtos     | `ProdutoController`        | `resources/views/produtos/`    |

Layout principal: `resources/views/layouts/app.blade.php`.

## Testes

```bash
php artisan test
```
