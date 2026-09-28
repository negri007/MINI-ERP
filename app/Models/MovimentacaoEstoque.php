<?php

// Arquivo criado com o comando:
//   php artisan make:model MovimentacaoEstoque

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimentacaoEstoque extends Model
{
    // Tipos de movimentação
    public const ENTRADA = 'entrada';

    public const SAIDA = 'saida';

    // Nome da tabela no banco de dados
    protected $table = 'movimentacoes_estoque';

    // Campos que podem ser preenchidos em massa
    protected $fillable = ['produto_id', 'tipo', 'quantidade', 'estoque_apos', 'motivo', 'venda_id', 'user_id'];

    // Conversão automática de tipos
    protected $casts = [
        'quantidade' => 'integer',
        'estoque_apos' => 'integer',
    ];

    // A movimentação é de um produto
    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }

    // Venda que gerou a movimentação (quando houver)
    public function venda(): BelongsTo
    {
        return $this->belongsTo(Venda::class);
    }

    // Usuário que fez a movimentação
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
