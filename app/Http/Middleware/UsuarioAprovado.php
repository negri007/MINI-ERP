<?php

// Arquivo criado com o comando:
//   php artisan make:middleware UsuarioAprovado

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// Barra quem está logado mas não tem cadastro aprovado (ex.: o acesso foi retirado depois).
// Registrado em bootstrap/app.php com o apelido "aprovado" e usado no grupo de rotas com login.
class UsuarioAprovado
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && ! $request->user()->estaAprovado()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => 'Seu cadastro aguarda aprovação.']);
        }

        return $next($request);
    }
}
