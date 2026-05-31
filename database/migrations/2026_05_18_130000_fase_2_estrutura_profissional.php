<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('ultimo_login_em')->nullable()->after('remember_token');
            $table->string('ultimo_ip', 45)->nullable()->after('ultimo_login_em');
            $table->string('timezone')->default('America/Sao_Paulo')->after('ultimo_ip');
            $table->string('idioma')->default('pt-BR')->after('timezone');
            $table->string('formato_data')->default('d/m/Y')->after('idioma');
            $table->boolean('receber_alertas_email')->default(true)->after('formato_data');
            $table->boolean('receber_notificacoes_aniversario')->default(true)->after('receber_alertas_email');
            $table->boolean('receber_notificacoes_renovacao')->default(true)->after('receber_notificacoes_aniversario');
            $table->boolean('receber_notificacoes_tarefas')->default(true)->after('receber_notificacoes_renovacao');
            $table->softDeletes();
        });

        Schema::table('corretor_perfis', function (Blueprint $table) {
            $table->string('cidade')->nullable()->after('cidade_regiao');
            $table->string('estado', 2)->nullable()->after('cidade');
            $table->unsignedInteger('anos_experiencia')->default(0)->after('estado');
            $table->boolean('publico_ativo')->default(true)->after('anos_experiencia');
        });

        Schema::table('propostas', function (Blueprint $table) {
            $table->foreignId('operadora_id')->nullable()->after('indicacao_id')->constrained('operadoras')->nullOnDelete();
            $table->foreignId('cliente_id')->nullable()->after('operadora_id')->constrained('clientes')->nullOnDelete();
            $table->date('validade')->nullable()->after('arquivo_pdf_path');
            $table->unsignedInteger('quantidade_vidas')->nullable()->after('validade');
            $table->text('observacoes')->nullable()->after('valor_mensal');
        });

        Schema::create('tipos_documentos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->string('slug')->unique();
            $table->text('descricao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('requisitos_documentais', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('tipo_proposta')->nullable()->index();
            $table->string('tipo_pessoa')->nullable()->index();
            $table->string('papel_vida')->nullable()->index();
            $table->string('parentesco')->nullable()->index();
            $table->string('sexo')->nullable()->index();
            $table->boolean('exige_gestante')->default(false);
            $table->foreignId('tipo_documento_id')->constrained('tipos_documentos')->cascadeOnDelete();
            $table->boolean('obrigatorio')->default(true);
            $table->string('grupo_alternativo')->nullable()->index();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('documentos_obrigatorios_pre_cadastro', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pre_cadastro_id')->constrained('pre_cadastros')->cascadeOnDelete();
            $table->foreignId('vida_proposta_id')->nullable()->constrained('vidas')->cascadeOnDelete();
            $table->foreignId('tipo_documento_id')->constrained('tipos_documentos')->cascadeOnDelete();

            $table->unsignedBigInteger('requisito_documental_id')->nullable();

            $table->string('titulo');
            $table->boolean('obrigatorio')->default(true);
            $table->unsignedInteger('ordem')->default(1);
            $table->enum('status', ['pendente', 'enviado', 'aprovado', 'recusado', 'corrigir', 'dispensado'])->default('pendente')->index();
            $table->string('grupo_alternativo')->nullable()->index();
            $table->text('observacoes')->nullable();
            $table->boolean('dispensado_por_ia')->default(false);
            $table->foreignId('dispensado_por_documento_id')->nullable();
            $table->text('motivo_dispensa')->nullable();
            $table->timestamp('dispensado_em')->nullable();
            $table->timestamps();

            $table->foreign('requisito_documental_id', 'doc_obrig_req_doc_fk')
                ->references('id')
                ->on('requisitos_documentais')
                ->nullOnDelete();
        });

        Schema::table('documentos_enviados', function (Blueprint $table) {
            $table->unsignedBigInteger('documento_obrigatorio_pre_cadastro_id')->nullable()->after('documento_solicitado_id');
            $table->unsignedBigInteger('tipo_documento_solicitado_id')->nullable()->after('documento_obrigatorio_pre_cadastro_id');
            $table->unsignedBigInteger('tipo_documento_detectado_id')->nullable()->after('tipo_documento_solicitado_id');

            $table->enum('status_ia', ['nao_analisado', 'aguardando_analise', 'analisando', 'aprovado', 'alerta', 'recusado', 'falhou'])->default('nao_analisado')->after('tipo_documento_detectado_id');
            $table->json('analise_ia')->nullable()->after('status_ia');
            $table->boolean('documento_compativel')->nullable()->after('analise_ia');
            $table->boolean('legivel')->nullable()->after('documento_compativel');
            $table->boolean('cortado')->nullable()->after('legivel');
            $table->boolean('tremido')->nullable()->after('cortado');
            $table->string('nome_detectado')->nullable()->after('tremido');
            $table->timestamp('analisado_em')->nullable()->after('nome_detectado');
            $table->text('motivo_recusa')->nullable()->after('analisado_em');

            $table->foreign('documento_obrigatorio_pre_cadastro_id', 'doc_env_doc_obrig_fk')
                ->references('id')
                ->on('documentos_obrigatorios_pre_cadastro')
                ->cascadeOnDelete();

            $table->foreign('tipo_documento_solicitado_id', 'doc_env_tipo_sol_fk')
                ->references('id')
                ->on('tipos_documentos')
                ->nullOnDelete();

            $table->foreign('tipo_documento_detectado_id', 'doc_env_tipo_det_fk')
                ->references('id')
                ->on('tipos_documentos')
                ->nullOnDelete();
        });

        Schema::create('interacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('indicacao_id')->nullable()->constrained('indicacoes')->cascadeOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('proposta_id')->nullable()->constrained('propostas')->cascadeOnDelete();
            $table->foreignId('pre_cadastro_id')->nullable()->constrained('pre_cadastros')->cascadeOnDelete();
            $table->string('tipo')->index();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->timestamp('interacao_em')->index();
            $table->timestamps();
        });

        Schema::create('dependentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('nome');
            $table->string('documento')->nullable();
            $table->date('data_nascimento')->nullable()->index();
            $table->string('sexo')->nullable();
            $table->string('parentesco')->nullable()->index();
            $table->boolean('gestante')->default(false);
            $table->string('status')->default('ativo')->index();
            $table->timestamps();
        });

        Schema::create('contratos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('proposta_id')->nullable()->constrained('propostas')->nullOnDelete();
            $table->foreignId('operadora_id')->nullable()->constrained('operadoras')->nullOnDelete();
            $table->string('tipo_contrato')->index();
            $table->string('status')->default('ativo')->index();
            $table->unsignedInteger('quantidade_vidas')->default(1);
            $table->decimal('valor_mensal', 10, 2)->nullable();
            $table->string('numero_contrato')->nullable()->index();
            $table->date('iniciado_em')->nullable()->index();
            $table->date('renovacao_em')->nullable()->index();
            $table->date('reajuste_em')->nullable()->index();
            $table->date('cancelado_em')->nullable();
            $table->string('motivo_cancelamento')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });

        Schema::create('sessoes_usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->string('session_id')->index();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('dispositivo')->nullable();
            $table->string('navegador')->nullable();
            $table->string('sistema_operacional')->nullable();
            $table->timestamp('ultima_atividade_em')->nullable()->index();
            $table->boolean('atual')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessoes_usuarios');
        Schema::dropIfExists('contratos');
        Schema::dropIfExists('dependentes');
        Schema::dropIfExists('interacoes');
        Schema::dropIfExists('documentos_obrigatorios_pre_cadastro');
        Schema::dropIfExists('requisitos_documentais');
        Schema::dropIfExists('tipos_documentos');
    }
};
