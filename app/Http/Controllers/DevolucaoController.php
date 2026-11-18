<?php

// Arquivo criado com o comando:
//   php artisan make:controller DevolucaoController

namespace App\Http\Controllers;

use App\Http\Requests\DevolucaoRequest;
use App\Models\Devolucao;
use App\Models\Venda;
use App\Services\DevolucaoService;
use Illuminate\Http\Request;

// Devoluções de cliente: lista de tudo que já voltou e o formulário a partir de uma venda.
// A regra (quantidade, valor com desconto, estoque) fica no DevolucaoService.
class DevolucaoController extends Controller
{
    // Lista: todas as devoluções, com filtro por destino (estoque, perda, fornecedor)
    public function index(Request $request)
    {
        $destino = array_key_exists((string) $request->input('destino'), Devolucao::DESTINOS) ? $request->input('destino') : null;

        $devolucoes = Devolucao::with(['venda.cliente', 'usuario', 'itens.produto'])
            ->when($destino, fn ($q) => $q->where('destino', $destino))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        // Totais por destino (para as pílulas e o resumo)
        $porDestino = Devolucao::selectRaw('destino, count(*) as quantidade, sum(valor) as valor')
            ->groupBy('destino')
            ->get()
            ->keyBy('destino');

        $resumo = [
            'quantidade' => (int) $porDestino->sum('quantidade'),
            'valor' => (float) $porDestino->sum('valor'),
            'perda' => (float) ($porDestino['perda']->valor ?? 0),
            'fornecedor' => (float) ($porDestino['fornecedor']->valor ?? 0),
        ];

        return view('devolucoes.index', compact('devolucoes', 'porDestino', 'resumo', 'destino'));
    }

    // Formulário: itens da venda com quanto ainda dá para devolver de cada um
    public function create(Venda $venda)
    {
        $venda->load(['cliente', 'itens.produto', 'devolucoes.itens.produto']);

        if (! $venda->podeDevolver()) {
            return redirect()->route('vendas.show', $venda)->withErrors([
                'venda' => $venda->estaCancelada()
                    ? 'Venda cancelada não tem devolução: o estoque já voltou no cancelamento.'
                    : 'Todos os produtos desta venda já foram devolvidos.',
            ]);
        }

        return view('devolucoes.create', compact('venda'));
    }

    // Registra a devolução e volta para a venda
    public function store(DevolucaoRequest $request, Venda $venda, DevolucaoService $service)
    {
        $dados = $request->validated();

        $devolucao = $service->registrar($venda, $dados['itens'], $dados['motivo'], $dados['destino'], $dados['observacao'] ?? null);

        $texto = [
            'estoque' => 'Os produtos voltaram para o estoque.',
            'perda' => 'Registrado como perda da loja; o estoque não mudou.',
            'fornecedor' => 'Separado para devolver ao fornecedor; o estoque não mudou.',
        ][$devolucao->destino];

        return redirect()->route('vendas.show', $venda)
            ->with('success', "Devolução #{$devolucao->id} registrada: R$ ".number_format($devolucao->valor, 2, ',', '.')." para o cliente. {$texto}");
    }
}
