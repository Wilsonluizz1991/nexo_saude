<?php

namespace App\Http\Controllers\Interno;

use App\Http\Controllers\Controller;
use App\Services\AssinaturaService;

class AssinaturaController extends Controller
{
    public function bloqueada(AssinaturaService $assinaturaService)
    {
        $assinatura = auth()->user()->assinatura;

        return view('interno.assinatura.bloqueada', [
            'assinatura' => $assinatura,
            'statusComercial' => $assinaturaService->statusComercial($assinatura),
            'diasRestantesTeste' => $assinaturaService->diasRestantesTeste($assinatura),
        ]);
    }

    public function assinar(AssinaturaService $service)
    {
        $service->ativar(auth()->user()->assinatura);

        return redirect()->route('dashboard')->with('status', 'Assinatura ativada por R$ 49,90/mês.');
    }
}