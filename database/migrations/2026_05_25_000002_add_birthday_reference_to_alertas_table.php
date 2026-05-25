<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('alertas')) {
            return;
        }

        Schema::table('alertas', function (Blueprint $table) {
            if (! Schema::hasColumn('alertas', 'chave')) {
                $table->string('chave')->nullable()->after('cliente_id');
            }

            if (! Schema::hasColumn('alertas', 'data_referencia')) {
                $table->date('data_referencia')->nullable()->after('chave');
            }
        });

        Schema::table('alertas', function (Blueprint $table) {
            $table->unique(['user_id', 'chave'], 'alertas_user_id_chave_unique');
            $table->index(['user_id', 'data_referencia'], 'alertas_user_id_data_referencia_index');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('alertas')) {
            return;
        }

        Schema::table('alertas', function (Blueprint $table) {
            $table->dropUnique('alertas_user_id_chave_unique');
            $table->dropIndex('alertas_user_id_data_referencia_index');
        });

        Schema::table('alertas', function (Blueprint $table) {
            if (Schema::hasColumn('alertas', 'data_referencia')) {
                $table->dropColumn('data_referencia');
            }

            if (Schema::hasColumn('alertas', 'chave')) {
                $table->dropColumn('chave');
            }
        });
    }
};
