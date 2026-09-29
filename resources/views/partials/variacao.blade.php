{{--
    Selo de variação em % contra o mesmo período do mês passado.
    O triângulo é só visual; o leitor de tela ouve "Alta de" / "Queda de".
    Uso: @include('partials.variacao', ['valor' => $mes['variacao']])
--}}
@if (! is_null($valor))
    <span class="variacao {{ $valor >= 0 ? 'sobe' : 'desce' }}">
        <span aria-hidden="true">{{ $valor >= 0 ? '▲' : '▼' }}</span>
        <span class="visually-hidden">{{ $valor >= 0 ? 'Alta de' : 'Queda de' }}</span>
        {{ number_format(abs($valor), 1, ',', '.') }}%
    </span>
@endif
