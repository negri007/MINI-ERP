<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\MovimentacaoEstoque;
use App\Models\Produto;
use App\Models\User;
use App\Models\Venda;
use App\Services\LucroService;
use App\Services\VendaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

// Devolução de cliente: estoque só volta no destino "estoque", valor com desconto,
// limite do que foi vendido, caixa e faturamento descontando o que voltou
class DevolucaoTest extends TestCase
{
    use RefreshDatabase;

    private Produto $produto;

    private Venda $venda;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
        $this->produto = Produto::factory()->create(['preco' => 10, 'custo' => 6, 'estoque' => 20]);
        // 4 × R$ 10 = 40, desconto de 4 (10%) → total 36
        $this->venda = app(VendaService::class)->registrar(
            Cliente::factory()->create()->id, Carbon::today()->toDateString(),
            [['produto_id' => $this->produto->id, 'quantidade' => 4]], 'dinheiro', 4
        );
    }

    private function item(): int
    {
        return $this->venda->itens()->first()->id;
    }

    public function test_devolucao_para_o_estoque_soma_no_estoque_e_desconta_o_desconto(): void
    {
        $this->post("/vendas/{$this->venda->id}/devolucao", [
            'itens' => [$this->item() => 1], 'motivo' => 'desistencia', 'destino' => 'estoque',
        ])->assertRedirect("/vendas/{$this->venda->id}");

        $this->assertSame(17, $this->produto->fresh()->estoque); // 20 - 4 + 1
        $this->assertEquals(9.00, $this->venda->fresh()->total_devolvido); // 10 com 10% de desconto
        $this->assertEquals(27.00, $this->venda->fresh()->totalLiquido());

        $mov = MovimentacaoEstoque::where('origem', 'devolucao')->first();
        $this->assertNotNull($mov);
        $this->assertSame('Devolução', $mov->nomeOrigem());
        $this->assertSame($this->venda->id, $mov->venda_id);
    }

    public function test_perda_e_fornecedor_nao_mexem_no_estoque(): void
    {
        foreach (['perda', 'fornecedor'] as $destino) {
            $this->post("/vendas/{$this->venda->id}/devolucao", [
                'itens' => [$this->item() => 1], 'motivo' => 'defeito', 'destino' => $destino,
            ])->assertSessionHasNoErrors();
        }

        $this->assertSame(16, $this->produto->fresh()->estoque);
        $this->assertSame(0, MovimentacaoEstoque::where('origem', 'devolucao')->count());
        $this->assertEquals(18.00, $this->venda->fresh()->total_devolvido);
    }

    public function test_nao_devolve_mais_do_que_foi_vendido(): void
    {
        $this->post("/vendas/{$this->venda->id}/devolucao", ['itens' => [$this->item() => 3], 'motivo' => 'outro', 'destino' => 'estoque']);

        $this->post("/vendas/{$this->venda->id}/devolucao", ['itens' => [$this->item() => 2], 'motivo' => 'outro', 'destino' => 'estoque'])
            ->assertSessionHasErrors("itens.{$this->item()}");

        $this->assertSame(1, $this->venda->devolucoes()->count());
        $this->assertSame(19, $this->produto->fresh()->estoque);
    }

    public function test_devolver_tudo_zera_o_valor_da_venda_sem_sobrar_centavo(): void
    {
        $this->post("/vendas/{$this->venda->id}/devolucao", ['itens' => [$this->item() => 4], 'motivo' => 'erro_venda', 'destino' => 'estoque']);

        $venda = $this->venda->fresh();
        $this->assertEquals(36.00, $venda->total_devolvido);
        $this->assertEquals(0.0, $venda->totalLiquido());
        $this->assertFalse($venda->podeDevolver());
        $this->get("/vendas/{$venda->id}/devolucao")->assertRedirect("/vendas/{$venda->id}");
    }

    public function test_venda_com_devolucao_nao_pode_ser_cancelada(): void
    {
        $this->post("/vendas/{$this->venda->id}/devolucao", ['itens' => [$this->item() => 1], 'motivo' => 'outro', 'destino' => 'estoque']);

        $this->from("/vendas/{$this->venda->id}")->post("/vendas/{$this->venda->id}/cancelar")->assertSessionHasErrors('venda');
        $this->assertFalse($this->venda->fresh()->estaCancelada());
    }

    public function test_motivo_e_destino_sao_obrigatorios(): void
    {
        $this->post("/vendas/{$this->venda->id}/devolucao", ['itens' => [$this->item() => 1], 'motivo' => 'x', 'destino' => 'lixo'])
            ->assertSessionHasErrors(['motivo', 'destino']);
        $this->post("/vendas/{$this->venda->id}/devolucao", ['itens' => [$this->item() => 0], 'motivo' => 'outro', 'destino' => 'estoque'])
            ->assertSessionHasErrors('itens');
    }

    public function test_caixa_faturamento_e_lucro_descontam_a_devolucao(): void
    {
        $this->post("/vendas/{$this->venda->id}/devolucao", ['itens' => [$this->item() => 2], 'motivo' => 'desistencia', 'destino' => 'estoque']);

        $caixa = $this->get('/caixa')->assertOk()->assertSee('Devoluções do dia');
        $this->assertEquals(18.0, $caixa->viewData('porForma')['dinheiro']['total']); // 36 - 18
        $this->assertEquals(18.0, $caixa->viewData('resumo')['devolvido']);

        $this->assertEquals(18.0, $this->get('/vendas')->viewData('resumo')['faturamento']);

        // Lucro: recebeu 18, custo de 2 unidades que ficaram com o cliente = 12
        $lucro = app(LucroService::class)->calcular(Venda::query());
        $this->assertEquals(18.0, $lucro['faturamento']);
        $this->assertEquals(6.0, $lucro['lucro']);
    }

    public function test_tela_de_devolucoes_lista_motivo_e_destino(): void
    {
        $this->post("/vendas/{$this->venda->id}/devolucao", ['itens' => [$this->item() => 1], 'motivo' => 'avariado', 'destino' => 'perda']);

        $this->get('/devolucoes')->assertOk()
            ->assertSee('Avariado ou vencido')
            ->assertSee('Perda da loja (descartado)');
        $this->get('/devolucoes?destino=estoque')->assertOk()->assertDontSee('Avariado ou vencido');
        $this->get("/vendas/{$this->venda->id}")->assertOk()->assertSee('Devoluções desta venda')->assertSee('Registrar devolução');
        $this->get("/vendas/{$this->venda->id}/devolucao")->assertOk()->assertSee('Produtos devolvidos');
    }

    public function test_estoque_mostra_a_origem_de_cada_movimentacao(): void
    {
        $this->get('/estoque')->assertOk()->assertSee('Origem')->assertSee('venda #'.str_pad($this->venda->id, 4, '0', STR_PAD_LEFT));
    }
}
