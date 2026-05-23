<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreCadastro extends Model
{
    protected $table = 'pre_cadastros';

    protected $fillable = [
        'indicacao_id',
        'token',
        'chave_acesso',
        'chave_expira_em',
        'sms_enviado_em',
        'sms_status',
        'tipo_proposta',
        'pessoa',
        'status',
        'formulario_bloqueado',
        'motivos_correcao',
        'enviado_em',
        'bloqueado_em',
    ];

    protected function casts(): array
    {
        return [
            'formulario_bloqueado' => 'boolean',
            'chave_expira_em' => 'datetime',
            'sms_enviado_em' => 'datetime',
            'enviado_em' => 'datetime',
            'bloqueado_em' => 'datetime',
        ];
    }

    public function indicacao() { return $this->belongsTo(Indicacao::class); }
    public function vidas() { return $this->hasMany(Vida::class); }
    public function documentosObrigatorios() { return $this->hasMany(DocumentoObrigatorioPreCadastro::class); }
}
