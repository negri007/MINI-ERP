{{--
    Custo de uma unidade do produto, ou "sem custo informado" (nunca mostramos zero inventado).
    Uso: @include('partials.custo', ['produto' => $produto])
--}}
@if (is_null($produto->custo))
    <small class="custo-produto sem-custo">sem custo informado</small>
@else
    <small class="custo-produto">custo R$ {{ number_format($produto->custo, 2, ',', '.') }}</small>
@endif
