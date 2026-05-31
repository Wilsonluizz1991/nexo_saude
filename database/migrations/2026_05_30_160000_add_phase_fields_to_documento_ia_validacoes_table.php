<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documento_ia_validacoes', function (Blueprint $table) {
            if (! Schema::hasColumn('documento_ia_validacoes', 'fase_validacao')) {
                $table->string('fase_validacao', 30)->default('completa')->after('status');
            }

            if (! Schema::hasColumn('documento_ia_validacoes', 'validacao_documental_status')) {
                $table->string('validacao_documental_status', 40)->nullable()->after('fase_validacao');
            }

            if (! Schema::hasColumn('documento_ia_validacoes', 'validacao_titularidade_status')) {
                $table->string('validacao_titularidade_status', 40)->nullable()->after('validacao_documental_status');
            }

            if (! Schema::hasColumn('documento_ia_validacoes', 'titularidade_pendente')) {
                $table->boolean('titularidade_pendente')->default(false)->after('validacao_titularidade_status');
            }

            if (! Schema::hasColumn('documento_ia_validacoes', 'nome_beneficiario_usado')) {
                $table->string('nome_beneficiario_usado')->nullable()->after('titularidade_pendente');
            }

            if (! Schema::hasColumn('documento_ia_validacoes', 'cpf_beneficiario_usado')) {
                $table->string('cpf_beneficiario_usado')->nullable()->after('nome_beneficiario_usado');
            }

            if (! Schema::hasColumn('documento_ia_validacoes', 'data_nascimento_usada')) {
                $table->string('data_nascimento_usada')->nullable()->after('cpf_beneficiario_usado');
            }

            if (! Schema::hasColumn('documento_ia_validacoes', 'dados_extraidos')) {
                $table->json('dados_extraidos')->nullable()->after('data_nascimento_usada');
            }

            if (! Schema::hasColumn('documento_ia_validacoes', 'dados_comparados')) {
                $table->json('dados_comparados')->nullable()->after('dados_extraidos');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documento_ia_validacoes', function (Blueprint $table) {
            foreach ([
                'dados_comparados',
                'dados_extraidos',
                'data_nascimento_usada',
                'cpf_beneficiario_usado',
                'nome_beneficiario_usado',
                'titularidade_pendente',
                'validacao_titularidade_status',
                'validacao_documental_status',
                'fase_validacao',
            ] as $column) {
                if (Schema::hasColumn('documento_ia_validacoes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
