<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Fornecedor;
use App\Models\Produto;

class DashboardController extends Controller
{
    // Exibe o painel inicial com os totais de cada cadastro
    public function index()
    {
        $totais = [
            'categorias' => Categoria::count(),
            'fornecedores' => Fornecedor::count(),
            'clientes' => Cliente::count(),
            'produtos' => Produto::count(),
        ];

        // Produtos com estoque baixo (5 unidades ou menos)
        $estoqueBaixo = Produto::with('categoria')
            ->where('estoque', '<=', 5)
            ->orderBy('estoque')
            ->limit(10)
            ->get();

        return view('dashboard', compact('totais', 'estoqueBaixo'));
    }
}
