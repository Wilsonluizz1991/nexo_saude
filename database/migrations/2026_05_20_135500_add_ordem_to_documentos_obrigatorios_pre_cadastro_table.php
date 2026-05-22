<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('documentos_obrigatorios_pre_cadastro')) {
            return;
        }

        Schema::table('documentos_obrigatorios_pre_cadastro', function (Blueprint $table) {
            if (! Schema::hasColumn('documentos_obrigatorios_pre_cadastro', 'ordem')) {
                $table->unsignedInteger('ordem')->default(1)->after('obrigatorio');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('documentos_obrigatorios_pre_cadastro')) {
            return;
        }

        Schema::table('documentos_obrigatorios_pre_cadastro', function (Blueprint $table) {
            if (Schema::hasColumn('documentos_obrigatorios_pre_cadastro', 'ordem')) {
                $table->dropColumn('ordem');
            }
        });
    }
};
