<?php

// Arquivo criado com o comando:
//   php artisan make:controller ProdutoController --resource
// (--resource já cria os métodos index, create, store, show, edit, update e destroy)

namespace App\Http\Controllers;

use App\Http\Requests\ProdutoRequest;
use App\Models\Categoria;
use App\Models\Fornecedor;
use App\Models\Produto;
use App\Services\EstoqueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProdutoController extends Controller
{
    // Lista os produtos com filtros de nome, categoria e estoque baixo
    public function index(Request $request)
    {
        $produtos = Produto::with(['categoria', 'fornecedor'])
            ->busca($request->input('busca'))
            ->when($request->filled('categoria_id'), fn ($q) => $q->where('categoria_id', $request->input('categoria_id')))
            ->when($request->boolean('estoque_baixo'), fn ($q) => $q->estoqueBaixo())
            ->orderBy('nome')
            ->paginate(10)
            ->withQueryString();

        $categorias = Categoria::orderBy('nome')->get();

        return view('produtos.index', compact('produtos', 'categorias'));
    }

    // Exibe o formulário de cadastro
    public function create()
    {
        return view('produtos.create', [
            'produto' => new Produto(['estoque_minimo' => 5]),
            'categorias' => Categoria::orderBy('nome')->get(),
            'fornecedores' => Fornecedor::orderBy('nome')->get(),
        ]);
    }

    // Salva um novo produto e registra o estoque inicial no histórico
    public function store(ProdutoRequest $request, EstoqueService $estoque)
    {
        DB::transaction(function () use ($request, $estoque) {
            $dados = $request->validated();
            $estoqueInicial = (int) $dados['estoque'];

            // O produto nasce com estoque 0 e o saldo entra como movimentação
            $produto = Produto::create(array_merge($dados, ['estoque' => 0]));

            if ($estoqueInicial > 0) {
                $estoque->entrada($produto, $estoqueInicial, 'Estoque inicial');
            }
        });

        return redirect()->route('produtos.index')->with('success', 'Produto cadastrado com sucesso!');
    }

    // Exibe o formulário de edição
    public function edit(Produto $produto)
    {
        return view('produtos.edit', [
            'produto' => $produto,
            'categorias' => Categoria::orderBy('nome')->get(),
            'fornecedores' => Fornecedor::orderBy('nome')->get(),
        ]);
    }

    // Atualiza um produto (o estoque não é alterado aqui)
    public function update(ProdutoRequest $request, Produto $produto)
    {
        $produto->update($request->validated());

        return redirect()->route('produtos.index')->with('success', 'Produto atualizado com sucesso!');
    }

    // Exclui um produto (bloqueia se ele já apareceu em alguma venda)
    public function destroy(Produto $produto)
    {
        if ($produto->itensVenda()->exists()) {
            return redirect()->route('produtos.index')->with('error', 'Não é possível excluir: este produto já foi vendido.');
        }

        $produto->delete();

        return redirect()->route('produtos.index')->with('success', 'Produto excluído com sucesso!');
    }
}
