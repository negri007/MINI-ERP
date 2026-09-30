<?php

// Arquivo criado com o comando:
//   php artisan make:controller VendaController

namespace App\Http\Controllers;

use App\Http\Requests\VendaRequest;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Venda;
use App\Services\VendaService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

// Vendas não são editadas nem apagadas: se houver erro, a venda é CANCELADA
// e o estoque volta. Assim o histórico fica sempre correto.
class VendaController extends Controller
{
    // Lista as vendas: busca (cliente, CPF/CNPJ ou nº), período, situação, ordenação,
    // resumo do filtro e os itens de cada venda ao abrir a linha
    public function index(Request $request)
    {
        $busca = $request->input('busca');
        $periodo = in_array($request->input('periodo'), ['hoje', '7dias', 'mes'], true) ? $request->input('periodo') : null;
        $status = in_array($request->input('status'), [Venda::CONCLUIDA, Venda::CANCELADA], true) ? $request->input('status') : null;

        // Filtros aplicados numa consulta (usado na lista, no resumo e nas contagens)
        $filtrar = function ($q, $usarPeriodo = true, $usarStatus = true) use ($request, $busca, $periodo, $status) {
            return $q
                ->when($busca, fn ($q) => $q->where(function ($q) use ($busca) {
                    $numero = (int) ltrim($busca, '#0');
                    $q->whereHas('cliente', fn ($c) => $c->busca($busca))
                        ->when($numero > 0, fn ($q) => $q->orWhere('id', $numero));
                }))
                ->when($usarPeriodo && $periodo === 'hoje', fn ($q) => $q->whereDate('data', Carbon::today()->toDateString()))
                ->when($usarPeriodo && $periodo === '7dias', fn ($q) => $q->whereDate('data', '>=', Carbon::today()->subDays(6)->toDateString()))
                ->when($usarPeriodo && $periodo === 'mes', fn ($q) => $q->whereDate('data', '>=', Carbon::today()->startOfMonth()->toDateString()))
                ->when($usarStatus && $status, fn ($q) => $q->where('status', $status))
                ->when($request->filled('cliente'), fn ($q) => $q->where('cliente_id', (int) $request->input('cliente')))
                // filtros de data manuais (links antigos e relatórios)
                ->when($request->filled('de'), fn ($q) => $q->whereDate('data', '>=', $request->input('de')))
                ->when($request->filled('ate'), fn ($q) => $q->whereDate('data', '<=', $request->input('ate')));
        };

        $query = $filtrar(Venda::query())
            ->with(['itens.produto', 'cliente' => fn ($q) => $q->withCount(['vendas as compras_no_mes' => fn ($v) => $v
                ->where('status', Venda::CONCLUIDA)
                ->whereDate('data', '>=', Carbon::today()->startOfMonth()->toDateString())])])
            ->withCount('itens');

        [$ordem, $dir] = $this->ordenar($query, $request, [
            'numero' => 'id',
            'data' => 'data',
            'total' => 'total',
            'cliente' => Cliente::select('nome')->whereColumn('clientes.id', 'vendas.cliente_id'),
        ], 'data', 'desc');

        $vendas = $query->paginate(10)->withQueryString();

        // Resumo do que está filtrado (canceladas não entram no faturamento)
        $filtradas = $filtrar(Venda::query());
        $concluidas = (clone $filtradas)->where('status', Venda::CONCLUIDA);
        $resumo = [
            'faturamento' => (clone $concluidas)->sum('total'),
            'concluidas' => (clone $concluidas)->count(),
            'canceladas' => (clone $filtradas)->where('status', Venda::CANCELADA)->count(),
        ];
        $resumo['ticket_medio'] = $resumo['concluidas'] ? $resumo['faturamento'] / $resumo['concluidas'] : 0;

        // Contagens das pílulas: período (sem o filtro de período) e situação (sem o filtro de situação)
        $contagem = [
            'todos' => $filtrar(Venda::query(), false)->count(),
            'hoje' => $filtrar(Venda::query(), false)->whereDate('data', Carbon::today()->toDateString())->count(),
            '7dias' => $filtrar(Venda::query(), false)->whereDate('data', '>=', Carbon::today()->subDays(6)->toDateString())->count(),
            'mes' => $filtrar(Venda::query(), false)->whereDate('data', '>=', Carbon::today()->startOfMonth()->toDateString())->count(),
            'concluida' => $filtrar(Venda::query(), true, false)->where('status', Venda::CONCLUIDA)->count(),
            'cancelada' => $filtrar(Venda::query(), true, false)->where('status', Venda::CANCELADA)->count(),
        ];

        return view('vendas.index', compact('vendas', 'resumo', 'contagem', 'periodo', 'status', 'ordem', 'dir'));
    }

    // Formulário de nova venda
    public function create()
    {
        return view('vendas.create', [
            // Lista completa só para o <select> de reserva (usado se o JavaScript da busca não carregar)
            'clientes' => Cliente::ordemParaVenda()->get(),
            'produtos' => Produto::where('estoque', '>', 0)->orderBy('nome')->get(),
            // Sem estoque: aparecem no fim da lista, sem poder escolher (o Service também barra)
            'esgotados' => Produto::where('estoque', '<=', 0)->orderBy('nome')->get(['id', 'nome']),
        ]);
    }

    // Registra a venda (a regra de negócio fica no VendaService)
    public function store(VendaRequest $request, VendaService $service)
    {
        $dados = $request->validated();

        $venda = $service->registrar(
            $dados['cliente_id'],
            $dados['data'],
            $dados['itens'],
            $dados['forma_pagamento'],
            $dados['desconto'] ?? 0,
            $dados['observacao'] ?? null,
        );

        return redirect()->route('vendas.show', $venda)->with('success', "Venda #{$venda->id} registrada com sucesso!");
    }

    // Detalhes de uma venda
    public function show(Venda $venda)
    {
        $venda->load(['cliente', 'usuario', 'itens.produto']);

        return view('vendas.show', compact('venda'));
    }

    // Cancela a venda e devolve o estoque
    public function cancelar(Venda $venda, VendaService $service)
    {
        $service->cancelar($venda);

        return redirect()->route('vendas.show', $venda)->with('success', "Venda #{$venda->id} cancelada. O estoque foi devolvido.");
    }
}
