<?php

// Arquivo criado com o comando:
//   php artisan make:controller Auth/CadastroController

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\CadastroRequest;
use App\Models\User;

// "Criar conta": a pessoa se cadastra, mas só entra depois de aprovada em "Usuários"
class CadastroController extends Controller
{
    // Formulário de cadastro
    public function create()
    {
        return view('auth.cadastro');
    }

    // Grava o pedido de acesso (aprovado_em fica vazio = aguardando)
    public function store(CadastroRequest $request)
    {
        User::create($request->validated()); // a senha é criptografada pelo model User

        return redirect()->route('login')
            ->with('aviso', 'Cadastro enviado! Assim que um usuário do sistema aprovar seu acesso, é só entrar com seu e-mail e senha.');
    }
}
