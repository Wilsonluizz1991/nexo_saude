<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditoriaLog extends Model
{
    protected $table = 'auditoria_logs';

    protected $fillable = [
        'admin_user_id',
        'target_user_id',
        'acao',
        'modulo',
        'descricao',
        'dados_anteriores',
        'dados_novos',
        'ip',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'dados_anteriores' => 'array',
            'dados_novos' => 'array',
        ];
    }

    public function administrador()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function usuarioAlvo()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}