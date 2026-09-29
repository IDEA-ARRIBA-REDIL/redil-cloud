<?php

namespace App\Http\Controllers;

use App\Exports\InformeMateriaPorSedeExport;
use App\Http\Requests\InformeMateriaPorSedeRequest;
use App\Models\Configuracion;
use App\Models\MateriaPeriodo;
use App\Models\Matricula;
use App\Models\Periodo;
use App\Services\GestionCierreMateriaService;
use App\Services\ResumenPeriodoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DashboardPeriodoController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Periodo $periodo, ResumenPeriodoService $resumen): View
    {
        return view('contenido.paginas.escuelas.periodos.dashboard-periodo', [
            'periodo' => $periodo,
            'dashboard' => $resumen->obtener($periodo),
            'configuracion' => Configuracion::query()->find(1),
        ]);
    }

    public function cerrar(Request $solicitud, Periodo $periodo, MateriaPeriodo $materiaPeriodo, GestionCierreMateriaService $gestion): RedirectResponse
    {
        abort_unless((int) $materiaPeriodo->periodo_id === (int) $periodo->id, 404);
        $gestion->solicitar($materiaPeriodo, $solicitud->user());

        return redirect()->route('periodo.dashboard', $periodo)->with('success', 'Cierre solicitado. Se calcularán las aprobaciones en segundo plano; actualiza el dashboard para consultar el resultado.');
    }

    public function reabrir(Periodo $periodo, MateriaPeriodo $materiaPeriodo, GestionCierreMateriaService $gestion): RedirectResponse
    {
        abort_unless((int) $materiaPeriodo->periodo_id === (int) $periodo->id, 404);
        $gestion->reabrir($materiaPeriodo);

        return redirect()->route('periodo.dashboard', $periodo)->with('success', 'Materia reabierta. Se conservaron las notas y asistencias originales; los resultados finales se calcularán al cerrarla nuevamente.');
    }

    public function informe(InformeMateriaPorSedeRequest $solicitud, Periodo $periodo, MateriaPeriodo $materiaPeriodo, GestionCierreMateriaService $gestion): BinaryFileResponse
    {
        abort_unless((int) $materiaPeriodo->periodo_id === (int) $periodo->id, 404);
        abort_if($periodo->estado || ! $materiaPeriodo->finalizado || $gestion->estado($materiaPeriodo->id) === 'procesando', 409, 'El periodo y la materia deben estar cerrados para descargar el informe.');
        $horarios = $materiaPeriodo->horariosMateriaPeriodo()
            ->whereHas('horarioBase', fn ($consulta) => $consulta->withTrashed()->whereHas('aula', fn ($aulas) => $aulas->withTrashed()->where('sede_id', $solicitud->integer('sede_id'))))
            ->with(['horarioBase' => fn ($consulta) => $consulta->withTrashed()->with(['aula' => fn ($aulas) => $aulas->withTrashed()->with('sede')])])
            ->orderBy('id')->get();
        abort_if($horarios->isEmpty(), 422, 'Esta materia no tiene horarios en la sede seleccionada.');
        $variosHorarios = Matricula::query()->where('periodo_id', $periodo->id)
            ->whereHas('horarioMateriaPeriodo', fn ($consulta) => $consulta->where('materia_periodo_id', $materiaPeriodo->id))
            ->whereNotIn('estado_pago_matricula', ['anulada', 'rechazada'])
            ->select('user_id')->groupBy('user_id')->havingRaw('COUNT(DISTINCT horario_materia_periodo_id) > 1')->exists();
        abort_if($variosHorarios, 409, 'Hay alumnos en varios horarios de esta materia. Revisa sus matrículas antes de exportar resultados finales por horario.');
        $materiaPeriodo->loadMissing(['materia', 'periodo']);
        $nombre = 'Informe-'.Str::slug($materiaPeriodo->materia?->nombre ?? 'materia').'-sede-'.$solicitud->integer('sede_id').'.xlsx';

        return Excel::download(new InformeMateriaPorSedeExport($materiaPeriodo, $horarios), $nombre);
    }
}
