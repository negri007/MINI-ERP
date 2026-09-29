{{-- Cabeçalho das telas de formulário: trilha ("Produtos › Novo") + título.
     Uso: @include('partials.cabeca-form', ['lista' => 'Produtos', 'rota' => 'produtos.index', 'titulo' => 'Novo produto'])
     Dica opcional abaixo do título: 'chaveDica' => 'produto-form', 'dica' => 'Texto curto' --}}
<nav class="trilha" aria-label="Você está em">
    <a href="{{ route($rota) }}">{{ $lista }}</a>
    <span aria-hidden="true">›</span>
    <span aria-current="page">{{ $titulo }}</span>
</nav>
<h1 class="h3 mb-3">{{ $titulo }}</h1>
@isset($dica)
    @include('partials.dica', ['chave' => $chaveDica, 'texto' => $dica])
@endisset
