<?php

// Arquivo criado com o comando:
//   php artisan make:model Produto

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Produto extends Model
{
    // Nome da tabela no banco de dados
    protected $table = 'produtos';

    // Campos que podem ser preenchidos em massa
    protected $fillable = ['nome', 'descricao', 'preco', 'estoque', 'categoria_id', 'fornecedor_id'];

    // Conversão automática de tipos
    protected $casts = [
        'preco' => 'decimal:2',
        'estoque' => 'integer',
    ];

    // Um produto pertence a uma categoria
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    // Um produto pertence a um fornecedor (opcional)
    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class);
    }
}
