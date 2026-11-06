<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Periodo;
use App\Services\ResumenHorariosPeriodoService;
use Illuminate\View\View;

class DashboardHorariosPeriodoController extends Controller
{
    /**
     * Muestra el dashboard consolidado de horarios de un periodo.
     */
    public function __invoke(Periodo $periodo, ResumenHorariosPeriodoService $resumenService): View
    {
        return view('contenido.paginas.escuelas.periodos.dashboard-horarios', [
            'periodo' => $periodo,
            'dashboard' => $resumenService->obtener($periodo),
            'configuracion' => Configuracion::query()->find(1),
        ]);
    }
}
