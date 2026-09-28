<?php

// Arquivo criado com o comando:
//   php artisan make:model Venda

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venda extends Model
{
    // Situações possíveis de uma venda
    public const CONCLUIDA = 'concluida';

    public const CANCELADA = 'cancelada';

    // Nome da tabela no banco de dados
    protected $table = 'vendas';

    // Campos que podem ser preenchidos em massa
    protected $fillable = ['cliente_id', 'user_id', 'data', 'status', 'total', 'observacao'];

    // Conversão automática de tipos
    protected $casts = [
        'data' => 'date',
        'total' => 'decimal:2',
    ];

    // Uma venda pertence a um cliente
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    // Usuário que registrou a venda
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Uma venda tem vários itens
    public function itens(): HasMany
    {
        return $this->hasMany(VendaItem::class);
    }

    // Diz se a venda já foi cancelada
    public function estaCancelada(): bool
    {
        return $this->status === self::CANCELADA;
    }
}
