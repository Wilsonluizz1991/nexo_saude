<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interacao extends Model
{
    protected $table = 'interacoes';

    protected $fillable = ['usuario_id', 'indicacao_id', 'cliente_id', 'proposta_id', 'pre_cadastro_id', 'tipo', 'titulo', 'descricao', 'interacao_em'];

    protected function casts(): array
    {
        return ['interacao_em' => 'datetime'];
    }
}
