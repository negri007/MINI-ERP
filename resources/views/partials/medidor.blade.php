{{--
    Medidor de estoque: barra de nível + traço no estoque mínimo + texto da situação.
    A cor mostra a situação, mas o texto (OK / Baixo / Esgotado) sempre acompanha,
    para não depender só da cor (acessibilidade).
    Uso: @include('partials.medidor', ['produto' => $produto])
--}}
@php($m = $produto->medidorEstoque())
<div class="medidor {{ $m['situacao'] }}" title="Estoque {{ $produto->estoque }} · mínimo {{ $produto->estoque_minimo }}">
    <div class="trilho">
        <div class="nivel" style="width: {{ max($m['nivel'], 2) }}%"></div>
        <div class="minimo" style="left: {{ $m['minimo'] }}%"></div>
    </div>
    <div class="legenda">
        <span class="situacao">{{ ['ok' => 'OK', 'baixo' => 'Baixo', 'esgotado' => 'Esgotado'][$m['situacao']] }} · {{ $produto->estoque }} un.</span>
        <span>mín. {{ $produto->estoque_minimo }}</span>
    </div>
</div>
