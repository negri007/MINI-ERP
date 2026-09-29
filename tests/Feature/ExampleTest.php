<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    // Quem não está logado vê a apresentação em "/"; as telas internas mandam para o login
    public function test_visitante_ve_a_apresentacao_e_as_telas_internas_pedem_login(): void
    {
        $this->get('/')->assertOk()->assertViewIs('apresentacao');
        $this->get('/produtos')->assertRedirect('/login');
        $this->get('/login')->assertOk()->assertSee('Entrar');
    }
}
