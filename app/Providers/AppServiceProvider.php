<?php

namespace App\Providers;

use App\Models\Produto;
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

        // Toda vez que o layout é desenhado, envia a quantidade de produtos para repor
        // (aparece como contador vermelho no item "Produtos" do menu)
        View::composer('layouts.app', function ($view) {
            $view->with('qtdEstoqueBaixo', Produto::estoqueBaixo()->count());
        });
    }
}
