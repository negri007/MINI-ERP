<?php

// Arquivo criado com o comando:
//   php artisan make:model Cliente

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    // Nome da tabela no banco de dados
    protected $table = 'clientes';

    // Campos que podem ser preenchidos em massa
    protected $fillable = ['nome', 'cpf_cnpj', 'telefone', 'email'];

    // Um cliente pode ter várias vendas
    public function vendas(): HasMany
    {
        return $this->hasMany(Venda::class);
    }

    // Filtro de busca por nome, CPF/CNPJ ou e-mail
    public function scopeBusca(Builder $query, ?string $termo): Builder
    {
        if (! $termo) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($termo) {
            $q->where('nome', 'like', "%{$termo}%")
                ->orWhere('cpf_cnpj', 'like', "%{$termo}%")
                ->orWhere('email', 'like', "%{$termo}%");
        });
    }
}
