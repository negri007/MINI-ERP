<?php

// Arquivo criado com o comando:
//   php artisan make:request CadastroRequest

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Validação do "Criar conta" (página inicial)
class CadastroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            // confirmed = precisa vir igual no campo "password_confirmation"
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Informe seu nome.',
            'email.required' => 'Informe seu e-mail.',
            'email.email' => 'Digite um e-mail válido, como nome@loja.com.',
            'email.unique' => 'Já existe uma conta com este e-mail. Tente entrar.',
            'password.required' => 'Crie uma senha.',
            'password.min' => 'A senha precisa ter pelo menos 8 caracteres.',
            'password.confirmed' => 'As duas senhas não são iguais.',
        ];
    }
}
