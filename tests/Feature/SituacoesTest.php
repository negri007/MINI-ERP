<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Fornecedor;
use App\Models\Produto;
use App\Models\User;
use App\Models\Venda;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Situações que confundem: a tela avisa antes, mas o servidor continua barrando
class SituacoesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    private function vender(Cliente $cliente, Produto $produto, int $quantidade)
    {
        return $this->post('/vendas', [
            'cliente_id' => $cliente->id,
            'data' => now()->toDateString(),
            'forma_pagamento' => 'dinheiro',
            'itens' => [['produto_id' => $produto->id, 'quantidade' => $quantidade]],
        ]);
    }

    public function test_categoria_rapida_cria_e_valida_como_o_cadastro_normal(): void
    {
        $this->postJson('/categorias/rapida', ['nome' => 'Bebidas'])
            ->assertCreated()
            ->assertJson(['nome' => 'Bebidas']);

        $this->postJson('/categorias/rapida', ['nome' => ''])->assertUnprocessable()->assertJsonValidationErrors('nome');

        auth()->logout();
        $this->postJson('/categorias/rapida', ['nome' => 'Sem login'])->assertUnauthorized();
    }

    public function test_categoria_com_produtos_explica_e_continua_bloqueada_no_servidor(): void
    {
        $categoria = Categoria::factory()->create(['nome' => 'Bebidas']);
        Produto::factory(2)->create(['categoria_id' => $categoria->id]);
        $motivo = 'A categoria "Bebidas" tem 2 produtos. Mova-os para outra categoria antes de excluir.';

        $this->get('/categorias')->assertSee('data-bloqueio="'.e($motivo).'"', false)->assertSee('Ver produtos');

        $this->delete("/categorias/{$categoria->id}")->assertSessionHas('error', $motivo);
        $this->assertModelExists($categoria);
    }

    public function test_cliente_com_vendas_oferece_ver_as_vendas_e_nao_e_excluido(): void
    {
        $cliente = Cliente::factory()->create(['nome' => 'Maria']);
        $outro = Cliente::factory()->create(['nome' => 'João']);
        $produto = Produto::factory()->create(['estoque' => 10]);
        $this->vender($cliente, $produto, 1);
        $this->vender($outro, $produto, 1);

        $this->get('/clientes')->assertSee('Ver vendas do cliente')->assertSee("vendas?cliente={$cliente->id}", false);

        // o atalho mostra só as vendas desse cliente
        $vendas = $this->get("/vendas?cliente={$cliente->id}")->viewData('vendas');
        $this->assertSame([$cliente->id], $vendas->pluck('cliente_id')->unique()->values()->all());

        $this->delete("/clientes/{$cliente->id}")->assertSessionHas('error');
        $this->assertModelExists($cliente);
    }

    public function test_excluir_fornecedor_avisa_quantos_produtos_ficam_sem_fornecedor(): void
    {
        $fornecedor = Fornecedor::factory()->create();
        Produto::factory(3)->create(['fornecedor_id' => $fornecedor->id]);

        $this->get('/fornecedores')->assertSee('3 produtos ficarão sem fornecedor.');
    }

    public function test_produto_vendido_explica_por_que_nao_pode_ser_excluido(): void
    {
        $produto = Produto::factory()->create(['nome' => 'Suco', 'estoque' => 5]);
        $this->vender(Cliente::factory()->create(), $produto, 1);

        $this->delete("/produtos/{$produto->id}")
            ->assertSessionHas('error', 'O produto "Suco" aparece em vendas e o histórico precisa dele. Se não vende mais, deixe o estoque em zero.');
        $this->assertModelExists($produto);
    }

    public function test_quantidade_acima_do_estoque_diz_quanto_ha_e_o_que_fazer(): void
    {
        $produto = Produto::factory()->create(['nome' => 'Água', 'estoque' => 3]);

        $this->vender(Cliente::factory()->create(), $produto, 5)
            ->assertSessionHasErrors(['quantidade' => 'Só há 3 unidades de "Água". Diminua a quantidade ou registre uma entrada.']);
        $this->assertSame(0, Venda::count());

        $this->post('/estoque', ['produto_id' => $produto->id, 'tipo' => 'saida', 'quantidade' => 5, 'motivo' => 'Quebra'])
            ->assertSessionHasErrors(['quantidade' => 'Só há 3 unidades de "Água". Confira a quantidade.']);
        $this->assertSame(3, $produto->fresh()->estoque);
    }

    public function test_nova_venda_mostra_produto_sem_estoque_sem_deixar_escolher(): void
    {
        Produto::factory()->create(['nome' => 'Suco de uva', 'estoque' => 0]);

        $this->get('/vendas/create')
            ->assertSee('Sem estoque (registre uma entrada para vender)')
            ->assertSee('<option disabled>Suco de uva — sem estoque</option>', false)
            ->assertSee('Nenhum produto com estoque. Registre uma entrada para começar a vender.');
    }
}
