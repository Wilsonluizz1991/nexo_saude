<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('corretor_perfis') || Schema::hasColumn('corretor_perfis', 'mensagem_primeiro_contato_whatsapp')) {
            return;
        }

        Schema::table('corretor_perfis', function (Blueprint $table) {
            $table->text('mensagem_primeiro_contato_whatsapp')->nullable()->after('publico_ativo');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('corretor_perfis') || ! Schema::hasColumn('corretor_perfis', 'mensagem_primeiro_contato_whatsapp')) {
            return;
        }

        Schema::table('corretor_perfis', function (Blueprint $table) {
            $table->dropColumn('mensagem_primeiro_contato_whatsapp');
        });
    }
};
