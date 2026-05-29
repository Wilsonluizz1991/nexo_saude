<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('perfil', 'users_perfil_perf_index');
            $table->index('is_admin', 'users_is_admin_perf_index');
            $table->index('blocked_at', 'users_blocked_at_perf_index');
        });

        Schema::table('assinaturas', function (Blueprint $table) {
            $table->index('status_assinatura', 'assinaturas_status_assinatura_perf_index');
            $table->index(['status', 'status_assinatura'], 'assinaturas_status_combo_perf_index');
        });

        Schema::table('auditoria_logs', function (Blueprint $table) {
            $table->index('acao', 'auditoria_logs_acao_perf_index');
            $table->index('created_at', 'auditoria_logs_created_at_perf_index');
        });

        Schema::table('indicacoes', function (Blueprint $table) {
            $table->index(['user_id', 'etapa', 'created_at'], 'indicacoes_user_etapa_created_perf_index');
            $table->index(['user_id', 'status'], 'indicacoes_user_status_perf_index');
        });

        Schema::table('tarefas', function (Blueprint $table) {
            $table->index(['user_id', 'status', 'vencimento'], 'tarefas_user_status_vencimento_perf_index');
        });

        Schema::table('alertas', function (Blueprint $table) {
            $table->index(['user_id', 'lido', 'created_at'], 'alertas_user_lido_created_perf_index');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->index(['user_id', 'status', 'created_at'], 'clientes_user_status_created_perf_index');
        });

        Schema::table('contratos', function (Blueprint $table) {
            $table->index(['usuario_id', 'status', 'created_at'], 'contratos_usuario_status_created_perf_index');
        });
    }

    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->dropIndex('contratos_usuario_status_created_perf_index');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropIndex('clientes_user_status_created_perf_index');
        });

        Schema::table('alertas', function (Blueprint $table) {
            $table->dropIndex('alertas_user_lido_created_perf_index');
        });

        Schema::table('tarefas', function (Blueprint $table) {
            $table->dropIndex('tarefas_user_status_vencimento_perf_index');
        });

        Schema::table('indicacoes', function (Blueprint $table) {
            $table->dropIndex('indicacoes_user_status_perf_index');
            $table->dropIndex('indicacoes_user_etapa_created_perf_index');
        });

        Schema::table('auditoria_logs', function (Blueprint $table) {
            $table->dropIndex('auditoria_logs_created_at_perf_index');
            $table->dropIndex('auditoria_logs_acao_perf_index');
        });

        Schema::table('assinaturas', function (Blueprint $table) {
            $table->dropIndex('assinaturas_status_combo_perf_index');
            $table->dropIndex('assinaturas_status_assinatura_perf_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_blocked_at_perf_index');
            $table->dropIndex('users_is_admin_perf_index');
            $table->dropIndex('users_perfil_perf_index');
        });
    }
};
