<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pre_cadastros', function (Blueprint $table) {
            if (! Schema::hasColumn('pre_cadastros', 'chave_acesso')) {
                $table->string('chave_acesso', 16)->nullable()->unique()->after('token');
            }

            if (! Schema::hasColumn('pre_cadastros', 'chave_expira_em')) {
                $table->timestamp('chave_expira_em')->nullable()->after('chave_acesso');
            }

            if (! Schema::hasColumn('pre_cadastros', 'sms_enviado_em')) {
                $table->timestamp('sms_enviado_em')->nullable()->after('chave_expira_em');
            }

            if (! Schema::hasColumn('pre_cadastros', 'sms_status')) {
                $table->string('sms_status')->nullable()->after('sms_enviado_em');
            }
        });

        if (! Schema::hasTable('sms_messages')) {
            Schema::create('sms_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('indicacao_id')->nullable()->constrained('indicacoes')->cascadeOnDelete();
                $table->foreignId('pre_cadastro_id')->nullable()->constrained('pre_cadastros')->cascadeOnDelete();
                $table->string('to');
                $table->text('message');
                $table->string('provider')->default('log');
                $table->string('status')->default('pending')->index();
                $table->unsignedInteger('attempts')->default(0);
                $table->text('last_error')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_messages');

        Schema::table('pre_cadastros', function (Blueprint $table) {
            foreach (['sms_status', 'sms_enviado_em', 'chave_expira_em', 'chave_acesso'] as $column) {
                if (Schema::hasColumn('pre_cadastros', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
