<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tarefas')) {
            Schema::table('tarefas', function (Blueprint $table) {
                if (! Schema::hasColumn('tarefas', 'tipo')) {
                    $table->string('tipo')->default('tarefa')->index()->after('indicacao_id');
                }

                if (! Schema::hasColumn('tarefas', 'descricao')) {
                    $table->text('descricao')->nullable()->after('titulo');
                }
            });
        }

        if (Schema::hasTable('alertas')) {
            Schema::table('alertas', function (Blueprint $table) {
                if (! Schema::hasColumn('alertas', 'tarefa_id')) {
                    $table->unsignedBigInteger('tarefa_id')->nullable()->after('indicacao_id');
                    $table->index('tarefa_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('alertas') && Schema::hasColumn('alertas', 'tarefa_id')) {
            Schema::table('alertas', function (Blueprint $table) {
                $table->dropIndex(['tarefa_id']);
                $table->dropColumn('tarefa_id');
            });
        }

        if (Schema::hasTable('tarefas')) {
            Schema::table('tarefas', function (Blueprint $table) {
                if (Schema::hasColumn('tarefas', 'descricao')) {
                    $table->dropColumn('descricao');
                }

                if (Schema::hasColumn('tarefas', 'tipo')) {
                    $table->dropColumn('tipo');
                }
            });
        }
    }
};
