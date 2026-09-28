<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Venda;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_e_logout(): void
    {
        User::factory()->create(['email' => 'admin@minierp.com', 'password' => 'admin123']);

        $this->post('/login', ['email' => 'admin@minierp.com', 'password' => 'errada'])
            ->assertSessionHasErrors(['email' => 'E-mail ou senha incorretos.']);
        $this->assertGuest();

        $this->post('/login', ['email' => 'admin@minierp.com', 'password' => 'admin123'])->assertRedirect('/');
        $this->assertAuthenticated();

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_seeder_cria_dados_de_exemplo(): void
    {
        $this->seed();

        $this->post('/login', ['email' => 'admin@minierp.com', 'password' => 'admin123']);
        $this->get('/')->assertOk();
        $this->assertDatabaseCount('categorias', 4);
        $this->assertGreaterThan(0, Venda::count());
    }
}
