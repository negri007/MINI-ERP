<?php

// Arquivo criado com o comando:
//   php artisan make:model Cliente

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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

    // Motivo para não excluir (null = pode excluir): vendas, mesmo canceladas, ficam no histórico.
    // Usa o withCount('vendas as vendas_registradas') da lista, se houver.
    public function motivoParaNaoExcluir(): ?string
    {
        $temVendas = isset($this->vendas_registradas) ? $this->vendas_registradas > 0 : $this->vendas()->exists();

        return $temVendas
            ? "O cliente \"{$this->nome}\" tem vendas registradas e precisa ficar no histórico, por isso não pode ser excluído."
            : null;
    }

    // Clientes com nome parecido com o digitado (para evitar cadastro repetido sem querer).
    // Parecido = cada palavra digitada é o começo de alguma palavra do nome existente,
    // sem diferenciar maiúsculas nem acentos. Ex.: "Antô" e "antonio z" batem com "Antônio Zambrano";
    // "Maria Oliveira" não bate com "Maria Souza".
    public static function comNomeParecido(string $nome, int $limite = 3): Collection
    {
        $normalizar = fn (string $texto) => Str::of($texto)->ascii()->lower()->squish()->explode(' ')->filter()->values();
        $digitadas = $normalizar($nome);
        if ($digitadas->isEmpty() || mb_strlen($digitadas[0]) < 2) {
            return collect();
        }

        // Primeiro filtro no banco: até 3 letras do começo da primeira palavra, paradas antes do
        // primeiro acento (nem todo banco ignora acento no LIKE: "antonio" precisa achar "Antônio").
        // A comparação completa, sem acentos, é feita logo abaixo.
        preg_match('/^[A-Za-z0-9]{0,3}/', (string) Str::of($nome)->squish()->before(' '), $inicio);
        $prefixo = $inicio[0];

        return static::comuns()
            ->when($prefixo !== '', fn (Builder $q) => $q->where(fn (Builder $q) => $q
                ->where('nome', 'like', "{$prefixo}%")
                ->orWhere('nome', 'like', "% {$prefixo}%")))
            ->orderBy('nome')
            ->limit(200)
            ->get(['id', 'nome', 'cpf_cnpj', 'consumidor_final'])
            ->filter(function (Cliente $cliente) use ($normalizar, $digitadas) {
                $palavras = $normalizar($cliente->nome);

                return $digitadas->every(fn ($d) => $palavras->contains(fn ($p) => str_starts_with($p, $d)));
            })
            ->take($limite)
            ->values();
    }
}
