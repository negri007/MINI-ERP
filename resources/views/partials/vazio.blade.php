{{--
    Tela vazia que explica o próximo passo, com o botão da ação.
    Dois casos:
      - nada cadastrado ainda: diz o que vai aparecer aqui e oferece cadastrar;
      - busca ou filtro sem resultado: mostra o que foi buscado e oferece limpar os filtros.
    Uso:
      @include('partials.vazio', [
          'nome' => 'produto', 'nomePlural' => 'produtos', 'feminino' => false,
          'texto' => 'Cadastre o que você vende...',          // texto quando não há nenhum
          'rotaLista' => 'produtos.index',                     // para "Limpar filtros"
          'acao' => ['Cadastrar produto', route('produtos.create')],
      ])
--}}
@php
    // Tem filtro na URL? (paginação, ordem e modo de visão não contam)
    $filtrando = collect(request()->except(['page', 'ordem', 'dir', 'visao']))->filter(fn ($v) => filled($v))->isNotEmpty();
    $nenhum = ($feminino ?? false) ? 'Nenhuma' : 'Nenhum';
    $busca = trim((string) request('busca'));
@endphp
<section class="lista-vazia" aria-labelledby="vazio-titulo">
    @if ($filtrando)
        <h2 class="h5" id="vazio-titulo">
            {{ $nenhum }} {{ $nome }} {{ $busca !== '' ? "com \"{$busca}\"" : 'neste filtro' }}
        </h2>
        <p>Confira a digitação ou limpe os filtros para ver {{ ($feminino ?? false) ? 'todas' : 'todos' }} {{ ($feminino ?? false) ? 'as' : 'os' }} {{ $nomePlural }}.</p>
        <div class="acoes-vazio">
            <a href="{{ route($rotaLista) }}" class="btn btn-secondary">Limpar filtros</a>
            @isset($acao)
                <a href="{{ $acao[1] }}" class="btn btn-outline-primary">{{ $acao[0] }}</a>
            @endisset
        </div>
    @else
        <h2 class="h5" id="vazio-titulo">{{ $nenhum }} {{ $nome }} ainda</h2>
        <p>{{ $texto }}</p>
        @isset($acao)
            <div class="acoes-vazio">
                <a href="{{ $acao[1] }}" class="btn btn-primary">{{ $acao[0] }}</a>
            </div>
        @endisset
    @endif
</section>
