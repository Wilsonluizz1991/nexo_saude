<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ServicoPrivacidade
{
    public function excluirConta(User $user, string $senha, string $confirmacao): void
    {
        if ($confirmacao !== 'EXCLUIR MINHA CONTA') {
            throw ValidationException::withMessages(['confirmacao' => 'Digite EXCLUIR MINHA CONTA para confirmar.']);
        }

        if (! Hash::check($senha, $user->password)) {
            throw ValidationException::withMessages(['senha' => 'Senha inválida.']);
        }

        $user->assinatura?->update(['status_assinatura' => 'cancelada']);
        $user->corretorPerfil?->update(['publico_ativo' => false]);
        $user->delete();
    }
}
