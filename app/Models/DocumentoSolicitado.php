<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoSolicitado extends Model
{
    protected $table = 'documentos_solicitados';
    protected $fillable = ['vida_id', 'nome', 'obrigatorio', 'status'];

    public function documentoEnviado()
    {
        return $this->hasOne(DocumentoEnviado::class);
    }
}
