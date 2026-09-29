<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Produto;
use App\Models\User;
use App\Models\Venda;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Cliente especial "Consumidor final": vendas sem identificar o cliente
class ConsumidorFinalTest extends TestCase
{
    use RefreshDatabase;

    private Cliente $consumidor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
        $this->consumidor = Cliente::consumidorFinal();
    }

    public function test_a_migration_cria_um_unico_consumidor_final(): void
    {
        $this->assertNotNull($this->consumidor);
        $this->assertSame('Consumidor final', $this->consumidor->nome);

        // o índice único do banco impede um segundo
        $this->expectException(QueryException::class);
        Cliente::factory()->create()->forceFill(['consumidor_final' => true])->save();
    }

    public function test_venda_com_consumidor_final(): void
    {
        $produto = Produto::factory()->create(['preco' => 10, 'estoque' => 5]);

        $this->post('/vendas', [
            'cliente_id' => $this->consumidor->id,
            'data' => now()->toDateString(),
            'itens' => [['produto_id' => $produto->id, 'quantidade' => 2]],
        ])->assertRedirect();

        $venda = Venda::first();
        $this->assertSame($this->consumidor->id, $venda->cliente_id);
        $this->assertEquals(20, $venda->total);
        $this->assertSame(3, $produto->fresh()->estoque);
    }

    public function test_consumidor_final_nao_pode_ser_editado_nem_excluido(): void
    {
        $this->get("/clientes/{$this->consumidor->id}/edit")->assertRedirect('/clientes')->assertSessionHas('error');

        $this->put("/clientes/{$this->consumidor->id}", ['nome' => 'Outro nome'])->assertRedirect('/clientes');
        $this->assertSame('Consumidor final', $this->consumidor->fresh()->nome);

        $this->delete("/clientes/{$this->consumidor->id}")->assertRedirect('/clientes');
        $this->assertModelExists($this->consumidor);
    }

    public function test_formulario_nao_transforma_cliente_comum_em_consumidor_final(): void
    {
        $this->post('/clientes', ['nome' => 'Esperto', 'consumidor_final' => 1]);

        $this->assertNull(Cliente::where('nome', 'Esperto')->value('consumidor_final'));
    }

    public function test_consumidor_final_fica_fora_da_lista_e_das_contagens_de_clientes(): void
    {
        Cliente::factory()->create(['nome' => 'Bruno Costa']);

        $resposta = $this->get('/clientes')->assertOk();
        $resposta->assertSee('Bruno Costa');
        $this->assertFalse($resposta->viewData('clientes')->contains(fn (Cliente $c) => $c->ehConsumidorFinal()));
        $this->assertSame(['todos' => 1, 'com' => 0, 'sem' => 1], $resposta->viewData('contagem'));
    }

    public function test_nova_venda_oferece_o_consumidor_final(): void
    {
        $this->get('/vendas/create')->assertOk()->assertSee('Consumidor final');
    }
}
