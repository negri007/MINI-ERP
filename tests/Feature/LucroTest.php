<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Produto;
use App\Models\User;
use App\Models\Venda;
use App\Services\LucroService;
use App\Services\VendaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Custo guardado na venda e lucro bruto (vendas sem custo ficam de fora, nunca com valor inventado)
class LucroTest extends TestCase
{
    use RefreshDatabase;

    private Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
        $this->cliente = Cliente::factory()->create();
    }

    private function vender(Produto $produto, int $quantidade): Venda
    {
        return app(VendaService::class)->registrar($this->cliente->id, now()->toDateString(), [
            ['produto_id' => $produto->id, 'quantidade' => $quantidade],
        ]);
    }

    private function lucroDeHoje(): array
    {
        return app(LucroService::class)->calcular(Venda::whereDate('data', now()->toDateString()));
    }

    public function test_venda_guarda_o_custo_do_momento_e_nao_muda_depois(): void
    {
        $suco = Produto::factory()->create(['preco' => 10, 'custo' => 6, 'estoque' => 10]);
        $venda = $this->vender($suco, 2);

        $suco->update(['custo' => 9]); // o custo mudou depois da venda

        $this->assertEquals(6, $venda->itens()->first()->custo_unitario);
        $lucro = $this->lucroDeHoje();
        $this->assertEquals(8.0, $lucro['lucro']);   // 20 - 2 x 6
        $this->assertEquals(40.0, $lucro['margem']); // 8 / 20
    }

    public function test_venda_sem_custo_fica_fora_do_lucro_e_e_contada(): void
    {
        $comCusto = Produto::factory()->create(['preco' => 10, 'custo' => 4, 'estoque' => 10]);
        $semCusto = Produto::factory()->create(['preco' => 50, 'custo' => null, 'estoque' => 10]);

        $this->vender($comCusto, 1);   // lucro 6
        $this->vender($semCusto, 1);   // sem custo: fora

        $lucro = $this->lucroDeHoje();
        $this->assertEquals(6.0, $lucro['lucro']);
        $this->assertEquals(10.0, $lucro['faturamento']); // só a venda que entrou na conta
        $this->assertEquals(60.0, $lucro['margem']);
        $this->assertSame(1, $lucro['com_custo']);
        $this->assertSame(1, $lucro['sem_custo']);
        $this->assertNull($semCusto->itensVenda()->first()->custo_unitario); // nada inventado
    }

    public function test_venda_cancelada_nao_entra_no_lucro(): void
    {
        $produto = Produto::factory()->create(['preco' => 10, 'custo' => 4, 'estoque' => 10]);
        app(VendaService::class)->cancelar($this->vender($produto, 3));

        $lucro = $this->lucroDeHoje();
        $this->assertEquals(0.0, $lucro['lucro']);
        $this->assertNull($lucro['margem']);
    }

    public function test_dashboard_e_relatorio_mostram_lucro_e_aviso(): void
    {
        $this->vender(Produto::factory()->create(['preco' => 10, 'custo' => 4, 'estoque' => 5]), 1);
        $this->vender(Produto::factory()->create(['preco' => 10, 'custo' => null, 'estoque' => 5]), 1);

        $this->get('/')->assertSee('Lucro bruto')->assertSee('margem 60,0%')->assertSee('1 venda sem custo ficou fora');
        $this->get('/relatorios/vendas')->assertSee('Lucro bruto')->assertSee('1 venda sem custo informado ficou');
    }

    public function test_custo_do_produto_e_opcional_e_nao_pode_ser_negativo(): void
    {
        $dados = ['nome' => 'Suco', 'preco' => 10, 'estoque' => 1, 'estoque_minimo' => 0,
            'categoria_id' => \App\Models\Categoria::factory()->create()->id];

        $this->post('/produtos', $dados + ['custo' => -1])->assertSessionHasErrors('custo');
        $this->post('/produtos', $dados + ['custo' => ''])->assertSessionHasNoErrors();
        $this->assertNull(Produto::where('nome', 'Suco')->value('custo'));

        $this->get('/produtos')->assertSee('sem custo informado');
    }
}
