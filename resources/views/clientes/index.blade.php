@extends('layouts.app')

@section('title', 'Clientes')

@section('content')
    {{-- Cabeçalho: título, quantidade e botão de cadastro --}}
    <div class="cabeca-lista">
        <h1 class="h3">Clientes <span class="qtd" data-atualiza="qtd">{{ $clientes->total() }} {{ $clientes->total() === 1 ? 'cliente' : 'clientes' }} · clique numa linha para ver as compras</span></h1>
        <div class="acoes-topo">
            <a href="{{ route('clientes.create') }}" class="btn btn-primary">+ Novo cliente</a>
        </div>
    </div>
    @include('partials.dica', ['chave' => 'clientes', 'texto' => 'Quem compra de você. Para vender sem identificar, use "Consumidor final".'])

    {{-- Filtros em pílulas + busca instantânea --}}
    <div class="filtros">
        <span data-atualiza="chips" style="display: contents">
            @include('partials.chips', ['chips' => [
                ['Todos', request()->fullUrlWithQuery(['filtro' => null, 'page' => null]), $contagem['todos'], ! $filtro, '', null],
                ['Já compraram', request()->fullUrlWithQuery(['filtro' => 'com', 'page' => null]), $contagem['com'], $filtro === 'com', '', null],
                ['Ainda não compraram', request()->fullUrlWithQuery(['filtro' => 'sem', 'page' => null]), $contagem['sem'], $filtro === 'sem', '', null],
            ]])
        </span>
        @include('partials.busca', ['placeholder' => 'Nome, CPF/CNPJ ou e-mail...'])
    </div>

    {{-- Lista (esta parte é trocada pela busca instantânea) --}}
    <div data-atualiza="conteudo">
        <div data-lista-conteudo>
            @if ($clientes->isEmpty())
                @include('partials.vazio', ['nome' => 'cliente', 'nomePlural' => 'clientes', 'texto' => 'Cadastre quem compra de você para ver o histórico de compras. Para vender sem identificar, use Consumidor final.', 'rotaLista' => 'clientes.index', 'acao' => ['Cadastrar cliente', route('clientes.create')]])
            @else
                <table class="lista">
                    <thead>
                        <tr>
                            @include('partials.th-ordem', ['campo' => 'nome', 'titulo' => 'Cliente'])
                            <th>CPF/CNPJ</th>
                            <th>Contato</th>
                            @include('partials.th-ordem', ['campo' => 'compras', 'titulo' => 'Compras', 'classe' => 'text-center'])
                            @include('partials.th-ordem', ['campo' => 'total', 'titulo' => 'Total gasto', 'classe' => 'text-end'])
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($clientes as $cliente)
                            {{-- Linha principal: clique para abrir os detalhes --}}
                            <tr class="linha" data-expande tabindex="0" aria-expanded="false" style="--cor: var(--ceu)">
                                <td>
                                    <div class="item-lista">
                                        <span class="seta-abrir">›</span>
                                        <span class="avatar-lista redondo">{{ \App\Support\Texto::iniciais($cliente->nome) }}</span>
                                        <div class="nome">{{ $cliente->nome }}</div>
                                    </div>
                                </td>
                                <td class="text-nowrap">{{ $cliente->cpf_cnpj ?? '-' }}</td>
                                <td>
                                    <div class="small">{{ $cliente->telefone ?? '-' }}</div>
                                    <div class="small text-muted">{{ $cliente->email }}</div>
                                </td>
                                <td class="text-center">{{ $cliente->vendas_count }}</td>
                                <td class="text-end valor-lista">R$ {{ number_format($cliente->total_gasto ?? 0, 2, ',', '.') }}</td>
                                <td>
                                    {{-- Ações: aparecem ao passar o mouse --}}
                                    <div class="acoes-linha">
                                        <a href="{{ route('clientes.edit', $cliente) }}" class="botao-icone amarelo" title="Editar">@include('partials.icone', ['nome' => 'lapis'])</a>
                                        @include('partials.excluir', ['rota' => route('clientes.destroy', $cliente), 'nome' => $cliente->nome, 'tipo' => 'cliente',
                                            'bloqueio' => $cliente->motivoParaNaoExcluir(), 'link' => ['Ver vendas do cliente', route('vendas.index', ['cliente' => $cliente->id])]])
                                    </div>
                                </td>
                            </tr>

                            {{-- Detalhes (aparecem ao clicar na linha) --}}
                            <tr class="detalhe">
                                <td colspan="6">
                                    <div class="grade-detalhe">
                                        <div class="bloco">
                                            <h6>Contato</h6>
                                            <div>{{ $cliente->nome }}</div>
                                            <div class="text-muted small">{{ $cliente->telefone ?? 'Sem telefone' }} · {{ $cliente->email ?? 'sem e-mail' }}</div>
                                            <div class="text-muted small">Cliente desde {{ $cliente->created_at->format('d/m/Y') }}</div>
                                        </div>
                                        <div class="bloco">
                                            <h6>Últimas compras</h6>
                                            @forelse ($cliente->vendas as $venda)
                                                <div class="d-flex justify-content-between small py-1">
                                                    <a href="{{ route('vendas.show', $venda) }}">#{{ str_pad($venda->id, 4, '0', STR_PAD_LEFT) }} · {{ $venda->data->format('d/m') }}</a>
                                                    <span class="{{ $venda->estaCancelada() ? 'text-muted text-decoration-line-through' : '' }}">R$ {{ number_format($venda->total, 2, ',', '.') }}</span>
                                                </div>
                                            @empty
                                                <div class="vazio">Ainda não comprou.</div>
                                            @endforelse
                                        </div>
                                        <div class="bloco">
                                            <h6>Atalhos</h6>
                                            <div class="d-flex flex-wrap gap-2">
                                                <a href="{{ route('vendas.create') }}" class="btn btn-sm btn-outline-primary">Nova venda</a>
                                                <a href="{{ route('vendas.index', ['busca' => $cliente->nome]) }}" class="btn btn-sm btn-secondary">Ver todas as compras</a>
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
            <div class="mt-3">{{ $clientes->links() }}</div>
        </div>
    </div>
@endsection
