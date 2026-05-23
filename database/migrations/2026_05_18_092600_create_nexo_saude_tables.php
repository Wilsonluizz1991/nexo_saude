<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assinaturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->date('data_inicio_teste_gratis');
            $table->date('data_fim_teste_gratis');
            $table->enum('status_assinatura', ['teste_gratis', 'ativa', 'vencida', 'cancelada', 'bloqueada'])->default('teste_gratis');
            $table->decimal('valor_assinatura', 10, 2)->default(49.90);
            $table->date('vencimento_assinatura')->nullable();
            $table->timestamps();
        });

        Schema::create('corretor_perfis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('foto_path')->nullable();
            $table->string('nome_publico');
            $table->text('bio')->nullable();
            $table->json('especialidades')->nullable();
            $table->string('cidade_regiao')->nullable();
            $table->timestamps();
        });

        Schema::create('operadoras', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->boolean('ativa')->default(true);
            $table->timestamps();
        });

        Schema::create('indicacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('origem')->default('pagina_publica');
            $table->string('nome_cliente');
            $table->string('telefone');
            $table->string('email')->nullable();
            $table->string('tipo_plano');
            $table->unsignedInteger('quantidade_vidas')->default(1);
            $table->string('cidade');
            $table->string('estado', 2);
            $table->boolean('possui_preferencias')->nullable();
            $table->json('operadoras_preferidas')->nullable();
            $table->json('hospitais_preferidos')->nullable();
            $table->string('faixa_valor_mensal')->nullable();
            $table->enum('etapa', ['lead', 'propostas', 'pre_cadastros', 'implantacoes', 'clientes', 'carteira'])->default('lead')->index();
            $table->string('status')->default('nova')->index();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });

        Schema::create('propostas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicacao_id')->constrained('indicacoes')->cascadeOnDelete();
            $table->string('titulo');
            $table->string('arquivo_pdf_path');
            $table->decimal('valor_mensal', 10, 2)->nullable();
            $table->enum('status', ['anexada', 'enviada', 'aceita', 'recusada'])->default('anexada');
            $table->timestamps();
        });

        Schema::create('pre_cadastros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicacao_id')->unique()->constrained('indicacoes')->cascadeOnDelete();
            $table->string('token')->unique();
            $table->enum('tipo_proposta', ['individual', 'familiar', 'empresarial']);
            $table->enum('pessoa', ['PF', 'PJ']);
            $table->enum('status', [
                'criado', 'aguardando_envio', 'aberto', 'em_preenchimento', 'documentacao_pendente',
                'documentacao_em_analise', 'documentacao_aprovada', 'correcao_solicitada', 'pronto_para_envio',
                'enviado_para_operadora', 'aprovado', 'recusado', 'convertido_em_cliente',
                'expirado', 'cancelado',
            ])->default('criado');
            $table->boolean('formulario_bloqueado')->default(false);
            $table->text('motivos_correcao')->nullable();
            $table->timestamps();
        });

        Schema::create('vidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pre_cadastro_id')->constrained('pre_cadastros')->cascadeOnDelete();
            $table->enum('tipo', ['titular', 'dependente', 'socio', 'colaborador', 'dependente_socio', 'dependente_colaborador', 'responsavel_legal']);
            $table->foreignId('vinculo_beneficiario_id')->nullable()->constrained('vidas')->nullOnDelete();
            $table->unsignedInteger('ordem')->default(1);
            $table->string('nome')->nullable();
            $table->string('parentesco')->nullable();
            $table->enum('sexo', ['masculino', 'feminino', 'outro'])->nullable();
            $table->date('data_nascimento')->nullable();
            $table->boolean('gestante')->default(false);
            $table->string('cpf')->nullable();
            $table->string('cargo')->nullable();
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('documentos_solicitados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vida_id')->constrained('vidas')->cascadeOnDelete();
            $table->string('nome');
            $table->boolean('obrigatorio')->default(true);
            $table->enum('status', ['pendente', 'enviado', 'aprovado', 'recusado'])->default('pendente');
            $table->timestamps();
        });

        Schema::create('documentos_enviados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_solicitado_id')->nullable()->constrained('documentos_solicitados')->cascadeOnDelete();
            $table->string('arquivo_path');
            $table->text('observacao_cliente')->nullable();
            $table->timestamps();
        });

        Schema::create('implantacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicacao_id')->unique()->constrained('indicacoes')->cascadeOnDelete();
            $table->enum('status', ['contrato_em_analise', 'pendencia_na_operadora', 'aguardando_vigencia', 'contrato_vigente', 'contrato_recusado'])->default('contrato_em_analise');
            $table->date('data_inicio')->nullable();
            $table->date('data_aprovacao')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });

        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicacao_id')->unique()->constrained('indicacoes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nome');
            $table->string('email')->nullable();
            $table->string('telefone')->nullable();
            $table->date('inicio_vigencia')->nullable();
            $table->decimal('valor_mensal', 10, 2)->nullable();
            $table->enum('status', ['ativo', 'em_relacionamento', 'cancelado'])->default('ativo');
            $table->timestamps();
        });

        Schema::create('tarefas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('indicacao_id')->nullable()->constrained('indicacoes')->nullOnDelete();
            $table->string('titulo');
            $table->date('vencimento')->nullable();
            $table->enum('status', ['pendente', 'concluida', 'atrasada'])->default('pendente');
            $table->timestamps();
        });

        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('indicacao_id')->nullable()->constrained('indicacoes')->nullOnDelete();
            $table->string('titulo');
            $table->text('mensagem')->nullable();
            $table->enum('tipo', ['info', 'atencao', 'erro', 'sucesso', 'pre_cadastro_enviado'])->default('info');
            $table->boolean('lido')->default(false);
            $table->timestamps();
        });

        Schema::create('timeline_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicacao_id')->constrained('indicacoes')->cascadeOnDelete();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'timeline_eventos', 'alertas', 'tarefas', 'clientes', 'implantacoes', 'documentos_enviados',
            'documentos_solicitados', 'vidas', 'pre_cadastros', 'propostas', 'indicacoes',
            'operadoras', 'corretor_perfis', 'assinaturas',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
