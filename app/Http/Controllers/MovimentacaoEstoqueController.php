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
    // Histórico de entradas e saídas: busca por produto ou motivo, filtro por tipo, ordenação
    public function index(Request $request)
    {
        $busca = $request->input('busca');
        $tipo = in_array($request->input('tipo'), [MovimentacaoEstoque::ENTRADA, MovimentacaoEstoque::SAIDA], true) ? $request->input('tipo') : null;

        $filtrar = fn ($q) => $q
            ->when($request->filled('produto_id'), fn ($q) => $q->where('produto_id', $request->input('produto_id')))
            ->when($busca, fn ($q) => $q->where(fn ($q) => $q
                ->whereHas('produto', fn ($p) => $p->busca($busca))
                ->orWhere('motivo', 'like', "%{$busca}%")));

        $query = $filtrar(MovimentacaoEstoque::with(['produto.categoria', 'usuario']))
            ->when($tipo, fn ($q) => $q->where('tipo', $tipo));

        [$ordem, $dir] = $this->ordenar($query, $request, [
            'data' => 'created_at',
            'quantidade' => 'quantidade',
            'produto' => Produto::select('nome')->whereColumn('produtos.id', 'movimentacoes_estoque.produto_id'),
        ], 'data', 'desc');

        $movimentacoes = $query->paginate(15)->withQueryString();

        $contagem = [
            'todas' => $filtrar(MovimentacaoEstoque::query())->count(),
            'entrada' => $filtrar(MovimentacaoEstoque::query())->where('tipo', MovimentacaoEstoque::ENTRADA)->count(),
            'saida' => $filtrar(MovimentacaoEstoque::query())->where('tipo', MovimentacaoEstoque::SAIDA)->count(),
        ];

        return view('estoque.index', compact('movimentacoes', 'contagem', 'tipo', 'ordem', 'dir'));
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
