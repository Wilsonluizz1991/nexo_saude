<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequisitoDocumental extends Model
{
    protected $table = 'requisitos_documentais';

    protected $fillable = ['nome', 'tipo_proposta', 'tipo_pessoa', 'papel_vida', 'parentesco', 'sexo', 'exige_gestante', 'tipo_documento_id', 'obrigatorio', 'grupo_alternativo', 'ativo'];

    protected function casts(): array
    {
        return ['exige_gestante' => 'boolean', 'obrigatorio' => 'boolean', 'ativo' => 'boolean'];
    }

    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class);
    }
}
