<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorretorMetaMensal extends Model
{
    protected $table = 'corretor_metas_mensais';

    protected $fillable = [
        'user_id',
        'mes_referencia',
        'meta_comissao',
        'comissao_realizada',
    ];

    protected function casts(): array
    {
        return [
            'mes_referencia' => 'date',
            'meta_comissao' => 'decimal:2',
            'comissao_realizada' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
