<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoIaValidacao extends Model
{
    protected $table = 'documento_ia_validacoes';

    protected $fillable = [
        'pre_cadastro_id',
        'beneficiario_id',
        'documento_obrigatorio_pre_cadastro_id',
        'documento_enviado_id',
        'tipo_documento_id',
        'tipo_documento_esperado',
        'arquivo_nome',
        'arquivo_path',
        'status',
        'fase_validacao',
        'validacao_documental_status',
        'validacao_titularidade_status',
        'titularidade_pendente',
        'nome_beneficiario_usado',
        'cpf_beneficiario_usado',
        'data_nascimento_usada',
        'dados_extraidos',
        'dados_comparados',
        'tipo_documento_identificado',
        'documento_corresponde_ao_tipo',
        'legivel',
        'cortado',
        'desfocado',
        'escuro',
        'possui_foto',
        'documento_possui_nome',
        'documento_possui_cpf',
        'documento_possui_cnpj',
        'nome_extraido',
        'cpf_extraido',
        'cnpj_extraido',
        'data_nascimento_extraida',
        'nome_vinculado_extraido',
        'razao_social_extraida',
        'endereco_extraido',
        'data_documento_extraida',
        'match_nome',
        'match_cpf',
        'match_cnpj',
        'match_data_nascimento',
        'match_titular_responsavel',
        'criterio_titularidade_usado',
        'confianca',
        'analise_parcial',
        'paginas_analisadas',
        'total_paginas_pdf',
        'motivos',
        'mensagem_cliente',
        'mensagem_corretor',
        'raw_response',
        'erro',
        'analisado_em',
    ];

    protected function casts(): array
    {
        return [
            'documento_corresponde_ao_tipo' => 'boolean',
            'legivel' => 'boolean',
            'cortado' => 'boolean',
            'desfocado' => 'boolean',
            'escuro' => 'boolean',
            'possui_foto' => 'boolean',
            'documento_possui_nome' => 'boolean',
            'documento_possui_cpf' => 'boolean',
            'documento_possui_cnpj' => 'boolean',
            'titularidade_pendente' => 'boolean',
            'dados_extraidos' => 'array',
            'dados_comparados' => 'array',
            'match_nome' => 'boolean',
            'match_cpf' => 'boolean',
            'match_cnpj' => 'boolean',
            'match_data_nascimento' => 'boolean',
            'match_titular_responsavel' => 'boolean',
            'analise_parcial' => 'boolean',
            'motivos' => 'array',
            'raw_response' => 'array',
            'analisado_em' => 'datetime',
        ];
    }
}
