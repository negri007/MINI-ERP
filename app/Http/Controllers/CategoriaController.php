<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    // Lista todas as categorias
    public function index()
    {
        $categorias = Categoria::withCount('produtos')->orderBy('nome')->get();

        return view('categorias.index', compact('categorias'));
    }

    // Exibe o formulário de cadastro
    public function create()
    {
        return view('categorias.create');
    }

    // Salva uma nova categoria
    public function store(Request $request)
    {
        Categoria::create($this->validar($request));

        return redirect()->route('categorias.index')->with('success', 'Categoria cadastrada com sucesso!');
    }

    // Exibe o formulário de edição
    public function edit(Categoria $categoria)
    {
        return view('categorias.edit', compact('categoria'));
    }

    // Atualiza uma categoria existente
    public function update(Request $request, Categoria $categoria)
    {
        $categoria->update($this->validar($request));

        return redirect()->route('categorias.index')->with('success', 'Categoria atualizada com sucesso!');
    }

    // Exclui uma categoria (bloqueia se houver produtos vinculados)
    public function destroy(Categoria $categoria)
    {
        if ($categoria->produtos()->exists()) {
            return redirect()->route('categorias.index')->with('error', 'Não é possível excluir: existem produtos nesta categoria.');
        }

        $categoria->delete();

        return redirect()->route('categorias.index')->with('success', 'Categoria excluída com sucesso!');
    }

    // Regras de validação compartilhadas entre store e update
    private function validar(Request $request): array
    {
        return $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
        ]);
    }
}
