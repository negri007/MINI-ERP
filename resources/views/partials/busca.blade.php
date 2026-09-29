{{--
    Busca instantânea: filtra enquanto você digita (o JavaScript recarrega só a lista).
    Mantém os outros filtros da URL (categoria, ordem...). Sem JavaScript, funciona com Enter.
    Uso: @include('partials.busca', ['placeholder' => 'Buscar por nome...'])
--}}
<form method="GET" class="busca-viva" data-busca-viva role="search">
    {{-- Filtros atuais (categoria, ordem...), atualizados junto com a lista --}}
    <span data-atualiza="busca-filtros">
        @foreach (request()->except(['busca', 'page']) as $nome => $valor)
            @if (is_string($valor))
                <input type="hidden" name="{{ $nome }}" value="{{ $valor }}">
            @endif
        @endforeach
    </span>
    @include('partials.icone', ['nome' => 'busca'])
    <input type="search" name="busca" value="{{ request('busca') }}" class="form-control" placeholder="{{ $placeholder ?? 'Buscar...' }}" autocomplete="off" aria-label="Buscar">
</form>
