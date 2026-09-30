<?php

// Arquivo criado com o comando:
//   php artisan make:request VendaRequest

namespace App\Http\Requests;

use App\Models\Venda;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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

        // Desconto no formato brasileiro: "5,50" ou "1.234,56" viram "5.50" e "1234.56"
        // (com vírgula, ela é o decimal e os pontos são milhar). Vazio vira 0.
        $desconto = trim((string) $this->input('desconto', ''));
        if (str_contains($desconto, ',')) {
            $desconto = str_replace(',', '.', str_replace('.', '', $desconto));
        }
        $this->merge(['desconto' => $desconto === '' ? 0 : $desconto]);
    }

    public function rules(): array
    {
        return [
            'cliente_id' => 'required|exists:clientes,id',
            'data' => 'required|date',
            'observacao' => 'nullable|string|max:1000',
            'forma_pagamento' => ['required', Rule::in(array_keys(Venda::FORMAS_PAGAMENTO))],
            // o limite "não passa do subtotal" é conferido no VendaService, que calcula o subtotal
            'desconto' => 'nullable|numeric|min:0|max:9999999999.99',
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
            'forma_pagamento.required' => 'Escolha a forma de pagamento.',
            'forma_pagamento.in' => 'Escolha uma das formas de pagamento da lista.',
            'desconto.min' => 'O desconto não pode ser negativo.',
            'desconto.numeric' => 'Digite o desconto só com números, como 5,00.',
        ];
    }
}
