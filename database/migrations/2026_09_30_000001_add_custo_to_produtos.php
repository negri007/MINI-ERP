<?php

// Arquivo criado com o comando:
//   php artisan make:migration add_custo_to_produtos

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Custo de uma unidade do produto (quanto a loja pagou). Serve para calcular o lucro.
    // Vazio (null) quer dizer "custo não informado": nunca inventamos zero.
    public function up(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->decimal('custo', 10, 2)->nullable()->after('preco');
        });

        // Regra no banco (MySQL/MariaDB): custo não pode ser negativo.
        // O SQLite dos testes não aceita CHECK em tabela já criada; lá vale a validação do ProdutoRequest.
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement('ALTER TABLE produtos ADD CONSTRAINT produtos_custo_nao_negativo CHECK (custo IS NULL OR custo >= 0)');
        }
    }

    public function down(): void
    {
        // Primeiro a regra, depois a coluna
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement('ALTER TABLE produtos DROP CONSTRAINT produtos_custo_nao_negativo');
        }

        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn('custo');
        });
    }
};
