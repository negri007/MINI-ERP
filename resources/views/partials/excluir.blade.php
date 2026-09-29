{{-- Botão "Excluir" (ícone de lixeira) com confirmação: @include('partials.excluir', ['rota' => ..., 'nome' => ...]) --}}
<form action="{{ $rota }}" method="POST" class="d-inline" data-confirmar="Deseja realmente excluir &quot;{{ $nome }}&quot;?">
    @csrf
    @method('DELETE')
    <button type="submit" class="botao-icone perigo" title="Excluir" aria-label="Excluir {{ $nome }}">@include('partials.icone', ['nome' => 'lixo'])</button>
</form>
