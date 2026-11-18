<?php

// Arquivo criado com o comando:
//   php artisan make:model Devolucao

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Devolucao extends Model
{
    // Por que o cliente devolveu. Cada motivo sugere um destino na tela,
    // mas quem registra pode trocar (ex.: defeito que a loja mesma vai descartar).
    public const MOTIVOS = [
        'desistencia' => ['nome' => 'Cliente desistiu ou trocou', 'destino' => 'estoque'],
        'defeito' => ['nome' => 'Produto com defeito', 'destino' => 'fornecedor'],
        'avariado' => ['nome' => 'Avariado ou vencido', 'destino' => 'perda'],
        'erro_venda' => ['nome' => 'Erro na venda', 'destino' => 'estoque'],
        'outro' => ['nome' => 'Outro motivo', 'destino' => 'estoque'],
    ];

    // O que acontece com o produto devolvido. Só "estoque" soma no estoque;
    // nos outros dois o produto voltou para a loja, mas não pode ser vendido de novo.
    public const DESTINOS = [
        'estoque' => 'Volta para o estoque',
        'perda' => 'Perda da loja (descartado)',
        'fornecedor' => 'Devolver ao fornecedor',
    ];

    // Nome da tabela no banco de dados
    protected $table = 'devolucoes';

    // Campos que podem ser preenchidos em massa
    protected $fillable = ['venda_id', 'user_id', 'motivo', 'destino', 'valor', 'observacao'];

    // Conversão automática de tipos
    protected $casts = [
        'valor' => 'decimal:2',
    ];

    // A devolução é de uma venda
    public function venda(): BelongsTo
    {
        return $this->belongsTo(Venda::class);
    }

    // Usuário que registrou a devolução
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Itens devolvidos (quais produtos e quantos)
    public function itens(): HasMany
    {
        return $this->hasMany(DevolucaoItem::class);
    }

    // Textos para a tela
    public function nomeMotivo(): string
    {
        return self::MOTIVOS[$this->motivo]['nome'] ?? 'Outro motivo';
    }

    public function nomeDestino(): string
    {
        return self::DESTINOS[$this->destino] ?? '-';
    }

    // Só "volta para o estoque" soma no estoque
    public function voltaAoEstoque(): bool
    {
        return $this->destino === 'estoque';
    }
}
