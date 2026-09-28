<?php

// Arquivo criado com o comando:
//   php artisan make:migration add_estoque_minimo_to_produtos_table
// Adiciona uma coluna numa tabela que já existe (sem apagar os dados).

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Cada produto passa a ter o seu próprio limite de "estoque baixo"
    public function up(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->integer('estoque_minimo')->default(5)->after('estoque');
        });
    }

    public function down(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn('estoque_minimo');
        });
    }
};
