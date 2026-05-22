<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tarefa extends Model
{
    protected $table = 'tarefas';

    protected $fillable = ['user_id', 'indicacao_id', 'tipo', 'titulo', 'descricao', 'vencimento', 'status'];

    protected function casts(): array
    {
        return ['vencimento' => 'date'];
    }

    public function indicacao() { return $this->belongsTo(Indicacao::class); }
}
