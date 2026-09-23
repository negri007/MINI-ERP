<?php

/*
|--------------------------------------------------------------------------
| PASSO A PASSO DO PROJETO (comandos usados, na ordem)
|--------------------------------------------------------------------------
|
| 1) Criar o projeto Laravel (dentro de C:\laragon\www):
|      composer create-project laravel/laravel mini-erp
|      cd mini-erp
|
| 2) Configurar o banco no arquivo .env (MySQL do Laragon):
|      DB_CONNECTION=mysql
|      DB_HOST=127.0.0.1
|      DB_PORT=3306
|      DB_DATABASE=mini_erp
|      DB_USERNAME=root
|      DB_PASSWORD=
|    E criar o banco "mini_erp" no HeidiSQL (botão Database do Laragon).
|
| 3) Criar os models:
|      php artisan make:model Categoria
|      php artisan make:model Fornecedor
|      php artisan make:model Cliente
|      php artisan make:model Produto
|
| 4) Criar as migrations (produtos por último, pois depende das outras):
|      php artisan make:migration create_categorias_table
|      php artisan make:migration create_fornecedores_table
|      php artisan make:migration create_clientes_table
|      php artisan make:migration create_produtos_table
|
| 5) Criar as tabelas no banco:
|      php artisan migrate
|
| 6) Criar os controllers:
|      php artisan make:controller DashboardController
|      php artisan make:controller CategoriaController --resource
|      php artisan make:controller FornecedorController --resource
|      php artisan make:controller ClienteController --resource
|      php artisan make:controller ProdutoController --resource
|
| 7) Registrar as rotas (este arquivo) e conferir:
|      php artisan route:list
|
| 8) Criar as views em resources/views (layouts, categorias, fornecedores,
|    clientes, produtos e dashboard.blade.php).
|
| 9) Rodar o servidor e abrir http://127.0.0.1:8000
|      php artisan serve
|
| 10) Rodar os testes automáticos:
|      php artisan test
|
| Se baixar este projeto pronto do GitHub, basta:
|      composer install
|      copy .env.example .env
|      php artisan key:generate
|      php artisan migrate
|      php artisan serve
|
*/

use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FornecedorController;
use App\Http\Controllers\ProdutoController;
use Illuminate\Support\Facades\Route;

// Página inicial (Dashboard)
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// CRUDs do sistema (sem a rota "show", que não é usada)
Route::resource('categorias', CategoriaController::class)->except('show');

Route::resource('fornecedores', FornecedorController::class)
    ->except('show')
    ->parameters(['fornecedores' => 'fornecedor']);

Route::resource('clientes', ClienteController::class)->except('show');

Route::resource('produtos', ProdutoController::class)->except('show');
