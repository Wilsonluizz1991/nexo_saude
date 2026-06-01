<?php

namespace App\Services\Admin;

use App\Models\AuditoriaLog;
use App\Models\User;
use Illuminate\Http\Request;

class RegistrarAuditoriaService
{
    public function registrar(
        string $acao,
        ?User $usuarioAlvo = null,
        ?string $descricao = null,
        ?array $dadosAnteriores = null,
        ?array $dadosNovos = null,
        string $modulo = 'admin',
        ?Request $request = null
    ): AuditoriaLog {
        $request = $request ?: request();

        return AuditoriaLog::create([
            'admin_user_id' => auth()->id(),
            'target_user_id' => $usuarioAlvo?->id,
            'acao' => $acao,
            'modulo' => $modulo,
            'descricao' => $descricao,
            'dados_anteriores' => $dadosAnteriores,
            'dados_novos' => $dadosNovos,
            'ip' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}