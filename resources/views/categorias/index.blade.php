@extends('layouts.app')

@section('title', 'Categorias')

@section('content')
    {{-- Cabeçalho: título, quantidade e botão de cadastro --}}
    <div class="cabeca-lista">
        <h1 class="h3">Categorias <span class="qtd"><span role="status"><span data-atualiza="qtd">{{ $categorias->total() }} {{ $categorias->total() === 1 ? 'categoria' : 'categorias' }}</span></span><span class="dica-lista"> · clique numa linha para ver os produtos</span></span></h1>
        <div class="acoes-topo">
            <a href="{{ route('categorias.create') }}" class="btn btn-primary">+ Nova categoria</a>
        </div>
    </div>

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
                <div class="lista-vazia">Nenhuma categoria encontrada.</div>
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
                            {{-- Linha principal: clique na linha (mouse) ou no botão da seta (teclado) para abrir os detalhes --}}
                            <tr class="linha" data-expande style="--cor: {{ $categoria->cor() }}">
                                <td>
                                    <div class="item-lista">
                                        <button type="button" class="seta-abrir" aria-expanded="false" aria-controls="detalhe-{{ $categoria->id }}" aria-label="Ver detalhes de {{ $categoria->nome }}"><span aria-hidden="true">›</span></button>
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
                                        @include('partials.excluir', ['rota' => route('categorias.destroy', $categoria), 'nome' => $categoria->nome])
                                    </div>
                                </td>
                            </tr>

                            {{-- Detalhes (aparecem ao clicar na linha) --}}
                            <tr class="detalhe" id="detalhe-{{ $categoria->id }}">
                                <td colspan="3">
                                    <div class="grade-detalhe">
                                        <div class="bloco">
                                            <h2>Sobre</h2>
                                            <div>{{ $categoria->descricao ?: 'Sem descrição.' }}</div>
                                            <div class="text-muted small mt-1">Criada em {{ $categoria->created_at->format('d/m/Y') }}</div>
                                        </div>
                                        <div class="bloco">
                                            <h2>Produtos</h2>
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
                                            <h2>Atalhos</h2>
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
