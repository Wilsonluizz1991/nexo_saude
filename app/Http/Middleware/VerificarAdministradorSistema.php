<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarAdministradorSistema
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || (! $user->is_admin && $user->perfil !== 'admin')) {
            abort(403, 'Acesso restrito ao administrador do sistema.');
        }

        return $next($request);
    }
}
