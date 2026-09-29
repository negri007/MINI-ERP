<?php

// Arquivo criado com o comando:
//   php artisan make:controller DashboardController

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Cliente;
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
        $mesAnterior = $concluidas()
            ->whereDate('data', '>=', $inicioAnterior->toDateString())
            ->whereDate('data', '<=', $fimAnterior->toDateString());
        $faturamentoAnterior = (clone $mesAnterior)->sum('total');
        $quantidadeAnterior = (clone $mesAnterior)->count();
        $ticketAnterior = $quantidadeAnterior ? $faturamentoAnterior / $quantidadeAnterior : 0;

        // Variação em %; fica null quando não há mês anterior para comparar
        $variacao = fn ($atual, $anterior) => $anterior > 0 ? ($atual - $anterior) / $anterior * 100 : null;
        $mes['variacao'] = $variacao($mes['faturamento'], $faturamentoAnterior);
        $mes['variacao_quantidade'] = $variacao($mes['quantidade'], $quantidadeAnterior);
        $mes['variacao_ticket'] = $variacao($mes['ticket_medio'], $ticketAnterior);

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

        // Primeiros passos: cada um se marca sozinho pelo banco (o Consumidor final não conta como cliente)
        $passos = [
            ['chave' => 'categoria', 'titulo' => 'Cadastrar uma categoria', 'feito' => Categoria::exists(), 'url' => route('categorias.create'), 'acao' => 'Cadastrar categoria'],
            ['chave' => 'produto', 'titulo' => 'Cadastrar um produto', 'feito' => Produto::exists(), 'url' => route('produtos.create'), 'acao' => 'Cadastrar produto'],
            ['chave' => 'cliente', 'titulo' => 'Cadastrar um cliente', 'feito' => Cliente::comuns()->exists(), 'url' => route('clientes.create'), 'acao' => 'Cadastrar cliente'],
            ['chave' => 'venda', 'titulo' => 'Registrar a primeira venda', 'feito' => Venda::exists(), 'url' => route('vendas.create'), 'acao' => 'Registrar venda'],
        ];
        // Com tudo feito o cartão some; o comando "Mostrar primeiros passos" (?passos=1) mostra mesmo assim
        $mostrarPassos = collect($passos)->contains('feito', false) || request()->boolean('passos');

        return view('dashboard', compact('mes', 'estoqueBaixo', 'qtdParaRepor', 'grafico', 'ultimasVendas', 'passos', 'mostrarPassos'));
    }
}
