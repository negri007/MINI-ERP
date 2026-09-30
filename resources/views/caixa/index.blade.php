@extends('layouts.app')

@section('title', 'Caixa do dia')

@section('content')
    @php($ehHoje = $dia->isToday())

    {{-- Cabeçalho: título, data escolhível e impressão --}}
    <div class="cabeca-lista">
        <h1 class="h3">Caixa do dia <span class="qtd">{{ $dia->format('d/m/Y') }}{{ $ehHoje ? ' · hoje' : '' }}</span></h1>
        <form method="GET" action="{{ route('caixa.index') }}" class="acoes-topo no-print caixa-data">
            <label for="data" class="visually-hidden">Dia</label>
            <input type="date" name="data" id="data" class="form-control" value="{{ $dia->toDateString() }}">
            <button type="submit" class="btn btn-secondary">Ver dia</button>
            <button type="button" class="btn btn-secondary d-inline-flex align-items-center gap-2" onclick="window.print()">
                @include('partials.icone', ['nome' => 'impressora']) Imprimir
            </button>
        </form>
    </div>
    <div class="no-print">
        @include('partials.dica', ['chave' => 'caixa', 'texto' => 'Confira no fim do dia se o dinheiro na gaveta bate com o total em Dinheiro.'])
    </div>

    {{-- Total por forma de pagamento (só vendas concluídas) --}}
    <section aria-labelledby="caixaFormas">
        <h2 class="visually-hidden" id="caixaFormas">Total por forma de pagamento</h2>
        <div class="caixa-formas">
            @foreach ($porForma as $chave => $forma)
                <div class="card caixa-forma {{ $chave === 'nao_informada' ? 'nao-informada' : '' }}">
                    <div class="rotulo">{{ $forma['nome'] }}</div>
                    <div class="valor"><small>R$</small>{{ number_format($forma['total'], 2, ',', '.') }}</div>
                    <div class="rodape">{{ $forma['quantidade'] }} {{ $forma['quantidade'] === 1 ? 'venda' : 'vendas' }}</div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Resumo do dia; canceladas ficam à parte, fora do total --}}
    <dl class="caixa-resumo">
        <div><dt>Total do dia</dt><dd>R$ {{ number_format($resumo['total'], 2, ',', '.') }}</dd></div>
        <div><dt>Vendas</dt><dd>{{ $resumo['quantidade'] }}</dd></div>
        <div><dt>Descontos dados</dt><dd>R$ {{ number_format($resumo['descontos'], 2, ',', '.') }}</dd></div>
        <div class="canceladas"><dt>Canceladas (fora do total)</dt>
            <dd>{{ $resumo['canceladas'] }} {{ $resumo['canceladas'] === 1 ? 'venda' : 'vendas' }} · R$ {{ number_format($resumo['total_canceladas'], 2, ',', '.') }}</dd></div>
    </dl>

    {{-- Vendas do dia --}}
    <div class="card">
        <div class="card-header"><h2 class="titulo-cartao">Vendas do dia</h2></div>
        <div class="card-body p-0">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Venda</th>
                        <th>Cliente</th>
                        <th>Pagamento</th>
                        <th>Situação</th>
                        <th class="text-end">Desconto</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vendas as $venda)
                        <tr>
                            <td><a href="{{ route('vendas.show', $venda) }}">#{{ str_pad($venda->id, 4, '0', STR_PAD_LEFT) }}</a></td>
                            <td>{{ $venda->cliente->nome }}</td>
                            <td class="{{ $venda->forma_pagamento ? '' : 'text-muted' }}">{{ $venda->nomeFormaPagamento() }}</td>
                            <td>
                                @if ($venda->estaCancelada())
                                    <span class="badge bg-danger">Cancelada</span>
                                @else
                                    <span class="badge bg-success">Concluída</span>
                                @endif
                            </td>
                            <td class="text-end">{{ $venda->desconto > 0 ? 'R$ '.number_format($venda->desconto, 2, ',', '.') : '-' }}</td>
                            <td class="text-end valor-lista {{ $venda->estaCancelada() ? 'riscado' : '' }}">R$ {{ number_format($venda->total, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        {{-- Dia vazio: a tabela continua no lugar e explica o próximo passo --}}
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Nenhuma venda em {{ $dia->format('d/m/Y') }}.
                                @if ($ehHoje)
                                    <a href="{{ route('vendas.create') }}" class="no-print">Registrar venda</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
