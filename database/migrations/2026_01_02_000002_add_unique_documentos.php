<?php

// Arquivo criado com o comando:
//   php artisan make:migration add_unique_documentos
// Impede, direto no banco, dois clientes com o mesmo CPF/CNPJ
// e dois fornecedores com o mesmo CNPJ.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->unique('cpf_cnpj');
        });

        Schema::table('fornecedores', function (Blueprint $table) {
            $table->unique('cnpj');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropUnique(['cpf_cnpj']);
        });

        Schema::table('fornecedores', function (Blueprint $table) {
            $table->dropUnique(['cnpj']);
        });
    }
};
