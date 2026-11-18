<?php

// Arquivo criado com o comando:
//   php artisan make:model VendaItem

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    // Devoluções deste item (pode haver mais de uma, cada uma com parte da quantidade)
    public function devolucaoItens(): HasMany
    {
        return $this->hasMany(DevolucaoItem::class);
    }

    // Quantas unidades deste item já foram devolvidas
    public function quantidadeDevolvida(): int
    {
        return (int) $this->devolucaoItens()->sum('quantidade');
    }

    // Quantas unidades ainda podem ser devolvidas
    public function quantidadeDisponivelParaDevolver(): int
    {
        return max(0, $this->quantidade - $this->quantidadeDevolvida());
    }

    // O item se refere a um produto
    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }
}
