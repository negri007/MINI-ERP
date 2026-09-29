{{--
    Filtros em pílulas, com contagem.
    Uso: @include('partials.chips', ['chips' => [[rótulo, url, quantidade, ativo, classe extra, cor do pontinho], ...]])
--}}
@foreach ($chips as [$rotulo, $url, $qtd, $ativo, $extra, $cor])
    <a href="{{ $url }}" class="chip {{ $ativo ? 'ativo' : '' }} {{ $extra }}" data-link-lista>
        @if ($cor)<i style="background: {{ $cor }}"></i>@endif
        {{ $rotulo }}
        @if (! is_null($qtd))<b>{{ $qtd }}</b>@endif
    </a>
@endforeach
