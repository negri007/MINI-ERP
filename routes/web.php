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
| 5.1) Popular o banco com dados de exemplo (usuário admin, produtos, vendas...):
|      php artisan db:seed
|    Ou apagar tudo, recriar as tabelas e popular de uma vez:
|      php artisan migrate:fresh --seed
|    Login criado pelo seeder:  admin@minierp.com  /  senha: admin123
|
| 6) Criar os controllers:
|      php artisan make:controller DashboardController
|      php artisan make:controller CategoriaController --resource
|      php artisan make:controller FornecedorController --resource
|      php artisan make:controller ClienteController --resource
|      php artisan make:controller ProdutoController --resource
|      php artisan make:controller VendaController
|      php artisan make:controller MovimentacaoEstoqueController
|      php artisan make:controller RelatorioController
|      php artisan make:controller Auth/LoginController
|
| 6.1) Criar as validações (Form Requests) e a regra de CPF/CNPJ:
|      php artisan make:request ProdutoRequest   (e os demais *Request)
|      php artisan make:rule CpfCnpj
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
|      php artisan migrate --seed
|      php artisan serve
|
*/

use App\Http\Controllers\ApresentacaoController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CaixaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\FornecedorController;
use App\Http\Controllers\MovimentacaoEstoqueController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\VendaController;
use Illuminate\Support\Facades\Route;

// Página inicial: visitante vê a apresentação; quem entrou vê o Dashboard (por isso o nome "dashboard")
Route::get('/', [ApresentacaoController::class, 'inicio'])->name('dashboard');
// Apresentação do sistema, com ou sem login (link "Sobre o Mini ERP" no menu)
Route::get('/sobre', [ApresentacaoController::class, 'sobre'])->name('sobre');

// Rotas para quem NÃO está logado (tela de login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    // throttle:5,1 = no máximo 5 tentativas por minuto (protege contra "chute" de senha)
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');
});

// Rotas que exigem login (middleware "auth" manda para /login quem não entrou)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');


    // CRUDs de cadastro (sem a rota "show", que não é usada)
    // Cadastro rápido de categoria usado pelo formulário de produto
    Route::post('/categorias/rapida', [CategoriaController::class, 'rapida'])->name('categorias.rapida');
    Route::resource('categorias', CategoriaController::class)->except('show');

    Route::resource('fornecedores', FornecedorController::class)
        ->except('show')
        ->parameters(['fornecedores' => 'fornecedor']);

    // Busca e cadastro rápido de clientes usados pela Nova venda (antes do resource)
    Route::get('/clientes/buscar', [ClienteController::class, 'buscar'])->name('clientes.buscar');
    Route::post('/clientes/rapido', [ClienteController::class, 'rapido'])->name('clientes.rapido');
    Route::resource('clientes', ClienteController::class)->except('show');

    Route::resource('produtos', ProdutoController::class)->except('show');

    // Vendas: registrar e consultar (não se edita nem se apaga venda, só se cancela)
    Route::resource('vendas', VendaController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('/vendas/{venda}/cancelar', [VendaController::class, 'cancelar'])->name('vendas.cancelar');

    // Estoque: histórico e entradas/saídas manuais
    // Caixa do dia: só consulta (totais por forma de pagamento de um dia)
    Route::get('/caixa', [CaixaController::class, 'index'])->name('caixa.index');

    Route::get('/estoque', [MovimentacaoEstoqueController::class, 'index'])->name('estoque.index');
    Route::get('/estoque/movimentar', [MovimentacaoEstoqueController::class, 'create'])->name('estoque.create');
    Route::post('/estoque', [MovimentacaoEstoqueController::class, 'store'])->name('estoque.store');

    // Relatórios
    Route::get('/relatorios/vendas', [RelatorioController::class, 'vendas'])->name('relatorios.vendas');
    Route::get('/relatorios/vendas/exportar', [RelatorioController::class, 'exportarVendas'])->name('relatorios.vendas.exportar');
});
