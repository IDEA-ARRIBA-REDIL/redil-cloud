<?php

namespace App\Exports;

use App\Models\Grupo;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithTitle;

class InformeDeGruposNoReportadosExport implements FromCollection, ShouldAutoSize, WithStyles, WithTitle
{
    protected $grupoId;
    protected $semanaString;
    protected $filtroTipoGrupos;

    // Propiedad para saber en qué fila empieza la segunda tabla
    private $filaInicioDetalle;

    public function __construct(int $grupoId, string $semanaString, array $filtroTipoGrupos)
    {
        $this->grupoId = $grupoId;
        $this->semanaString = $semanaString;
        $this->filtroTipoGrupos = $filtroTipoGrupos;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection(): Collection
    {
        // === 1. OBTENER DATOS DEL RESUMEN ===
        $datosResumen = $this->obtenerDatosResumen();

        // === 2. OBTENER DATOS DEL DETALLE ===
        $datosDetalle = $this->obtenerDatosDetalle();

        // === 3. CONSTRUIR LA COLECCIÓN FINAL ===
        $informe = collect([]);

        // Tabla de resumen
        $informe->push(['TABLA DE RESUMEN']); // Título
        $informe->push(['GRUPO SELECCIONADO', 'NO REPORTADOS', 'NO REALIZADOS']); // Encabezados
        $informe->push($datosResumen); // Datos

        // Fila vacía como separador
        $informe->push(['']);

        // Calculamos dónde empezará la siguiente tabla para el estilo
        $this->filaInicioDetalle = $informe->count() + 1;

        // Tabla de detalle
        $informe->push(['TABLA DE DETALLE']); // Título
        $informe->push(['GRUPO', 'ENCARGADOS', 'TIPO DE GRUPO', 'ESTADO DEL REPORTE', 'MOTIVO', 'DESCRIPCIÓN']); // Encabezados

        foreach ($datosDetalle as $detalle) {
            $informe->push([
                $detalle->nombre,
                $detalle->encargados_nombres,
                $detalle->nombreTipo,
                $detalle->estado_reporte,
                $detalle->nombre_motivo,
                $detalle->descripcion_adicional_motivo
            ]);
        }

        return $informe;
    }

    /**
     * Aplica estilos a la hoja, como poner los encabezados en negrita.
     */
    public function styles(Worksheet $sheet)
    {
        // Fila 1: Título de la primera tabla
        $sheet->mergeCells('A1:C1');
        $sheet->getStyle(1)->getFont()->setBold(true)->setSize(16);

        // Fila 2: Encabezados de la primera tabla
        $sheet->getStyle(2)->getFont()->setBold(true);

        // Fila X: Título de la segunda tabla
        $sheet->mergeCells("A{$this->filaInicioDetalle}:C{$this->filaInicioDetalle}");
        $sheet->getStyle($this->filaInicioDetalle)->getFont()->setBold(true)->setSize(16);

        // Fila X+1: Encabezados de la segunda tabla
        $sheet->getStyle($this->filaInicioDetalle + 1)->getFont()->setBold(true);
    }

    public function title(): string // <-- 3. AÑADE ESTE MÉTODO
    {
        return 'No realizados';
    }

    // --- MÉTODOS PRIVADOS PARA OBTENER DATOS ---

    private function obtenerDatosResumen(): array
    {
        // Lógica para obtener el resumen (la misma que ya tenías)
        [$inicioDeSemana, $finDeSemana] = $this->getFechasSemana();
        $grupoSeleccionado = Grupo::find($this->grupoId);

        $subconsulta = $this->crearSubconsulta($grupoSeleccionado, $inicioDeSemana, $finDeSemana);

        $resumenReporte = DB::query()
            ->fromSub($subconsulta, 'resumen_sub')
            ->selectRaw("estado_reporte, COUNT(*) as cantidad")
            ->groupBy('estado_reporte')
            ->pluck('cantidad', 'estado_reporte')
            ->toArray();

        return [
            $grupoSeleccionado->nombre,
            $resumenReporte['No reportado'] ?? '0',
            $resumenReporte['No realizado'] ?? '0',
        ];
    }

    private function obtenerDatosDetalle(): Collection
    {
        // Lógica para obtener el detalle (la misma que ya tenías)
        [$inicioDeSemana, $finDeSemana] = $this->getFechasSemana();
        $grupoSeleccionado = Grupo::find($this->grupoId);

        $subconsulta = $this->crearSubconsulta($grupoSeleccionado, $inicioDeSemana, $finDeSemana);

        $resultados = DB::query()
            ->fromSub($subconsulta, 'sub')
            ->where('estado_reporte', '!=', 'Correcto')
            ->join('tipo_grupos', 'sub.tipo_grupo_id', '=', 'tipo_grupos.id')
            ->select('sub.id', 'sub.nombre', 'tipo_grupos.nombre as nombreTipo', 'sub.estado_reporte', 'sub.nombre_motivo', 'sub.descripcion_adicional_motivo')
            ->orderBy('sub.nombre', 'asc')
            ->get();

        // Obtener encargados
        $grupoIds = $resultados->pluck('id');
        $encargados = DB::table('encargados_grupo')
          ->join('users', 'encargados_grupo.user_id', '=', 'users.id')
          ->whereIn('encargados_grupo.grupo_id', $grupoIds)
          ->select('encargados_grupo.grupo_id', DB::raw("CONCAT(users.primer_nombre, ' ', users.primer_apellido) as nombre_completo"))
          ->get()
          ->groupBy('grupo_id');

        // Mapear encargados a resultados
        $resultados->transform(function($grupo) use ($encargados) {
            $grupo->encargados_nombres = isset($encargados[$grupo->id])
                ? $encargados[$grupo->id]->pluck('nombre_completo')->implode(', ')
                : '';
            return $grupo;
        });

        return $resultados;
    }

    // --- MÉTODOS DE AYUDA PARA NO REPETIR CÓDIGO ---

    private function getFechasSemana(): array
    {
        sscanf($this->semanaString, "%d-W%d", $year, $week);
        $fecha = Carbon::now()->setISODate($year, $week);
        return [
            $fecha->copy()->startOfWeek(Carbon::MONDAY)->startOfDay(),
            $fecha->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay()
        ];
    }

    private function crearSubconsulta(Grupo $grupo, Carbon $inicio, Carbon $fin)
    {
        return $grupo->gruposMinisterio()
          ->whereIn('tipo_grupo_id', $this->filtroTipoGrupos)
          ->selectRaw("
              grupos.*,
              CASE
                  WHEN reportes.id IS NULL THEN 'No reportado'
                  WHEN reportes.no_reporte = TRUE THEN 'No realizado'
                  WHEN reportes.finalizado = FALSE THEN 'No reportado'
                  ELSE 'Correcto'
              END as estado_reporte,
              motivos.nombre as nombre_motivo,
              reportes.descripcion_adicional_motivo
          ")
          ->leftJoin('reporte_grupos as reportes', function ($join) use ($inicio, $fin) {
              $join->on('grupos.id', '=', 'reportes.grupo_id')
                  ->whereBetween('reportes.fecha', [$inicio, $fin]);
          })
          ->leftJoin('motivos_no_reporte_grupo as motivos', 'reportes.motivo_no_reporte_grupo_id', '=', 'motivos.id');
    }


}
