<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Dicas das telas e telas vazias que explicam o próximo passo
class MicroajudaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_lista_sem_nada_cadastrado_oferece_o_cadastro(): void
    {
        $this->get('/produtos')
            ->assertOk()
            ->assertSee('Nenhum produto ainda')
            ->assertSee('Cadastrar produto');
    }

    public function test_busca_sem_resultado_oferece_limpar_os_filtros(): void
    {
        Categoria::factory()->create(['nome' => 'Sucos']);

        $this->get('/categorias?busca=xyz')
            ->assertOk()
            ->assertSee('Nenhuma categoria com &quot;xyz&quot;', false)
            ->assertSee('Limpar filtros');
    }

    public function test_telas_mostram_a_dica_com_o_botao_entendi(): void
    {
        $this->get('/estoque/movimentar')
            ->assertOk()
            ->assertSee('Vendas baixam o estoque sozinhas.')
            ->assertSee('Entendi');
    }
}
