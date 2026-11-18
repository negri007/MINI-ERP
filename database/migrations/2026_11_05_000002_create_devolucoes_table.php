<?php

// Arquivo criado com o comando:
//   php artisan make:migration create_devolucoes_table

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Devolução de cliente: sempre ligada a uma venda.
    //   motivo:  desistencia | defeito | avariado | erro_venda | outro
    //   destino: estoque (volta para a prateleira) | perda (descartado pela loja) | fornecedor (vai voltar ao fornecedor)
    //   valor:   quanto foi devolvido ao cliente (já com o desconto da venda repartido entre os itens)
    //
    //   vendas.total_devolvido guarda a soma das devoluções da venda: o faturamento e o lucro
    //   passam a usar "total - total_devolvido", sem precisar somar as devoluções toda vez.
    public function up(): void
    {
        Schema::create('devolucoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venda_id')->constrained('vendas')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('motivo', 20);
            $table->string('destino', 20);
            $table->decimal('valor', 12, 2);
            $table->text('observacao')->nullable();
            $table->timestamps();
        });

        Schema::create('devolucao_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devolucao_id')->constrained('devolucoes')->cascadeOnDelete();
            $table->foreignId('venda_item_id')->constrained('venda_itens')->restrictOnDelete();
            $table->foreignId('produto_id')->constrained('produtos')->restrictOnDelete();
            $table->integer('quantidade');
            $table->decimal('valor', 12, 2);                 // valor devolvido por este item
            $table->decimal('custo_unitario', 10, 2)->nullable(); // custo guardado na venda (para o lucro)
            $table->timestamps();
        });

        Schema::table('vendas', function (Blueprint $table) {
            $table->decimal('total_devolvido', 12, 2)->default(0)->after('total');
        });

        // A entrada de estoque de uma devolução aponta para ela (para mostrar "Devolução #3" no histórico)
        Schema::table('movimentacoes_estoque', function (Blueprint $table) {
            $table->foreignId('devolucao_id')->nullable()->after('venda_id')->constrained('devolucoes')->nullOnDelete();
        });

        // Regras no banco (MySQL/MariaDB). O SQLite dos testes não aceita CHECK em tabela
        // já criada; lá valem as regras do DevolucaoService, cobertas pelos testes.
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE devolucoes ADD CONSTRAINT devolucoes_motivo_valido CHECK (motivo IN ('desistencia', 'defeito', 'avariado', 'erro_venda', 'outro'))");
            DB::statement("ALTER TABLE devolucoes ADD CONSTRAINT devolucoes_destino_valido CHECK (destino IN ('estoque', 'perda', 'fornecedor'))");
            DB::statement('ALTER TABLE devolucoes ADD CONSTRAINT devolucoes_valor_positivo CHECK (valor >= 0)');
            DB::statement('ALTER TABLE devolucao_itens ADD CONSTRAINT devolucao_itens_quantidade_positiva CHECK (quantidade > 0)');
            DB::statement('ALTER TABLE vendas ADD CONSTRAINT vendas_devolvido_valido CHECK (total_devolvido >= 0 AND total_devolvido <= total)');
        }
    }

    // Desfaz na ordem inversa. Atenção: apaga as devoluções registradas depois desta migration
    // (o estoque que elas devolveram continua; o faturamento volta a contar a venda inteira).
    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement('ALTER TABLE vendas DROP CONSTRAINT vendas_devolvido_valido');
        }

        Schema::table('movimentacoes_estoque', function (Blueprint $table) {
            $table->dropConstrainedForeignId('devolucao_id');
        });

        Schema::table('vendas', function (Blueprint $table) {
            $table->dropColumn('total_devolvido');
        });

        Schema::dropIfExists('devolucao_itens');
        Schema::dropIfExists('devolucoes');
    }
};
