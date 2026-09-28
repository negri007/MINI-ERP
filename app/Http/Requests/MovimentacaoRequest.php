<?php

// Arquivo criado com o comando:
//   php artisan make:request MovimentacaoRequest

namespace App\Http\Requests;

use App\Models\MovimentacaoEstoque;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Validação da entrada/saída manual de estoque
class MovimentacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'produto_id' => 'required|exists:produtos,id',
            'tipo' => ['required', Rule::in([MovimentacaoEstoque::ENTRADA, MovimentacaoEstoque::SAIDA])],
            'quantidade' => 'required|integer|min:1',
            'motivo' => 'required|string|max:255',
        ];
    }
}
