<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Produto;
use App\Models\User;
use App\Models\Venda;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendaTest extends TestCase
{
    use RefreshDatabase;

    private Cliente $cliente;

    private Produto $suco;

    private Produto $agua;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
        $this->cliente = Cliente::factory()->create();
        $this->suco = Produto::factory()->create(['nome' => 'Suco', 'preco' => 7.50, 'estoque' => 10]);
        $this->agua = Produto::factory()->create(['nome' => 'Água', 'preco' => 2.00, 'estoque' => 5]);
    }

    private function vender(array $itens)
    {
        return $this->post('/vendas', [
            'cliente_id' => $this->cliente->id,
            'data' => now()->toDateString(),
            'forma_pagamento' => 'dinheiro',
            'itens' => $itens,
        ]);
    }

    public function test_venda_calcula_total_e_baixa_estoque(): void
    {
        $this->vender([
            ['produto_id' => $this->suco->id, 'quantidade' => 2],
            ['produto_id' => $this->agua->id, 'quantidade' => 3],
            ['produto_id' => '', 'quantidade' => 1], // linha em branco é ignorada
        ])->assertSessionHasNoErrors();

        $venda = Venda::firstOrFail();
        $this->assertEquals(21.00, $venda->total); // 2 x 7,50 + 3 x 2,00
        $this->assertSame(8, $this->suco->fresh()->estoque);
        $this->assertSame(2, $this->agua->fresh()->estoque);
        $this->assertDatabaseHas('movimentacoes_estoque', ['venda_id' => $venda->id, 'tipo' => 'saida', 'produto_id' => $this->suco->id, 'quantidade' => 2]);

        $this->get("/vendas/{$venda->id}")->assertOk()->assertSee('R$ 21,00');
        $this->get('/vendas')->assertSee($this->cliente->nome);
    }

    public function test_estoque_insuficiente_nao_grava_nada(): void
    {
        // O primeiro item tem estoque, o segundo não: a transação desfaz tudo
        $this->vender([
            ['produto_id' => $this->suco->id, 'quantidade' => 2],
            ['produto_id' => $this->agua->id, 'quantidade' => 6],
        ])->assertSessionHasErrors('quantidade');

        $this->assertDatabaseCount('vendas', 0);
        $this->assertDatabaseCount('movimentacoes_estoque', 0);
        $this->assertSame(10, $this->suco->fresh()->estoque);
    }

    public function test_venda_sem_itens_e_recusada(): void
    {
        $this->vender([])->assertSessionHasErrors('itens');
    }

    public function test_cancelar_devolve_estoque(): void
    {
        $this->vender([['produto_id' => $this->suco->id, 'quantidade' => 4]]);
        $venda = Venda::firstOrFail();

        $this->post("/vendas/{$venda->id}/cancelar")->assertRedirect("/vendas/{$venda->id}");

        $this->assertTrue($venda->fresh()->estaCancelada());
        $this->assertSame(10, $this->suco->fresh()->estoque);

        // Não dá para cancelar duas vezes
        $this->post("/vendas/{$venda->id}/cancelar")->assertSessionHasErrors('venda');
        $this->assertSame(10, $this->suco->fresh()->estoque);
    }

    public function test_cliente_e_produto_com_venda_nao_podem_ser_excluidos(): void
    {
        $this->vender([['produto_id' => $this->suco->id, 'quantidade' => 1]]);

        $this->delete("/clientes/{$this->cliente->id}")->assertSessionHas('error');
        $this->delete("/produtos/{$this->suco->id}")->assertSessionHas('error');
        $this->assertDatabaseHas('clientes', ['id' => $this->cliente->id]);
        $this->assertDatabaseHas('produtos', ['id' => $this->suco->id]);
    }

    public function test_movimentacao_manual_de_estoque(): void
    {
        $this->post('/estoque', ['produto_id' => $this->agua->id, 'tipo' => 'entrada', 'quantidade' => 10, 'motivo' => 'Compra NF 123'])
            ->assertRedirect('/estoque');
        $this->assertSame(15, $this->agua->fresh()->estoque);

        $this->post('/estoque', ['produto_id' => $this->agua->id, 'tipo' => 'saida', 'quantidade' => 100, 'motivo' => 'Perda'])
            ->assertSessionHasErrors('quantidade');
        $this->assertSame(15, $this->agua->fresh()->estoque);

        $this->get('/estoque')->assertSee('Compra NF 123');
    }

    public function test_relatorio_e_exportacao(): void
    {
        $this->vender([['produto_id' => $this->suco->id, 'quantidade' => 2]]);
        $this->vender([['produto_id' => $this->agua->id, 'quantidade' => 1]]);
        Venda::latest('id')->first()->update(['status' => Venda::CANCELADA]);

        // Só a venda concluída conta
        $this->get('/relatorios/vendas')->assertOk()->assertSee('R$ 15,00')->assertSee('Suco');

        $csv = $this->get('/relatorios/vendas/exportar')->assertOk()->streamedContent();
        $this->assertStringContainsString('Venda;Data;Cliente;"Forma de pagamento";Subtotal;Desconto;Total', $csv);
        $this->assertStringContainsString(';Dinheiro;15,00;0,00;15,00', $csv);
    }
}
