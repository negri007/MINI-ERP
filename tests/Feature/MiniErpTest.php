<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Fornecedor;
use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiniErpTest extends TestCase
{
    use RefreshDatabase;

    // Todas as páginas de listagem e cadastro devem abrir
    public function test_paginas_abrem(): void
    {
        foreach (['/', '/categorias', '/categorias/create', '/fornecedores', '/fornecedores/create',
            '/clientes', '/clientes/create', '/produtos', '/produtos/create'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_crud_categoria(): void
    {
        $this->post('/categorias', ['nome' => 'Bebidas'])
            ->assertRedirect('/categorias')->assertSessionHas('success');
        $categoria = Categoria::firstOrFail();

        $this->get("/categorias/{$categoria->id}/edit")->assertOk()->assertSee('Bebidas');
        $this->put("/categorias/{$categoria->id}", ['nome' => 'Sucos'])->assertRedirect('/categorias');
        $this->assertSame('Sucos', $categoria->fresh()->nome);

        $this->delete("/categorias/{$categoria->id}")->assertRedirect('/categorias');
        $this->assertDatabaseCount('categorias', 0);
    }

    public function test_categoria_com_produtos_nao_pode_ser_excluida(): void
    {
        $categoria = Categoria::create(['nome' => 'Bebidas']);
        Produto::create(['nome' => 'Água', 'preco' => 2, 'estoque' => 10, 'categoria_id' => $categoria->id]);

        $this->delete("/categorias/{$categoria->id}")->assertSessionHas('error');
        $this->assertDatabaseCount('categorias', 1);
    }

    public function test_crud_fornecedor(): void
    {
        $this->post('/fornecedores', ['nome' => ''])->assertSessionHasErrors('nome');
        $this->post('/fornecedores', ['nome' => 'ACME'])->assertRedirect('/fornecedores');
        $fornecedor = Fornecedor::firstOrFail();

        $this->get("/fornecedores/{$fornecedor->id}/edit")->assertOk()->assertSee('ACME');
        $this->put("/fornecedores/{$fornecedor->id}", ['nome' => 'ACME Ltda', 'email' => 'contato@acme.com'])
            ->assertRedirect('/fornecedores');
        $this->assertSame('contato@acme.com', $fornecedor->fresh()->email);

        $this->delete("/fornecedores/{$fornecedor->id}")->assertRedirect('/fornecedores');
        $this->assertDatabaseCount('fornecedores', 0);
    }

    public function test_crud_cliente(): void
    {
        $this->post('/clientes', ['nome' => 'Maria', 'cpf_cnpj' => '123.456.789-00'])->assertRedirect('/clientes');
        $cliente = Cliente::firstOrFail();

        $this->get('/clientes')->assertSee('123.456.789-00');
        $this->put("/clientes/{$cliente->id}", ['nome' => 'Maria Silva'])->assertRedirect('/clientes');
        $this->assertSame('Maria Silva', $cliente->fresh()->nome);

        $this->delete("/clientes/{$cliente->id}")->assertRedirect('/clientes');
        $this->assertDatabaseCount('clientes', 0);
    }

    public function test_crud_produto(): void
    {
        $categoria = Categoria::create(['nome' => 'Bebidas']);
        $fornecedor = Fornecedor::create(['nome' => 'ACME']);

        $this->post('/produtos', ['nome' => 'Suco'])->assertSessionHasErrors(['preco', 'estoque', 'categoria_id']);
        $this->post('/produtos', [
            'nome' => 'Suco', 'preco' => '7.50', 'estoque' => 3,
            'categoria_id' => $categoria->id, 'fornecedor_id' => $fornecedor->id,
        ])->assertRedirect('/produtos');
        $produto = Produto::firstOrFail();

        $this->get('/produtos')->assertSee('R$ 7,50')->assertSee('ACME');
        $this->get('/')->assertSee('Suco');
        $this->get("/produtos/{$produto->id}/edit")->assertOk();
        $this->put("/produtos/{$produto->id}", [
            'nome' => 'Suco de Uva', 'preco' => 8, 'estoque' => 20, 'categoria_id' => $categoria->id, 'fornecedor_id' => '',
        ])->assertRedirect('/produtos');
        $this->assertNull($produto->fresh()->fornecedor_id);

        $this->delete("/produtos/{$produto->id}")->assertRedirect('/produtos');
        $this->assertDatabaseCount('produtos', 0);
    }
}
