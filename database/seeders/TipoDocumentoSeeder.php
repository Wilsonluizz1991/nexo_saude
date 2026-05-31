<?php

namespace Database\Seeders;

use App\Models\TipoDocumento;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TipoDocumentoSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'RG' => false,
            'CPF' => true,
            'Documento de identidade com foto' => true,
            'CNH' => false,
            'Certidão de Nascimento' => true,
            'Certidão de Casamento' => true,
            'Declaração de União Estável' => true,
            'Comprovante de Residência' => true,
            'Carta de Permanência' => true,
            'Cartão CNPJ' => true,
            'Contrato Social' => true,
            'Relação de Vidas' => true,
            'Documento do Responsável Legal' => true,
            'PDF da Proposta' => true,
            'Outro' => true,
        ] as $nome => $ativo) {
            TipoDocumento::updateOrCreate(
                ['slug' => Str::slug($nome)],
                ['nome' => $nome, 'descricao' => $nome, 'ativo' => $ativo]
            );
        }
    }
}
