{{-- Régua de estoque: barra de nível + traço no estoque mínimo. Uso: @include('partials.regua', ['produto' => $produto]) --}}
@php($r = $produto->reguaEstoque())
<span class="regua {{ $produto->estaComEstoqueBaixo() ? 'baixo' : '' }}" role="img" title="Estoque {{ $produto->estoque }} · mínimo {{ $produto->estoque_minimo }}"
      aria-label="Estoque {{ $produto->estoque }}, mínimo {{ $produto->estoque_minimo }}{{ $produto->estaComEstoqueBaixo() ? ', abaixo do mínimo' : '' }}">
    <span class="nivel" style="width: {{ $r['nivel'] }}%"></span>
    <span class="minimo" style="left: {{ $r['minimo'] }}%"></span>
</span>
