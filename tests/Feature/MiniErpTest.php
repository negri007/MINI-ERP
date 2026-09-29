<?php

// Arquivo criado com o comando:
//   php artisan make:test MiniErpTest
// Para rodar os testes:  php artisan test

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Fornecedor;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiniErpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Todos os testes deste arquivo rodam com um usuário logado
        $this->actingAs(User::factory()->create());
    }

    // Todas as páginas de listagem e cadastro devem abrir
    public function test_paginas_abrem(): void
    {
        foreach (['/', '/categorias', '/categorias/create', '/fornecedores', '/fornecedores/create',
            '/clientes', '/clientes/create', '/produtos', '/produtos/create', '/vendas', '/vendas/create',
            '/estoque', '/estoque/movimentar', '/relatorios/vendas'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_mensagens_de_validacao_em_portugues(): void
    {
        $this->post('/categorias', ['nome' => ''])
            ->assertSessionHasErrors(['nome' => 'O campo nome é obrigatório.']);
    }

    public function test_crud_categoria(): void
    {
        $this->post('/categorias', ['nome' => 'Bebidas'])
            ->assertRedirect('/categorias')->assertSessionHas('success');
        $categoria = Categoria::firstOrFail();

        $this->get("/categorias/{$categoria->id}/edit")->assertOk()->assertSee('value="Bebidas"', false); // o valor no campo (a dica também cita "Bebidas")
        $this->put("/categorias/{$categoria->id}", ['nome' => 'Sucos'])->assertRedirect('/categorias');
        $this->assertSame('Sucos', $categoria->fresh()->nome);

        $this->delete("/categorias/{$categoria->id}")->assertRedirect('/categorias');
        $this->assertDatabaseCount('categorias', 0);
    }

    public function test_categoria_com_produtos_nao_pode_ser_excluida(): void
    {
        $produto = Produto::factory()->create();

        $this->delete("/categorias/{$produto->categoria_id}")->assertSessionHas('error');
        $this->assertDatabaseCount('categorias', 1);
    }

    public function test_busca_e_paginacao(): void
    {
        // nomes que não aparecem nos textos de ajuda da tela (a dica cita "Bebidas, Limpeza")
        Categoria::factory()->create(['nome' => 'Sucos']);
        Categoria::factory()->create(['nome' => 'Higiene']);

        $this->get('/categorias?busca=suc')->assertSee('Sucos')->assertDontSee('Higiene');

        Categoria::factory(12)->create();
        $this->get('/categorias')->assertSee('page=2');
    }

    public function test_crud_fornecedor(): void
    {
        $this->post('/fornecedores', ['nome' => ''])->assertSessionHasErrors('nome');
        // CNPJ sem máscara é salvo já formatado
        $this->post('/fornecedores', ['nome' => 'ACME', 'cnpj' => '11222333000181'])->assertRedirect('/fornecedores');
        $fornecedor = Fornecedor::firstOrFail();
        $this->assertSame('11.222.333/0001-81', $fornecedor->cnpj);

        $this->get("/fornecedores/{$fornecedor->id}/edit")->assertOk()->assertSee('ACME');
        // Editar mantendo o mesmo CNPJ não pode acusar "já existe"
        $this->put("/fornecedores/{$fornecedor->id}", ['nome' => 'ACME Ltda', 'cnpj' => '11.222.333/0001-81', 'email' => 'contato@acme.com'])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/fornecedores');
        $this->assertSame('contato@acme.com', $fornecedor->fresh()->email);

        $this->delete("/fornecedores/{$fornecedor->id}")->assertRedirect('/fornecedores');
        $this->assertDatabaseCount('fornecedores', 0);
    }

    public function test_cpf_e_cnpj_sao_validados(): void
    {
        $this->post('/clientes', ['nome' => 'Maria', 'cpf_cnpj' => '123.456.789-00'])->assertSessionHasErrors('cpf_cnpj');
        $this->post('/fornecedores', ['nome' => 'ACME', 'cnpj' => '529.982.247-25'])->assertSessionHasErrors('cnpj'); // CPF no campo de CNPJ

        $this->post('/clientes', ['nome' => 'Maria', 'cpf_cnpj' => '529.982.247-25'])->assertSessionHasNoErrors();
        $this->post('/clientes', ['nome' => 'João', 'cpf_cnpj' => '52998224725'])
            ->assertSessionHasErrors(['cpf_cnpj' => 'Já existe um cadastro com este CPF/CNPJ.']);
    }

    public function test_crud_cliente(): void
    {
        $this->post('/clientes', ['nome' => 'Maria', 'cpf_cnpj' => '529.982.247-25'])->assertRedirect('/clientes');
        $cliente = Cliente::comuns()->firstOrFail(); // o Consumidor final já vem criado pela migration

        $this->get('/clientes')->assertSee('529.982.247-25');
        $this->put("/clientes/{$cliente->id}", ['nome' => 'Maria Silva', 'cpf_cnpj' => '529.982.247-25'])->assertRedirect('/clientes');
        $this->assertSame('Maria Silva', $cliente->fresh()->nome);

        $this->delete("/clientes/{$cliente->id}")->assertRedirect('/clientes');
        $this->assertSame(0, Cliente::comuns()->count());
    }

    public function test_crud_produto(): void
    {
        $categoria = Categoria::factory()->create();
        $fornecedor = Fornecedor::factory()->create(['nome' => 'ACME']);

        $this->post('/produtos', ['nome' => 'Suco'])->assertSessionHasErrors(['preco', 'estoque', 'estoque_minimo', 'categoria_id']);
        $this->post('/produtos', [
            'nome' => 'Suco', 'preco' => '7.50', 'estoque' => 3, 'estoque_minimo' => 5,
            'categoria_id' => $categoria->id, 'fornecedor_id' => $fornecedor->id,
        ])->assertRedirect('/produtos');
        $produto = Produto::firstOrFail();

        // O estoque inicial entra como movimentação
        $this->assertSame(3, $produto->estoque);
        $this->assertDatabaseHas('movimentacoes_estoque', ['produto_id' => $produto->id, 'tipo' => 'entrada', 'quantidade' => 3]);

        $this->get('/produtos')->assertSee('R$ 7,50')->assertSee('ACME');
        $this->get('/')->assertSee('Suco'); // aparece no alerta de estoque baixo (3 <= 5)
        $this->get("/produtos/{$produto->id}/edit")->assertOk();

        // A edição não mexe no estoque
        $this->put("/produtos/{$produto->id}", [
            'nome' => 'Suco de Uva', 'preco' => 8, 'estoque' => 999, 'estoque_minimo' => 2,
            'categoria_id' => $categoria->id, 'fornecedor_id' => '',
        ])->assertRedirect('/produtos');
        $produto->refresh();
        $this->assertNull($produto->fornecedor_id);
        $this->assertSame(3, $produto->estoque);

        $this->delete("/produtos/{$produto->id}")->assertRedirect('/produtos');
        $this->assertDatabaseCount('produtos', 0);
    }

    public function test_filtro_de_estoque_baixo(): void
    {
        Produto::factory()->create(['nome' => 'Acabando', 'estoque' => 1, 'estoque_minimo' => 5]);
        Produto::factory()->create(['nome' => 'Sobrando', 'estoque' => 50, 'estoque_minimo' => 5]);

        $this->get('/produtos?estoque_baixo=1')->assertSee('Acabando')->assertDontSee('Sobrando');
    }
}
