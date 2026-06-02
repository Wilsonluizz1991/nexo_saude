<?php

use App\Http\Middleware\VerificarAdministradorSistema;
use App\Http\Middleware\VerificarAssinaturaAtiva;
use App\Http\Middleware\VerificarEmailConfirmado;
use App\Http\Middleware\VerificarUsuarioAtivo;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $trustedProxies = array_filter(array_map(
            'trim',
            explode(',', (string) env('TRUSTED_PROXIES', ''))
        ));

        if ($trustedProxies !== []) {
            $middleware->trustProxies(
                at: $trustedProxies,
                headers: Request::HEADER_X_FORWARDED_FOR
                    | Request::HEADER_X_FORWARDED_HOST
                    | Request::HEADER_X_FORWARDED_PORT
                    | Request::HEADER_X_FORWARDED_PROTO
                    | Request::HEADER_X_FORWARDED_PREFIX
                    | Request::HEADER_X_FORWARDED_AWS_ELB
            );
        }

        $middleware->validateCsrfTokens(except: [
            'webhooks/asaas',
        ]);

        $middleware->alias([
            'assinatura.ativa' => VerificarAssinaturaAtiva::class,
            'admin.sistema' => VerificarAdministradorSistema::class,
            'usuario.ativo' => VerificarUsuarioAtivo::class,
            'email.confirmado' => VerificarEmailConfirmado::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
