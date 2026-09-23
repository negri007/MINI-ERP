<?php

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
