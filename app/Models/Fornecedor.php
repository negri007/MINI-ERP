<?php

// Arquivo criado com o comando:
//   php artisan make:model Fornecedor

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fornecedor extends Model
{
    use HasFactory;

    // Nome da tabela no banco de dados (o plural automático seria "fornecedors")
    protected $table = 'fornecedores';

    // Campos que podem ser preenchidos em massa
    protected $fillable = ['nome', 'cnpj', 'telefone', 'email'];

    // Um fornecedor fornece vários produtos
    public function produtos(): HasMany
    {
        return $this->hasMany(Produto::class);
    }

    // Filtro de busca usado na listagem
    public function scopeBusca(Builder $query, ?string $termo): Builder
    {
        if (! $termo) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($termo) {
            $q->where('nome', 'like', "%{$termo}%")
                ->orWhere('cnpj', 'like', "%{$termo}%");
        });
    }
}
