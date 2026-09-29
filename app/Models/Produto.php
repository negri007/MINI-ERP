<?php

// Arquivo criado com o comando:
//   php artisan make:model Produto

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produto extends Model
{
    use HasFactory;

    // Nome da tabela no banco de dados
    protected $table = 'produtos';

    // Campos que podem ser preenchidos em massa
    protected $fillable = ['nome', 'descricao', 'preco', 'estoque', 'estoque_minimo', 'categoria_id', 'fornecedor_id'];

    // Conversão automática de tipos
    protected $casts = [
        'preco' => 'decimal:2',
        'estoque' => 'integer',
        'estoque_minimo' => 'integer',
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

    // Itens de venda em que este produto aparece
    public function itensVenda(): HasMany
    {
        return $this->hasMany(VendaItem::class);
    }

    // Histórico de entradas e saídas do estoque
    public function movimentacoes(): HasMany
    {
        return $this->hasMany(MovimentacaoEstoque::class);
    }

    // Filtro reutilizável: Produto::estoqueBaixo()->get()
    public function scopeEstoqueBaixo(Builder $query): Builder
    {
        return $query->whereColumn('estoque', '<=', 'estoque_minimo');
    }

    // Filtro de busca por nome: Produto::busca('suco')->get()
    public function scopeBusca(Builder $query, ?string $termo): Builder
    {
        return $termo ? $query->where('nome', 'like', "%{$termo}%") : $query;
    }

    // Diz se o produto está com estoque baixo (usado nas views)
    public function estaComEstoqueBaixo(): bool
    {
        return $this->estoque <= $this->estoque_minimo;
    }

    // Dados do medidor de estoque: nível e marca do mínimo (em %) e a situação.
    // A escala vai até 3x o mínimo, para o traço do mínimo ficar em 1/3 da barra.
    public function medidorEstoque(): array
    {
        $maximo = max($this->estoque_minimo * 3, $this->estoque, 1);

        return [
            'nivel' => round($this->estoque / $maximo * 100),
            'minimo' => round($this->estoque_minimo / $maximo * 100),
            'situacao' => match (true) {
                $this->estoque <= 0 => 'esgotado',
                $this->estaComEstoqueBaixo() => 'baixo',
                default => 'ok',
            },
        ];
    }
}
