<?php

// Arquivo criado com o comando:
//   php artisan make:request FornecedorRequest

namespace App\Http\Requests;

use App\Rules\CpfCnpj;
use App\Support\Documento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Validação do formulário de fornecedores: apenas o nome é obrigatório
class FornecedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // Antes de validar, padroniza o CNPJ com a máscara (00.000.000/0000-00)
    protected function prepareForValidation(): void
    {
        if ($this->filled('cnpj')) {
            $this->merge(['cnpj' => Documento::formatar($this->input('cnpj'))]);
        }
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            // unique ignorando o próprio fornecedor na edição
            'cnpj' => ['nullable', new CpfCnpj('cnpj'), Rule::unique('fornecedores', 'cnpj')->ignore($this->route('fornecedor'))],
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ];
    }
}
