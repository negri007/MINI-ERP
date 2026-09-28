<?php

// Arquivo criado com o comando:
//   php artisan make:factory FornecedorFactory

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FornecedorFactory extends Factory
{
    public function definition(): array
    {
        $faker = fake('pt_BR');

        return [
            'nome' => $faker->company(),
            'cnpj' => $faker->unique()->cnpj(), // já vem com máscara e dígitos válidos
            'telefone' => $faker->landlineNumber(),
            'email' => $faker->unique()->companyEmail(),
        ];
    }
}
