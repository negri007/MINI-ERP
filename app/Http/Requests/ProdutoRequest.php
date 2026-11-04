<?php

// Arquivo criado com o comando:
//   php artisan make:request ProdutoRequest

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Validação do formulário de produtos
class ProdutoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $regras = [
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'preco' => 'required|numeric|min:0',
            'custo' => 'nullable|numeric|min:0|max:99999999.99', // opcional: vazio = não informado
            'estoque_minimo' => 'required|integer|min:0',
            'categoria_id' => 'required|exists:categorias,id',
            'fornecedor_id' => 'nullable|exists:fornecedores,id',
        ];

        // O estoque só é informado no cadastro (estoque inicial).
        // Depois disso, ele só muda por vendas ou movimentações de estoque.
        if ($this->isMethod('post')) {
            $regras['estoque'] = 'required|integer|min:0';
        }

        return $regras;
    }
}
