{{-- Cabeçalho das telas de formulário: trilha ("Produtos › Novo") + título + aviso dos campos obrigatórios.
     Uso: @include('partials.cabeca-form', ['lista' => 'Produtos', 'rota' => 'produtos.index', 'titulo' => 'Novo produto']) --}}
<nav class="trilha" aria-label="Você está em">
    <a href="{{ route($rota) }}">{{ $lista }}</a>
    <span aria-hidden="true">›</span>
    <span aria-current="page">{{ $titulo }}</span>
</nav>
<h1 class="h3 mb-1">{{ $titulo }}</h1>
{{-- Explica o asterisco vermelho (o leitor de tela já recebe "obrigatório" pelo aria-required de cada campo) --}}
<p class="aviso-obrigatorio" aria-hidden="true">Campos com <span class="text-danger">*</span> são obrigatórios.</p>
