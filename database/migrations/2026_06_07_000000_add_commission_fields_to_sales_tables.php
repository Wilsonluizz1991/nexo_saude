<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('propostas', function (Blueprint $table) {
            if (! Schema::hasColumn('propostas', 'percentual_comissao')) {
                $table->decimal('percentual_comissao', 5, 2)->nullable()->after('valor_mensal');
            }

            if (! Schema::hasColumn('propostas', 'valor_comissao_prevista')) {
                $table->decimal('valor_comissao_prevista', 10, 2)->nullable()->after('percentual_comissao');
            }
        });

        Schema::table('contratos', function (Blueprint $table) {
            if (! Schema::hasColumn('contratos', 'percentual_comissao')) {
                $table->decimal('percentual_comissao', 5, 2)->nullable()->after('valor_mensal');
            }

            if (! Schema::hasColumn('contratos', 'valor_comissao_prevista')) {
                $table->decimal('valor_comissao_prevista', 10, 2)->nullable()->after('percentual_comissao');
            }

            if (! Schema::hasColumn('contratos', 'valor_comissao_real')) {
                $table->decimal('valor_comissao_real', 10, 2)->nullable()->after('valor_comissao_prevista');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            foreach (['valor_comissao_real', 'valor_comissao_prevista', 'percentual_comissao'] as $column) {
                if (Schema::hasColumn('contratos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('propostas', function (Blueprint $table) {
            foreach (['valor_comissao_prevista', 'percentual_comissao'] as $column) {
                if (Schema::hasColumn('propostas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
