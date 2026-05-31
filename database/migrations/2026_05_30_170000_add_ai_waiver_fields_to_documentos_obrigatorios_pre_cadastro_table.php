<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos_obrigatorios_pre_cadastro', function (Blueprint $table) {
            if (! Schema::hasColumn('documentos_obrigatorios_pre_cadastro', 'dispensado_por_ia')) {
                $table->boolean('dispensado_por_ia')->default(false)->after('observacoes');
            }

            if (! Schema::hasColumn('documentos_obrigatorios_pre_cadastro', 'dispensado_por_documento_id')) {
                $table->foreignId('dispensado_por_documento_id')->nullable()->after('dispensado_por_ia');
            }

            if (! Schema::hasColumn('documentos_obrigatorios_pre_cadastro', 'motivo_dispensa')) {
                $table->text('motivo_dispensa')->nullable()->after('dispensado_por_documento_id');
            }

            if (! Schema::hasColumn('documentos_obrigatorios_pre_cadastro', 'dispensado_em')) {
                $table->timestamp('dispensado_em')->nullable()->after('motivo_dispensa');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documentos_obrigatorios_pre_cadastro', function (Blueprint $table) {
            foreach (['dispensado_em', 'motivo_dispensa', 'dispensado_por_documento_id', 'dispensado_por_ia'] as $column) {
                if (Schema::hasColumn('documentos_obrigatorios_pre_cadastro', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
