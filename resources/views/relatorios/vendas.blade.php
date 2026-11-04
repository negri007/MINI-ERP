@extends('layouts.app')

@section('title', 'Relatório de Vendas')

@section('content')
    @php
        // Mostra a data como 01/09/2026; se vier algo estranho na URL, mostra do jeito que veio
        $br = fn ($data) => rescue(fn () => \Illuminate\Support\Carbon::parse($data)->format('d/m/Y'), $data, false);
    @endphp

    {{-- Cabeçalho no mesmo padrão das listas: título, período escolhido e o botão de exportar --}}
    <div class="cabeca-lista">
        <h1 class="h3">Relatório de vendas <span class="qtd">{{ $br($de) }} a {{ $br($ate) }}</span></h1>
        <div class="acoes-topo">
            <a href="{{ route('relatorios.vendas.exportar', ['de' => $de, 'ate' => $ate]) }}" class="btn btn-outline-success">Exportar Excel (CSV)</a>
        </div>
    </div>
    @include('partials.dica', ['chave' => 'relatorio', 'texto' => 'Escolha o período. Vendas canceladas não entram no total.'])

    {{-- Filtro de período na barra de filtros. Cada rótulo está ligado ao seu campo (for/id). --}}
    <form method="GET" class="filtros filtro-periodo">
        <label for="de" class="form-label">De</label>
        <input type="date" name="de" id="de" value="{{ $de }}" class="form-control">
        <label for="ate" class="form-label">Até</label>
        <input type="date" name="ate" id="ate" value="{{ $ate }}" class="form-control">
        <button type="submit" class="btn btn-primary">Gerar relatório</button>
    </form>

    {{-- Resumo do período (só vendas concluídas), no mesmo formato do resumo da tela de Vendas --}}
    <div class="resumo-lista resumo-5">
        <div class="mini"><small>Vendas concluídas</small><b>{{ $resumo['quantidade'] }}</b></div>
        <div class="mini"><small>Faturamento</small><b>R$ {{ number_format($resumo['faturamento'], 2, ',', '.') }}</b></div>
        <div class="mini"><small>Ticket médio</small><b>R$ {{ number_format($resumo['ticket_medio'], 2, ',', '.') }}</b></div>
        <div class="mini"><small>Lucro bruto</small><b>R$ {{ number_format($lucro['lucro'], 2, ',', '.') }}</b></div>
        <div class="mini"><small>Margem</small><b>{{ is_null($lucro['margem']) ? '-' : number_format($lucro['margem'], 1, ',', '.').'%' }}</b></div>
    </div>
    @if ($lucro['sem_custo'] > 0)
        <p class="aviso-lucro mb-3">
            {{ $lucro['sem_custo'] }} {{ $lucro['sem_custo'] === 1 ? 'venda sem custo informado ficou' : 'vendas sem custo informado ficaram' }}
            fora do lucro e da margem. Informe o custo nos produtos para as próximas vendas entrarem na conta.
        </p>
    @endif

    <div class="painel-relatorio">
        {{-- Produtos mais vendidos --}}
        <div class="card">
            <div class="card-header"><h2 class="titulo-cartao">Produtos mais vendidos</h2></div>
            <div class="card-body p-0">
                <table class="table table-sm">
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
                            <tr><td colspan="3" class="text-center text-muted py-4">Sem vendas no período.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Vendas do período (número no mesmo formato "#0015" do resto do sistema) --}}
        <div class="card">
            <div class="card-header"><h2 class="titulo-cartao">Vendas do período</h2></div>
            <div class="card-body p-0">
                <table class="table table-sm">
                    <thead>
                        <tr><th>Venda</th><th>Data</th><th>Cliente</th><th class="text-end">Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($vendas as $venda)
                            <tr>
                                <td><a href="{{ route('vendas.show', $venda) }}">#{{ str_pad($venda->id, 4, '0', STR_PAD_LEFT) }}</a></td>
                                <td>{{ $venda->data->format('d/m/Y') }}</td>
                                <td>{{ $venda->cliente->nome }}</td>
                                <td class="text-end text-nowrap">R$ {{ number_format($venda->total, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Sem vendas no período.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
