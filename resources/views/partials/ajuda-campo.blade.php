{{--
    Botão "?" ao lado do rótulo de um campo que costuma confundir.
    Abre e fecha um texto curto logo abaixo do campo (funciona no clique, no toque e no teclado).
    O campo aponta para esse texto com aria-describedby, então o leitor de tela lê a explicação
    mesmo com ela fechada.
    Uso: @include('partials.ajuda-campo', ['id' => 'ajuda-estoque-minimo', 'campo' => 'estoque mínimo'])
         e, abaixo do campo: <p class="ajuda-texto" id="ajuda-estoque-minimo" hidden>Explicação.</p>
--}}
<button type="button" class="ajuda-botao" data-ajuda aria-expanded="false" aria-controls="{{ $id }}" aria-label="O que é {{ $campo }}?">?</button>
