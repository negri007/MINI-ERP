@extends('layouts.app')

@section('title', 'Produtos')

@section('content')
    {{-- Cabeçalho: título, quantidade, Tabela | Vitrine e botão de cadastro --}}
    <div class="cabeca-lista">
        <h1 class="h3">Produtos <span class="qtd" data-atualiza="qtd">{{ $produtos->total() }} {{ $produtos->total() === 1 ? 'item' : 'itens' }}{{ $visao === 'tabela' ? ' · clique numa linha para ver detalhes' : '' }}</span></h1>
        <div class="acoes-topo">
            <div class="alterna-visao">
                <a href="{{ request()->fullUrlWithQuery(['visao' => null, 'page' => null]) }}" class="{{ $visao === 'tabela' ? 'ativo' : '' }}">@include('partials.icone', ['nome' => 'lista']) Tabela</a>
                <a href="{{ request()->fullUrlWithQuery(['visao' => 'vitrine', 'page' => null]) }}" class="{{ $visao === 'vitrine' ? 'ativo' : '' }}">@include('partials.icone', ['nome' => 'grade']) Vitrine</a>
            </div>
            <a href="{{ route('produtos.create') }}" class="btn btn-primary">+ Novo produto</a>
        </div>
    </div>
    @include('partials.dica', ['chave' => 'produtos', 'texto' => 'O que você vende. O estoque muda sozinho com vendas e movimentações.'])

    {{-- Filtros em pílulas (categorias com cor + estoque baixo) e busca instantânea --}}
    <div class="filtros">
        <span data-atualiza="chips" style="display: contents">
            @php
                $chips = [['Todos', request()->fullUrlWithQuery(['categoria_id' => null, 'page' => null]), $contagem['todos'], ! request('categoria_id'), '', null]];
                foreach ($categorias as $categoria) {
                    $chips[] = [$categoria->nome, request()->fullUrlWithQuery(['categoria_id' => $categoria->id, 'page' => null]), $porCategoria[$categoria->id] ?? 0, request('categoria_id') == $categoria->id, '', $categoria->cor()];
                }
                $baixo = request()->boolean('estoque_baixo');
                $chips[] = ['⚠ Estoque baixo', request()->fullUrlWithQuery(['estoque_baixo' => $baixo ? null : 1, 'page' => null]), $contagem['estoque_baixo'], $baixo, 'alerta', null];
            @endphp
            @include('partials.chips', ['chips' => $chips])
        </span>
        @include('partials.busca', ['placeholder' => 'Filtrar enquanto digita...'])
    </div>

    {{-- Lista (esta parte é trocada pela busca instantânea) --}}
    <div data-atualiza="conteudo">
        <div data-lista-conteudo>
            @if ($produtos->isEmpty())
                @include('partials.vazio', ['nome' => 'produto', 'nomePlural' => 'produtos', 'texto' => 'Cadastre o que você vende para registrar vendas e controlar o estoque.', 'rotaLista' => 'produtos.index', 'acao' => ['Cadastrar produto', route('produtos.create')]])

            @elseif ($visao === 'vitrine')
                {{-- Visualização em cartões --}}
                <div class="vitrine">
                    @foreach ($produtos as $produto)
                        <div class="cartao-produto" style="--cor: {{ $produto->categoria->cor() }}">
                            @if ($produto->estoque <= 0)
                                <span class="fita-esgotado">ESGOTADO</span>
                            @endif
                            <span class="avatar-lista">{{ \App\Support\Texto::iniciais($produto->nome) }}</span>
                            <div class="nome">{{ $produto->nome }}</div>
                            <div class="linha-info">
                                <span class="pilula"><i></i>{{ $produto->categoria->nome }}</span>
                                {{ $produto->fornecedor->nome ?? '' }}
                            </div>
                            <div class="preco"><small>R$</small>{{ number_format($produto->preco, 2, ',', '.') }}</div>
                            @include('partials.medidor', ['produto' => $produto])
                            <div class="rodape-cartao">
                                <a href="{{ route('estoque.create', ['produto_id' => $produto->id]) }}" class="botao-icone" title="Movimentar estoque">@include('partials.icone', ['nome' => 'caixa'])</a>
                                <a href="{{ route('produtos.edit', $produto) }}" class="botao-icone amarelo" title="Editar">@include('partials.icone', ['nome' => 'lapis'])</a>
                                @include('partials.excluir', ['rota' => route('produtos.destroy', $produto), 'nome' => $produto->nome])
                            </div>
                        </div>
                    @endforeach
                </div>

            @else
                {{-- Visualização em tabela --}}
                <table class="lista">
                    <thead>
                        <tr>
                            @include('partials.th-ordem', ['campo' => 'nome', 'titulo' => 'Produto'])
                            @include('partials.th-ordem', ['campo' => 'categoria', 'titulo' => 'Categoria'])
                            @include('partials.th-ordem', ['campo' => 'preco', 'titulo' => 'Preço'])
                            @include('partials.th-ordem', ['campo' => 'estoque', 'titulo' => 'Estoque'])
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($produtos as $produto)
                            @php($vendido = $vendas7dias[$produto->id])

                            {{-- Linha principal: clique para abrir os detalhes --}}
                            <tr class="linha" data-expande tabindex="0" aria-expanded="false" style="--cor: {{ $produto->categoria->cor() }}">
                                <td>
                                    <div class="item-lista">
                                        <span class="seta-abrir">›</span>
                                        <span class="avatar-lista">{{ \App\Support\Texto::iniciais($produto->nome) }}</span>
                                        <div>
                                            <div class="nome">{{ $produto->nome }}</div>
                                            <small>{{ collect([$produto->descricao, $produto->fornecedor->nome ?? null])->filter()->implode(' · ') ?: 'Sem descrição' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="pilula"><i></i>{{ $produto->categoria->nome }}</span></td>
                                <td class="valor-lista">R$ {{ number_format($produto->preco, 2, ',', '.') }}</td>
                                <td>@include('partials.medidor', ['produto' => $produto])</td>
                                <td>
                                    {{-- Ações: aparecem ao passar o mouse --}}
                                    <div class="acoes-linha">
                                        <a href="{{ route('estoque.create', ['produto_id' => $produto->id]) }}" class="botao-icone" title="Movimentar estoque">@include('partials.icone', ['nome' => 'caixa'])</a>
                                        <a href="{{ route('produtos.edit', $produto) }}" class="botao-icone amarelo" title="Editar">@include('partials.icone', ['nome' => 'lapis'])</a>
                                        @include('partials.excluir', ['rota' => route('produtos.destroy', $produto), 'nome' => $produto->nome])
                                    </div>
                                </td>
                            </tr>

                            {{-- Detalhes (aparecem ao clicar na linha) --}}
                            <tr class="detalhe">
                                <td colspan="5">
                                    <div class="grade-detalhe">
                                        <div class="bloco">
                                            <h6>Detalhes</h6>
                                            <div>{{ $produto->descricao ?: 'Sem descrição.' }}</div>
                                            <div class="text-muted small mt-1">
                                                Fornecedor: {{ $produto->fornecedor->nome ?? 'nenhum' }}{{ $produto->fornecedor?->telefone ? ' · '.$produto->fornecedor->telefone : '' }}
                                            </div>
                                            <div class="text-muted small">Cadastrado em {{ $produto->created_at->format('d/m/Y') }}</div>
                                        </div>
                                        <div class="bloco">
                                            <h6>Últimas movimentações</h6>
                                            @if ($produto->movimentacoes->isEmpty())
                                                <div class="vazio">Nenhuma movimentação.</div>
                                            @else
                                                <ul class="linha-tempo">
                                                    @foreach ($produto->movimentacoes as $mov)
                                                        <li>
                                                            <span class="q {{ $mov->tipo === 'entrada' ? 'mais' : 'menos' }}">{{ $mov->tipo === 'entrada' ? '+' : '−' }}{{ $mov->quantidade }}</span>
                                                            <span>{{ $mov->motivo }} · {{ $mov->created_at->isToday() ? 'hoje' : $mov->created_at->format('d/m') }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                        <div class="bloco">
                                            <h6>Vendido nos últimos 7 dias</h6>
                                            @include('partials.sparkline', ['valores' => $vendido['dias']])
                                            <div class="small text-muted">{{ $vendido['quantidade'] }} {{ $vendido['quantidade'] === 1 ? 'unidade' : 'unidades' }} · R$ {{ number_format($vendido['valor'], 2, ',', '.') }}</div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            {{-- Links de paginação --}}
            <div class="mt-3">{{ $produtos->links() }}</div>
        </div>
    </div>
@endsection
