@extends('layouts.app')

@section('title', 'Produtos')

@section('content')
    {{-- Cabeçalho com botão de cadastro --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Produtos</h1>
        <a href="{{ route('produtos.create') }}" class="btn btn-primary">Novo Produto</a>
    </div>

    {{-- Filtros: nome, categoria e estoque baixo --}}
    <form method="GET" class="row g-2 mb-3 align-items-center">
        <div class="col-sm-6 col-lg-4">
            <input type="search" name="busca" value="{{ request('busca') }}" class="form-control" placeholder="Buscar por nome...">
        </div>
        <div class="col-sm-6 col-lg-3">
            <select name="categoria_id" class="form-select">
                <option value="">Todas as categorias</option>
                @foreach ($categorias as $categoria)
                    <option value="{{ $categoria->id }}" @selected(request('categoria_id') == $categoria->id)>{{ $categoria->nome }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="estoque_baixo" value="1" id="estoque_baixo" @checked(request('estoque_baixo'))>
                <label class="form-check-label" for="estoque_baixo">Só estoque baixo</label>
            </div>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-primary">Filtrar</button>
            @if (request()->hasAny(['busca', 'categoria_id', 'estoque_baixo']))
                <a href="{{ route('produtos.index') }}" class="btn btn-outline-secondary">Limpar</a>
            @endif
        </div>
    </form>

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
                        <th>Estoque</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($produtos as $produto)
                        <tr>
                            <td>{{ $produto->nome }}</td>
                            <td>{{ $produto->categoria->nome ?? '-' }}</td>
                            <td>{{ $produto->fornecedor->nome ?? '-' }}</td>
                            <td class="text-end text-nowrap">R$ {{ number_format($produto->preco, 2, ',', '.') }}</td>
                            {{-- Medidor de estoque: nível, marca do mínimo e situação (OK / Baixo / Esgotado) --}}
                            <td>@include('partials.medidor', ['produto' => $produto])</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('estoque.create', ['produto_id' => $produto->id]) }}" class="btn btn-sm btn-outline-secondary">Estoque</a>
                                <a href="{{ route('produtos.edit', $produto) }}" class="btn btn-sm btn-warning">Editar</a>
                                @include('partials.excluir', ['rota' => route('produtos.destroy', $produto), 'nome' => $produto->nome])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Nenhum produto encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Links de paginação --}}
    <div class="mt-3">{{ $produtos->links() }}</div>
@endsection
