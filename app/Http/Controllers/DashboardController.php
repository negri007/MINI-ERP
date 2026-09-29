<?php

// Arquivo criado com o comando:
//   php artisan make:controller DashboardController

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Venda;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    // Exibe o painel inicial: indicadores do mês, gráfico, estoque baixo e últimas vendas
    public function index()
    {
        $hoje = Carbon::today();

        // Vendas concluídas (canceladas não contam no faturamento)
        $concluidas = fn () => Venda::where('status', Venda::CONCLUIDA);

        // Mês atual até hoje
        $mesAtual = $concluidas()->whereDate('data', '>=', $hoje->copy()->startOfMonth()->toDateString());

        $mes = [
            'faturamento' => (clone $mesAtual)->sum('total'),
            'quantidade' => (clone $mesAtual)->count(),
            'hoje' => $concluidas()->whereDate('data', $hoje->toDateString())->count(),
        ];
        $mes['ticket_medio'] = $mes['quantidade'] ? $mes['faturamento'] / $mes['quantidade'] : 0;

        // Mesmo período do mês passado (do dia 1 até o mesmo dia), para comparar
        $inicioAnterior = $hoje->copy()->subMonthNoOverflow()->startOfMonth();
        $fimAnterior = $hoje->copy()->subMonthNoOverflow();
        $faturamentoAnterior = $concluidas()
            ->whereDate('data', '>=', $inicioAnterior->toDateString())
            ->whereDate('data', '<=', $fimAnterior->toDateString())
            ->sum('total');

        // Variação em %; fica null quando não há mês anterior para comparar
        $mes['variacao'] = $faturamentoAnterior > 0
            ? ($mes['faturamento'] - $faturamentoAnterior) / $faturamentoAnterior * 100
            : null;

        // Produtos no estoque mínimo ou abaixo dele
        $estoqueBaixo = Produto::with('categoria')->estoqueBaixo()->orderBy('estoque')->limit(6)->get();
        $qtdParaRepor = Produto::estoqueBaixo()->count();

        // Gráfico: faturamento por dia nos últimos 14 dias
        $inicio = $hoje->copy()->subDays(13);
        $porDia = $concluidas()
            ->whereDate('data', '>=', $inicio->toDateString())
            ->get(['data', 'total'])
            ->groupBy(fn ($v) => $v->data->toDateString())
            ->map(fn ($grupo) => (float) $grupo->sum('total'));

        $grafico = ['labels' => [], 'valores' => []];
        for ($dia = $inicio->copy(); $dia->lte($hoje); $dia->addDay()) {
            $grafico['labels'][] = $dia->format('d/m');
            $grafico['valores'][] = $porDia[$dia->toDateString()] ?? 0;
        }

        // Últimas vendas
        $ultimasVendas = Venda::with('cliente')->withCount('itens')->latest('id')->limit(5)->get();

        return view('dashboard', compact('mes', 'estoqueBaixo', 'qtdParaRepor', 'grafico', 'ultimasVendas'));
    }
}
