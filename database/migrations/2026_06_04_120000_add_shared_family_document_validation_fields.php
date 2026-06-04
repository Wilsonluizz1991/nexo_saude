<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('documentos_obrigatorios_pre_cadastro')) {
            Schema::table('documentos_obrigatorios_pre_cadastro', function (Blueprint $table) {
                if (! Schema::hasColumn('documentos_obrigatorios_pre_cadastro', 'validado_por_documento_compartilhado')) {
                    $table->boolean('validado_por_documento_compartilhado')->default(false)->after('dispensado_em');
                }

                if (! Schema::hasColumn('documentos_obrigatorios_pre_cadastro', 'documento_origem_id')) {
                    $table->foreignId('documento_origem_id')->nullable()->after('validado_por_documento_compartilhado');
                }

                if (! Schema::hasColumn('documentos_obrigatorios_pre_cadastro', 'beneficiario_origem_id')) {
                    $table->foreignId('beneficiario_origem_id')->nullable()->after('documento_origem_id');
                }

                if (! Schema::hasColumn('documentos_obrigatorios_pre_cadastro', 'motivo_validacao')) {
                    $table->text('motivo_validacao')->nullable()->after('beneficiario_origem_id');
                }

                if (! Schema::hasColumn('documentos_obrigatorios_pre_cadastro', 'tipo_regra_validacao')) {
                    $table->string('tipo_regra_validacao', 80)->nullable()->after('motivo_validacao');
                }
            });

            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE documentos_obrigatorios_pre_cadastro MODIFY status ENUM('pendente','enviado','aprovado','aprovado_ia','recusado','corrigir','dispensado') NOT NULL DEFAULT 'pendente'");
            }
        }

        if (Schema::hasTable('documento_ia_validacoes')) {
            Schema::table('documento_ia_validacoes', function (Blueprint $table) {
                if (! Schema::hasColumn('documento_ia_validacoes', 'beneficiarios_extraidos')) {
                    $table->json('beneficiarios_extraidos')->nullable()->after('dados_extraidos');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('documentos_obrigatorios_pre_cadastro')) {
            DB::table('documentos_obrigatorios_pre_cadastro')
                ->where('status', 'aprovado_ia')
                ->update(['status' => 'aprovado']);

            Schema::table('documentos_obrigatorios_pre_cadastro', function (Blueprint $table) {
                foreach ([
                    'tipo_regra_validacao',
                    'motivo_validacao',
                    'beneficiario_origem_id',
                    'documento_origem_id',
                    'validado_por_documento_compartilhado',
                ] as $column) {
                    if (Schema::hasColumn('documentos_obrigatorios_pre_cadastro', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });

            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE documentos_obrigatorios_pre_cadastro MODIFY status ENUM('pendente','enviado','aprovado','recusado','corrigir','dispensado') NOT NULL DEFAULT 'pendente'");
            }
        }

        if (Schema::hasTable('documento_ia_validacoes') && Schema::hasColumn('documento_ia_validacoes', 'beneficiarios_extraidos')) {
            Schema::table('documento_ia_validacoes', function (Blueprint $table) {
                $table->dropColumn('beneficiarios_extraidos');
            });
        }
    }
};
