<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pre_cadastros')) {
            Schema::table('pre_cadastros', function (Blueprint $table) {
                if (! Schema::hasColumn('pre_cadastros', 'formulario_bloqueado')) {
                    $table->boolean('formulario_bloqueado')->default(false);
                }
                if (! Schema::hasColumn('pre_cadastros', 'motivos_correcao')) {
                    $table->text('motivos_correcao')->nullable();
                }
                if (! Schema::hasColumn('pre_cadastros', 'enviado_em')) {
                    $table->timestamp('enviado_em')->nullable();
                }
                if (! Schema::hasColumn('pre_cadastros', 'bloqueado_em')) {
                    $table->timestamp('bloqueado_em')->nullable();
                }
            });
        }

        if (Schema::hasTable('vidas')) {
            Schema::table('vidas', function (Blueprint $table) {
                $table->string('nome')->nullable()->change();
                $table->enum('sexo', ['masculino', 'feminino', 'outro'])->nullable()->change();
                $table->date('data_nascimento')->nullable()->change();
            });
        }

        if (Schema::hasTable('documentos_enviados')) {
            Schema::table('documentos_enviados', function (Blueprint $table) {
                if (! Schema::hasColumn('documentos_enviados', 'pre_cadastro_id')) {
                    $table->unsignedBigInteger('pre_cadastro_id')->nullable()->after('id');
                }
                if (! Schema::hasColumn('documentos_enviados', 'beneficiario_id')) {
                    $table->unsignedBigInteger('beneficiario_id')->nullable()->after('pre_cadastro_id');
                }
                if (! Schema::hasColumn('documentos_enviados', 'documento_obrigatorio_pre_cadastro_id')) {
                    $table->unsignedBigInteger('documento_obrigatorio_pre_cadastro_id')->nullable()->after('documento_solicitado_id');
                }
                if (! Schema::hasColumn('documentos_enviados', 'tipo_documento_solicitado_id')) {
                    $table->unsignedBigInteger('tipo_documento_solicitado_id')->nullable()->after('documento_obrigatorio_pre_cadastro_id');
                }
                if (! Schema::hasColumn('documentos_enviados', 'tipo_documento_detectado_id')) {
                    $table->unsignedBigInteger('tipo_documento_detectado_id')->nullable()->after('tipo_documento_solicitado_id');
                }
                if (! Schema::hasColumn('documentos_enviados', 'status_ia')) {
                    $table->string('status_ia')->default('nao_analisado')->after('tipo_documento_detectado_id');
                }
                if (! Schema::hasColumn('documentos_enviados', 'analise_ia')) {
                    $table->json('analise_ia')->nullable()->after('status_ia');
                }
                if (! Schema::hasColumn('documentos_enviados', 'documento_compativel')) {
                    $table->boolean('documento_compativel')->nullable()->after('analise_ia');
                }
                if (! Schema::hasColumn('documentos_enviados', 'legivel')) {
                    $table->boolean('legivel')->nullable()->after('documento_compativel');
                }
                if (! Schema::hasColumn('documentos_enviados', 'cortado')) {
                    $table->boolean('cortado')->nullable()->after('legivel');
                }
                if (! Schema::hasColumn('documentos_enviados', 'tremido')) {
                    $table->boolean('tremido')->nullable()->after('cortado');
                }
                if (! Schema::hasColumn('documentos_enviados', 'nome_detectado')) {
                    $table->string('nome_detectado')->nullable()->after('tremido');
                }
                if (! Schema::hasColumn('documentos_enviados', 'analisado_em')) {
                    $table->timestamp('analisado_em')->nullable()->after('nome_detectado');
                }
                if (! Schema::hasColumn('documentos_enviados', 'motivo_recusa')) {
                    $table->text('motivo_recusa')->nullable()->after('analisado_em');
                }
            });
        }

        if (Schema::hasTable('alertas')) {
            Schema::table('alertas', function (Blueprint $table) {
                if (! Schema::hasColumn('alertas', 'pre_cadastro_id')) {
                    $table->unsignedBigInteger('pre_cadastro_id')->nullable()->after('indicacao_id');
                }
                if (! Schema::hasColumn('alertas', 'proposta_id')) {
                    $table->unsignedBigInteger('proposta_id')->nullable()->after('pre_cadastro_id');
                }
                if (! Schema::hasColumn('alertas', 'cliente_id')) {
                    $table->unsignedBigInteger('cliente_id')->nullable()->after('proposta_id');
                }
                if (! Schema::hasColumn('alertas', 'status')) {
                    $table->string('status')->default('nao_lido')->after('tipo');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pre_cadastros')) {
            Schema::table('pre_cadastros', function (Blueprint $table) {
                foreach (['formulario_bloqueado', 'motivos_correcao', 'enviado_em', 'bloqueado_em'] as $column) {
                    if (Schema::hasColumn('pre_cadastros', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
