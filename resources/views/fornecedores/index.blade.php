@extends('layouts.app')

@section('title', 'Fornecedores')

@section('content')
    {{-- Cabeçalho com botão de cadastro --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Fornecedores</h1>
        <a href="{{ route('fornecedores.create') }}" class="btn btn-primary">Novo Fornecedor</a>
    </div>

    {{-- Campo de busca --}}
    @include('partials.busca', ['placeholder' => 'Buscar por nome ou CNPJ...'])

    {{-- Tabela de registros --}}
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th class="text-nowrap">CNPJ</th>
                        <th class="text-nowrap">Telefone</th>
                        <th>E-mail</th>
                        <th class="text-center">Produtos</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($fornecedores as $fornecedor)
                        <tr>
                            <td>{{ $fornecedor->nome }}</td>
                            <td class="text-nowrap">{{ $fornecedor->cnpj ?? '-' }}</td>
                            <td class="text-nowrap">{{ $fornecedor->telefone ?? '-' }}</td>
                            <td>{{ $fornecedor->email ?? '-' }}</td>
                            <td class="text-center">{{ $fornecedor->produtos_count }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('fornecedores.edit', $fornecedor) }}" class="btn btn-sm btn-warning">Editar</a>
                                @include('partials.excluir', ['rota' => route('fornecedores.destroy', $fornecedor), 'nome' => $fornecedor->nome])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Nenhum fornecedor encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Links de paginação --}}
    <div class="mt-3">{{ $fornecedores->links() }}</div>
@endsection
