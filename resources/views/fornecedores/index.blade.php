@extends('layouts.app')

@section('title', 'Fornecedores')

@section('content')
    {{-- Cabeçalho: título, quantidade e botão de cadastro --}}
    <div class="cabeca-lista">
        <h1 class="h3">Fornecedores <span class="qtd" data-atualiza="qtd">{{ $fornecedores->total() }} {{ $fornecedores->total() === 1 ? 'fornecedor' : 'fornecedores' }} · clique numa linha para ver os produtos</span></h1>
        <div class="acoes-topo">
            <a href="{{ route('fornecedores.create') }}" class="btn btn-primary">+ Novo fornecedor</a>
        </div>
    </div>
    @include('partials.dica', ['chave' => 'fornecedores', 'texto' => 'Quem vende para você. Ligar o produto ao fornecedor ajuda na hora de repor.'])

    {{-- Filtros em pílulas + busca instantânea --}}
    <div class="filtros">
        <span data-atualiza="chips" style="display: contents">
            @include('partials.chips', ['chips' => [
                ['Todos', request()->fullUrlWithQuery(['filtro' => null, 'page' => null]), $contagem['todos'], ! $filtro, '', null],
                ['Com produtos', request()->fullUrlWithQuery(['filtro' => 'com', 'page' => null]), $contagem['com'], $filtro === 'com', '', null],
                ['Sem produtos', request()->fullUrlWithQuery(['filtro' => 'sem', 'page' => null]), $contagem['sem'], $filtro === 'sem', '', null],
            ]])
        </span>
        @include('partials.busca', ['placeholder' => 'Buscar por nome ou CNPJ...'])
    </div>

    {{-- Lista (esta parte é trocada pela busca instantânea) --}}
    <div data-atualiza="conteudo">
        <div data-lista-conteudo>
            @if ($fornecedores->isEmpty())
                @include('partials.vazio', ['nome' => 'fornecedor', 'nomePlural' => 'fornecedores', 'texto' => 'Cadastre quem vende para você e saiba de quem comprar na hora de repor.', 'rotaLista' => 'fornecedores.index', 'acao' => ['Cadastrar fornecedor', route('fornecedores.create')]])
            @else
                <table class="lista">
                    <thead>
                        <tr>
                            @include('partials.th-ordem', ['campo' => 'nome', 'titulo' => 'Fornecedor'])
                            <th>CNPJ</th>
                            <th>Contato</th>
                            @include('partials.th-ordem', ['campo' => 'produtos', 'titulo' => 'Produtos'])
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fornecedores as $fornecedor)
                            {{-- Linha principal: clique para abrir os detalhes --}}
                            <tr class="linha" data-expande tabindex="0" aria-expanded="false" style="--cor: var(--ceu)">
                                <td>
                                    <div class="item-lista">
                                        <span class="seta-abrir">›</span>
                                        <span class="avatar-lista">{{ \App\Support\Texto::iniciais($fornecedor->nome) }}</span>
                                        <div class="nome">{{ $fornecedor->nome }}</div>
                                    </div>
                                </td>
                                <td class="text-nowrap">{{ $fornecedor->cnpj ?? '-' }}</td>
                                <td>
                                    <div class="small">{{ $fornecedor->telefone ?? '-' }}</div>
                                    <div class="small text-muted">{{ $fornecedor->email }}</div>
                                </td>
                                <td><span class="pilula">{{ $fornecedor->produtos_count }} {{ $fornecedor->produtos_count === 1 ? 'produto' : 'produtos' }}</span></td>
                                <td>
                                    {{-- Ações: aparecem ao passar o mouse --}}
                                    <div class="acoes-linha">
                                        <a href="{{ route('fornecedores.edit', $fornecedor) }}" class="botao-icone amarelo" title="Editar">@include('partials.icone', ['nome' => 'lapis'])</a>
                                        @include('partials.excluir', ['rota' => route('fornecedores.destroy', $fornecedor), 'nome' => $fornecedor->nome, 'tipo' => 'fornecedor',
                                            'aviso' => match ($fornecedor->produtos_count) { 0 => '', 1 => '1 produto ficará sem fornecedor.', default => "{$fornecedor->produtos_count} produtos ficarão sem fornecedor." }])
                                    </div>
                                </td>
                            </tr>

                            {{-- Detalhes (aparecem ao clicar na linha) --}}
                            <tr class="detalhe">
                                <td colspan="5">
                                    <div class="grade-detalhe">
                                        <div class="bloco">
                                            <h6>Contato</h6>
                                            <div>{{ $fornecedor->nome }}</div>
                                            <div class="text-muted small">CNPJ: {{ $fornecedor->cnpj ?? 'não informado' }}</div>
                                            <div class="text-muted small">{{ $fornecedor->telefone ?? 'Sem telefone' }} · {{ $fornecedor->email ?? 'sem e-mail' }}</div>
                                        </div>
                                        <div class="bloco">
                                            <h6>Produtos fornecidos</h6>
                                            @forelse ($fornecedor->produtos as $produto)
                                                <div class="d-flex justify-content-between small py-1">
                                                    <span>{{ $produto->nome }}</span>
                                                    <span class="text-muted">{{ $produto->estoque }} un.</span>
                                                </div>
                                            @empty
                                                <div class="vazio">Nenhum produto deste fornecedor.</div>
                                            @endforelse
                                        </div>
                                        <div class="bloco">
                                            <h6>Atalhos</h6>
                                            <div class="d-flex flex-wrap gap-2">
                                                <a href="{{ route('estoque.create') }}" class="btn btn-sm btn-outline-primary">Registrar compra</a>
                                                <a href="{{ route('fornecedores.edit', $fornecedor) }}" class="btn btn-sm btn-secondary">Editar</a>
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
            <div class="mt-3">{{ $fornecedores->links() }}</div>
        </div>
    </div>
@endsection
