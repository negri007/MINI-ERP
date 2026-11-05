@extends('layouts.app')

@section('title', 'Devoluções')

@section('content')
    {{-- Cabeçalho: título e quantidade. A devolução nasce numa venda, por isso não há botão "Nova" aqui. --}}
    <div class="cabeca-lista">
        <h1 class="h3">Devoluções <span class="qtd">{{ $devolucoes->total() }} {{ $devolucoes->total() === 1 ? 'registro' : 'registros' }}</span></h1>
        <div class="acoes-topo">
            <a href="{{ route('vendas.index') }}" class="btn btn-secondary">Ir para Vendas</a>
        </div>
    </div>
    @include('partials.dica', ['chave' => 'devolucoes', 'texto' => 'Para registrar uma devolução, abra a venda e clique em "Registrar devolução".'])

    {{-- Resumo geral: quanto voltou ao cliente e quanto virou prejuízo ou crédito com fornecedor --}}
    <div class="resumo-lista">
        <div class="mini"><small>Devolvido aos clientes</small><b>R$ {{ number_format($resumo['valor'], 2, ',', '.') }}</b></div>
        <div class="mini"><small>Devoluções</small><b>{{ $resumo['quantidade'] }}</b></div>
        <div class="mini"><small>Perda da loja</small><b>R$ {{ number_format($resumo['perda'], 2, ',', '.') }}</b></div>
        <div class="mini"><small>A devolver ao fornecedor</small><b>R$ {{ number_format($resumo['fornecedor'], 2, ',', '.') }}</b></div>
    </div>

    {{-- Filtro pelo destino do produto --}}
    <div class="filtros">
        @include('partials.chips', ['chips' => array_merge(
            [['Todas', request()->fullUrlWithQuery(['destino' => null, 'page' => null]), $resumo['quantidade'], ! $destino, '', null]],
            collect(\App\Models\Devolucao::DESTINOS)->map(fn ($nome, $chave) => [
                $nome, request()->fullUrlWithQuery(['destino' => $chave, 'page' => null]), (int) ($porDestino[$chave]->quantidade ?? 0), $destino === $chave, '',
                ['estoque' => '#4fd08c', 'perda' => '#ff8a8a', 'fornecedor' => '#f3c969'][$chave],
            ])->values()->all(),
        )])
    </div>

    <div data-lista-conteudo>
    @if ($devolucoes->isEmpty())
        @include('partials.vazio', ['nome' => 'devolução', 'nomePlural' => 'devoluções', 'feminino' => true, 'texto' => 'Quando um cliente devolver um produto, registre pela tela da venda. Tudo que voltou aparece aqui, com o motivo.', 'rotaLista' => 'devolucoes.index', 'acao' => ['Ver vendas', route('vendas.index')]])
    @else
        <table class="lista">
            <thead>
                <tr>
                    <th>Devolução</th>
                    <th>Venda e cliente</th>
                    <th>Produtos</th>
                    <th>Motivo</th>
                    <th>O que aconteceu com o produto</th>
                    <th class="text-end">Valor devolvido</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($devolucoes as $devolucao)
                    <tr class="linha">
                        <td class="text-nowrap">
                            <div class="fw-semibold">#{{ $devolucao->id }}</div>
                            <small class="text-muted">{{ $devolucao->created_at->format('d/m/Y H:i') }}</small>
                        </td>
                        <td>
                            <a href="{{ route('vendas.show', $devolucao->venda) }}">Venda #{{ str_pad($devolucao->venda_id, 4, '0', STR_PAD_LEFT) }}</a>
                            <div class="text-muted">{{ $devolucao->venda->cliente->nome }}</div>
                        </td>
                        <td>
                            <ul class="lista-simples">
                                @foreach ($devolucao->itens as $item)
                                    <li>{{ $item->quantidade }}× {{ $item->produto->nome }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td>
                            {{ $devolucao->nomeMotivo() }}
                            @if ($devolucao->observacao)
                                <div class="text-muted">{{ $devolucao->observacao }}</div>
                            @endif
                        </td>
                        <td><span class="selo-destino {{ $devolucao->destino }}">{{ $devolucao->nomeDestino() }}</span></td>
                        <td class="text-end valor-lista">R$ {{ number_format($devolucao->valor, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="mt-3">{{ $devolucoes->links() }}</div>
    </div>
@endsection
