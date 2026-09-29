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
    // Soma quantidade ao estoque
    public function entrada(Produto $produto, int $quantidade, string $motivo, ?int $vendaId = null): MovimentacaoEstoque
    {
        return $this->movimentar($produto, MovimentacaoEstoque::ENTRADA, $quantidade, $motivo, $vendaId);
    }

    // Tira quantidade do estoque (não deixa ficar negativo)
    public function saida(Produto $produto, int $quantidade, string $motivo, ?int $vendaId = null): MovimentacaoEstoque
    {
        if ($quantidade > $produto->estoque) {
            // Diz quanto há e o que fazer; na venda, a saída sugere diminuir ou registrar entrada
            $unidades = $produto->estoque === 1 ? 'unidade' : 'unidades';
            $proximoPasso = $vendaId ? 'Diminua a quantidade ou registre uma entrada.' : 'Confira a quantidade.';

            throw ValidationException::withMessages([
                'quantidade' => "Só há {$produto->estoque} {$unidades} de \"{$produto->nome}\". {$proximoPasso}",
            ]);
        }

        return $this->movimentar($produto, MovimentacaoEstoque::SAIDA, $quantidade, $motivo, $vendaId);
    }

    private function movimentar(Produto $produto, string $tipo, int $quantidade, string $motivo, ?int $vendaId): MovimentacaoEstoque
    {
        $produto->estoque += $tipo === MovimentacaoEstoque::ENTRADA ? $quantidade : -$quantidade;
        $produto->save();

        return $produto->movimentacoes()->create([
            'tipo' => $tipo,
            'quantidade' => $quantidade,
            'estoque_apos' => $produto->estoque,
            'motivo' => $motivo,
            'venda_id' => $vendaId,
            'user_id' => Auth::id(),
        ]);
    }
}
