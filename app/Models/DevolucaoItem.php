<?php

// Arquivo criado com o comando:
//   php artisan make:model DevolucaoItem

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevolucaoItem extends Model
{
    // Nome da tabela no banco de dados
    protected $table = 'devolucao_itens';

    // Campos que podem ser preenchidos em massa
    protected $fillable = ['devolucao_id', 'venda_item_id', 'produto_id', 'quantidade', 'valor', 'custo_unitario'];

    // Conversão automática de tipos
    protected $casts = [
        'quantidade' => 'integer',
        'valor' => 'decimal:2',
        'custo_unitario' => 'decimal:2', // custo guardado na venda; null = sem custo informado
    ];

    // O item pertence a uma devolução
    public function devolucao(): BelongsTo
    {
        return $this->belongsTo(Devolucao::class);
    }

    // Item da venda que foi devolvido
    public function vendaItem(): BelongsTo
    {
        return $this->belongsTo(VendaItem::class);
    }

    // Produto devolvido
    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }
}
