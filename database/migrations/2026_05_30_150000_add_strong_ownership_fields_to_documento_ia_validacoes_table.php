<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documento_ia_validacoes', function (Blueprint $table) {
            if (! Schema::hasColumn('documento_ia_validacoes', 'match_data_nascimento')) {
                $table->boolean('match_data_nascimento')->nullable()->after('match_cnpj');
            }

            if (! Schema::hasColumn('documento_ia_validacoes', 'nome_vinculado_extraido')) {
                $table->string('nome_vinculado_extraido')->nullable()->after('data_nascimento_extraida');
            }

            if (! Schema::hasColumn('documento_ia_validacoes', 'documento_possui_nome')) {
                $table->boolean('documento_possui_nome')->nullable()->after('possui_foto');
            }

            if (! Schema::hasColumn('documento_ia_validacoes', 'documento_possui_cpf')) {
                $table->boolean('documento_possui_cpf')->nullable()->after('documento_possui_nome');
            }

            if (! Schema::hasColumn('documento_ia_validacoes', 'documento_possui_cnpj')) {
                $table->boolean('documento_possui_cnpj')->nullable()->after('documento_possui_cpf');
            }

            if (! Schema::hasColumn('documento_ia_validacoes', 'criterio_titularidade_usado')) {
                $table->string('criterio_titularidade_usado')->nullable()->after('match_titular_responsavel');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documento_ia_validacoes', function (Blueprint $table) {
            foreach ([
                'criterio_titularidade_usado',
                'documento_possui_cnpj',
                'documento_possui_cpf',
                'documento_possui_nome',
                'nome_vinculado_extraido',
                'match_data_nascimento',
            ] as $column) {
                if (Schema::hasColumn('documento_ia_validacoes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
