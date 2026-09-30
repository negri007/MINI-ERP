{{--
    Cabeçalho de coluna que ordena a lista ao ser clicado.
    Uso: @include('partials.th-ordem', ['campo' => 'preco', 'titulo' => 'Preço'])
    Precisa das variáveis $ordem e $dir, que o controller envia para a view.
    aria-sort conta ao leitor de tela qual coluna ordena a lista e em que sentido;
    a setinha é só visual (aria-hidden), senão ele leria "seta para cima".
--}}
@php
    $ativa = $ordem === $campo;
    // Clicar de novo na mesma coluna inverte a direção
    $novaDir = $ativa && $dir === 'asc' ? 'desc' : 'asc';
@endphp
<th class="ordenavel {{ $ativa ? 'ativa' : '' }} {{ $classe ?? '' }}" @if ($ativa) aria-sort="{{ $dir === 'asc' ? 'ascending' : 'descending' }}" @endif>
    <a href="{{ request()->fullUrlWithQuery(['ordem' => $campo, 'dir' => $novaDir, 'page' => null]) }}" data-link-lista>
        {{ $titulo }} <span class="seta" aria-hidden="true">{{ $ativa ? ($dir === 'asc' ? '↑' : '↓') : '↕' }}</span>
    </a>
</th>
