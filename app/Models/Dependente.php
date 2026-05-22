<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dependente extends Model
{
    protected $table = 'dependentes';

    protected $fillable = ['cliente_id', 'nome', 'documento', 'data_nascimento', 'sexo', 'parentesco', 'gestante', 'status'];

    protected function casts(): array
    {
        return ['data_nascimento' => 'date', 'gestante' => 'boolean'];
    }
}
