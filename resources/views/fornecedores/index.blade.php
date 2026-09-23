@extends('layouts.app')

@section('title', 'Fornecedores')

@section('content')
    {{-- Cabeçalho com botão de cadastro --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Fornecedores</h1>
        <a href="{{ route('fornecedores.create') }}" class="btn btn-primary">Novo Fornecedor</a>
    </div>

    {{-- Tabela de registros --}}
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>CNPJ</th>
                        <th>Telefone</th>
                        <th>E-mail</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($fornecedores as $fornecedor)
                        <tr>
                            <td>{{ $fornecedor->nome ?? '-' }}</td>
                            <td>{{ $fornecedor->cnpj ?? '-' }}</td>
                            <td>{{ $fornecedor->telefone ?? '-' }}</td>
                            <td>{{ $fornecedor->email ?? '-' }}</td>
                            <td class="text-end text-nowrap">
                                {{-- Botão editar --}}
                                <a href="{{ route('fornecedores.edit', $fornecedor) }}" class="btn btn-sm btn-warning">Editar</a>

                                {{-- Botão excluir (formulário com DELETE) --}}
                                <form action="{{ route('fornecedores.destroy', $fornecedor) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja realmente excluir?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Nenhum fornecedor cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
