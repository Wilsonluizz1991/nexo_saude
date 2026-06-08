<?php

namespace App\Providers;

use App\Services\CabecalhoService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        app()->setLocale('pt_BR');

        View::composer(['components.app-header', 'components.layout.sidebar'], function ($view): void {
            $user = auth()->user();

            $view->with('cabecalho', $user
                ? app(CabecalhoService::class)->dadosPara($user)
                : [
                    'compromissosHoje' => collect(),
                    'quantidadeCompromissosHoje' => 0,
                    'tarefasPendentes' => collect(),
                    'quantidadeTarefasPendentes' => 0,
                    'alertasNaoLidos' => collect(),
                    'quantidadeAlertasNaoLidos' => 0,
                    'quantidadePreCadastrosPendentes' => 0,
                ]);
        });
    }
}
