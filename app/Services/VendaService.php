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
     * Tudo roda dentro de uma TRANSAÇÃO: se qualquer item falhar
     * (ex.: estoque insuficiente), nada é gravado — nem a venda, nem os itens,
     * nem as baixas de estoque dos itens anteriores.
     */
    public function registrar(int $clienteId, string $data, array $itens, ?string $observacao = null): Venda
    {
        return DB::transaction(function () use ($clienteId, $data, $itens, $observacao) {
            $venda = Venda::create([
                'cliente_id' => $clienteId,
                'user_id' => Auth::id(),
                'data' => $data,
                'status' => Venda::CONCLUIDA,
                'total' => 0,
                'observacao' => $observacao,
            ]);

            $total = 0;

            foreach ($this->agruparItens($itens) as $produtoId => $quantidade) {
                // lockForUpdate: trava a linha do produto até o fim da transação,
                // evitando que duas vendas ao mesmo tempo vendam o mesmo estoque
                $produto = Produto::lockForUpdate()->findOrFail($produtoId);

                $subtotal = round($produto->preco * $quantidade, 2);

                $venda->itens()->create([
                    'produto_id' => $produto->id,
                    'quantidade' => $quantidade,
                    'preco_unitario' => $produto->preco,
                    // custo do momento da venda (como o preço); vazio se o produto não tem custo
                    'custo_unitario' => $produto->custo,
                    'subtotal' => $subtotal,
                ]);

                $this->estoque->saida($produto, $quantidade, "Venda #{$venda->id}", $venda->id);

                $total += $subtotal;
            }

            $venda->update(['total' => $total]);

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
