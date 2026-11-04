<?php

// Arquivo criado com o comando:
//   php artisan make:controller RelatorioController

namespace App\Http\Controllers;

use App\Models\Venda;
use App\Models\VendaItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RelatorioController extends Controller
{
    // Relatório de vendas por período
    public function vendas(Request $request)
    {
        [$de, $ate] = $this->periodo($request);

        $vendas = $this->vendasDoPeriodo($de, $ate)->with('cliente')->orderBy('data')->get();

        $resumo = [
            'quantidade' => $vendas->count(),
            'faturamento' => $vendas->sum('total'),
            'ticket_medio' => $vendas->count() ? $vendas->sum('total') / $vendas->count() : 0,
        ];

        // Lucro bruto do período (vendas sem custo em algum item ficam de fora e são contadas)
        $lucro = app(\App\Services\LucroService::class)
            ->calcular(Venda::whereDate('data', '>=', $de)->whereDate('data', '<=', $ate));

        // Produtos mais vendidos no período (soma das quantidades de cada produto)
        $maisVendidos = VendaItem::with('produto')
            ->selectRaw('produto_id, SUM(quantidade) as quantidade, SUM(subtotal) as total')
            ->whereIn('venda_id', $vendas->pluck('id'))
            ->groupBy('produto_id')
            ->orderByDesc('quantidade')
            ->limit(10)
            ->get();

        return view('relatorios.vendas', compact('vendas', 'resumo', 'lucro', 'maisVendidos', 'de', 'ate'));
    }

    // Exporta as vendas do período em CSV (abre no Excel)
    public function exportarVendas(Request $request)
    {
        [$de, $ate] = $this->periodo($request);

        $vendas = $this->vendasDoPeriodo($de, $ate)->with('cliente')->orderBy('data')->get();

        return response()->streamDownload(function () use ($vendas) {
            $saida = fopen('php://output', 'w');
            fwrite($saida, "\xEF\xBB\xBF"); // BOM: faz o Excel reconhecer os acentos

            // Ponto e vírgula como separador, que é o padrão do Excel em português
            fputcsv($saida, ['Venda', 'Data', 'Cliente', 'Forma de pagamento', 'Subtotal', 'Desconto', 'Total'], ';');

            foreach ($vendas as $venda) {
                fputcsv($saida, [
                    $venda->id,
                    $venda->data->format('d/m/Y'),
                    self::celulaSegura($venda->cliente->nome),
                    $venda->nomeFormaPagamento(),
                    number_format($venda->subtotal, 2, ',', ''),
                    number_format($venda->desconto, 2, ',', ''),
                    number_format($venda->total, 2, ',', ''),
                ], ';');
            }

            fclose($saida);
        }, "vendas_{$de}_a_{$ate}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // Período do filtro: padrão é o mês atual
    private function periodo(Request $request): array
    {
        $de = $request->input('de', Carbon::now()->startOfMonth()->toDateString());
        $ate = $request->input('ate', Carbon::now()->toDateString());

        return [$de, $ate];
    }

    // Apenas vendas concluídas (canceladas não contam no faturamento)
    private function vendasDoPeriodo(string $de, string $ate)
    {
        return Venda::where('status', Venda::CONCLUIDA)
            ->whereDate('data', '>=', $de)
            ->whereDate('data', '<=', $ate);
    }

    // Proteção contra "injeção de fórmula" no Excel: um texto digitado pelo usuário que começa
    // com = + - @ (ou tab/enter) seria executado como fórmula ao abrir o CSV.
    // Um apóstrofo na frente faz o Excel tratar como texto comum.
    public static function celulaSegura(?string $texto): string
    {
        $texto = (string) $texto;

        return preg_match('/^[=+\-@\t\r]/', $texto) ? "'".$texto : $texto;
    }
}
