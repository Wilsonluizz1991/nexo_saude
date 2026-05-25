<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alerta extends Model
{
    protected $table = 'alertas';

    protected $fillable = [
        'user_id',
        'indicacao_id',
        'tarefa_id',
        'pre_cadastro_id',
        'proposta_id',
        'cliente_id',
        'chave',
        'data_referencia',
        'titulo',
        'mensagem',
        'tipo',
        'status',
        'lido',
    ];

    protected function casts(): array
    {
        return [
            'lido' => 'boolean',
            'data_referencia' => 'date',
        ];
    }
}
