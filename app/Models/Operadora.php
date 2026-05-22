<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operadora extends Model
{
    protected $table = 'operadoras';

    protected $fillable = ['nome', 'ativa'];
}
