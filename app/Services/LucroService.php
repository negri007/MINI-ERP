<?php

namespace App\Services;

use App\Models\Venda;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// Lucro bruto das vendas concluídas de um período.
//
// Regra: só entram as vendas em que TODOS os itens têm custo guardado (custo_unitario).
// A venda inteira fica de fora quando falta custo em algum item, porque o desconto vale
// para a venda toda e não dá para calcular o lucro de uma venda pela metade.
// Nada é inventado: as vendas de fora são contadas e mostradas num aviso.
class LucroService
{
    /**
     * @param  Builder  $vendas  consulta de vendas já filtrada pelo período (ex.: Venda::whereDate(...))
     * @return array{lucro: float, faturamento: float, margem: ?float, com_custo: int, sem_custo: int}
     */
    public function calcular(Builder $vendas): array
    {
        $concluidas = (clone $vendas)->where('vendas.status', Venda::CONCLUIDA);

        // Venda sem custo = tem algum item com custo_unitario vazio
        $itemSemCusto = fn ($q) => $q->select(DB::raw(1))
            ->from('venda_itens')
            ->whereColumn('venda_itens.venda_id', 'vendas.id')
            ->whereNull('venda_itens.custo_unitario');

        $comCusto = (clone $concluidas)->whereNotExists($itemSemCusto);
        $semCusto = (clone $concluidas)->whereExists($itemSemCusto)->count();

        $faturamento = (float) (clone $comCusto)->sum('vendas.total');
        $custo = (float) DB::table('venda_itens')
            ->whereIn('venda_id', (clone $comCusto)->select('vendas.id'))
            ->sum(DB::raw('quantidade * custo_unitario'));

        $lucro = round($faturamento - $custo, 2);

        return [
            'lucro' => $lucro,
            'faturamento' => $faturamento,
            // margem sobre o faturamento das MESMAS vendas que entraram no lucro
            'margem' => $faturamento > 0 ? round($lucro / $faturamento * 100, 1) : null,
            'com_custo' => (clone $comCusto)->count(),
            'sem_custo' => $semCusto,
        ];
    }
}
