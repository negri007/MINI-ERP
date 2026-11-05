<?php

// Arquivo criado com o comando:
//   php artisan make:controller Auth/LoginController

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Mostra a tela de login
    public function create()
    {
        return view('auth.login');
    }

    // Confere e-mail e senha e inicia a sessão
    public function store(Request $request)
    {
        $credenciais = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // 1) Confere e-mail e senha (sem entrar ainda)
        if (! Auth::validate($credenciais)) {
            return back()
                ->withErrors(['email' => 'E-mail ou senha incorretos.'])
                ->onlyInput('email');
        }

        // 2) Senha certa, mas o cadastro ainda não foi aprovado: não entra
        $usuario = User::where('email', $credenciais['email'])->firstOrFail();
        if (! $usuario->estaAprovado()) {
            return back()
                ->withErrors(['email' => 'Seu cadastro ainda aguarda aprovação. Peça a um usuário do sistema para liberar seu acesso em "Usuários".'])
                ->onlyInput('email');
        }

        // 3) Tudo certo: entra
        Auth::login($usuario, $request->boolean('lembrar'));

        // Gera uma nova sessão (protege contra roubo de sessão)
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    // Encerra a sessão
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
