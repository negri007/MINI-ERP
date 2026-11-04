<?php

namespace App\Services;

use App\Models\Produto;
use App\Models\Venda;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

// Regras de negócio da venda: registrar (com baixa de estoque) e cancelar (devolvendo o estoque)
class VendaService
{
    public function __construct(private EstoqueService $estoque) {}

    /**
     * Registra uma venda.
     * $itens = [['produto_id' => 1, 'quantidade' => 2], ...]
     *
     * Dinheiro é calculado AQUI, em centavos (números inteiros, sem erro de arredondamento).
     * O que o navegador mostra é só uma prévia: preço, subtotal e total são recalculados
     * a partir do banco, e o desconto não pode passar do subtotal.
     *
     * Tudo roda dentro de uma TRANSAÇÃO: se qualquer item falhar
     * (ex.: estoque insuficiente), nada é gravado — nem a venda, nem os itens,
     * nem as baixas de estoque dos itens anteriores.
     */
    public function registrar(int $clienteId, string $data, array $itens, string $formaPagamento, float|int|string $desconto = 0, ?string $observacao = null): Venda
    {
        if (! array_key_exists($formaPagamento, Venda::FORMAS_PAGAMENTO)) {
            throw ValidationException::withMessages(['forma_pagamento' => 'Escolha a forma de pagamento.']);
        }

        $descontoCentavos = $this->centavos($desconto);
        if ($descontoCentavos < 0) {
            throw ValidationException::withMessages(['desconto' => 'O desconto não pode ser negativo.']);
        }

        return DB::transaction(function () use ($clienteId, $data, $itens, $formaPagamento, $descontoCentavos, $observacao) {
            $venda = Venda::create([
                'cliente_id' => $clienteId,
                'user_id' => Auth::id(),
                'data' => $data,
                'status' => Venda::CONCLUIDA,
                'subtotal' => 0,
                'desconto' => 0,
                'total' => 0,
                'forma_pagamento' => $formaPagamento,
                'observacao' => $observacao,
            ]);

            $subtotalCentavos = 0;

            foreach ($this->agruparItens($itens) as $produtoId => $quantidade) {
                // lockForUpdate: trava a linha do produto até o fim da transação,
                // evitando que duas vendas ao mesmo tempo vendam o mesmo estoque
                $produto = Produto::lockForUpdate()->findOrFail($produtoId);

                $subtotalItem = $this->centavos($produto->preco) * $quantidade;

                $venda->itens()->create([
                    'produto_id' => $produto->id,
                    'quantidade' => $quantidade,
                    'preco_unitario' => $produto->preco,
                    // custo do momento da venda (como o preço); vazio se o produto não tem custo
                    'custo_unitario' => $produto->custo,
                    'subtotal' => $subtotalItem / 100,
                ]);

                $this->estoque->saida($produto, $quantidade, "Venda #{$venda->id}", $venda->id);

                $subtotalCentavos += $subtotalItem;
            }

            // Desconto maior que o subtotal: recusa (a transação desfaz a venda e as baixas de estoque)
            if ($descontoCentavos > $subtotalCentavos) {
                throw ValidationException::withMessages([
                    'desconto' => 'O desconto (R$ '.$this->reais($descontoCentavos).') é maior que o subtotal (R$ '
                        .$this->reais($subtotalCentavos).'). Diminua o desconto.',
                ]);
            }

            // Os três juntos, para a regra do banco (total = subtotal - desconto) valer sempre
            $venda->update([
                'subtotal' => $subtotalCentavos / 100,
                'desconto' => $descontoCentavos / 100,
                'total' => ($subtotalCentavos - $descontoCentavos) / 100,
            ]);

            return $venda;
        });
    }

    // Cancela a venda e devolve os produtos ao estoque
    public function cancelar(Venda $venda): void
    {
        if ($venda->estaCancelada()) {
            throw ValidationException::withMessages(['venda' => 'Esta venda já está cancelada.']);
        }

        DB::transaction(function () use ($venda) {
            foreach ($venda->itens as $item) {
                $produto = Produto::lockForUpdate()->findOrFail($item->produto_id);
                $this->estoque->entrada($produto, $item->quantidade, "Cancelamento da venda #{$venda->id}", $venda->id);
            }

            $venda->update(['status' => Venda::CANCELADA]);
        });
    }

    // Valor em reais (ex.: "10.50" ou 10.5) para centavos inteiros (1050)
    private function centavos(float|int|string|null $valor): int
    {
        return (int) round(((float) $valor) * 100);
    }

    // Centavos para texto em reais no formato brasileiro (1050 -> "10,50")
    private function reais(int $centavos): string
    {
        return number_format($centavos / 100, 2, ',', '.');
    }

    // Se o mesmo produto aparecer duas vezes, soma as quantidades numa linha só
    private function agruparItens(array $itens): array
    {
        $agrupados = [];

        foreach ($itens as $item) {
            $id = (int) $item['produto_id'];
            $agrupados[$id] = ($agrupados[$id] ?? 0) + (int) $item['quantidade'];
        }

        return $agrupados;
    }
}
