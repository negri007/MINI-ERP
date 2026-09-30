<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Produto;
use App\Models\User;
use App\Models\Venda;
use App\Services\VendaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Caixa do dia: totais por forma de pagamento, canceladas à parte, só o dia escolhido
class CaixaTest extends TestCase
{
    use RefreshDatabase;

    private Cliente $cliente;

    private Produto $produto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
        $this->cliente = Cliente::factory()->create();
        $this->produto = Produto::factory()->create(['preco' => 10, 'estoque' => 100]);
    }

    private function vender(string $forma, int $quantidade, string $data, float $desconto = 0): Venda
    {
        return app(VendaService::class)->registrar(
            $this->cliente->id, $data, [['produto_id' => $this->produto->id, 'quantidade' => $quantidade]], $forma, $desconto
        );
    }

    public function test_soma_por_forma_de_pagamento_com_canceladas_a_parte(): void
    {
        $dia = '2026-09-10';
        $this->vender('dinheiro', 2, $dia);          // 20
        $this->vender('dinheiro', 1, $dia, 3);       // 10 - 3 = 7
        $this->vender('pix', 5, $dia);               // 50
        app(VendaService::class)->cancelar($this->vender('credito', 4, $dia)); // cancelada: 40, fora do total
        $this->vender('pix', 9, '2026-09-11');       // outro dia: não conta

        $resposta = $this->get("/caixa?data={$dia}")->assertOk();

        $porForma = $resposta->viewData('porForma');
        $this->assertEquals(27.0, $porForma['dinheiro']['total']);
        $this->assertSame(2, $porForma['dinheiro']['quantidade']);
        $this->assertEquals(50.0, $porForma['pix']['total']);
        $this->assertEquals(0.0, $porForma['credito']['total']);
        $this->assertEquals(0.0, $porForma['debito']['total']);

        $this->assertSame([
            'total' => 77.0, 'quantidade' => 3, 'descontos' => 3.0, 'canceladas' => 1, 'total_canceladas' => 40.0,
        ], $resposta->viewData('resumo'));
    }

    public function test_venda_antiga_sem_forma_aparece_como_nao_informada(): void
    {
        $venda = $this->vender('pix', 1, '2026-09-10');
        $venda->forceFill(['forma_pagamento' => null])->save();

        $porForma = $this->get('/caixa?data=2026-09-10')->viewData('porForma');
        $this->assertEquals(10.0, $porForma['nao_informada']['total']);
        $this->assertEquals(0.0, $porForma['pix']['total']);
    }

    public function test_data_invalida_ou_vazia_usa_hoje(): void
    {
        $this->get('/caixa?data=31/02/2026')->assertOk()->assertViewHas('dia', fn ($dia) => $dia->isToday());
        $this->get('/caixa?data=2026-02-31')->assertOk()->assertViewHas('dia', fn ($dia) => $dia->isToday());
        $this->get('/caixa')->assertOk()->assertSee('Nenhuma venda em '.now()->format('d/m/Y'))->assertSee('Registrar venda');
    }

    public function test_caixa_esta_no_menu_na_busca_rapida_e_exige_login(): void
    {
        $this->get('/vendas')
            ->assertSee('href="'.route('caixa.index').'"', false)
            ->assertSee('Caixa do dia');

        auth()->logout();
        $this->get('/caixa')->assertRedirect('/login');
    }
}
