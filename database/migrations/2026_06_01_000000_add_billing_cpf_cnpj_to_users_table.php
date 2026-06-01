<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'billing_cpf_cnpj')) {
                $table->string('billing_cpf_cnpj', 14)->nullable()->after('telefone')->unique();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'billing_cpf_cnpj')) {
                $table->dropUnique('users_billing_cpf_cnpj_unique');
                $table->dropColumn('billing_cpf_cnpj');
            }
        });
    }
};
