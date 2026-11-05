<?php

// Arquivo criado com o comando:
//   php artisan make:migration add_aprovado_em_to_users_table

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Cadastro com aprovação: qualquer pessoa pode pedir acesso na página inicial,
    // mas só entra depois que um usuário aprovado libera (tela Usuários).
    //   aprovado_em vazio = aguardando aprovação
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('aprovado_em')->nullable()->after('password');
        });

        // Quem já existia (ex.: o admin) continua entrando normalmente
        DB::table('users')->whereNull('aprovado_em')->update(['aprovado_em' => now()]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('aprovado_em');
        });
    }
};
