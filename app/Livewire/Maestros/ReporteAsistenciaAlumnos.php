<?php

namespace App\Livewire\Maestros;

use App\Exports\AsistenciasClaseExport;
use App\Models\HorarioMateriaPeriodo;
use App\Models\Maestro;
use App\Models\ReporteAsistenciaClase;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    public function exportarTodosLosReportes(): BinaryFileResponse
    {
        $this->autorizarExportacion();

        return Excel::download(
            new AsistenciasClaseExport($this->horarioAsignado),
            'asistencias-clase-'.$this->horarioAsignado->id.'.xlsx'
        );
    }

    public function exportarReporte(int $reporteId): BinaryFileResponse
    {
        $this->autorizarExportacion();
        $reporte = $this->horarioAsignado->reportesAsistencia()->findOrFail($reporteId);

        return Excel::download(
            new AsistenciasClaseExport($this->horarioAsignado, $reporte),
            'asistencias-reporte-'.$reporte->id.'.xlsx'
        );
    }

    private function autorizarExportacion(): void
    {
        abort_unless(Auth::check(), 403);
        abort_unless($this->horarioAsignado->maestros()->whereKey($this->maestro->id)->exists(), 403);
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
     */
    public function verificarSiSePuedeEditarReporte(ReporteAsistenciaClase $reporte): bool
    {
        return $reporte->permiteEdicionPorFecha(Auth::user());
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
