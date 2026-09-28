<?php

// Arquivo criado com o comando:
//   php artisan make:factory CategoriaFactory
// Factories geram dados falsos para testes e para popular o banco.

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoriaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome' => ucfirst(fake('pt_BR')->unique()->word()),
            'descricao' => fake('pt_BR')->sentence(),
        ];
    }
}
