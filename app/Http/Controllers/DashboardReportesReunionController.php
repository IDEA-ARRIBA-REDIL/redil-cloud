<?php

namespace App\Http\Controllers;

use App\Exports\DashboardReporteReunionExport;
use App\Models\Reunion;
use App\Models\Sede;
use App\Models\TipoServicioReporteReunion;
use App\Services\ReunionStatsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DashboardReportesReunionController extends Controller
{
    /**
     * Muestra la vista principal del dashboard analítico de reportes de reunión.
     */
    public function index(Request $request, ReunionStatsService $statsService)
    {
        $user = auth()->user();
        $rolActivo = $user->roles()->wherePivot('activo', true)->first();
        if (! $rolActivo) {
            return redirect()->route('pagina-no-encontrada');
        }

        $rolActivo->verificacionDelPermiso('reporte_reuniones.ver_dashboard_estadistico');

        // Sedes autorizadas para poblar los filtros
        $sedesAutorizadasIds = $statsService->obtenerSedesAutorizadasIds($user, $rolActivo);
        $sedesDisponibles = Sede::whereIn('id', $sedesAutorizadasIds)->orderBy('nombre')->get();

        // Tipos de servicio y reuniones disponibles
        $tiposDisponibles = TipoServicioReporteReunion::orderBy('nombre')->get();
        $reunionesDisponibles = Reunion::whereIn('sede_id', $sedesAutorizadasIds)->orderBy('nombre')->get();

        // Capturar filtros
        $filtros = [
            'fecha_inicio' => $request->input('fecha_inicio', Carbon::now()->subDays(30)->format('Y-m-d')),
            'fecha_fin' => $request->input('fecha_fin', Carbon::now()->format('Y-m-d')),
            'sedes_id' => $request->input('sedes_id', []),
            'tipos_reunion_id' => $request->input('tipos_reunion_id', []),
            'reuniones_id' => $request->input('reuniones_id', []),
            'estado' => $request->input('estado', 'finalizado'),
        ];

        // Generar estadísticas
        $datos = $statsService->generarDashboard($filtros, $user);

        return view('contenido.paginas.reporte-reuniones.dashboard', [
            'datos' => $datos,
            'filtros' => $filtros,
            'sedesDisponibles' => $sedesDisponibles,
            'tiposDisponibles' => $tiposDisponibles,
            'reunionesDisponibles' => $reunionesDisponibles,
            'rolActivo' => $rolActivo,
        ]);
    }

    /**
     * Descarga el informe en Excel del dashboard con los filtros activos.
     */
    public function exportarExcel(Request $request, ReunionStatsService $statsService)
    {
        $user = auth()->user();
        $rolActivo = $user->roles()->wherePivot('activo', true)->first();
        if (! $rolActivo) {
            return redirect()->route('pagina-no-encontrada');
        }

        $rolActivo->verificacionDelPermiso('reporte_reuniones.exportar_dashboard_estadistico');

        $filtros = [
            'fecha_inicio' => $request->input('fecha_inicio', Carbon::now()->subDays(30)->format('Y-m-d')),
            'fecha_fin' => $request->input('fecha_fin', Carbon::now()->format('Y-m-d')),
            'sedes_id' => $request->input('sedes_id', []),
            'tipos_reunion_id' => $request->input('tipos_reunion_id', []),
            'reuniones_id' => $request->input('reuniones_id', []),
            'estado' => $request->input('estado', 'finalizado'),
        ];

        $datos = $statsService->generarDashboard($filtros, $user);

        $nombreArchivo = 'dashboard_reuniones_'.Carbon::now()->format('Ymd_His').'.xlsx';

        return Excel::download(new DashboardReporteReunionExport($datos), $nombreArchivo);
    }
}
