<?php

// Arquivo criado com o comando:
//   php artisan make:controller Auth/LoginController

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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

        if (! Auth::attempt($credenciais, $request->boolean('lembrar'))) {
            return back()
                ->withErrors(['email' => 'E-mail ou senha incorretos.'])
                ->onlyInput('email');
        }

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
