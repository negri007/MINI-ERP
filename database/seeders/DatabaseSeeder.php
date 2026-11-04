<?php

// Popula o banco com dados de exemplo. Rodar com:
//   php artisan db:seed
// ou, apagando tudo e recriando:
//   php artisan migrate:fresh --seed

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Fornecedor;
use App\Models\Produto;
use App\Models\User;
use App\Models\Venda;
use App\Services\EstoqueService;
use App\Services\VendaService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DatabaseSeeder extends Seeder
{
    public function run(EstoqueService $estoque, VendaService $vendas): void
    {
        // Usuário para entrar no sistema
        $admin = User::firstOrCreate(
            ['email' => 'admin@minierp.com'],
            ['name' => 'Administrador', 'password' => 'admin123'] // a senha é criptografada pelo model User
        );

        // Se já existem dados, não duplica
        if (Categoria::exists()) {
            $this->command->info('O banco já tem dados; só o usuário admin foi conferido.');

            return;
        }

        // As movimentações ficam registradas em nome do admin
        Auth::setUser($admin);

        // Categorias e seus produtos: [nome, preço, estoque inicial, estoque mínimo]
        $catalogo = [
            'Bebidas' => [['Água mineral 500ml', 2.50, 120, 30], ['Suco de laranja 1L', 8.90, 40, 10], ['Refrigerante 2L', 10.50, 4, 10]],
            'Mercearia' => [['Arroz 5kg', 27.90, 35, 10], ['Feijão carioca 1kg', 8.49, 50, 15], ['Café 500g', 18.90, 3, 8]],
            'Limpeza' => [['Detergente 500ml', 2.99, 80, 20], ['Sabão em pó 1kg', 14.90, 25, 10]],
            'Higiene' => [['Sabonete 90g', 2.29, 100, 25], ['Creme dental 90g', 4.99, 0, 10]],
        ];

        $fornecedores = Fornecedor::factory(3)->create();

        foreach ($catalogo as $nomeCategoria => $produtos) {
            $categoria = Categoria::create(['nome' => $nomeCategoria, 'descricao' => "Produtos de {$nomeCategoria}"]);

            foreach ($produtos as $i => [$nome, $preco, $inicial, $minimo]) {
                $produto = Produto::create([
                    'nome' => $nome,
                    'preco' => $preco,
                    // custo entre 55% e 70% do preço (sempre abaixo do preço de venda)
                    'custo' => round($preco * (0.55 + ($i % 4) * 0.05), 2),
                    'estoque' => 0,
                    'estoque_minimo' => $minimo,
                    'categoria_id' => $categoria->id,
                    'fornecedor_id' => $fornecedores->random()->id,
                ]);

                // Estoque inicial entra pelo histórico, como no sistema
                if ($inicial > 0) {
                    $estoque->entrada($produto, $inicial, 'Estoque inicial');
                }
            }
        }

        $clientes = Cliente::factory(8)->create();

        // Vendas espalhadas pelos últimos 14 dias (só de produtos com estoque sobrando)
        for ($i = 0; $i < 15; $i++) {
            $disponiveis = Produto::where('estoque', '>', 10)->inRandomOrder()->limit(rand(1, 3))->get();

            $itens = $disponiveis->map(fn ($p) => ['produto_id' => $p->id, 'quantidade' => rand(1, 3)])->all();

            if ($itens) {
                $vendas->registrar($clientes->random()->id, Carbon::today()->subDays(rand(0, 13))->toDateString(), $itens, array_rand(Venda::FORMAS_PAGAMENTO));
            }
        }

        $this->command->info('Dados de exemplo criados. Login: admin@minierp.com / admin123');
    }
}
