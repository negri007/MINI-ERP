<?php

namespace Tests\Feature;

use App\Models\Produto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Página de apresentação: "/" para visitantes e "/sobre" para todos
class ApresentacaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_ve_a_apresentacao_em_inicio(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertViewIs('apresentacao')
            ->assertSee('Controle de estoque e vendas para quem toca o balcão.')
            ->assertSee('Hoje todo usuário acessa tudo. Separar dono e caixa está nos planos.')
            ->assertSee('Não emite nota fiscal eletrônica. Use o emissor que seu contador indicar.')
            ->assertSee('href="'.route('login').'"', false);
    }

    public function test_logado_continua_vendo_o_dashboard_em_inicio(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/')->assertOk()->assertViewIs('dashboard');
    }

    public function test_sobre_abre_com_e_sem_login(): void
    {
        $this->get('/sobre')->assertOk()->assertSee('Entrar');

        $this->actingAs(User::factory()->create());
        $this->get('/sobre')->assertOk()->assertViewIs('apresentacao')->assertSee('Ir para o Dashboard');
    }

    public function test_amostra_nao_grava_nada_no_banco(): void
    {
        $this->get('/sobre')->assertSee('Tela do sistema com dados de exemplo.');

        $this->assertSame(0, Produto::count());
    }
}
