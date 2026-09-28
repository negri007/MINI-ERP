@extends('layouts.app')

@section('title', 'Clientes')

@section('content')
    {{-- Cabeçalho com botão de cadastro --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Clientes</h1>
        <a href="{{ route('clientes.create') }}" class="btn btn-primary">Novo Cliente</a>
    </div>

    {{-- Campo de busca --}}
    @include('partials.busca', ['placeholder' => 'Buscar por nome, CPF/CNPJ ou e-mail...'])

    {{-- Tabela de registros --}}
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th class="text-nowrap">CPF/CNPJ</th>
                        <th class="text-nowrap">Telefone</th>
                        <th>E-mail</th>
                        <th class="text-center">Vendas</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clientes as $cliente)
                        <tr>
                            <td>{{ $cliente->nome }}</td>
                            <td class="text-nowrap">{{ $cliente->cpf_cnpj ?? '-' }}</td>
                            <td class="text-nowrap">{{ $cliente->telefone ?? '-' }}</td>
                            <td>{{ $cliente->email ?? '-' }}</td>
                            <td class="text-center">{{ $cliente->vendas_count }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-sm btn-warning">Editar</a>
                                @include('partials.excluir', ['rota' => route('clientes.destroy', $cliente), 'nome' => $cliente->nome])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Nenhum cliente encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Links de paginação --}}
    <div class="mt-3">{{ $clientes->links() }}</div>
@endsection
