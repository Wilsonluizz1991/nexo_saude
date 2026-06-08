<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $agora = now();
        $slugIdentidade = Str::slug('Documento de identidade com foto');

        $tipoIdentidade = DB::table('tipos_documentos')->where('slug', $slugIdentidade)->first();

        if (! $tipoIdentidade) {
            $idIdentidade = DB::table('tipos_documentos')->insertGetId([
                'nome' => 'Documento de identidade com foto',
                'slug' => $slugIdentidade,
                'descricao' => 'Documento de identidade com foto',
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ]);
        } else {
            $idIdentidade = $tipoIdentidade->id;

            DB::table('tipos_documentos')
                ->where('id', $idIdentidade)
                ->update([
                    'nome' => 'Documento de identidade com foto',
                    'descricao' => 'Documento de identidade com foto',
                    'ativo' => true,
                    'updated_at' => $agora,
                ]);
        }

        $tipoRg = DB::table('tipos_documentos')->where('nome', 'RG')->first();

        DB::table('tipos_documentos')
            ->whereIn('nome', ['RG', 'CNH'])
            ->update([
                'ativo' => false,
                'updated_at' => $agora,
            ]);

        if (! $tipoRg) {
            return;
        }

        $nomes = [
            'Titular PF - RG' => 'Titular PF - Documento de identidade com foto',
            'Cônjuge - RG' => 'Cônjuge - Documento de identidade com foto',
            'Cônjuge - RG' => 'Cônjuge - Documento de identidade com foto',
            'Sócio - RG' => 'Sócio - Documento de identidade com foto',
            'Sócio - RG' => 'Sócio - Documento de identidade com foto',
            'Responsável legal - RG' => 'Responsável legal - Documento de identidade com foto',
            'Responsável legal - RG' => 'Responsável legal - Documento de identidade com foto',
            'Colaborador - RG' => 'Colaborador - Documento de identidade com foto',
        ];

        foreach ($nomes as $nomeAtual => $nomeNovo) {
            DB::table('requisitos_documentais')
                ->where('nome', $nomeAtual)
                ->where('tipo_documento_id', $tipoRg->id)
                ->update([
                    'nome' => $nomeNovo,
                    'tipo_documento_id' => $idIdentidade,
                    'ativo' => true,
                    'updated_at' => $agora,
                ]);
        }

        DB::table('requisitos_documentais')
            ->where('tipo_documento_id', $tipoRg->id)
            ->where('ativo', true)
            ->update([
                'tipo_documento_id' => $idIdentidade,
                'updated_at' => $agora,
            ]);
    }

    public function down(): void
    {
        DB::table('tipos_documentos')
            ->whereIn('nome', ['RG', 'CNH'])
            ->update([
                'ativo' => true,
                'updated_at' => now(),
            ]);
    }
};
