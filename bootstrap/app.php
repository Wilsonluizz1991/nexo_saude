<?php

use App\Http\Middleware\VerificarAdministradorSistema;
use App\Http\Middleware\VerificarAssinaturaAtiva;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->validateCsrfTokens(except: [
            'webhooks/asaas',
        ]);

        $middleware->alias([
            'assinatura.ativa' => VerificarAssinaturaAtiva::class,
            'admin.sistema' => VerificarAdministradorSistema::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();