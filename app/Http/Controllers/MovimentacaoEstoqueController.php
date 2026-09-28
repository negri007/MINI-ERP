<?php

// Arquivo criado com o comando:
//   php artisan make:controller MovimentacaoEstoqueController

namespace App\Http\Controllers;

use App\Http\Requests\MovimentacaoRequest;
use App\Models\MovimentacaoEstoque;
use App\Models\Produto;
use App\Services\EstoqueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovimentacaoEstoqueController extends Controller
{
    // Histórico de entradas e saídas (filtros por produto e tipo)
    public function index(Request $request)
    {
        $movimentacoes = MovimentacaoEstoque::with(['produto', 'usuario'])
            ->when($request->filled('produto_id'), fn ($q) => $q->where('produto_id', $request->input('produto_id')))
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->input('tipo')))
            ->latest()
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $produtos = Produto::orderBy('nome')->get();

        return view('estoque.index', compact('movimentacoes', 'produtos'));
    }

    // Formulário de entrada/saída manual (compra de fornecedor, perda, ajuste...)
    public function create(Request $request)
    {
        return view('estoque.create', [
            'produtos' => Produto::orderBy('nome')->get(),
            'produtoSelecionado' => $request->input('produto_id'),
        ]);
    }

    // Registra a movimentação
    public function store(MovimentacaoRequest $request, EstoqueService $estoque)
    {
        $dados = $request->validated();

        DB::transaction(function () use ($dados, $estoque) {
            $produto = Produto::lockForUpdate()->findOrFail($dados['produto_id']);

            if ($dados['tipo'] === MovimentacaoEstoque::ENTRADA) {
                $estoque->entrada($produto, $dados['quantidade'], $dados['motivo']);
            } else {
                $estoque->saida($produto, $dados['quantidade'], $dados['motivo']);
            }
        });

        return redirect()->route('estoque.index')->with('success', 'Movimentação de estoque registrada!');
    }
}
