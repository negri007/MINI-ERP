@extends('layouts.app')

@section('title', 'Vendas')

@section('content')
    {{-- Cabeçalho: título, quantidade e "Nova venda" (a ação principal desta tela, em amarelo) --}}
    <div class="cabeca-lista">
        <h1 class="h3">Vendas <span class="qtd"><span role="status"><span data-atualiza="qtd">{{ $vendas->total() }} {{ $vendas->total() === 1 ? 'venda' : 'vendas' }}</span></span><span class="dica-lista"> · clique numa venda para ver os itens</span></span></h1>
        <div class="acoes-topo">
            <a href="{{ route('vendas.create') }}" class="btn btn-primary">+ Nova venda</a>
        </div>
    </div>
    @include('partials.dica', ['chave' => 'vendas', 'texto' => 'Venda registrada não se edita: se errar, cancele e registre de novo.'])

    {{-- Resumo do que está filtrado (muda junto com os filtros) --}}
    <div class="resumo-lista" data-atualiza="resumo">
        <div class="mini"><small>Faturamento no filtro</small><b>R$ {{ number_format($resumo['faturamento'], 2, ',', '.') }}</b></div>
        <div class="mini"><small>Vendas concluídas</small><b>{{ $resumo['concluidas'] }}</b></div>
        <div class="mini"><small>Canceladas</small><b>{{ $resumo['canceladas'] }}</b></div>
        <div class="mini"><small>Ticket médio</small><b>R$ {{ number_format($resumo['ticket_medio'], 2, ',', '.') }}</b></div>
    </div>

    {{-- Filtros: período e situação em pílulas + busca instantânea --}}
    <div class="filtros">
        <span data-atualiza="chips" style="display: contents">
            @include('partials.chips', ['chips' => [
                ['Todas', request()->fullUrlWithQuery(['periodo' => null, 'page' => null]), $contagem['todos'], ! $periodo, '', null],
                ['Hoje', request()->fullUrlWithQuery(['periodo' => 'hoje', 'page' => null]), $contagem['hoje'], $periodo === 'hoje', '', null],
                ['7 dias', request()->fullUrlWithQuery(['periodo' => '7dias', 'page' => null]), $contagem['7dias'], $periodo === '7dias', '', null],
                ['Este mês', request()->fullUrlWithQuery(['periodo' => 'mes', 'page' => null]), $contagem['mes'], $periodo === 'mes', '', null],
            ]])
            <span class="chips-separador"></span>
            @include('partials.chips', ['chips' => [
                ['✓ Concluídas', request()->fullUrlWithQuery(['status' => $status === 'concluida' ? null : 'concluida', 'page' => null]), $contagem['concluida'], $status === 'concluida', '', null],
                ['× Canceladas', request()->fullUrlWithQuery(['status' => $status === 'cancelada' ? null : 'cancelada', 'page' => null]), $contagem['cancelada'], $status === 'cancelada', 'alerta', null],
            ]])
        </span>
        @include('partials.busca', ['placeholder' => 'Cliente, CPF ou nº da venda...'])
    </div>

    {{-- Lista (esta parte é trocada pela busca instantânea) --}}
    <div data-atualiza="conteudo">
        <div data-lista-conteudo>
            @if ($vendas->isEmpty())
                @include('partials.vazio', ['nome' => 'venda', 'nomePlural' => 'vendas', 'feminino' => true, 'texto' => 'Registre a primeira venda: o estoque baixa sozinho e ela aparece no Dashboard.', 'rotaLista' => 'vendas.index', 'acao' => ['Registrar venda', route('vendas.create')]])
            @else
                <table class="lista">
                    <thead>
                        <tr>
                            @include('partials.th-ordem', ['campo' => 'numero', 'titulo' => 'Venda'])
                            @include('partials.th-ordem', ['campo' => 'cliente', 'titulo' => 'Cliente'])
                            @include('partials.th-ordem', ['campo' => 'data', 'titulo' => 'Data'])
                            <th class="text-center">Itens</th>
                            <th>Situação</th>
                            <th>Pagamento</th>
                            @include('partials.th-ordem', ['campo' => 'total', 'titulo' => 'Total', 'classe' => 'text-end'])
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($vendas as $venda)
                            {{-- Linha principal: clique na linha (mouse) ou no botão da seta (teclado) para abrir os itens --}}
                            <tr class="linha" data-expande>
                                <td class="text-nowrap"><button type="button" class="seta-abrir" aria-expanded="false" aria-controls="detalhe-{{ $venda->id }}" aria-label="Ver itens da venda #{{ str_pad($venda->id, 4, '0', STR_PAD_LEFT) }}"><span aria-hidden="true">›</span></button><span class="valor-lista">#{{ str_pad($venda->id, 4, '0', STR_PAD_LEFT) }}</span></td>
                                <td>
                                    <div class="item-lista">
                                        <span class="avatar-lista redondo">{{ \App\Support\Texto::iniciais($venda->cliente->nome) }}</span>
                                        <div class="nome">{{ $venda->cliente->nome }}</div>
                                    </div>
                                </td>
                                <td>{{ $venda->data->format('d/m/Y') }}</td>
                                <td class="text-center">{{ $venda->itens_count }}</td>
                                <td>
                                    @if ($venda->estaCancelada())
                                        <span class="badge bg-danger">Cancelada</span>
                                    @else
                                        <span class="badge bg-success">Concluída</span>
                                    @endif
                                </td>
                                <td class="{{ $venda->forma_pagamento ? '' : 'text-muted' }}">{{ $venda->nomeFormaPagamento() }}</td>
                                <td class="text-end">
                                    <span class="valor-lista {{ $venda->estaCancelada() ? 'riscado' : '' }}">R$ {{ number_format($venda->total, 2, ',', '.') }}</span>
                                    @if ($venda->desconto > 0)
                                        <small class="d-block text-muted">desconto R$ {{ number_format($venda->desconto, 2, ',', '.') }}</small>
                                    @endif
                                </td>
                                <td>
                                    {{-- Ações: aparecem ao passar o mouse --}}
                                    <div class="acoes-linha">
                                        <a href="{{ route('vendas.show', $venda) }}" class="botao-icone amarelo" title="Abrir venda">@include('partials.icone', ['nome' => 'olho'])</a>
                                    </div>
                                </td>
                            </tr>

                            {{-- Detalhes: itens, cliente e ações --}}
                            <tr class="detalhe" id="detalhe-{{ $venda->id }}">
                                <td colspan="8">
                                    <div class="grade-detalhe">
                                        <div class="bloco">
                                            <h2>Itens da venda</h2>
                                            <div class="itens-venda">
                                                @foreach ($venda->itens as $item)
                                                    <div><span>{{ $item->quantidade }}× {{ $item->produto->nome }}</span><b>R$ {{ number_format($item->subtotal, 2, ',', '.') }}</b></div>
                                                @endforeach
                                            </div>
                                            @if ($venda->observacao)
                                                <div class="text-muted small mt-2">Obs.: {{ $venda->observacao }}</div>
                                            @endif
                                        </div>
                                        <div class="bloco">
                                            <h2>Cliente</h2>
                                            <div>{{ $venda->cliente->nome }}</div>
                                            <div class="text-muted small">{{ $venda->cliente->cpf_cnpj ?? 'Sem CPF/CNPJ' }}{{ $venda->cliente->telefone ? ' · '.$venda->cliente->telefone : '' }}</div>
                                            <div class="text-muted small">{{ $venda->cliente->compras_no_mes }} {{ $venda->cliente->compras_no_mes === 1 ? 'compra' : 'compras' }} este mês</div>
                                        </div>
                                        <div class="bloco">
                                            <h2>Ações</h2>
                                            <div class="d-flex flex-wrap gap-2">
                                                <a href="{{ route('vendas.show', $venda) }}" class="btn btn-sm btn-outline-primary">Abrir venda</a>
                                                @unless ($venda->estaCancelada())
                                                    <form action="{{ route('vendas.cancelar', $venda) }}" method="POST"
                                                          data-confirmar="Cancelar a venda #{{ $venda->id }}? Os produtos voltam para o estoque e a venda sai do faturamento."
                                                          data-confirmar-titulo="Cancelar venda?" data-confirmar-sim="Cancelar venda" data-confirmar-nao="Manter venda">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Cancelar</button>
                                                    </form>
                                                @endunless
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            {{-- Links de paginação --}}
            <div class="mt-3">{{ $vendas->links() }}</div>
        </div>
    </div>
@endsection
