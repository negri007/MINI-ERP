<?php

// Arquivo criado com o comando:
//   php artisan make:factory ProdutoFactory

namespace Database\Factories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProdutoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome' => ucfirst(fake('pt_BR')->words(2, true)),
            'descricao' => fake('pt_BR')->sentence(),
            'preco' => fake()->randomFloat(2, 2, 200),
            'estoque' => fake()->numberBetween(0, 50),
            'estoque_minimo' => 5,
            'categoria_id' => Categoria::factory(),
        ];
    }
}
