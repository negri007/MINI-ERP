<?php

// Arquivo criado com o comando:
//   php artisan make:migration create_movimentacoes_estoque_table

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Histórico de tudo que entrou e saiu do estoque
    public function up(): void
    {
        Schema::create('movimentacoes_estoque', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produto_id')->constrained('produtos')->cascadeOnDelete();
            $table->string('tipo', 10); // entrada | saida
            $table->integer('quantidade');
            $table->integer('estoque_apos'); // saldo do produto depois desta movimentação
            $table->string('motivo');
            $table->foreignId('venda_id')->nullable()->constrained('vendas')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimentacoes_estoque');
    }
};
