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

    // Cores das categorias (a mesma categoria sempre tem a mesma cor, pelo id)
    public const CORES = ['#4394e8', '#d9a441', '#d9749b', '#2bb3a3', '#9b7be0', '#7fb069'];

    public function cor(): string
    {
        return self::CORES[($this->id - 1) % count(self::CORES)];
    }

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
