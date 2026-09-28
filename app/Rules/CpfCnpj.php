<?php

// Arquivo criado com o comando:
//   php artisan make:rule CpfCnpj

namespace App\Rules;

use App\Support\Documento;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

// Regra de validação: aceita CPF e/ou CNPJ com dígitos verificadores corretos
class CpfCnpj implements ValidationRule
{
    // $aceita pode ser 'ambos', 'cpf' ou 'cnpj'
    public function __construct(private string $aceita = 'ambos') {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $tamanho = strlen(Documento::digitos($value));

        $valido = match (true) {
            $tamanho === 11 && $this->aceita !== 'cnpj' => Documento::cpfValido($value),
            $tamanho === 14 && $this->aceita !== 'cpf' => Documento::cnpjValido($value),
            default => false,
        };

        if (! $valido) {
            $fail(match ($this->aceita) {
                'cpf' => 'O :attribute informado não é um CPF válido.',
                'cnpj' => 'O :attribute informado não é um CNPJ válido.',
                default => 'O :attribute informado não é um CPF ou CNPJ válido.',
            });
        }
    }
}
