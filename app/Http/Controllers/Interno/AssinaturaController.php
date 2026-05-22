<?php

namespace App\Http\Controllers\Interno;

use App\Http\Controllers\Controller;
use App\Services\AssinaturaService;

class AssinaturaController extends Controller
{
    public function bloqueada()
    {
        return view('interno.assinatura.bloqueada', ['assinatura' => auth()->user()->assinatura]);
    }

    public function assinar(AssinaturaService $service)
    {
        $service->ativar(auth()->user()->assinatura);

        return redirect()->route('dashboard')->with('status', 'Assinatura ativada por R$ 249,90/mês.');
    }
}
