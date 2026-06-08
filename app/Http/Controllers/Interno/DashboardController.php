<?php

namespace App\Http\Controllers\Interno;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardMetricsService;
use App\Services\Dashboard\NexoAiInsightService;
use App\Services\ServicoAlerta;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function __invoke(
        Request $request,
        ServicoAlerta $alertasAutomaticos,
        DashboardMetricsService $metrics,
        NexoAiInsightService $insights
    ) {
        $usuario = auth()->user();

        $alertasAutomaticos->gerarAutomaticos($usuario);

        $limites = $metrics->periodoDisponivel($usuario->id);
        $inicioPadrao = now()->startOfMonth();
        $fimPadrao = now()->startOfDay();

        $inicio = $this->dataDoFiltro($request->query('inicio'), $inicioPadrao);
        $fim = $this->dataDoFiltro($request->query('fim'), $fimPadrao);

        if ($inicio->lt($limites['min'])) {
            $inicio = $limites['min']->copy();
        }

        if ($fim->gt($limites['max'])) {
            $fim = $limites['max']->copy();
        }

        if ($inicio->gt($fim)) {
            $inicio = $fim->copy()->startOfDay();
        }

        $filtroNormalizado = [
            'inicio' => $inicio->toDateString(),
            'fim' => $fim->toDateString(),
        ];

        if ($request->query('inicio') !== null || $request->query('fim') !== null) {
            if ($request->query('inicio') !== $filtroNormalizado['inicio'] || $request->query('fim') !== $filtroNormalizado['fim']) {
                return redirect()->route('dashboard', $filtroNormalizado);
            }
        }

        $dashboard = $metrics->forUser($usuario->id, $inicio, $fim);
        $dashboard['usuario'] = $usuario;
        $dashboard['resumoIa'] = $insights->insightForUser($usuario->id, $dashboard);

        return view('interno.dashboard', $dashboard);
    }

    private function dataDoFiltro(mixed $valor, CarbonInterface $padrao): CarbonInterface
    {
        if (! is_string($valor) || trim($valor) === '') {
            return $padrao->copy();
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $valor)->startOfDay();
        } catch (\Throwable) {
            return $padrao->copy();
        }
    }
}
