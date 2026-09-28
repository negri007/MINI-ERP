@extends('layouts.app')

@section('title', 'Estoque')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Movimentações de Estoque</h1>
        <a href="{{ route('estoque.create') }}" class="btn btn-primary">Nova Movimentação</a>
    </div>

    {{-- Filtros: produto e tipo --}}
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
            <select name="produto_id" class="form-select">
                <option value="">Todos os produtos</option>
                @foreach ($produtos as $produto)
                    <option value="{{ $produto->id }}" @selected(request('produto_id') == $produto->id)>{{ $produto->nome }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="tipo" class="form-select">
                <option value="">Entradas e saídas</option>
                <option value="entrada" @selected(request('tipo') === 'entrada')>Só entradas</option>
                <option value="saida" @selected(request('tipo') === 'saida')>Só saídas</option>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-primary">Filtrar</button>
            @if (request()->hasAny(['produto_id', 'tipo']))
                <a href="{{ route('estoque.index') }}" class="btn btn-outline-secondary">Limpar</a>
            @endif
        </div>
    </form>

    {{-- Histórico --}}
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Data/hora</th>
                        <th>Produto</th>
                        <th class="text-center">Tipo</th>
                        <th class="text-end">Quantidade</th>
                        <th class="text-end">Saldo após</th>
                        <th>Motivo</th>
                        <th>Usuário</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movimentacoes as $mov)
                        <tr>
                            <td class="text-nowrap">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $mov->produto->nome }}</td>
                            <td class="text-center">
                                @if ($mov->tipo === 'entrada')
                                    <span class="badge bg-success">Entrada</span>
                                @else
                                    <span class="badge bg-danger">Saída</span>
                                @endif
                            </td>
                            <td class="text-end">{{ $mov->tipo === 'entrada' ? '+' : '-' }}{{ $mov->quantidade }}</td>
                            <td class="text-end">{{ $mov->estoque_apos }}</td>
                            <td>
                                @if ($mov->venda_id)
                                    <a href="{{ route('vendas.show', $mov->venda_id) }}">{{ $mov->motivo }}</a>
                                @else
                                    {{ $mov->motivo }}
                                @endif
                            </td>
                            <td>{{ $mov->usuario->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Nenhuma movimentação encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $movimentacoes->links() }}</div>
@endsection
