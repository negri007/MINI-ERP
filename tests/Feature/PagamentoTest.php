<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Produto;
use App\Models\User;
use App\Models\Venda;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Forma de pagamento, desconto (calculado no servidor) e CSV seguro
class PagamentoTest extends TestCase
{
    use RefreshDatabase;

    private Cliente $cliente;

    private Produto $suco;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
        $this->cliente = Cliente::factory()->create();
        $this->suco = Produto::factory()->create(['nome' => 'Suco', 'preco' => 7.50, 'estoque' => 10]);
    }

    private function vender(array $extra = [], int $quantidade = 2)
    {
        return $this->post('/vendas', $extra + [
            'cliente_id' => $this->cliente->id,
            'data' => now()->toDateString(),
            'itens' => [['produto_id' => $this->suco->id, 'quantidade' => $quantidade]],
        ]);
    }

    public function test_forma_de_pagamento_e_obrigatoria_e_da_lista(): void
    {
        $this->vender()->assertSessionHasErrors(['forma_pagamento' => 'Escolha a forma de pagamento.']);
        $this->vender(['forma_pagamento' => 'fiado'])->assertSessionHasErrors('forma_pagamento');
        $this->vender(['forma_pagamento' => 'cheque'])->assertSessionHasErrors('forma_pagamento');

        $this->assertSame(0, Venda::count());
        $this->assertSame(10, $this->suco->fresh()->estoque);
    }

    public function test_total_com_desconto_e_calculado_no_servidor(): void
    {
        // o navegador manda um "total" falso: é ignorado, o servidor recalcula
        $this->vender(['forma_pagamento' => 'pix', 'desconto' => '2,50', 'total' => '1,00'])->assertSessionHasNoErrors();

        $venda = Venda::first();
        $this->assertEquals(15.00, $venda->subtotal);
        $this->assertEquals(2.50, $venda->desconto);
        $this->assertEquals(12.50, $venda->total);
        $this->assertSame('pix', $venda->forma_pagamento);
        $this->assertSame('Pix', $venda->nomeFormaPagamento());
    }

    public function test_desconto_maior_que_o_subtotal_e_recusado_sem_gravar_nada(): void
    {
        $this->vender(['forma_pagamento' => 'dinheiro', 'desconto' => '15,01'])
            ->assertSessionHasErrors(['desconto' => 'O desconto (R$ 15,01) é maior que o subtotal (R$ 15,00). Diminua o desconto.']);

        $this->assertSame(0, Venda::count());
        $this->assertSame(10, $this->suco->fresh()->estoque); // a baixa de estoque foi desfeita

        // desconto igual ao subtotal é aceito (total zero)
        $this->vender(['forma_pagamento' => 'dinheiro', 'desconto' => '15,00'])->assertSessionHasNoErrors();
        $this->assertEquals(0, Venda::first()->total);
    }

    public function test_desconto_negativo_ou_invalido_e_recusado(): void
    {
        $this->vender(['forma_pagamento' => 'dinheiro', 'desconto' => '-1'])->assertSessionHasErrors('desconto');
        $this->vender(['forma_pagamento' => 'dinheiro', 'desconto' => 'abc'])->assertSessionHasErrors('desconto');
        $this->assertSame(0, Venda::count());
    }

    public function test_cancelar_devolve_o_estoque_e_mantem_os_valores(): void
    {
        $this->vender(['forma_pagamento' => 'credito', 'desconto' => '5'], 3);
        $venda = Venda::first();
        $this->assertSame(7, $this->suco->fresh()->estoque);

        $this->post("/vendas/{$venda->id}/cancelar")->assertRedirect();

        $venda->refresh();
        $this->assertTrue($venda->estaCancelada());
        $this->assertSame(10, $this->suco->fresh()->estoque);
        // cancelar não mexe nos valores: a regra total = subtotal - desconto continua valendo
        $this->assertEquals(22.50, $venda->subtotal);
        $this->assertEquals(17.50, $venda->total);
    }

    public function test_detalhe_e_lista_mostram_pagamento_e_desconto(): void
    {
        $this->vender(['forma_pagamento' => 'debito', 'desconto' => '1,50']);
        $venda = Venda::first();

        $this->get("/vendas/{$venda->id}")->assertSee('Cartão de débito')->assertSee('− R$ 1,50', false);
        $this->get('/vendas')->assertSee('Cartão de débito')->assertSee('desconto R$ 1,50');

        // venda antiga, sem forma de pagamento
        $venda->forceFill(['forma_pagamento' => null])->save();
        $this->get("/vendas/{$venda->id}")->assertSee('não informada');
    }

    public function test_csv_protege_contra_formulas(): void
    {
        $this->cliente->update(['nome' => '=HYPERLINK("http://mal.example","clique")']);
        $this->vender(['forma_pagamento' => 'dinheiro']);

        $csv = $this->get('/relatorios/vendas/exportar')->assertOk()->streamedContent();

        // o nome vai com apóstrofo na frente: o Excel mostra como texto, não executa
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringNotContainsString(';"=HYPERLINK', $csv);
    }
}
