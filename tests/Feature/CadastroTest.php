<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Criar conta com aprovação: cadastra, não entra até ser aprovado, aprovação e recusa
class CadastroTest extends TestCase
{
    use RefreshDatabase;

    private function cadastrar(array $extra = [])
    {
        return $this->post('/cadastro', array_merge([
            'name' => 'Maria Caixa', 'email' => 'maria@loja.com',
            'password' => 'senha12345', 'password_confirmation' => 'senha12345',
        ], $extra));
    }

    public function test_cadastro_fica_aguardando_e_nao_entra(): void
    {
        $this->get('/cadastro')->assertOk()->assertSee('Criar conta');
        $this->cadastrar()->assertRedirect('/login');

        $maria = User::where('email', 'maria@loja.com')->first();
        $this->assertFalse($maria->estaAprovado());
        $this->assertGuest();

        $this->post('/login', ['email' => 'maria@loja.com', 'password' => 'senha12345'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_nao_da_para_se_aprovar_pelo_formulario(): void
    {
        $this->cadastrar(['aprovado_em' => now()->toDateTimeString()]);

        $this->assertNull(User::where('email', 'maria@loja.com')->first()->aprovado_em);
    }

    public function test_validacao_do_cadastro(): void
    {
        User::factory()->create(['email' => 'maria@loja.com']);

        $this->cadastrar(['password_confirmation' => 'outra'])->assertSessionHasErrors(['email', 'password']);
    }

    public function test_aprovar_libera_o_login(): void
    {
        $this->cadastrar();
        $maria = User::where('email', 'maria@loja.com')->first();

        $this->actingAs(User::factory()->create());
        $this->get('/usuarios')->assertOk()->assertSee('Maria Caixa');
        $this->post("/usuarios/{$maria->id}/aprovar")->assertRedirect('/usuarios');
        $this->post('/logout');

        $this->post('/login', ['email' => 'maria@loja.com', 'password' => 'senha12345'])->assertRedirect('/');
        $this->assertAuthenticatedAs($maria);
    }

    public function test_recusar_apaga_so_pedido_pendente(): void
    {
        $pendente = User::factory()->pendente()->create();
        $aprovado = User::factory()->create();

        $this->actingAs(User::factory()->create());
        $this->delete("/usuarios/{$aprovado->id}")->assertForbidden();
        $this->delete("/usuarios/{$pendente->id}")->assertRedirect('/usuarios');

        $this->assertModelMissing($pendente);
        $this->assertModelExists($aprovado);
    }

    public function test_usuario_pendente_logado_e_barrado(): void
    {
        $this->actingAs(User::factory()->pendente()->create());

        $this->get('/produtos')->assertRedirect('/login');
        $this->assertGuest();
    }
}
