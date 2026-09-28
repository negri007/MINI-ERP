<?php

// Arquivo criado com o comando:
//   php artisan make:migration create_venda_itens_table

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Itens da venda: cada produto vendido, com quantidade e preço do momento
    public function up(): void
    {
        Schema::create('venda_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venda_id')->constrained('vendas')->cascadeOnDelete();
            $table->foreignId('produto_id')->constrained('produtos')->restrictOnDelete();
            $table->integer('quantidade');
            $table->decimal('preco_unitario', 10, 2); // preço guardado: se o produto mudar de preço, a venda antiga não muda
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venda_itens');
    }
};
