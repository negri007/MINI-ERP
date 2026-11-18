<?php

// Arquivo criado com o comando:
//   php artisan make:request DevolucaoRequest

namespace App\Http\Requests;

use App\Models\Devolucao;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Validação do formulário de devolução (o limite de quantidade por item fica no DevolucaoService,
// porque depende do que já foi devolvido antes)
class DevolucaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'itens' => 'required|array',
            'itens.*' => 'nullable|integer|min:0',
            'motivo' => ['required', Rule::in(array_keys(Devolucao::MOTIVOS))],
            'destino' => ['required', Rule::in(array_keys(Devolucao::DESTINOS))],
            'observacao' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'itens.required' => 'Informe a quantidade de pelo menos um produto para devolver.',
            'itens.*.integer' => 'A quantidade precisa ser um número inteiro.',
            'itens.*.min' => 'A quantidade não pode ser negativa.',
            'motivo.required' => 'Escolha o motivo da devolução.',
            'destino.required' => 'Escolha o que acontece com o produto devolvido.',
        ];
    }
}
