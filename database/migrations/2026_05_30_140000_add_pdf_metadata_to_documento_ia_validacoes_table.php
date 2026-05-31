<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documento_ia_validacoes', function (Blueprint $table) {
            if (! Schema::hasColumn('documento_ia_validacoes', 'analise_parcial')) {
                $table->boolean('analise_parcial')->default(false)->after('confianca');
            }

            if (! Schema::hasColumn('documento_ia_validacoes', 'paginas_analisadas')) {
                $table->unsignedTinyInteger('paginas_analisadas')->nullable()->after('analise_parcial');
            }

            if (! Schema::hasColumn('documento_ia_validacoes', 'total_paginas_pdf')) {
                $table->unsignedSmallInteger('total_paginas_pdf')->nullable()->after('paginas_analisadas');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documento_ia_validacoes', function (Blueprint $table) {
            foreach (['total_paginas_pdf', 'paginas_analisadas', 'analise_parcial'] as $column) {
                if (Schema::hasColumn('documento_ia_validacoes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
