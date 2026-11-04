<?php

// Arquivo criado com o comando:
//   php artisan make:model VendaItem

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendaItem extends Model
{
    // Nome da tabela no banco de dados
    protected $table = 'venda_itens';

    // Campos que podem ser preenchidos em massa
    protected $fillable = ['venda_id', 'produto_id', 'quantidade', 'preco_unitario', 'custo_unitario', 'subtotal'];

    // Conversão automática de tipos
    protected $casts = [
        'quantidade' => 'integer',
        'preco_unitario' => 'decimal:2',
        'custo_unitario' => 'decimal:2', // custo guardado na hora da venda; null = sem custo informado
        'subtotal' => 'decimal:2',
    ];

    // O item pertence a uma venda
    public function venda(): BelongsTo
    {
        return $this->belongsTo(Venda::class);
    }

    // O item se refere a um produto
    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }
}
