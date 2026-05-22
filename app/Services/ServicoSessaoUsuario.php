<?php

namespace App\Services;

use App\Models\SessaoUsuario;
use App\Models\User;
use Illuminate\Http\Request;

class ServicoSessaoUsuario
{
    public function registrarAcesso(User $user, Request $request): void
    {
        $user->update([
            'ultimo_login_em' => now(),
            'ultimo_ip' => $request->ip(),
        ]);

        SessaoUsuario::updateOrCreate([
            'usuario_id' => $user->id,
            'session_id' => $request->session()->getId(),
        ], [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'dispositivo' => $this->dispositivo($request->userAgent()),
            'navegador' => $this->navegador($request->userAgent()),
            'sistema_operacional' => $this->sistema($request->userAgent()),
            'ultima_atividade_em' => now(),
            'atual' => true,
        ]);
    }

    public function encerrarOutrasSessoes(User $user, string $sessionId): void
    {
        $user->sessoesUsuario()->where('session_id', '!=', $sessionId)->delete();
    }

    private function navegador(?string $agent): string
    {
        return str_contains($agent ?? '', 'Chrome') ? 'Chrome' : (str_contains($agent ?? '', 'Firefox') ? 'Firefox' : 'Navegador');
    }

    private function sistema(?string $agent): string
    {
        return str_contains($agent ?? '', 'Windows') ? 'Windows' : (str_contains($agent ?? '', 'Mac') ? 'macOS' : 'Sistema');
    }

    private function dispositivo(?string $agent): string
    {
        return str_contains($agent ?? '', 'Mobile') ? 'Celular' : 'Computador';
    }
}
