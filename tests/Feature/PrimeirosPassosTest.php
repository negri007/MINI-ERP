<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Cartão "Primeiros passos" do Dashboard: marca sozinho pelo banco
class PrimeirosPassosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_banco_vazio_mostra_o_cartao_com_zero_de_quatro(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Primeiros passos')
            ->assertSee('0 de 4 feitos')
            ->assertSee('aria-valuenow="0"', false)
            ->assertSee('href="'.route('categorias.create').'"', false)
            ->assertSee('Registrar venda');
    }

    public function test_consumidor_final_nao_conta_como_cliente(): void
    {
        // o Consumidor final já existe (vem da migration), mas o passo "cliente" continua pendente
        $passos = collect($this->get('/')->viewData('passos'))->keyBy('chave');
        $this->assertFalse($passos['cliente']['feito']);

        Cliente::factory()->create();
        $passos = collect($this->get('/')->viewData('passos'))->keyBy('chave');
        $this->assertTrue($passos['cliente']['feito']);
    }

    public function test_marca_os_passos_feitos_pelo_banco(): void
    {
        Categoria::factory()->create();
        Produto::factory()->create();

        $this->get('/')->assertSee('2 de 4 feitos')->assertSee('aria-valuenow="2"', false);
    }

    public function test_cartao_some_com_tudo_feito_e_volta_pelo_comando(): void
    {
        $this->seed(); // banco de exemplo: categoria, produto, cliente e vendas

        $this->get('/')->assertOk()->assertDontSee('id="primeirosPassos"', false);

        // "Mostrar primeiros passos" (Ctrl + K) abre com ?passos=1
        $this->get('/?passos=1')->assertSee('id="primeirosPassos"', false)->assertSee('Tudo pronto: 4 de 4 feitos');
    }
}
