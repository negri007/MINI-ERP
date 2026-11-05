<?php

namespace App\Services;

use App\Models\MovimentacaoEstoque;
use App\Models\Produto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

// Único lugar do sistema que altera o estoque.
// Toda entrada ou saída atualiza o saldo do produto E grava o histórico.
class EstoqueService
{
    // Soma quantidade ao estoque.
    // $origem diz de onde veio: venda, cancelamento, estoque_inicial, manual ou devolucao
    // (ver MovimentacaoEstoque::ORIGENS); o histórico de estoque mostra isso na coluna "Origem".
    public function entrada(Produto $produto, int $quantidade, string $motivo, string $origem = 'manual', ?int $vendaId = null, ?int $devolucaoId = null): MovimentacaoEstoque
    {
        return $this->movimentar($produto, MovimentacaoEstoque::ENTRADA, $quantidade, $motivo, $origem, $vendaId, $devolucaoId);
    }

    // Tira quantidade do estoque (não deixa ficar negativo)
    public function saida(Produto $produto, int $quantidade, string $motivo, string $origem = 'manual', ?int $vendaId = null): MovimentacaoEstoque
    {
        if ($quantidade > $produto->estoque) {
            // Diz quanto há e o que fazer; na venda, a saída sugere diminuir ou registrar entrada
            $unidades = $produto->estoque === 1 ? 'unidade' : 'unidades';
            $proximoPasso = $vendaId ? 'Diminua a quantidade ou registre uma entrada.' : 'Confira a quantidade.';

            throw ValidationException::withMessages([
                'quantidade' => "Só há {$produto->estoque} {$unidades} de \"{$produto->nome}\". {$proximoPasso}",
            ]);
        }

        return $this->movimentar($produto, MovimentacaoEstoque::SAIDA, $quantidade, $motivo, $origem, $vendaId);
    }

    private function movimentar(Produto $produto, string $tipo, int $quantidade, string $motivo, string $origem, ?int $vendaId, ?int $devolucaoId = null): MovimentacaoEstoque
    {
        if (! array_key_exists($origem, MovimentacaoEstoque::ORIGENS)) {
            throw new \InvalidArgumentException("Origem de estoque desconhecida: {$origem}");
        }

        $produto->estoque += $tipo === MovimentacaoEstoque::ENTRADA ? $quantidade : -$quantidade;
        $produto->save();

        return $produto->movimentacoes()->create([
            'tipo' => $tipo,
            'origem' => $origem,
            'quantidade' => $quantidade,
            'estoque_apos' => $produto->estoque,
            'motivo' => $motivo,
            'venda_id' => $vendaId,
            'devolucao_id' => $devolucaoId,
            'user_id' => Auth::id(),
        ]);
    }
}
