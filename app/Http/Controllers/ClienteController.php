<?php

// Arquivo criado com o comando:
//   php artisan make:controller ClienteController --resource
// (--resource já cria os métodos index, create, store, show, edit, update e destroy)

namespace App\Http\Controllers;

use App\Http\Requests\ClienteRequest;
use App\Models\Cliente;
use App\Models\Venda;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    // Lista os clientes: busca, filtro (com/sem compras), ordenação e paginação
    public function index(Request $request)
    {
        $busca = $request->input('busca');
        $filtro = $request->input('filtro');

        $concluidas = fn ($q) => $q->where('status', Venda::CONCLUIDA);

        $query = Cliente::withCount(['vendas' => $concluidas])
            ->withSum(['vendas as total_gasto' => $concluidas], 'total')
            // As 3 últimas compras aparecem ao abrir a linha
            ->with(['vendas' => fn ($q) => $q->latest('data')->latest('id')->limit(3)])
            ->busca($busca)
            ->when($filtro === 'com', fn ($q) => $q->whereHas('vendas', $concluidas))
            ->when($filtro === 'sem', fn ($q) => $q->whereDoesntHave('vendas', $concluidas));

        [$ordem, $dir] = $this->ordenar($query, $request, [
            'nome' => 'nome',
            'compras' => 'vendas_count',
            'total' => 'total_gasto',
        ], 'nome');

        $clientes = $query->paginate(10)->withQueryString();

        $contagem = [
            'todos' => Cliente::busca($busca)->count(),
            'com' => Cliente::busca($busca)->whereHas('vendas', $concluidas)->count(),
            'sem' => Cliente::busca($busca)->whereDoesntHave('vendas', $concluidas)->count(),
        ];

        return view('clientes.index', compact('clientes', 'contagem', 'filtro', 'ordem', 'dir'));
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
