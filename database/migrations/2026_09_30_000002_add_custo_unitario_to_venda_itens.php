<?php

// Arquivo criado com o comando:
//   php artisan make:migration add_custo_unitario_to_venda_itens

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Custo da unidade guardado no item no momento da venda (como o preço):
    // se o custo do produto mudar depois, as vendas antigas não mudam.
    // Itens das vendas antigas ficam vazios (null = "sem custo informado"): não inventamos valor.
    public function up(): void
    {
        Schema::table('venda_itens', function (Blueprint $table) {
            $table->decimal('custo_unitario', 10, 2)->nullable()->after('preco_unitario');
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement('ALTER TABLE venda_itens ADD CONSTRAINT venda_itens_custo_nao_negativo CHECK (custo_unitario IS NULL OR custo_unitario >= 0)');
        }
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement('ALTER TABLE venda_itens DROP CONSTRAINT venda_itens_custo_nao_negativo');
        }

        Schema::table('venda_itens', function (Blueprint $table) {
            $table->dropColumn('custo_unitario');
        });
    }
};
