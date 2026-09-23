<?php

// Arquivo criado com o comando:
//   php artisan make:migration create_categorias_table
// Para criar a tabela no banco:  php artisan migrate
// Para apagar tudo e recriar:   php artisan migrate:fresh

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Cria a tabela de categorias
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->timestamps();
        });
    }

    // Remove a tabela de categorias
    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
