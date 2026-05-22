<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoEnviado extends Model
{
    protected $table = 'documentos_enviados';
    protected $fillable = [
        'pre_cadastro_id',
        'beneficiario_id',
        'documento_solicitado_id',
        'documento_obrigatorio_pre_cadastro_id',
        'tipo_documento_solicitado_id',
        'tipo_documento_detectado_id',
        'arquivo_path',
        'observacao_cliente',
        'status_ia',
        'analise_ia',
        'documento_compativel',
        'legivel',
        'cortado',
        'tremido',
        'nome_detectado',
        'analisado_em',
        'motivo_recusa',
    ];

    protected function casts(): array
    {
        return [
            'analise_ia' => 'array',
            'documento_compativel' => 'boolean',
            'legivel' => 'boolean',
            'cortado' => 'boolean',
            'tremido' => 'boolean',
            'analisado_em' => 'datetime',
        ];
    }
}
