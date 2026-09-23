<?php

// Arquivo criado com o comando:
//   php artisan make:controller FornecedorController --resource
// (--resource já cria os métodos index, create, store, show, edit, update e destroy)

namespace App\Http\Controllers;

use App\Models\Fornecedor;
use Illuminate\Http\Request;

class FornecedorController extends Controller
{
    // Lista todos os fornecedores
    public function index()
    {
        $fornecedores = Fornecedor::orderBy('nome')->get();

        return view('fornecedores.index', compact('fornecedores'));
    }

    // Exibe o formulário de cadastro
    public function create()
    {
        return view('fornecedores.create');
    }

    // Salva um novo fornecedor
    public function store(Request $request)
    {
        Fornecedor::create($this->validar($request));

        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor cadastrado com sucesso!');
    }

    // Exibe o formulário de edição
    public function edit(Fornecedor $fornecedor)
    {
        return view('fornecedores.edit', compact('fornecedor'));
    }

    // Atualiza um fornecedor existente
    public function update(Request $request, Fornecedor $fornecedor)
    {
        $fornecedor->update($this->validar($request));

        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor atualizado com sucesso!');
    }

    // Exclui um fornecedor (os produtos dele ficam sem fornecedor)
    public function destroy(Fornecedor $fornecedor)
    {
        $fornecedor->delete();

        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor excluído com sucesso!');
    }

    // Regras de validação: apenas o nome é obrigatório
    private function validar(Request $request): array
    {
        return $request->validate([
            'nome' => 'required|string|max:255',
            'cnpj' => 'nullable|string|max:18',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);
    }
}
