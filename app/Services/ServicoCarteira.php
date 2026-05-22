<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\User;

class ServicoCarteira
{
    public function clientesOperacionais(User $user)
    {
        return Cliente::where('user_id', $user->id)->with('contratos', 'dependentes')->latest()->get();
    }
}
