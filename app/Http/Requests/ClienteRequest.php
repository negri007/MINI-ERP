<?php

// Arquivo criado com o comando:
//   php artisan make:request ClienteRequest

namespace App\Http\Requests;

use App\Rules\CpfCnpj;
use App\Support\Documento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Validação do formulário de clientes: apenas o nome é obrigatório
class ClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // Antes de validar, padroniza o CPF/CNPJ com a máscara
    protected function prepareForValidation(): void
    {
        if ($this->filled('cpf_cnpj')) {
            $this->merge(['cpf_cnpj' => Documento::formatar($this->input('cpf_cnpj'))]);
        }
    }

    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            // unique ignorando o próprio cliente na edição
            'cpf_cnpj' => ['nullable', new CpfCnpj, Rule::unique('clientes', 'cpf_cnpj')->ignore($this->route('cliente'))],
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ];
    }
}
