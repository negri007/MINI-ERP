<?php

// Arquivo criado com o comando:
//   php artisan make:request CategoriaRequest

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Validação do formulário de categorias (usada no cadastro e na edição)
class CategoriaRequest extends FormRequest
{
    // Quem pode enviar este formulário (o login já é exigido nas rotas)
    public function authorize(): bool
    {
        return true;
    }

    // Regras de validação
    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
        ];
    }
}
