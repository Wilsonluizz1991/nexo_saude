<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoObrigatorioPreCadastro extends Model
{
    protected $table = 'documentos_obrigatorios_pre_cadastro';

    protected $fillable = ['pre_cadastro_id', 'vida_proposta_id', 'tipo_documento_id', 'requisito_documental_id', 'titulo', 'obrigatorio', 'ordem', 'status', 'grupo_alternativo', 'observacoes'];

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
}
