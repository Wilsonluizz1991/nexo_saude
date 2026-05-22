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

        if (! $user || app(AssinaturaService::class)->estaAtiva($user->assinatura)) {
            return $next($request);
        }

        if ($request->routeIs('assinatura.*')) {
            return $next($request);
        }

        return redirect()->route('assinatura.bloqueada');
    }
}
