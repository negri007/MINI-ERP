<?php

// Arquivo criado com o comando:
//   php artisan make:controller UsuarioController

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

// Usuários: aprovar ou recusar os pedidos de acesso feitos em "Criar conta".
// Hoje todo usuário aprovado pode aprovar outros (separar dono e caixa está nos planos).
class UsuarioController extends Controller
{
    public function index()
    {
        return view('usuarios.index', [
            'pendentes' => User::pendentes()->orderBy('created_at')->get(),
            'aprovados' => User::aprovados()->orderBy('name')->get(),
        ]);
    }

    // Libera o acesso
    public function aprovar(User $usuario)
    {
        if (! $usuario->estaAprovado()) {
            $usuario->aprovar();
        }

        return redirect()->route('usuarios.index')->with('success', "{$usuario->name} já pode entrar no sistema.");
    }

    // Recusa o pedido: apaga o cadastro que ainda não foi aprovado.
    // Usuário já aprovado não é apagado por aqui (as vendas e movimentações guardam quem registrou).
    public function recusar(Request $request, User $usuario)
    {
        abort_if($usuario->estaAprovado(), 403, 'Só dá para recusar cadastros que aguardam aprovação.');

        $usuario->delete();

        return redirect()->route('usuarios.index')->with('success', "Pedido de {$usuario->name} recusado.");
    }
}
