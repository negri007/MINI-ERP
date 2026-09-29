{{--
    Mini gráfico de linha (sem eixos) para mostrar tendência.
    Uso: @include('partials.sparkline', ['valores' => [1, 3, 2, 5]])
--}}
@php
    $maximo = max(max($valores), 1);
    $passo = count($valores) > 1 ? 200 / (count($valores) - 1) : 0;
    $pontos = collect($valores)->map(fn ($v, $i) => round($i * $passo, 1).','.round(44 - $v / $maximo * 38, 1))->implode(' ');
    $ultimo = explode(',', collect(explode(' ', $pontos))->last());
@endphp
<svg class="sparkline" viewBox="-4 0 208 50" preserveAspectRatio="none" aria-hidden="true">
    <polyline points="{{ $pontos }}"/>
    <circle cx="{{ $ultimo[0] }}" cy="{{ $ultimo[1] }}" r="4"/>
</svg>
