<?php

use App\Models\User;
use App\Services\ServicoLembrete;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('nexo:sincronizar-lembretes', function () {
    $servico = app(ServicoLembrete::class);
    $total = 0;

    User::query()->chunkById(100, function ($usuarios) use ($servico, &$total): void {
        foreach ($usuarios as $usuario) {
            $servico->sincronizarAlertas($usuario);
            $total++;
        }
    });

    $this->info("Lembretes sincronizados para {$total} corretor(es).");
})->purpose('Gerar alertas de lembretes um dia antes e no dia programado.');

Schedule::command('nexo:sincronizar-lembretes')->hourly();
