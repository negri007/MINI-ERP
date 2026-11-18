<?php

// Arquivo criado com o comando:
//   php artisan make:migration add_origem_to_movimentacoes_estoque

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // De onde veio cada entrada ou saída de estoque.
    //   Antes, a tela mostrava só um texto livre ("Venda #15", "Estoque inicial", "Compra NF 123"...),
    //   e não dava para saber de relance o que era automático e o que alguém digitou.
    //   origem: venda | cancelamento | estoque_inicial | manual | devolucao
    public function up(): void
    {
        // 1) Coluna nova com padrão "manual", para não quebrar as movimentações que já existem
        Schema::table('movimentacoes_estoque', function (Blueprint $table) {
            $table->string('origem', 20)->default('manual')->after('tipo');
        });

        // 2) Movimentações antigas: a origem sai do que já estava gravado
        //    - saída com venda   = venda
        //    - entrada com venda = cancelamento (o cancelamento devolve o estoque da venda)
        //    - motivo "Estoque inicial" = cadastro do produto
        DB::table('movimentacoes_estoque')->whereNotNull('venda_id')->where('tipo', 'saida')->update(['origem' => 'venda']);
        DB::table('movimentacoes_estoque')->whereNotNull('venda_id')->where('tipo', 'entrada')->update(['origem' => 'cancelamento']);
        DB::table('movimentacoes_estoque')->whereNull('venda_id')->where('motivo', 'Estoque inicial')->update(['origem' => 'estoque_inicial']);

        // 3) Regra no banco (MySQL/MariaDB): só as origens conhecidas.
        //    O SQLite dos testes não aceita CHECK em tabela já criada; lá vale o EstoqueService.
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE movimentacoes_estoque ADD CONSTRAINT movimentacoes_origem_valida CHECK (origem IN ('venda', 'cancelamento', 'estoque_inicial', 'manual', 'devolucao'))");
        }
    }

    // Desfaz na ordem inversa: a regra e só então a coluna (o texto do motivo continua lá)
    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement('ALTER TABLE movimentacoes_estoque DROP CONSTRAINT movimentacoes_origem_valida');
        }

        Schema::table('movimentacoes_estoque', function (Blueprint $table) {
            $table->dropColumn('origem');
        });
    }
};
