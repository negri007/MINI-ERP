<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Cadastro rápido e busca de clientes usados pela tela de Nova venda
class ClienteRapidoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_cadastro_rapido_cria_o_cliente_e_devolve_os_dados(): void
    {
        $resposta = $this->postJson('/clientes/rapido', [
            'nome' => 'Maria Souza',
            'cpf_cnpj' => '52998224725', // só números: a máscara é colocada pelo servidor
            'telefone' => '(11) 98888-7777',
        ]);

        $resposta->assertCreated()->assertJson([
            'nome' => 'Maria Souza',
            'documento' => '529.982.247-25',
            'consumidor_final' => false,
        ]);
        $this->assertDatabaseHas('clientes', ['id' => $resposta->json('id'), 'cpf_cnpj' => '529.982.247-25']);
    }

    public function test_cadastro_rapido_usa_a_mesma_validacao_do_cadastro_normal(): void
    {
        Cliente::factory()->create(['cpf_cnpj' => '529.982.247-25']);

        // sem nome, CPF inválido
        $this->postJson('/clientes/rapido', ['nome' => '', 'cpf_cnpj' => '111.111.111-11'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nome', 'cpf_cnpj']);

        // CPF já cadastrado
        $this->postJson('/clientes/rapido', ['nome' => 'Outra Maria', 'cpf_cnpj' => '529.982.247-25'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['cpf_cnpj']);
    }

    public function test_busca_encontra_por_nome_e_por_cpf_so_com_numeros(): void
    {
        Cliente::factory()->create(['nome' => 'João Pereira', 'cpf_cnpj' => '529.982.247-25']);
        Cliente::factory()->create(['nome' => 'Ana Lima', 'cpf_cnpj' => null]);

        $this->getJson('/clientes/buscar?q=joão')->assertOk()->assertJsonCount(1)->assertJsonPath('0.nome', 'João Pereira');
        $this->getJson('/clientes/buscar?q=5299822')->assertOk()->assertJsonCount(1)->assertJsonPath('0.nome', 'João Pereira');
    }

    public function test_busca_vazia_mostra_o_consumidor_final_primeiro(): void
    {
        Cliente::factory()->create(['nome' => 'Aaron Alves']);

        $this->getJson('/clientes/buscar')
            ->assertOk()
            ->assertJsonPath('0.nome', 'Consumidor final')
            ->assertJsonPath('0.consumidor_final', true);
    }

    public function test_busca_e_cadastro_rapido_exigem_login(): void
    {
        auth()->logout();

        $this->getJson('/clientes/buscar?q=a')->assertUnauthorized();
        $this->postJson('/clientes/rapido', ['nome' => 'Sem login'])->assertUnauthorized();
        $this->assertDatabaseMissing('clientes', ['nome' => 'Sem login']);
    }
}
