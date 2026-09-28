@extends('layouts.app')

@section('title', 'Categorias')

@section('content')
    {{-- Cabeçalho com botão de cadastro --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Categorias</h1>
        <a href="{{ route('categorias.create') }}" class="btn btn-primary">Nova Categoria</a>
    </div>

    {{-- Campo de busca --}}
    @include('partials.busca', ['placeholder' => 'Buscar por nome...'])

    {{-- Tabela de registros --}}
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th class="text-center">Produtos</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categorias as $categoria)
                        <tr>
                            <td>{{ $categoria->nome }}</td>
                            <td>{{ $categoria->descricao ?? '-' }}</td>
                            <td class="text-center">{{ $categoria->produtos_count }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('categorias.edit', $categoria) }}" class="btn btn-sm btn-warning">Editar</a>
                                @include('partials.excluir', ['rota' => route('categorias.destroy', $categoria), 'nome' => $categoria->nome])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Nenhuma categoria encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Links de paginação --}}
    <div class="mt-3">{{ $categorias->links() }}</div>
@endsection
