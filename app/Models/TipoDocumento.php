<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoDocumento extends Model
{
    protected $table = 'tipos_documentos';

    protected $fillable = ['nome', 'slug', 'descricao', 'ativo'];
}
