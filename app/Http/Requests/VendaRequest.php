<?php

// Arquivo criado com o comando:
//   php artisan make:request VendaRequest

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Validação do formulário de vendas (cabeçalho + lista de itens)
class VendaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // Remove linhas de item deixadas em branco antes de validar
    protected function prepareForValidation(): void
    {
        $itens = collect($this->input('itens', []))
            ->filter(fn ($item) => ! empty($item['produto_id']))
            ->values()
            ->all();

        $this->merge(['itens' => $itens]);
    }

    public function rules(): array
    {
        return [
            'cliente_id' => 'required|exists:clientes,id',
            'data' => 'required|date',
            'observacao' => 'nullable|string|max:1000',
            'itens' => 'required|array|min:1',
            'itens.*.produto_id' => 'required|exists:produtos,id',
            'itens.*.quantidade' => 'required|integer|min:1',
        ];
    }

    // Mensagens específicas deste formulário
    public function messages(): array
    {
        return [
            'itens.required' => 'Adicione pelo menos um produto à venda.',
            'itens.min' => 'Adicione pelo menos um produto à venda.',
            'itens.*.quantidade.min' => 'A quantidade de cada item deve ser pelo menos 1.',
        ];
    }
}
