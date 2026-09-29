{{--
    Dica curta abaixo do título da tela: para que ela serve ou o erro mais comum.
    "Entendi" esconde a dica desta tela (a escolha fica guardada no navegador);
    o botão "Mostrar dica" traz de volta. O texto fica logo depois do título,
    então o leitor de tela lê na ordem natural da página.
    Uso: @include('partials.dica', ['chave' => 'produtos', 'texto' => 'O que você vende...'])
--}}
<div class="dica" data-dica="{{ $chave }}">
    <p class="dica-texto" id="dica-{{ $chave }}">
        <span class="dica-marca" aria-hidden="true">?</span>
        <span>{{ $texto }}</span>
    </p>
    <button type="button" class="dica-fechar" data-dica-fechar>Entendi</button>
    <button type="button" class="dica-abrir" data-dica-abrir>
        <span class="dica-marca" aria-hidden="true">?</span> Mostrar dica
    </button>
</div>
