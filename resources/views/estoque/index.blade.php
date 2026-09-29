@extends('layouts.app')

@section('title', 'Estoque')

@section('content')
    {{-- Cabeçalho: título, quantidade e botão de nova movimentação --}}
    <div class="cabeca-lista">
        <h1 class="h3">Movimentações de estoque <span class="qtd" data-atualiza="qtd">{{ $movimentacoes->total() }} {{ $movimentacoes->total() === 1 ? 'registro' : 'registros' }}</span></h1>
        <div class="acoes-topo">
            <a href="{{ route('estoque.create') }}" class="btn btn-primary">+ Nova movimentação</a>
        </div>
    </div>

    {{-- Filtros: tipo em pílulas + busca instantânea --}}
    <div class="filtros">
        <span data-atualiza="chips" style="display: contents">
            @include('partials.chips', ['chips' => [
                ['Todas', request()->fullUrlWithQuery(['tipo' => null, 'page' => null]), $contagem['todas'], ! $tipo, '', null],
                ['+ Entradas', request()->fullUrlWithQuery(['tipo' => 'entrada', 'page' => null]), $contagem['entrada'], $tipo === 'entrada', '', '#4fd08c'],
                ['− Saídas', request()->fullUrlWithQuery(['tipo' => 'saida', 'page' => null]), $contagem['saida'], $tipo === 'saida', '', '#ff8a8a'],
            ]])
        </span>
        @include('partials.busca', ['placeholder' => 'Produto ou motivo...'])
    </div>

    {{-- Lista (esta parte é trocada pela busca instantânea) --}}
    <div data-atualiza="conteudo">
        <div data-lista-conteudo>
            @if ($movimentacoes->isEmpty())
                <div class="lista-vazia">Nenhuma movimentação encontrada.</div>
            @else
                <table class="lista">
                    <thead>
                        <tr>
                            @include('partials.th-ordem', ['campo' => 'data', 'titulo' => 'Data/hora'])
                            @include('partials.th-ordem', ['campo' => 'produto', 'titulo' => 'Produto'])
                            <th>Tipo</th>
                            @include('partials.th-ordem', ['campo' => 'quantidade', 'titulo' => 'Quantidade', 'classe' => 'text-end'])
                            <th class="text-end">Saldo após</th>
                            <th>Motivo</th>
                            <th>Usuário</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($movimentacoes as $mov)
                            <tr class="linha" style="--cor: {{ $mov->produto->categoria->cor() }}">
                                <td class="text-nowrap">
                                    <div>{{ $mov->created_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $mov->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    <div class="item-lista">
                                        <span class="avatar-lista" style="width: 34px; height: 34px; font-size: .75rem">{{ \App\Support\Texto::iniciais($mov->produto->nome) }}</span>
                                        <div class="nome">{{ $mov->produto->nome }}</div>
                                    </div>
                                </td>
                                <td>
                                    @if ($mov->tipo === 'entrada')
                                        <span class="badge bg-success">Entrada</span>
                                    @else
                                        <span class="badge bg-danger">Saída</span>
                                    @endif
                                </td>
                                <td class="text-end valor-lista" style="color: {{ $mov->tipo === 'entrada' ? '#4fd08c' : '#ff8a8a' }}">{{ $mov->tipo === 'entrada' ? '+' : '−' }}{{ $mov->quantidade }}</td>
                                <td class="text-end">{{ $mov->estoque_apos }}</td>
                                <td>
                                    @if ($mov->venda_id)
                                        <a href="{{ route('vendas.show', $mov->venda_id) }}">{{ $mov->motivo }}</a>
                                    @else
                                        {{ $mov->motivo }}
                                    @endif
                                </td>
                                <td class="text-muted">{{ $mov->usuario->name ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            {{-- Links de paginação --}}
            <div class="mt-3">{{ $movimentacoes->links() }}</div>
        </div>
    </div>
@endsection
