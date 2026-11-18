@extends('layouts.app')

@section('title', "Venda #{$venda->id}")

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 no-print">
        <div>
            {{-- Trilha igual à dos formulários: mostra de onde a pessoa veio --}}
            <nav class="trilha" aria-label="Você está em">
                <a href="{{ route('vendas.index') }}">Vendas</a>
                <span aria-hidden="true">›</span>
                <span aria-current="page">Venda #{{ str_pad($venda->id, 4, '0', STR_PAD_LEFT) }}</span>
            </nav>
            <h1 class="h3 mb-0">Venda #{{ str_pad($venda->id, 4, '0', STR_PAD_LEFT) }}</h1>
            <p class="subtitulo mb-0">Registrada por {{ $venda->usuario->name ?? '-' }} em {{ $venda->created_at->format('d/m/Y \à\s H:i') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('vendas.index') }}" class="btn btn-secondary">Voltar</a>
            <button type="button" class="btn btn-secondary d-inline-flex align-items-center gap-2" onclick="window.print()">
                @include('partials.icone', ['nome' => 'impressora']) Imprimir
            </button>

            {{-- Devolução: o cliente trouxe parte (ou tudo) de volta --}}
            @if ($venda->podeDevolver())
                <a href="{{ route('devolucoes.create', $venda) }}" class="btn btn-outline-primary">Registrar devolução</a>
            @endif

            {{-- Cancelar devolve os produtos ao estoque (só sem devolução registrada) --}}
            @unless ($venda->estaCancelada() || $venda->temDevolucao())
                <form action="{{ route('vendas.cancelar', $venda) }}" method="POST"
                      data-confirmar="Cancelar a venda #{{ $venda->id }}? Os produtos voltam para o estoque e a venda sai do faturamento."
                                                          data-confirmar-titulo="Cancelar venda?" data-confirmar-sim="Cancelar venda" data-confirmar-nao="Manter venda">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger">Cancelar venda</button>
                </form>
            @endunless
        </div>
    </div>

    <div class="no-print">
        @include('partials.dica', ['chave' => 'venda-detalhe', 'texto' => 'Cliente devolveu só uma parte? Use "Registrar devolução". Cancelar desfaz a venda inteira.'])
    </div>

    @error('venda')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror

    {{-- A venda, em formato de nota --}}
    <div class="card nota {{ $venda->estaCancelada() ? 'cancelada' : '' }}">
        @if ($venda->estaCancelada())
            <div class="faixa-cancelada">Venda cancelada: os produtos já voltaram para o estoque.</div>
        @endif

        <div class="cabecalho-nota">
            <div>
                <div class="text-muted small">Mini ERP · comprovante de venda</div>
                <div class="numero-venda">Nº {{ str_pad($venda->id, 4, '0', STR_PAD_LEFT) }}</div>
            </div>
            @if ($venda->estaCancelada())
                <span class="badge bg-danger">Cancelada</span>
            @else
                <span class="badge bg-success">Concluída</span>
            @endif
        </div>

        <div class="dados">
            <div><small>Cliente</small>{{ $venda->cliente->nome }}</div>
            <div><small>CPF/CNPJ</small>{{ $venda->cliente->cpf_cnpj ?? '-' }}</div>
            <div><small>Data</small>{{ $venda->data->format('d/m/Y') }}</div>
        </div>

        {{-- Itens --}}
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th class="text-center">Quantidade</th>
                    <th class="text-end">Preço unitário</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($venda->itens as $item)
                    <tr>
                        <td class="fw-semibold">{{ $item->produto->nome }}</td>
                        <td class="text-center">{{ $item->quantidade }}</td>
                        <td class="text-end">R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                        <td class="text-end">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($venda->observacao)
            <div class="px-4 pt-3 text-muted"><strong>Observação:</strong> {{ $venda->observacao }}</div>
        @endif

        {{-- Subtotal, desconto e forma de pagamento (vendas antigas: "não informada") --}}
        <dl class="contas-nota">
            <div><dt>Subtotal</dt><dd>R$ {{ number_format($venda->subtotal, 2, ',', '.') }}</dd></div>
            <div><dt>Desconto</dt><dd>{{ $venda->desconto > 0 ? '− R$ '.number_format($venda->desconto, 2, ',', '.') : 'R$ 0,00' }}</dd></div>
            <div><dt>Pago em</dt><dd>{{ $venda->nomeFormaPagamento() }}</dd></div>
        </dl>

        <div class="total-nota">
            <span class="text-muted">{{ $venda->itens->sum('quantidade') }} item(ns)</span>
            <span class="valor">R$ {{ number_format($venda->total, 2, ',', '.') }}</span>
        </div>

        @if ($venda->total_devolvido > 0)
            <dl class="contas-nota">
                <div><dt>Devolvido ao cliente</dt><dd class="text-danger">− R$ {{ number_format($venda->total_devolvido, 2, ',', '.') }}</dd></div>
                <div><dt>Ficou para a loja</dt><dd class="fw-semibold">R$ {{ number_format($venda->totalLiquido(), 2, ',', '.') }}</dd></div>
            </dl>
        @endif
    </div>

    {{-- Devoluções desta venda --}}
    @if ($venda->devolucoes->isNotEmpty())
        <div class="card mt-4 no-print">
            <div class="card-header"><h2 class="titulo-cartao">Devoluções desta venda</h2></div>
            <div class="card-body p-0">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Devolução</th>
                            <th>Produtos</th>
                            <th>Motivo</th>
                            <th>Destino</th>
                            <th class="text-end">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($venda->devolucoes as $devolucao)
                            <tr>
                                <td class="text-nowrap">#{{ $devolucao->id }}<div class="text-muted">{{ $devolucao->created_at->format('d/m/Y H:i') }}</div></td>
                                <td>
                                    @foreach ($devolucao->itens as $item)
                                        <div>{{ $item->quantidade }}× {{ $item->produto->nome }}</div>
                                    @endforeach
                                </td>
                                <td>{{ $devolucao->nomeMotivo() }}@if ($devolucao->observacao)<div class="text-muted">{{ $devolucao->observacao }}</div>@endif</td>
                                <td><span class="selo-destino {{ $devolucao->destino }}">{{ $devolucao->nomeDestino() }}</span></td>
                                <td class="text-end">R$ {{ number_format($devolucao->valor, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
