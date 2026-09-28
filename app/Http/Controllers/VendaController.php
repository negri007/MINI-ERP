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

// Vendas não são editadas nem apagadas: se houver erro, a venda é CANCELADA
// e o estoque volta. Assim o histórico fica sempre correto.
class VendaController extends Controller
{
    // Lista as vendas com filtros de cliente, situação e período
    public function index(Request $request)
    {
        $vendas = Venda::with('cliente')
            ->withCount('itens')
            ->when($request->filled('busca'), fn ($q) => $q->whereHas('cliente', fn ($c) => $c->busca($request->input('busca'))))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('de'), fn ($q) => $q->whereDate('data', '>=', $request->input('de')))
            ->when($request->filled('ate'), fn ($q) => $q->whereDate('data', '<=', $request->input('ate')))
            ->latest('data')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('vendas.index', compact('vendas'));
    }

    // Formulário de nova venda
    public function create()
    {
        return view('vendas.create', [
            'clientes' => Cliente::orderBy('nome')->get(),
            'produtos' => Produto::where('estoque', '>', 0)->orderBy('nome')->get(),
        ]);
    }

    // Registra a venda (a regra de negócio fica no VendaService)
    public function store(VendaRequest $request, VendaService $service)
    {
        $dados = $request->validated();

        $venda = $service->registrar($dados['cliente_id'], $dados['data'], $dados['itens'], $dados['observacao'] ?? null);

        // "imprimir" faz o cupom aparecer saindo da impressora (animação na tela)
        return redirect()->route('vendas.show', $venda)
            ->with('success', "Venda #{$venda->id} registrada com sucesso!")
            ->with('imprimir', true);
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

        // "carimbar" faz o carimbo de CANCELADA bater no cupom (animação na tela)
        return redirect()->route('vendas.show', $venda)
            ->with('success', "Venda #{$venda->id} cancelada. O estoque foi devolvido.")
            ->with('carimbar', true);
    }
}
