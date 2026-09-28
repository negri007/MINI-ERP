@extends('layouts.app')

@section('title', 'Vendas')

@section('content')
    {{-- Cabeçalho com botão de nova venda --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Vendas</h1>
        <a href="{{ route('vendas.create') }}" class="btn btn-primary">Nova Venda</a>
    </div>

    {{-- Filtros: cliente, situação e período --}}
    <form method="GET" class="row g-2 mb-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label small mb-0">Cliente</label>
            <input type="search" name="busca" value="{{ request('busca') }}" class="form-control" placeholder="Nome ou CPF/CNPJ">
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-0">Situação</label>
            <select name="status" class="form-select">
                <option value="">Todas</option>
                <option value="concluida" @selected(request('status') === 'concluida')>Concluída</option>
                <option value="cancelada" @selected(request('status') === 'cancelada')>Cancelada</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-0">De</label>
            <input type="date" name="de" value="{{ request('de') }}" class="form-control">
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-0">Até</label>
            <input type="date" name="ate" value="{{ request('ate') }}" class="form-control">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-primary">Filtrar</button>
            @if (request()->hasAny(['busca', 'status', 'de', 'ate']))
                <a href="{{ route('vendas.index') }}" class="btn btn-outline-secondary">Limpar</a>
            @endif
        </div>
    </form>

    {{-- Tabela de vendas --}}
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Data</th>
                        <th>Cliente</th>
                        <th class="text-center">Itens</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Situação</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vendas as $venda)
                        <tr>
                            <td>{{ $venda->id }}</td>
                            <td>{{ $venda->data->format('d/m/Y') }}</td>
                            <td>{{ $venda->cliente->nome }}</td>
                            <td class="text-center">{{ $venda->itens_count }}</td>
                            <td class="text-end text-nowrap">R$ {{ number_format($venda->total, 2, ',', '.') }}</td>
                            <td class="text-center">
                                @if ($venda->estaCancelada())
                                    <span class="badge bg-secondary">Cancelada</span>
                                @else
                                    <span class="badge bg-success">Concluída</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('vendas.show', $venda) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Nenhuma venda encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Links de paginação --}}
    <div class="mt-3">{{ $vendas->links() }}</div>
@endsection
