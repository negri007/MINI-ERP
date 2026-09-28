@extends('layouts.app')

@section('title', 'Relatório de Vendas')

@section('content')
    <h1 class="h3 mb-3">Relatório de Vendas</h1>

    {{-- Filtro de período --}}
    <form method="GET" class="row g-2 mb-4 align-items-end">
        <div class="col-auto">
            <label class="form-label small mb-0">De</label>
            <input type="date" name="de" value="{{ $de }}" class="form-control">
        </div>
        <div class="col-auto">
            <label class="form-label small mb-0">Até</label>
            <input type="date" name="ate" value="{{ $ate }}" class="form-control">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Gerar</button>
            <a href="{{ route('relatorios.vendas.exportar', ['de' => $de, 'ate' => $ate]) }}" class="btn btn-outline-success">Exportar Excel (CSV)</a>
        </div>
    </form>

    {{-- Resumo do período (só vendas concluídas) --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <div class="text-muted small">Vendas</div>
                <div class="fs-3">{{ $resumo['quantidade'] }}</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <div class="text-muted small">Faturamento</div>
                <div class="fs-3">R$ {{ number_format($resumo['faturamento'], 2, ',', '.') }}</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <div class="text-muted small">Ticket médio</div>
                <div class="fs-3">R$ {{ number_format($resumo['ticket_medio'], 2, ',', '.') }}</div>
            </div></div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Produtos mais vendidos --}}
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">Produtos mais vendidos</div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr><th>Produto</th><th class="text-end">Qtd.</th><th class="text-end">Total</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($maisVendidos as $item)
                                <tr>
                                    <td>{{ $item->produto->nome }}</td>
                                    <td class="text-end">{{ $item->quantidade }}</td>
                                    <td class="text-end text-nowrap">R$ {{ number_format($item->total, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">Sem vendas no período.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Vendas do período --}}
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">Vendas do período</div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr><th>#</th><th>Data</th><th>Cliente</th><th class="text-end">Total</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($vendas as $venda)
                                <tr>
                                    <td><a href="{{ route('vendas.show', $venda) }}">{{ $venda->id }}</a></td>
                                    <td>{{ $venda->data->format('d/m/Y') }}</td>
                                    <td>{{ $venda->cliente->nome }}</td>
                                    <td class="text-end text-nowrap">R$ {{ number_format($venda->total, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">Sem vendas no período.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
