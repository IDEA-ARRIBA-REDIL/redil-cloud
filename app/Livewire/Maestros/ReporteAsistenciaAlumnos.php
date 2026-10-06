<?php

namespace App\Livewire\Maestros;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ReporteAsistenciaClase;
use App\Models\HorarioMateriaPeriodo;
use App\Models\Maestro;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReporteAsistenciaAlumnos extends Component
{
    use WithPagination;

    // PROPIEDADES QUE SE MANTIENEN
    // Solo necesitamos las que identifican el contexto del listado.
    public HorarioMateriaPeriodo $horarioAsignado;
    public Maestro $maestro;

    // Listener para refrescar la lista si un reporte se crea desde otro lado.
    protected $listeners = ['reporteCreadoExternamente' => '$refresh'];

    public function mount(HorarioMateriaPeriodo $horarioAsignado, Maestro $maestro)
    {
        $this->horarioAsignado = $horarioAsignado;
        $this->maestro = $maestro;
    }

    // -------------------------------------------------------------------------
    // SE HAN ELIMINADO LAS SIGUIENTES PROPIEDADES Y MÉTODOS:
    // - $reporteClaseSeleccionado, $alumnosDelHorario, $asistencias, $motivosInasistencia, $mostrarModalDetalles
    // - rules(), messages(), cargarMotivosInasistencia()
    // - abrirModalParaEditarReporte(), updatedAsistencias(), guardarDetallesAsistencia(), resetearEstadoModal()
    // Toda esa lógica ahora vive en el nuevo componente 'EditarReporteAsistencia'.
    // -------------------------------------------------------------------------


    /**
     * MÉTODO QUE SE MANTIENE
     * Determina si el botón para editar un reporte debe estar habilitado.
     * Esta función es llamada desde la vista del componente (el bucle @foreach).
     *
     * @param ReporteAsistenciaClase $reporte
     * @return bool
     */
    public function verificarSiSePuedeEditarReporte(ReporteAsistenciaClase $reporte): bool
    {
        $usuarioActivo = Auth::user();

        // Regla 1: Si tiene permiso, siempre puede editar.
        if ($usuarioActivo && $usuarioActivo->hasPermissionTo('escuelas.reportar_asistencia_cualquier_dia')) {
            return true;
        }

        // --- Lógica para usuarios SIN permiso ---
        $reporte->loadMissing('horarioMateriaPeriodo.materiaPeriodo.materia');
        $datosMateria = $reporte->horarioMateriaPeriodo?->materiaPeriodo?->materia;
        if (!$datosMateria) {
            return false;
        }

        $fechaActualSoloFecha = Carbon::now()->startOfDay();
        $fechaClaseReportadaCarbon = Carbon::parse($reporte->fecha_clase_reportada)->startOfDay();

        if ($datosMateria->tiene_dia_limite && isset($datosMateria->dia_limite_reporte)) {
            // Escenario A: Con día límite semanal
            $diaClaseReportadaNum = $fechaClaseReportadaCarbon->dayOfWeek;
            $diaLimiteConfiguradoMateria = (int) $datosMateria->dia_limite_reporte;
            $diasParaAlcanzarDiaLimite = ($diaLimiteConfiguradoMateria - $diaClaseReportadaNum + 7) % 7;
            $fechaTopeParaEdicion = $fechaClaseReportadaCarbon->copy()->addDays($diasParaAlcanzarDiaLimite);
            return $fechaActualSoloFecha->lte($fechaTopeParaEdicion); // Puede editar si hoy <= fecha tope
        } else if (!$datosMateria->tiene_dia_limite && isset($datosMateria->dias_plazo_reporte)) {
            // Escenario B: Con días de plazo
            $diasPlazo = (int) $datosMateria->dias_plazo_reporte;
            $fechaTopeParaEdicion = $fechaClaseReportadaCarbon->copy()->addDays($diasPlazo);
            return $fechaActualSoloFecha->lte($fechaTopeParaEdicion); // Puede editar si hoy <= fecha tope
        }
        return false; // Por defecto, no se puede editar.
    }

    /**
     * Renderiza la vista del listado de reportes.
     */
    public function render()
    {
        // La consulta para obtener los reportes se mantiene igual.
        $reportesPaginados = ReporteAsistenciaClase::where('horario_materia_periodo_id', $this->horarioAsignado->id)
            ->orderBy('fecha_clase_reportada', 'desc')
            ->withCount(['detallesAsistencia', 'detallesAsistencia as presentes_count' => function ($query) {
                $query->where('asistio', true);
            }])
            ->paginate(10);

        return view('livewire.maestros.reporte-asistencia-alumnos', [
            'reportesPaginados' => $reportesPaginados,
        ]);
    }
}