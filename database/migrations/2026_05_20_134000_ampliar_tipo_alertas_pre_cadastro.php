<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('alertas') || ! Schema::hasColumn('alertas', 'tipo')) {
            return;
        }

        Schema::table('alertas', function (Blueprint $table) {
            $table->string('tipo')->default('info')->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('alertas') || ! Schema::hasColumn('alertas', 'tipo')) {
            return;
        }

        Schema::table('alertas', function (Blueprint $table) {
            $table->enum('tipo', ['info', 'atencao', 'erro', 'sucesso'])->default('info')->change();
        });
    }
};
