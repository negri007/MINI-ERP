{{--
    Botão "Excluir" (ícone de lixeira) com confirmação no modal.
    Uso: @include('partials.excluir', ['rota' => ..., 'nome' => ..., 'tipo' => 'categoria'])
    Opcionais:
      'aviso'    => consequência a mais (ex.: "3 produtos ficarão sem fornecedor.")
      'bloqueio' => motivo para NÃO poder excluir; o modal explica e não mostra o botão Excluir
      'link'     => [texto, url] de um atalho útil no bloqueio (ex.: ['Ver produtos', ...])
    O servidor continua conferindo tudo: isto só avisa antes.
--}}
@php($tipo = $tipo ?? null)
<form action="{{ $rota }}" method="POST" class="d-inline"
      @if (! empty($bloqueio))
          data-bloqueio="{{ $bloqueio }}"
          @isset($link) data-bloqueio-link="{{ $link[1] }}" data-bloqueio-link-texto="{{ $link[0] }}" @endisset
      @else
          data-confirmar="Excluir &quot;{{ $nome }}&quot;? {{ $aviso ?? '' }} Não dá para desfazer."
          data-confirmar-titulo="Excluir {{ $tipo ?? 'registro' }}?"
          data-confirmar-sim="Excluir {{ $tipo ?? '' }}"
          data-confirmar-nao="Manter {{ $tipo ?? '' }}"
      @endif>
    @csrf
    @method('DELETE')
    <button type="submit" class="botao-icone perigo" title="Excluir" aria-label="Excluir {{ $nome }}">@include('partials.icone', ['nome' => 'lixo'])</button>
</form>
