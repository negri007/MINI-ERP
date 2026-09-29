<?php

// Arquivo criado com o comando:
//   php artisan make:controller ApresentacaoController

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Support\Facades\Auth;

// Página de apresentação do Mini ERP (pública)
class ApresentacaoController extends Controller
{
    // Endereço "/": visitante vê a apresentação; quem já entrou vê o Dashboard
    public function inicio()
    {
        return Auth::check() ? app(DashboardController::class)->index() : $this->sobre();
    }

    // Endereço "/sobre": a apresentação, com ou sem login
    public function sobre()
    {
        // Produtos de exemplo para a amostra da tela "Estoque baixo".
        // Não são gravados no banco: servem só para desenhar o medidor de verdade.
        $exemplos = collect([
            ['nome' => 'Arroz 5 kg', 'categoria' => 'Mercearia', 'estoque' => 3, 'estoque_minimo' => 5],
            ['nome' => 'Café 500 g', 'categoria' => 'Mercearia', 'estoque' => 0, 'estoque_minimo' => 4],
            ['nome' => 'Detergente', 'categoria' => 'Limpeza', 'estoque' => 2, 'estoque_minimo' => 6],
        ])->map(fn (array $dados) => (new Produto)->forceFill($dados));

        return view('apresentacao', compact('exemplos'));
    }
}
