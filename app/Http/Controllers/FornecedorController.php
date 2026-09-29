<?php

// Arquivo criado com o comando:
//   php artisan make:controller FornecedorController --resource
// (--resource já cria os métodos index, create, store, show, edit, update e destroy)

namespace App\Http\Controllers;

use App\Http\Requests\FornecedorRequest;
use App\Models\Fornecedor;
use Illuminate\Http\Request;

class FornecedorController extends Controller
{
    // Lista os fornecedores: busca, filtro (com/sem produtos), ordenação e paginação
    public function index(Request $request)
    {
        $busca = $request->input('busca');
        $filtro = $request->input('filtro');

        $query = Fornecedor::withCount('produtos')
            // Os produtos fornecidos aparecem ao abrir a linha
            ->with(['produtos' => fn ($q) => $q->orderBy('nome')->limit(6)])
            ->busca($busca)
            ->when($filtro === 'com', fn ($q) => $q->has('produtos'))
            ->when($filtro === 'sem', fn ($q) => $q->doesntHave('produtos'));

        [$ordem, $dir] = $this->ordenar($query, $request, [
            'nome' => 'nome',
            'produtos' => 'produtos_count',
        ], 'nome');

        $fornecedores = $query->paginate(10)->withQueryString();

        $contagem = [
            'todos' => Fornecedor::busca($busca)->count(),
            'com' => Fornecedor::busca($busca)->has('produtos')->count(),
            'sem' => Fornecedor::busca($busca)->doesntHave('produtos')->count(),
        ];

        return view('fornecedores.index', compact('fornecedores', 'contagem', 'filtro', 'ordem', 'dir'));
    }

    // Exibe o formulário de cadastro
    public function create()
    {
        return view('fornecedores.create', ['fornecedor' => new Fornecedor]);
    }

    // Salva um novo fornecedor (a validação acontece no FornecedorRequest)
    public function store(FornecedorRequest $request)
    {
        Fornecedor::create($request->validated());

        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor cadastrado com sucesso!');
    }

    // Exibe o formulário de edição
    public function edit(Fornecedor $fornecedor)
    {
        return view('fornecedores.edit', compact('fornecedor'));
    }

    // Atualiza um fornecedor existente
    public function update(FornecedorRequest $request, Fornecedor $fornecedor)
    {
        $fornecedor->update($request->validated());

        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor atualizado com sucesso!');
    }

    // Exclui um fornecedor (os produtos dele ficam sem fornecedor)
    public function destroy(Fornecedor $fornecedor)
    {
        $fornecedor->delete();

        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor excluído com sucesso!');
    }
}
