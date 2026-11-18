<?php

namespace App\Services;

use App\Models\Devolucao;
use App\Models\Produto;
use App\Models\Venda;
use App\Models\VendaItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

// Regras da devolução de cliente.
//   - Sempre a partir de uma venda concluída; não dá para devolver mais do que foi vendido
//     (somando as devoluções anteriores do mesmo item).
//   - O valor devolvido respeita o desconto da venda: se a venda teve 10% de desconto,
//     cada item devolvido volta com 10% a menos. Conta feita em centavos (números inteiros).
//   - Destino "estoque": o produto volta para a prateleira (entrada no estoque).
//     Destinos "perda" e "fornecedor": o produto não volta a ser vendido, então o estoque não muda.
//   - vendas.total_devolvido soma as devoluções: faturamento e lucro usam "total - total_devolvido".
class DevolucaoService
{
    public function __construct(private EstoqueService $estoque) {}

    /**
     * $quantidades = [venda_item_id => quantidade, ...] (itens com 0 são ignorados)
     */
    public function registrar(Venda $venda, array $quantidades, string $motivo, string $destino, ?string $observacao = null): Devolucao
    {
        if (! array_key_exists($motivo, Devolucao::MOTIVOS)) {
            throw ValidationException::withMessages(['motivo' => 'Escolha o motivo da devolução.']);
        }
        if (! array_key_exists($destino, Devolucao::DESTINOS)) {
            throw ValidationException::withMessages(['destino' => 'Escolha o que acontece com o produto devolvido.']);
        }

        $quantidades = array_filter(array_map('intval', $quantidades), fn (int $q) => $q > 0);
        if (! $quantidades) {
            throw ValidationException::withMessages(['itens' => 'Informe a quantidade de pelo menos um produto para devolver.']);
        }

        return DB::transaction(function () use ($venda, $quantidades, $motivo, $destino, $observacao) {
            // Trava a venda até o fim: duas devoluções ao mesmo tempo não passam do que foi vendido
            $venda = Venda::lockForUpdate()->findOrFail($venda->id);

            if ($venda->estaCancelada()) {
                throw ValidationException::withMessages(['venda' => 'Venda cancelada não tem devolução: o estoque já voltou no cancelamento.']);
            }

            // Fator do desconto, em centavos: o cliente pagou "total" por "subtotal" em mercadoria
            $subtotal = $this->centavos($venda->subtotal);
            $total = $this->centavos($venda->total);

            $devolucao = $venda->devolucoes()->create([
                'user_id' => Auth::id(),
                'motivo' => $motivo,
                'destino' => $destino,
                'valor' => 0,
                'observacao' => $observacao,
            ]);

            $valorCentavos = 0;

            foreach ($quantidades as $itemId => $quantidade) {
                /** @var VendaItem|null $item */
                $item = $venda->itens()->whereKey($itemId)->first();
                if (! $item) {
                    throw ValidationException::withMessages(['itens' => 'Um dos itens não pertence a esta venda.']);
                }

                $disponivel = $item->quantidadeDisponivelParaDevolver();
                if ($quantidade > $disponivel) {
                    $nome = $item->produto->nome;
                    throw ValidationException::withMessages([
                        "itens.{$itemId}" => "Só dá para devolver {$disponivel} de \"{$nome}\": é o que foi vendido menos o que já voltou.",
                    ]);
                }

                // Valor deste item com o desconto da venda repartido proporcionalmente
                $bruto = $this->centavos($item->preco_unitario) * $quantidade;
                $valorItem = $subtotal > 0 ? intdiv($bruto * $total + intdiv($subtotal, 2), $subtotal) : 0;

                $devolucao->itens()->create([
                    'venda_item_id' => $item->id,
                    'produto_id' => $item->produto_id,
                    'quantidade' => $quantidade,
                    'valor' => $valorItem / 100,
                    'custo_unitario' => $item->custo_unitario,
                ]);

                if ($destino === 'estoque') {
                    $produto = Produto::lockForUpdate()->findOrFail($item->produto_id);
                    $this->estoque->entrada($produto, $quantidade, "Devolução #{$devolucao->id} da venda #{$venda->id}", 'devolucao', $venda->id, $devolucao->id);
                }

                $valorCentavos += $valorItem;
            }

            // Arredondamento item a item nunca pode passar do que ainda resta da venda.
            // Se com esta devolução TUDO voltou, devolve exatamente o restante (sem sobrar centavo).
            $restante = $total - $this->centavos($venda->total_devolvido);
            $tudoDevolvido = $venda->itens()->get()->every(fn (VendaItem $i) => $i->quantidadeDisponivelParaDevolver() === 0);
            $valorCentavos = $tudoDevolvido ? $restante : min($valorCentavos, $restante);

            $devolucao->update(['valor' => $valorCentavos / 100]);
            $venda->update(['total_devolvido' => ($this->centavos($venda->total_devolvido) + $valorCentavos) / 100]);

            return $devolucao;
        });
    }

    // Valor em reais (ex.: "10.50") para centavos inteiros (1050)
    private function centavos(float|int|string|null $valor): int
    {
        return (int) round(((float) $valor) * 100);
    }
}
