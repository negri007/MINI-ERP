@extends('layouts.app')

@section('title', 'Categorias')

@section('content')
    {{-- Cabeçalho: título, quantidade e botão de cadastro --}}
    <div class="cabeca-lista">
        <h1 class="h3">Categorias <span class="qtd" data-atualiza="qtd">{{ $categorias->total() }} {{ $categorias->total() === 1 ? 'categoria' : 'categorias' }} · clique numa linha para ver os produtos</span></h1>
        <div class="acoes-topo">
            <a href="{{ route('categorias.create') }}" class="btn btn-primary">+ Nova categoria</a>
        </div>
    </div>
    @include('partials.dica', ['chave' => 'categorias', 'texto' => 'Agrupam os produtos (ex.: Bebidas, Limpeza). Todo produto precisa de uma.'])

    {{-- Filtros em pílulas + busca instantânea --}}
    <div class="filtros">
        <span data-atualiza="chips" style="display: contents">
            @include('partials.chips', ['chips' => [
                ['Todas', request()->fullUrlWithQuery(['filtro' => null, 'page' => null]), $contagem['todas'], ! $filtro, '', null],
                ['Com produtos', request()->fullUrlWithQuery(['filtro' => 'com', 'page' => null]), $contagem['com'], $filtro === 'com', '', null],
                ['Vazias', request()->fullUrlWithQuery(['filtro' => 'vazias', 'page' => null]), $contagem['vazias'], $filtro === 'vazias', '', null],
            ]])
        </span>
        @include('partials.busca', ['placeholder' => 'Buscar categoria...'])
    </div>

    {{-- Lista (esta parte é trocada pela busca instantânea) --}}
    <div data-atualiza="conteudo">
        <div data-lista-conteudo>
            @if ($categorias->isEmpty())
                @include('partials.vazio', ['nome' => 'categoria', 'nomePlural' => 'categorias', 'feminino' => true, 'texto' => 'Crie categorias para organizar os produtos, como Bebidas ou Limpeza.', 'rotaLista' => 'categorias.index', 'acao' => ['Cadastrar categoria', route('categorias.create')]])
            @else
                <table class="lista">
                    <thead>
                        <tr>
                            @include('partials.th-ordem', ['campo' => 'nome', 'titulo' => 'Categoria'])
                            @include('partials.th-ordem', ['campo' => 'produtos', 'titulo' => 'Produtos'])
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categorias as $categoria)
                            {{-- Linha principal: clique para abrir os detalhes --}}
                            <tr class="linha" data-expande tabindex="0" aria-expanded="false" style="--cor: {{ $categoria->cor() }}">
                                <td>
                                    <div class="item-lista">
                                        <span class="seta-abrir">›</span>
                                        <span class="avatar-lista">{{ \App\Support\Texto::iniciais($categoria->nome) }}</span>
                                        <div>
                                            <div class="nome">{{ $categoria->nome }}</div>
                                            <small>{{ $categoria->descricao ?: 'Sem descrição' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="pilula"><i></i>{{ $categoria->produtos_count }} {{ $categoria->produtos_count === 1 ? 'produto' : 'produtos' }}</span></td>
                                <td>
                                    {{-- Ações: aparecem ao passar o mouse --}}
                                    <div class="acoes-linha">
                                        <a href="{{ route('categorias.edit', $categoria) }}" class="botao-icone amarelo" title="Editar">@include('partials.icone', ['nome' => 'lapis'])</a>
                                        @include('partials.excluir', ['rota' => route('categorias.destroy', $categoria), 'nome' => $categoria->nome, 'tipo' => 'categoria',
                                            'bloqueio' => $categoria->motivoParaNaoExcluir(), 'link' => ['Ver produtos', route('produtos.index', ['categoria_id' => $categoria->id])]])
                                    </div>
                                </td>
                            </tr>

                            {{-- Detalhes (aparecem ao clicar na linha) --}}
                            <tr class="detalhe">
                                <td colspan="3">
                                    <div class="grade-detalhe">
                                        <div class="bloco">
                                            <h6>Sobre</h6>
                                            <div>{{ $categoria->descricao ?: 'Sem descrição.' }}</div>
                                            <div class="text-muted small mt-1">Criada em {{ $categoria->created_at->format('d/m/Y') }}</div>
                                        </div>
                                        <div class="bloco">
                                            <h6>Produtos</h6>
                                            @forelse ($categoria->produtos as $produto)
                                                <div class="d-flex justify-content-between small py-1">
                                                    <span>{{ $produto->nome }}</span>
                                                    <span class="text-muted">R$ {{ number_format($produto->preco, 2, ',', '.') }}</span>
                                                </div>
                                            @empty
                                                <div class="vazio">Nenhum produto nesta categoria.</div>
                                            @endforelse
                                        </div>
                                        <div class="bloco">
                                            <h6>Atalhos</h6>
                                            <div class="d-flex flex-wrap gap-2">
                                                <a href="{{ route('produtos.index', ['categoria_id' => $categoria->id]) }}" class="btn btn-sm btn-outline-primary">Ver produtos</a>
                                                <a href="{{ route('categorias.edit', $categoria) }}" class="btn btn-sm btn-secondary">Editar</a>
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
            <div class="mt-3">{{ $categorias->links() }}</div>
        </div>
    </div>
@endsection
