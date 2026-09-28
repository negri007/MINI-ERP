{{-- Botão "Excluir" reaproveitável: @include('partials.excluir', ['rota' => ..., 'nome' => ...]) --}}
<form action="{{ $rota }}" method="POST" class="d-inline" data-confirmar="Deseja realmente excluir &quot;{{ $nome }}&quot;?">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
</form>
