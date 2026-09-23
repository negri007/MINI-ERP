<?php

// Arquivo criado com o comando:
//   php artisan make:controller ProdutoController --resource
// (--resource já cria os métodos index, create, store, show, edit, update e destroy)

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Fornecedor;
use App\Models\Produto;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    // Lista todos os produtos com categoria e fornecedor
    public function index()
    {
        $produtos = Produto::with(['categoria', 'fornecedor'])->orderBy('nome')->get();

        return view('produtos.index', compact('produtos'));
    }

    // Exibe o formulário de cadastro
    public function create()
    {
        $categorias = Categoria::orderBy('nome')->get();
        $fornecedores = Fornecedor::orderBy('nome')->get();

        return view('produtos.create', compact('categorias', 'fornecedores'));
    }

    // Salva um novo produto
    public function store(Request $request)
    {
        Produto::create($this->validar($request));

        return redirect()->route('produtos.index')->with('success', 'Produto cadastrado com sucesso!');
    }

    // Exibe o formulário de edição
    public function edit(Produto $produto)
    {
        $categorias = Categoria::orderBy('nome')->get();
        $fornecedores = Fornecedor::orderBy('nome')->get();

        return view('produtos.edit', compact('produto', 'categorias', 'fornecedores'));
    }

    // Atualiza um produto existente
    public function update(Request $request, Produto $produto)
    {
        $produto->update($this->validar($request));

        return redirect()->route('produtos.index')->with('success', 'Produto atualizado com sucesso!');
    }

    // Exclui um produto
    public function destroy(Produto $produto)
    {
        $produto->delete();

        return redirect()->route('produtos.index')->with('success', 'Produto excluído com sucesso!');
    }

    // Regras de validação compartilhadas entre store e update
    private function validar(Request $request): array
    {
        return $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'preco' => 'required|numeric|min:0',
            'estoque' => 'required|integer|min:0',
            'categoria_id' => 'required|exists:categorias,id',
            'fornecedor_id' => 'nullable|exists:fornecedores,id',
        ]);
    }
}
