<?php

// Arquivo criado com o comando:
//   php artisan make:migration add_pagamento_e_desconto_to_vendas

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Forma de pagamento e desconto na venda.
    //   subtotal = soma dos itens; desconto em R$; total = subtotal - desconto.
    //   forma_pagamento: dinheiro | pix | debito | credito. Vazio (null) só nas vendas
    //   antigas, feitas antes deste campo existir ("não informada"). O "fiado" entra na fase
    //   do financeiro, com migration própria (muda a regra e cria vencimento/situação).
    public function up(): void
    {
        // 1) Colunas novas: aceitam vazio ou têm padrão, para não quebrar as vendas que já existem
        Schema::table('vendas', function (Blueprint $table) {
            $table->decimal('subtotal', 12, 2)->default(0)->after('status');
            $table->decimal('desconto', 12, 2)->default(0)->after('subtotal');
            $table->string('forma_pagamento', 20)->nullable()->after('desconto');
            $table->index('data'); // Caixa do dia e Dashboard filtram por data
        });

        // 2) Vendas antigas: não tinham desconto, então subtotal = total (o total não muda).
        //    Um UPDATE só: a tabela é de uma loja (poucas linhas).
        DB::table('vendas')->update(['subtotal' => DB::raw('total')]);

        // 3) Regras no banco (MySQL/MariaDB), criadas depois de os dados estarem certos.
        //    O SQLite dos testes não aceita CHECK em tabela já criada; lá vale o VendaService.
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement('ALTER TABLE vendas ADD CONSTRAINT vendas_desconto_valido CHECK (desconto >= 0 AND desconto <= subtotal)');
            DB::statement('ALTER TABLE vendas ADD CONSTRAINT vendas_total_confere CHECK (total = subtotal - desconto)');
            DB::statement("ALTER TABLE vendas ADD CONSTRAINT vendas_forma_pagamento_valida CHECK (forma_pagamento IS NULL OR forma_pagamento IN ('dinheiro', 'pix', 'debito', 'credito'))");
        }
    }

    // Desfaz na ordem inversa: regras, índice e só então as colunas.
    // Atenção: apaga o registro de desconto e forma de pagamento das vendas feitas depois
    // desta migration (o total de cada venda continua certo).
    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement('ALTER TABLE vendas DROP CONSTRAINT vendas_forma_pagamento_valida');
            DB::statement('ALTER TABLE vendas DROP CONSTRAINT vendas_total_confere');
            DB::statement('ALTER TABLE vendas DROP CONSTRAINT vendas_desconto_valido');
        }

        Schema::table('vendas', function (Blueprint $table) {
            $table->dropIndex(['data']);
        });

        Schema::table('vendas', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'desconto', 'forma_pagamento']);
        });
    }
};
