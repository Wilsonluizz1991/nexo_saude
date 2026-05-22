<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Implantacao extends Model
{
    protected $table = 'implantacoes';

    protected $fillable = ['indicacao_id', 'status', 'data_inicio', 'data_aprovacao', 'observacoes'];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'date',
            'data_aprovacao' => 'date',
        ];
    }

    public function indicacao()
    {
        return $this->belongsTo(Indicacao::class);
    }
}
