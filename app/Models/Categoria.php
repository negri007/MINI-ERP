<?php

// Arquivo criado com o comando:
//   php artisan make:model Categoria

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    use HasFactory;

    // Nome da tabela no banco de dados
    protected $table = 'categorias';

    // Campos que podem ser preenchidos em massa
    protected $fillable = ['nome', 'descricao'];

    // Uma categoria possui vários produtos
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

        return $query->where('nome', 'like', "%{$termo}%");
    }
}
