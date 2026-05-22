<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ServicoSegurancaUsuario
{
    public function alterarSenha(User $user, string $senhaAtual, string $novaSenha): void
    {
        if (! Hash::check($senhaAtual, $user->password)) {
            throw ValidationException::withMessages(['senha_atual' => 'Senha atual inválida.']);
        }

        $user->update(['password' => Hash::make($novaSenha)]);
    }
}
