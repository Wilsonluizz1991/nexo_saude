<?php

namespace App\Http\Middleware;

use App\Services\AssinaturaService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarAssinaturaAtiva
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $assinatura = $user->assinatura;

        if (app(AssinaturaService::class)->estaAtiva($assinatura)) {
            return $next($request);
        }

        if ($request->routeIs('assinatura.*')) {
            return $next($request);
        }

        if ($request->routeIs('logout')) {
            return $next($request);
        }

        return redirect()->route('assinatura.bloqueada');
    }
}