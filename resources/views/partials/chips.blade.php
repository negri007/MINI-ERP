{{--
    Filtros em pílulas, com contagem.
    Uso: @include('partials.chips', ['chips' => [[rótulo, url, quantidade, ativo, classe extra, cor do pontinho], ...]])
    aria-current marca a pílula escolhida (antes só a cor verde mostrava isso).
--}}
@foreach ($chips as [$rotulo, $url, $qtd, $ativo, $extra, $cor])
    <a href="{{ $url }}" class="chip {{ $ativo ? 'ativo' : '' }} {{ $extra }}" data-link-lista @if ($ativo) aria-current="true" @endif>
        @if ($cor)<i style="background: {{ $cor }}" aria-hidden="true"></i>@endif
        {{ $rotulo }}
        @if (! is_null($qtd))<b>{{ $qtd }}</b>@endif
    </a>
@endforeach
