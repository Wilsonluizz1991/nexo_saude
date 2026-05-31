<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('propostas', function (Blueprint $table) {
            if (! Schema::hasColumn('propostas', 'public_token')) {
                $table->string('public_token', 64)->nullable()->unique()->after('arquivo_pdf_path');
            }

            if (! Schema::hasColumn('propostas', 'public_group_token')) {
                $table->string('public_group_token', 64)->nullable()->index()->after('public_token');
            }

            if (! Schema::hasColumn('propostas', 'enviado_email_em')) {
                $table->timestamp('enviado_email_em')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('propostas', function (Blueprint $table) {
            if (Schema::hasColumn('propostas', 'enviado_email_em')) {
                $table->dropColumn('enviado_email_em');
            }

            if (Schema::hasColumn('propostas', 'public_group_token')) {
                $table->dropColumn('public_group_token');
            }

            if (Schema::hasColumn('propostas', 'public_token')) {
                $table->dropUnique(['public_token']);
                $table->dropColumn('public_token');
            }
        });
    }
};
