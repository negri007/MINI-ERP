@extends('layouts.app')

@section('title', 'Produtos')

@section('content')
    {{-- Cabeçalho com botão de cadastro --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Produtos</h1>
        <a href="{{ route('produtos.create') }}" class="btn btn-primary">Novo Produto</a>
    </div>

    {{-- Tabela de registros --}}
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Categoria</th>
                        <th>Fornecedor</th>
                        <th class="text-end">Preço</th>
                        <th class="text-end">Estoque</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($produtos as $produto)
                        <tr>
                            <td>{{ $produto->nome }}</td>
                            <td>{{ $produto->categoria->nome ?? '-' }}</td>
                            <td>{{ $produto->fornecedor->nome ?? '-' }}</td>
                            <td class="text-end">R$ {{ number_format($produto->preco, 2, ',', '.') }}</td>
                            <td class="text-end">{{ $produto->estoque }}</td>
                            <td class="text-end text-nowrap">
                                {{-- Botão editar --}}
                                <a href="{{ route('produtos.edit', $produto) }}" class="btn btn-sm btn-warning">Editar</a>

                                {{-- Botão excluir (formulário com DELETE) --}}
                                <form action="{{ route('produtos.destroy', $produto) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja realmente excluir?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Nenhum produto cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
