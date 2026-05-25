<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avaliacoes_atendimento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('indicacao_id')->nullable()->constrained('indicacoes')->nullOnDelete();
            $table->string('token')->unique();
            $table->enum('status', ['pendente', 'respondida'])->default('pendente')->index();
            $table->unsignedTinyInteger('nota_atendimento')->nullable();
            $table->unsignedTinyInteger('nota_clareza')->nullable();
            $table->unsignedTinyInteger('nota_agilidade')->nullable();
            $table->unsignedTinyInteger('nota_confianca')->nullable();
            $table->unsignedTinyInteger('nota_recomendacao')->nullable();
            $table->text('comentario')->nullable();
            $table->timestamp('respondida_em')->nullable();
            $table->timestamps();

            $table->unique(['cliente_id', 'indicacao_id'], 'avaliacoes_cliente_indicacao_unique');
            $table->index(['user_id', 'status']);
        });

        Schema::table('corretor_perfis', function (Blueprint $table) {
            if (! Schema::hasColumn('corretor_perfis', 'mensagem_contrato_vigente_whatsapp')) {
                $table->text('mensagem_contrato_vigente_whatsapp')->nullable()->after('mensagem_primeiro_contato_whatsapp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('corretor_perfis', function (Blueprint $table) {
            if (Schema::hasColumn('corretor_perfis', 'mensagem_contrato_vigente_whatsapp')) {
                $table->dropColumn('mensagem_contrato_vigente_whatsapp');
            }
        });

        Schema::dropIfExists('avaliacoes_atendimento');
    }
};
