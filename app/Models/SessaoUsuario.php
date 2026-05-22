<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessaoUsuario extends Model
{
    protected $table = 'sessoes_usuarios';

    protected $fillable = ['usuario_id', 'session_id', 'ip', 'user_agent', 'dispositivo', 'navegador', 'sistema_operacional', 'ultima_atividade_em', 'atual'];

    protected function casts(): array
    {
        return ['ultima_atividade_em' => 'datetime', 'atual' => 'boolean'];
    }
}
