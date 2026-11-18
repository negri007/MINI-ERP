<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'aprovado_em' => 'datetime', // vazio = cadastro aguardando aprovação
            'password' => 'hashed',
        ];
    }

    // "aprovado_em" fica fora do $fillable de propósito: quem se cadastra não consegue
    // se aprovar sozinho mandando esse campo no formulário. Só a tela Usuários aprova.
    public function estaAprovado(): bool
    {
        return $this->aprovado_em !== null;
    }

    public function aprovar(): void
    {
        $this->forceFill(['aprovado_em' => now()])->save();
    }

    // Consultas prontas: User::pendentes()->get() e User::aprovados()->get()
    public function scopePendentes($query)
    {
        return $query->whereNull('aprovado_em');
    }

    public function scopeAprovados($query)
    {
        return $query->whereNotNull('aprovado_em');
    }
}
