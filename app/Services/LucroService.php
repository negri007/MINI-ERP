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

        // Faturamento líquido: o que foi devolvido ao cliente não conta
        $faturamento = (float) (clone $comCusto)->sum(DB::raw('vendas.total - vendas.total_devolvido'));

        // Custo do que saiu da loja. Produto devolvido que VOLTOU ao estoque não é custo
        // (vai ser vendido de novo); o que virou perda ou foi para o fornecedor continua sendo.
        $custoVendido = (float) DB::table('venda_itens')
            ->whereIn('venda_id', (clone $comCusto)->select('vendas.id'))
            ->sum(DB::raw('quantidade * custo_unitario'));
        $custoQueVoltou = (float) DB::table('devolucao_itens')
            ->join('devolucoes', 'devolucoes.id', '=', 'devolucao_itens.devolucao_id')
            ->where('devolucoes.destino', 'estoque')
            ->whereIn('devolucoes.venda_id', (clone $comCusto)->select('vendas.id'))
            ->sum(DB::raw('devolucao_itens.quantidade * devolucao_itens.custo_unitario'));
        $custo = $custoVendido - $custoQueVoltou;

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
