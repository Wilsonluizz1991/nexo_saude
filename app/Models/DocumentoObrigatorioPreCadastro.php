<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoObrigatorioPreCadastro extends Model
{
    protected $table = 'documentos_obrigatorios_pre_cadastro';

    protected $fillable = ['pre_cadastro_id', 'vida_proposta_id', 'tipo_documento_id', 'requisito_documental_id', 'titulo', 'obrigatorio', 'ordem', 'status', 'grupo_alternativo', 'observacoes', 'dispensado_por_ia', 'dispensado_por_documento_id', 'motivo_dispensa', 'dispensado_em', 'validado_por_documento_compartilhado', 'documento_origem_id', 'beneficiario_origem_id', 'motivo_validacao', 'tipo_regra_validacao'];

    protected function casts(): array
    {
        return [
            'obrigatorio' => 'boolean',
            'dispensado_por_ia' => 'boolean',
            'dispensado_em' => 'datetime',
            'validado_por_documento_compartilhado' => 'boolean',
        ];
    }

    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class);
    }

    public function preCadastro()
    {
        return $this->belongsTo(PreCadastro::class);
    }

    public function envio()
    {
        return $this->hasOne(DocumentoEnviado::class, 'documento_obrigatorio_pre_cadastro_id')->latestOfMany();
    }

    public function envios()
    {
        return $this->hasMany(DocumentoEnviado::class, 'documento_obrigatorio_pre_cadastro_id');
    }

    public function iaValidacoes()
    {
        return $this->hasMany(DocumentoIaValidacao::class, 'documento_obrigatorio_pre_cadastro_id');
    }

    public function dispensadoPorDocumento()
    {
        return $this->belongsTo(self::class, 'dispensado_por_documento_id');
    }

    public function documentoOrigem()
    {
        return $this->belongsTo(self::class, 'documento_origem_id');
    }

    public function beneficiarioOrigem()
    {
        return $this->belongsTo(Vida::class, 'beneficiario_origem_id');
    }
}
