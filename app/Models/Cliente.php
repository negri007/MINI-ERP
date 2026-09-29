<?php

// Arquivo criado com o comando:
//   php artisan make:model Cliente

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    // Nome da tabela no banco de dados
    protected $table = 'clientes';

    // Campos que podem ser preenchidos em massa
    protected $fillable = ['nome', 'cpf_cnpj', 'telefone', 'email'];

    // consumidor_final fica fora do $fillable: ninguém transforma um cliente comum nele pelo formulário
    protected $casts = ['consumidor_final' => 'boolean'];

    // O cliente especial das vendas sem cliente identificado (criado pela migration)
    public static function consumidorFinal(): ?self
    {
        return static::where('consumidor_final', true)->first();
    }

    public function ehConsumidorFinal(): bool
    {
        return (bool) $this->consumidor_final;
    }

    // Só os clientes cadastrados de verdade (sem o Consumidor final): usado na lista e nas contagens
    public function scopeComuns(Builder $query): Builder
    {
        return $query->whereNull('consumidor_final');
    }

    // Consumidor final primeiro, depois os outros em ordem alfabética
    public function scopeOrdemParaVenda(Builder $query): Builder
    {
        return $query->orderByRaw('consumidor_final is null')->orderBy('nome');
    }

    // Um cliente pode ter várias vendas
    public function vendas(): HasMany
    {
        return $this->hasMany(Venda::class);
    }

    // Filtro de busca por nome, CPF/CNPJ ou e-mail
    public function scopeBusca(Builder $query, ?string $termo): Builder
    {
        if (! $termo) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($termo) {
            $q->where('nome', 'like', "%{$termo}%")
                ->orWhere('cpf_cnpj', 'like', "%{$termo}%")
                ->orWhere('email', 'like', "%{$termo}%");

            // CPF/CNPJ digitado só com números também encontra (o banco guarda com a máscara)
            $digitos = preg_replace('/\D/', '', $termo);
            if (strlen($digitos) >= 3) {
                $q->orWhereRaw("REPLACE(REPLACE(REPLACE(cpf_cnpj, '.', ''), '-', ''), '/', '') like ?", ["%{$digitos}%"]);
            }
        });
    }
}
