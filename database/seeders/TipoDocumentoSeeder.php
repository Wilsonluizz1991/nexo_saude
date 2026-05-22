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
            'RG',
            'CPF',
            'Documento de identidade com foto',
            'CNH',
            'Certidão de Nascimento',
            'Certidão de Casamento',
            'Declaração de União Estável',
            'Comprovante de Residência',
            'Carta de Permanência',
            'Cartão CNPJ',
            'Contrato Social',
            'Relação de Vidas',
            'Documento do Responsável Legal',
            'PDF da Proposta',
            'Outro',
        ] as $nome) {
            TipoDocumento::firstOrCreate(
                ['slug' => Str::slug($nome)],
                ['nome' => $nome, 'descricao' => $nome, 'ativo' => true]
            );
        }
    }
}
