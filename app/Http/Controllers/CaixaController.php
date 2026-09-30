<?php

// Arquivo criado com o comando:
//   php artisan make:controller CaixaController

namespace App\Http\Controllers;

use App\Models\Venda;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

// Caixa do dia: só consulta (não há tabela nova). Soma as vendas de um dia por forma de pagamento,
// para conferir no fim do dia se a gaveta e as maquininhas batem com o sistema.
class CaixaController extends Controller
{
    public function index(Request $request)
    {
        // Data escolhida (AAAA-MM-DD). Se vier vazia ou inválida, usa hoje, sem erro.
        $texto = (string) $request->input('data');
        $dia = preg_match('/^\d{4}-\d{2}-\d{2}$/', $texto) && checkdate((int) substr($texto, 5, 2), (int) substr($texto, 8, 2), (int) substr($texto, 0, 4))
            ? Carbon::parse($texto)
            : Carbon::today();

        // Vendas de um só dia: poucas linhas, então agrupar aqui é simples e rápido
        $vendas = Venda::with('cliente')->whereDate('data', $dia->toDateString())->orderBy('id')->get();
        $concluidas = $vendas->reject->estaCancelada();
        $canceladas = $vendas->filter->estaCancelada();

        // Uma linha por forma de pagamento (sempre as 4) + "não informada" só se houver venda antiga
        $porForma = collect(Venda::FORMAS_PAGAMENTO)->map(fn ($nome, $chave) => [
            'nome' => $nome,
            'quantidade' => $concluidas->where('forma_pagamento', $chave)->count(),
            'total' => (float) $concluidas->where('forma_pagamento', $chave)->sum('total'),
        ]);
        $semForma = $concluidas->whereNull('forma_pagamento');
        if ($semForma->isNotEmpty()) {
            $porForma->put('nao_informada', ['nome' => 'Não informada', 'quantidade' => $semForma->count(), 'total' => (float) $semForma->sum('total')]);
        }

        $resumo = [
            'total' => (float) $concluidas->sum('total'),
            'quantidade' => $concluidas->count(),
            'descontos' => (float) $concluidas->sum('desconto'),
            'canceladas' => $canceladas->count(),
            'total_canceladas' => (float) $canceladas->sum('total'),
        ];

        return view('caixa.index', compact('dia', 'vendas', 'porForma', 'resumo'));
    }
}
