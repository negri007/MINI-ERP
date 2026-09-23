<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fornecedor extends Model
{
    // Nome da tabela no banco de dados (o plural automático seria "fornecedors")
    protected $table = 'fornecedores';

    // Campos que podem ser preenchidos em massa
    protected $fillable = ['nome', 'cnpj', 'telefone', 'email'];

    // Um fornecedor fornece vários produtos
    public function produtos(): HasMany
    {
        return $this->hasMany(Produto::class);
    }
}
