{{--
    Cabeçalho de coluna que ordena a lista ao ser clicado.
    Uso: @include('partials.th-ordem', ['campo' => 'preco', 'titulo' => 'Preço'])
    Precisa das variáveis $ordem e $dir, que o controller envia para a view.
--}}
@php
    $ativa = $ordem === $campo;
    // Clicar de novo na mesma coluna inverte a direção
    $novaDir = $ativa && $dir === 'asc' ? 'desc' : 'asc';
@endphp
<th class="ordenavel {{ $ativa ? 'ativa' : '' }} {{ $classe ?? '' }}">
    <a href="{{ request()->fullUrlWithQuery(['ordem' => $campo, 'dir' => $novaDir, 'page' => null]) }}" data-link-lista>
        {{ $titulo }} <span class="seta">{{ $ativa ? ($dir === 'asc' ? '↑' : '↓') : '↕' }}</span>
    </a>
</th>
