<?php

// Arquivo criado com o comando:
//   php artisan make:migration add_consumidor_final_to_clientes

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Cliente especial "Consumidor final": usado nas vendas sem cliente identificado.
    // A coluna vale true só nele e fica vazia (null) nos outros. Como é "unique" e o banco
    // aceita vários null, só pode existir UM Consumidor final.
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->boolean('consumidor_final')->nullable()->unique()->after('email');
        });

        // Já cria o registro aqui (e não só no seeder) para quem rodar apenas "php artisan migrate"
        DB::table('clientes')->insert([
            'nome' => 'Consumidor final',
            'consumidor_final' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Se o Consumidor final nunca vendeu, ele é apagado; se vendeu, fica como cliente comum
        $id = DB::table('clientes')->where('consumidor_final', true)->value('id');
        if ($id && ! DB::table('vendas')->where('cliente_id', $id)->exists()) {
            DB::table('clientes')->where('id', $id)->delete();
        }

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropUnique(['consumidor_final']);
            $table->dropColumn('consumidor_final');
        });
    }
};
