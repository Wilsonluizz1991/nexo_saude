<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('documento_ia_validacoes');

        Schema::create('documento_ia_validacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pre_cadastro_id')->nullable();
            $table->foreignId('beneficiario_id')->nullable();
            $table->foreignId('documento_obrigatorio_pre_cadastro_id')->nullable();
            $table->foreignId('documento_enviado_id')->nullable();
            $table->foreignId('tipo_documento_id')->nullable();
            $table->string('tipo_documento_esperado')->nullable();
            $table->string('arquivo_nome')->nullable();
            $table->string('arquivo_path')->nullable();
            $table->string('status', 40)->index();
            $table->string('fase_validacao', 30)->default('completa');
            $table->string('validacao_documental_status', 40)->nullable();
            $table->string('validacao_titularidade_status', 40)->nullable();
            $table->boolean('titularidade_pendente')->default(false);
            $table->string('nome_beneficiario_usado')->nullable();
            $table->string('cpf_beneficiario_usado')->nullable();
            $table->string('data_nascimento_usada')->nullable();
            $table->json('dados_extraidos')->nullable();
            $table->json('dados_comparados')->nullable();
            $table->string('tipo_documento_identificado')->nullable();
            $table->boolean('documento_corresponde_ao_tipo')->nullable();
            $table->boolean('legivel')->nullable();
            $table->boolean('cortado')->nullable();
            $table->boolean('desfocado')->nullable();
            $table->boolean('escuro')->nullable();
            $table->boolean('possui_foto')->nullable();
            $table->boolean('documento_possui_nome')->nullable();
            $table->boolean('documento_possui_cpf')->nullable();
            $table->boolean('documento_possui_cnpj')->nullable();
            $table->string('nome_extraido')->nullable();
            $table->string('cpf_extraido')->nullable();
            $table->string('cnpj_extraido')->nullable();
            $table->string('data_nascimento_extraida')->nullable();
            $table->string('nome_vinculado_extraido')->nullable();
            $table->string('razao_social_extraida')->nullable();
            $table->string('endereco_extraido')->nullable();
            $table->string('data_documento_extraida')->nullable();
            $table->boolean('match_nome')->nullable();
            $table->boolean('match_cpf')->nullable();
            $table->boolean('match_cnpj')->nullable();
            $table->boolean('match_data_nascimento')->nullable();
            $table->boolean('match_titular_responsavel')->nullable();
            $table->string('criterio_titularidade_usado')->nullable();
            $table->unsignedTinyInteger('confianca')->nullable();
            $table->boolean('analise_parcial')->default(false);
            $table->unsignedTinyInteger('paginas_analisadas')->nullable();
            $table->unsignedSmallInteger('total_paginas_pdf')->nullable();
            $table->json('motivos')->nullable();
            $table->text('mensagem_cliente')->nullable();
            $table->text('mensagem_corretor')->nullable();
            $table->json('raw_response')->nullable();
            $table->text('erro')->nullable();
            $table->timestamp('analisado_em')->nullable();
            $table->timestamps();

            $table->foreign('pre_cadastro_id', 'doc_ia_pre_fk')->references('id')->on('pre_cadastros')->nullOnDelete();
            $table->foreign('beneficiario_id', 'doc_ia_vida_fk')->references('id')->on('vidas')->nullOnDelete();
            $table->foreign('documento_obrigatorio_pre_cadastro_id', 'doc_ia_obrig_fk')->references('id')->on('documentos_obrigatorios_pre_cadastro')->nullOnDelete();
            $table->foreign('documento_enviado_id', 'doc_ia_envio_fk')->references('id')->on('documentos_enviados')->nullOnDelete();
            $table->foreign('tipo_documento_id', 'doc_ia_tipo_fk')->references('id')->on('tipos_documentos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documento_ia_validacoes');
    }
};
