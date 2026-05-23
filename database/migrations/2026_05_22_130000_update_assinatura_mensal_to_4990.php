<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('assinaturas') || ! Schema::hasColumn('assinaturas', 'valor_assinatura')) {
            return;
        }

        DB::table('assinaturas')
            ->where('valor_assinatura', 249.90)
            ->update(['valor_assinatura' => 49.90]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('assinaturas') || ! Schema::hasColumn('assinaturas', 'valor_assinatura')) {
            return;
        }

        DB::table('assinaturas')
            ->where('valor_assinatura', 49.90)
            ->update(['valor_assinatura' => 249.90]);
    }
};
