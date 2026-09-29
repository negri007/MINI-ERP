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

        // O Consumidor final não aparece na lista nem nas contagens (ele não é um cadastro de verdade)
        $query = Cliente::comuns()->withCount(['vendas' => $concluidas, 'vendas as vendas_registradas'])
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
            'todos' => Cliente::comuns()->busca($busca)->count(),
            'com' => Cliente::comuns()->busca($busca)->whereHas('vendas', $concluidas)->count(),
            'sem' => Cliente::comuns()->busca($busca)->whereDoesntHave('vendas', $concluidas)->count(),
        ];

        return view('clientes.index', compact('clientes', 'contagem', 'filtro', 'ordem', 'dir'));
    }

    // Busca para o campo Cliente da Nova venda (responde em JSON, até 10 clientes).
    // O Consumidor final vem sempre primeiro quando combina com o que foi digitado.
    public function buscar(Request $request)
    {
        $termo = trim((string) $request->input('q'));

        $clientes = Cliente::busca($termo)
            ->ordemParaVenda()
            ->limit(10)
            ->get(['id', 'nome', 'cpf_cnpj', 'consumidor_final']);

        return response()->json($clientes->map(fn (Cliente $c) => $this->paraLista($c)));
    }

    // Cadastro rápido feito pelo modal da Nova venda (mesma validação do cadastro normal)
    public function rapido(ClienteRequest $request)
    {
        $cliente = Cliente::create($request->validated());

        return response()->json($this->paraLista($cliente), 201);
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
        if ($bloqueio = $this->bloquearConsumidorFinal($cliente)) {
            return $bloqueio;
        }

        return view('clientes.edit', compact('cliente'));
    }

    // Atualiza um cliente existente
    public function update(ClienteRequest $request, Cliente $cliente)
    {
        if ($bloqueio = $this->bloquearConsumidorFinal($cliente)) {
            return $bloqueio;
        }

        $cliente->update($request->validated());

        return redirect()->route('clientes.index')->with('success', 'Cliente atualizado com sucesso!');
    }

    // Exclui um cliente (bloqueia se ele já tiver vendas)
    public function destroy(Cliente $cliente)
    {
        if ($bloqueio = $this->bloquearConsumidorFinal($cliente)) {
            return $bloqueio;
        }

        if ($motivo = $cliente->motivoParaNaoExcluir()) {
            return redirect()->route('clientes.index')->with('error', $motivo);
        }

        $cliente->delete();

        return redirect()->route('clientes.index')->with('success', 'Cliente excluído com sucesso!');
    }

    // O Consumidor final não pode ser editado nem excluído (as vendas sem cliente dependem dele)
    private function bloquearConsumidorFinal(Cliente $cliente)
    {
        if (! $cliente->ehConsumidorFinal()) {
            return null;
        }

        return redirect()->route('clientes.index')
            ->with('error', 'O Consumidor final é usado nas vendas sem cliente identificado e não pode ser alterado.');
    }

    // Formato usado pela busca e pelo cadastro rápido
    private function paraLista(Cliente $cliente): array
    {
        return [
            'id' => $cliente->id,
            'nome' => $cliente->nome,
            'documento' => $cliente->cpf_cnpj,
            'consumidor_final' => $cliente->ehConsumidorFinal(),
        ];
    }
}
