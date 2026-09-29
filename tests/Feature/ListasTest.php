<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Fornecedor;
use App\Models\Produto;
use App\Models\User;
use App\Services\VendaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Testes das listas: ordenação, filtros em pílulas, busca, vitrine e detalhes da linha
class ListasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_ordenacao_pelo_cabecalho(): void
    {
        $categoria = Categoria::factory()->create();
        Produto::factory()->create(['nome' => 'Barato', 'preco' => 1, 'categoria_id' => $categoria->id]);
        Produto::factory()->create(['nome' => 'Caro', 'preco' => 99, 'categoria_id' => $categoria->id]);

        $this->get('/produtos?ordem=preco&dir=desc')->assertSeeInOrder(['Caro', 'Barato']);
        $this->get('/produtos?ordem=preco&dir=asc')->assertSeeInOrder(['Barato', 'Caro']);

        // Coluna fora da lista permitida é ignorada (volta para a ordem padrão, sem erro)
        $this->get('/produtos?ordem=senha&dir=desc')->assertOk();
    }

    public function test_filtro_por_categoria_e_vitrine(): void
    {
        $bebidas = Categoria::factory()->create(['nome' => 'Bebidas']);
        $limpeza = Categoria::factory()->create(['nome' => 'Limpeza']);
        Produto::factory()->create(['nome' => 'Suco', 'categoria_id' => $bebidas->id]);
        Produto::factory()->create(['nome' => 'Sabão', 'categoria_id' => $limpeza->id]);

        $this->get("/produtos?categoria_id={$bebidas->id}")->assertSee('Suco')->assertDontSee('Sabão');
        $this->get('/produtos?visao=vitrine')->assertOk()->assertSee('cartao-produto', false)->assertSee('Suco');
    }

    public function test_detalhes_do_produto_mostram_movimentacoes_e_vendas(): void
    {
        $produto = Produto::factory()->create(['nome' => 'Suco', 'preco' => 5, 'estoque' => 0]);
        $this->post('/estoque', ['produto_id' => $produto->id, 'tipo' => 'entrada', 'quantidade' => 10, 'motivo' => 'Compra NF 777']);
        app(VendaService::class)->registrar(Cliente::factory()->create()->id, now()->toDateString(), [['produto_id' => $produto->id, 'quantidade' => 3]]);

        $this->get('/produtos')
            ->assertSee('Compra NF 777')
            ->assertSee('3 unidades · R$ 15,00');
    }

    public function test_filtros_de_vendas_e_busca_pelo_numero(): void
    {
        $servico = app(VendaService::class);
        $produto = Produto::factory()->create(['estoque' => 50]);
        $ana = Cliente::factory()->create(['nome' => 'Ana Souza']);
        $bruno = Cliente::factory()->create(['nome' => 'Bruno Lima']);

        $hoje = $servico->registrar($ana->id, now()->toDateString(), [['produto_id' => $produto->id, 'quantidade' => 1]]);
        $antiga = $servico->registrar($bruno->id, now()->subDays(20)->toDateString(), [['produto_id' => $produto->id, 'quantidade' => 1]]);
        $servico->cancelar($antiga);

        $this->get('/vendas?periodo=hoje')->assertSee('Ana Souza')->assertDontSee('Bruno Lima');
        $this->get('/vendas?status=cancelada')->assertSee('Bruno Lima')->assertDontSee('Ana Souza');
        $this->get("/vendas?busca=%23{$hoje->id}")->assertSee('Ana Souza');
        $this->get('/vendas?busca=bruno')->assertSee('Bruno Lima')->assertDontSee('Ana Souza');
    }

    public function test_filtros_de_clientes_fornecedores_e_categorias(): void
    {
        $comCompra = Cliente::factory()->create(['nome' => 'Comprador']);
        Cliente::factory()->create(['nome' => 'Curioso']);
        app(VendaService::class)->registrar($comCompra->id, now()->toDateString(), [['produto_id' => Produto::factory()->create(['estoque' => 5])->id, 'quantidade' => 1]]);

        $this->get('/clientes?filtro=com')->assertSee('Comprador')->assertDontSee('Curioso');
        $this->get('/clientes?filtro=sem')->assertSee('Curioso')->assertDontSee('Comprador');

        Fornecedor::factory()->create(['nome' => 'Sem Nada Ltda']);
        $this->get('/fornecedores?filtro=sem')->assertSee('Sem Nada Ltda');

        Categoria::factory()->create(['nome' => 'Vazia']);
        $this->get('/categorias?filtro=vazias')->assertSee('Vazia');
    }
}
