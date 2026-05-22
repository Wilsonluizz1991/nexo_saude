<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('corretor_metas_mensais')) {
            return;
        }

        Schema::create('corretor_metas_mensais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('mes_referencia');
            $table->decimal('meta_comissao', 10, 2)->nullable();
            $table->decimal('comissao_realizada', 10, 2)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'mes_referencia']);
            $table->index('mes_referencia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('corretor_metas_mensais');
    }
};
