{{-- Régua de estoque: barra de nível + traço no estoque mínimo. Uso: @include('partials.regua', ['produto' => $produto]) --}}
@php($r = $produto->reguaEstoque())
<span class="regua {{ $produto->estaComEstoqueBaixo() ? 'baixo' : '' }}" title="Estoque {{ $produto->estoque }} · mínimo {{ $produto->estoque_minimo }}">
    <span class="nivel" style="width: {{ $r['nivel'] }}%"></span>
    <span class="minimo" style="left: {{ $r['minimo'] }}%"></span>
</span>
