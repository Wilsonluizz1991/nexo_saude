<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimelineEvento extends Model
{
    protected $table = 'timeline_eventos';
    protected $fillable = ['indicacao_id', 'titulo', 'descricao'];
}
