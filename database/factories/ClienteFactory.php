<?php

// Arquivo criado com o comando:
//   php artisan make:factory ClienteFactory

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    public function definition(): array
    {
        $faker = fake('pt_BR');

        return [
            'nome' => $faker->firstName().' '.$faker->lastName(),
            'cpf_cnpj' => $faker->unique()->cpf(), // já vem com máscara e dígitos válidos
            'telefone' => $faker->cellphoneNumber(),
            'email' => $faker->unique()->safeEmail(),
        ];
    }
}
