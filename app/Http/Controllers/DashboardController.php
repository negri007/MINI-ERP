<?php

// Arquivo criado com o comando:
//   php artisan make:controller DashboardController

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Fornecedor;
use App\Models\Produto;
use App\Models\Venda;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    // Exibe o painel inicial com totais, gráficos e alertas de estoque
    public function index()
    {
        $totais = [
            'categorias' => Categoria::count(),
            'fornecedores' => Fornecedor::count(),
            'clientes' => Cliente::count(),
            'produtos' => Produto::count(),
        ];

        // Vendas concluídas do mês atual
        $vendasMes = Venda::where('status', Venda::CONCLUIDA)
            ->whereDate('data', '>=', Carbon::now()->startOfMonth()->toDateString());

        $mes = [
            'quantidade' => (clone $vendasMes)->count(),
            'faturamento' => (clone $vendasMes)->sum('total'),
        ];

        // Últimas vendas para a "fita de caixa"
        $ultimasVendas = Venda::with('cliente')->latest('id')->limit(6)->get();

        // Produtos no estoque mínimo ou abaixo dele
        $estoqueBaixo = Produto::with('categoria')->estoqueBaixo()->orderBy('estoque')->limit(10)->get();

        // Gráfico 1: faturamento por dia nos últimos 14 dias
        $inicio = Carbon::today()->subDays(13);
        $porDia = Venda::where('status', Venda::CONCLUIDA)
            ->whereDate('data', '>=', $inicio->toDateString())
            ->get(['data', 'total'])
            ->groupBy(fn ($v) => $v->data->toDateString())
            ->map(fn ($grupo) => (float) $grupo->sum('total'));

        $graficoVendas = ['labels' => [], 'valores' => []];
        for ($dia = $inicio->copy(); $dia->lte(Carbon::today()); $dia->addDay()) {
            $graficoVendas['labels'][] = $dia->format('d/m');
            $graficoVendas['valores'][] = $porDia[$dia->toDateString()] ?? 0;
        }

        // Gráfico 2: quantidade de produtos por categoria
        $categorias = Categoria::withCount('produtos')->orderBy('nome')->get();
        $graficoCategorias = [
            'labels' => $categorias->pluck('nome'),
            'valores' => $categorias->pluck('produtos_count'),
        ];

        return view('dashboard', compact('totais', 'mes', 'ultimasVendas', 'estoqueBaixo', 'graficoVendas', 'graficoCategorias'));
    }
}
