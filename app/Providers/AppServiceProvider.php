<?php

namespace App\Providers;

use App\Models\Produto;
use App\Models\Venda;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Links de paginação ({{ $lista->links() }}) com o visual do Bootstrap 5
        Paginator::useBootstrapFive();

        // Toda vez que o layout é desenhado, envia os números que aparecem no menu:
        // - produtos para repor (contador vermelho no item "Produtos")
        // - vendas concluídas hoje (etiqueta verde na seção "Operações")
        View::composer('layouts.app', function ($view) {
            $view->with('qtdEstoqueBaixo', Produto::estoqueBaixo()->count());
            $view->with('vendasHoje', Venda::where('status', Venda::CONCLUIDA)->whereDate('data', today()->toDateString())->count());
        });
    }
}
