<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assinatura extends Model
{
    protected $table = 'assinaturas';

    protected $fillable = ['user_id', 'data_inicio_teste_gratis', 'data_fim_teste_gratis', 'status_assinatura', 'valor_assinatura', 'vencimento_assinatura'];

    protected function casts(): array
    {
        return [
            'data_inicio_teste_gratis' => 'date',
            'data_fim_teste_gratis' => 'date',
            'vencimento_assinatura' => 'date',
            'valor_assinatura' => 'decimal:2',
        ];
    }
}
