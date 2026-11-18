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

    // De onde veio a movimentação (a mesma lista está na regra CHECK do banco).
    // O texto aparece na coluna "Origem" do histórico de estoque.
    public const ORIGENS = [
        'venda' => 'Venda',
        'cancelamento' => 'Cancelamento',
        'estoque_inicial' => 'Estoque inicial',
        'manual' => 'Ajuste manual',
        'devolucao' => 'Devolução',
    ];

    // Nome da tabela no banco de dados
    protected $table = 'movimentacoes_estoque';

    // Campos que podem ser preenchidos em massa
    protected $fillable = ['produto_id', 'tipo', 'origem', 'quantidade', 'estoque_apos', 'motivo', 'venda_id', 'devolucao_id', 'user_id'];

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

    // Devolução de cliente que gerou a movimentação (quando houver)
    public function devolucao(): BelongsTo
    {
        return $this->belongsTo(Devolucao::class);
    }

    // Nome da origem para mostrar na tela ("Venda", "Ajuste manual"...)
    public function nomeOrigem(): string
    {
        return self::ORIGENS[$this->origem] ?? 'Ajuste manual';
    }

    // Usuário que fez a movimentação
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
