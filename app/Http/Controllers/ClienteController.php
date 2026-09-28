<?php

// Arquivo criado com o comando:
//   php artisan make:controller ClienteController --resource
// (--resource já cria os métodos index, create, store, show, edit, update e destroy)

namespace App\Http\Controllers;

use App\Http\Requests\ClienteRequest;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    // Lista os clientes (com busca e paginação)
    public function index(Request $request)
    {
        $clientes = Cliente::withCount('vendas')
            ->busca($request->input('busca'))
            ->orderBy('nome')
            ->paginate(10)
            ->withQueryString();

        return view('clientes.index', compact('clientes'));
    }

    // Exibe o formulário de cadastro
    public function create()
    {
        return view('clientes.create', ['cliente' => new Cliente]);
    }

    // Salva um novo cliente (a validação acontece no ClienteRequest)
    public function store(ClienteRequest $request)
    {
        Cliente::create($request->validated());

        return redirect()->route('clientes.index')->with('success', 'Cliente cadastrado com sucesso!');
    }

    // Exibe o formulário de edição
    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    // Atualiza um cliente existente
    public function update(ClienteRequest $request, Cliente $cliente)
    {
        $cliente->update($request->validated());

        return redirect()->route('clientes.index')->with('success', 'Cliente atualizado com sucesso!');
    }

    // Exclui um cliente (bloqueia se ele já tiver vendas)
    public function destroy(Cliente $cliente)
    {
        if ($cliente->vendas()->exists()) {
            return redirect()->route('clientes.index')->with('error', 'Não é possível excluir: este cliente possui vendas registradas.');
        }

        $cliente->delete();

        return redirect()->route('clientes.index')->with('success', 'Cliente excluído com sucesso!');
    }
}
