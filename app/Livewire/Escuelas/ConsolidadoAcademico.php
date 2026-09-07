<?php

namespace App\Livewire\Escuelas;

use App\Exports\ConsolidadoAcademicoExport;
use App\Models\Escuela;
use App\Models\Materia;
use App\Models\MateriaAprobadaUsuario;
use App\Models\NivelAprobadoUsuario;
use App\Models\NivelEscuela;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class ConsolidadoAcademico extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // --- FILTROS DE SELECCIÓN ---
    public $escuelaSeleccionadaId = '';

    public $escuelaId = '';

    public bool $consultado = false;

    public $modo = 'materias'; // 'materias' o 'niveles'

    public $filtroTipoMaterias = 'obligatorias'; // 'obligatorias', 'todas', 'opcionales'

    public $filtroEstadoUsuario = 'activos'; // 'activos', 'todos', 'dados_de_baja'

    public $buscar = '';

    public $perPage = 25;

    // --- LISTAS AUXILIARES ---
    public $escuelas = [];

    public function mount()
    {
        $this->escuelas = Escuela::orderBy('nombre')->get();
        $this->escuelaSeleccionadaId = '';
        $this->escuelaId = '';
        $this->consultado = false;
    }

    /**
     * Ejecuta la consulta al presionar el botón de confirmación de filtros.
     */
    public function consultar()
    {
        if (empty($this->escuelaSeleccionadaId)) {
            $this->dispatch('notificacion', [
                'tipo' => 'warning',
                'mensaje' => 'Por favor selecciona una escuela para consultar.',
            ]);

            return;
        }

        $this->escuelaId = $this->escuelaSeleccionadaId;
        $this->consultado = true;
        $this->actualizarModoEscuela();
        $this->resetPage();
    }

    public function updatedBuscar()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    /**
     * Determina si la escuela activa trabaja por materias o por niveles.
     */
    protected function actualizarModoEscuela()
    {
        if (empty($this->escuelaId)) {
            $this->modo = 'materias';

            return;
        }

        $escuela = Escuela::find($this->escuelaId);
        if ($escuela) {
            $this->modo = $escuela->esPorNiveles() ? 'niveles' : 'materias';
        }
    }

    /**
     * Obtiene las columnas de materias o niveles que forman el pensum a evaluar.
     */
    protected function obtenerItemsEvaluados()
    {
        if (empty($this->escuelaId)) {
            return collect();
        }

        if ($this->modo === 'materias') {
            $query = Materia::where('escuela_id', $this->escuelaId)
                ->select('id', 'nombre', 'caracter_obligatorio', 'creditos', 'orden');

            if ($this->filtroTipoMaterias === 'obligatorias') {
                $query->where('caracter_obligatorio', true);
            } elseif ($this->filtroTipoMaterias === 'opcionales') {
                $query->where('caracter_obligatorio', false);
            }

            return $query->orderBy('orden')->orderBy('nombre')->get();
        } else {
            $query = NivelEscuela::where('escuela_id', $this->escuelaId)
                ->select('id', 'nombre', 'caracter_obligatorio', 'orden');

            if ($this->filtroTipoMaterias === 'obligatorias') {
                $query->where('caracter_obligatorio', true);
            } elseif ($this->filtroTipoMaterias === 'opcionales') {
                $query->where('caracter_obligatorio', false);
            }

            return $query->orderBy('orden')->orderBy('nombre')->get();
        }
    }

    /**
     * Construye y procesa la matriz de estudiantes con sus notas y avances de forma ultra-optimizada.
     */
    protected function procesarMatrizCompleta()
    {
        if (! $this->consultado || empty($this->escuelaId)) {
            return [
                'estudiantes' => collect(),
                'metricas' => [
                    'total' => 0,
                    'completados' => 0,
                    'en_curso' => 0,
                    'sin_avance' => 0,
                    'promedio_general' => 0,
                    'total_creditos_pensum' => 0,
                ],
            ];
        }

        $itemsEvaluados = $this->obtenerItemsEvaluados();
        $totalItems = $itemsEvaluados->count();

        // 1. Obtener IDs del pensum de la escuela
        if ($this->modo === 'materias') {
            $todosIdsEscuela = Materia::where('escuela_id', $this->escuelaId)->pluck('id');
        } else {
            $todosIdsEscuela = NivelEscuela::where('escuela_id', $this->escuelaId)->pluck('id');
        }

        if ($todosIdsEscuela->isEmpty()) {
            return [
                'estudiantes' => collect(),
                'metricas' => [
                    'total' => 0,
                    'completados' => 0,
                    'en_curso' => 0,
                    'sin_avance' => 0,
                    'promedio_general' => 0,
                    'total_creditos_pensum' => 0,
                ],
            ];
        }

        // 2. Cargar aprobaciones con select específico y mapeo nativo en memoria O(1)
        $aprobacionesPorUsuario = [];
        $userIdsMap = [];

        if ($this->modo === 'materias') {
            $registros = MateriaAprobadaUsuario::whereIn('materia_id', $todosIdsEscuela)
                ->select('user_id', 'materia_id', 'aprobado', 'nota_final', 'creditos_aprobados', 'es_homologacion')
                ->get();

            foreach ($registros as $reg) {
                $uId = (int) $reg->user_id;
                $userIdsMap[$uId] = true;
                $aprobacionesPorUsuario[$uId][$reg->materia_id] = [
                    'aprobado' => (int) $reg->aprobado,
                    'nota_final' => $reg->nota_final,
                    'creditos_aprobados' => $reg->creditos_aprobados,
                    'es_homologacion' => (bool) $reg->es_homologacion,
                ];
            }
        } else {
            $registros = NivelAprobadoUsuario::whereIn('nivel_id', $todosIdsEscuela)
                ->select('user_id', 'nivel_id', 'aprobado', 'nota_final', 'es_homologacion')
                ->get();

            foreach ($registros as $reg) {
                $uId = (int) $reg->user_id;
                $userIdsMap[$uId] = true;
                $aprobacionesPorUsuario[$uId][$reg->nivel_id] = [
                    'aprobado' => (int) $reg->aprobado,
                    'nota_final' => $reg->nota_final,
                    'creditos_aprobados' => 0,
                    'es_homologacion' => (bool) $reg->es_homologacion,
                ];
            }
        }

        $userIds = array_keys($userIdsMap);

        if (empty($userIds)) {
            return [
                'estudiantes' => collect(),
                'metricas' => [
                    'total' => 0,
                    'completados' => 0,
                    'en_curso' => 0,
                    'sin_avance' => 0,
                    'promedio_general' => 0,
                    'total_creditos_pensum' => 0,
                ],
            ];
        }

        // 3. Consultar usuarios asociados trayendo solo columnas estrictamente necesarias
        $queryUsuarios = User::query()
            ->whereIn('id', $userIds)
            ->select('id', 'identificacion', 'primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido', 'deleted_at');

        // Filtro de Estado del Usuario (Activos vs Dados de baja)
        if ($this->filtroEstadoUsuario === 'todos') {
            $queryUsuarios->withTrashed();
        } elseif ($this->filtroEstadoUsuario === 'dados_de_baja') {
            $queryUsuarios->onlyTrashed();
        }

        // Filtro de Búsqueda Inteligente (Nombres + Apellidos, Cédula e indiferente a acentos/tildes)
        if (! empty(trim($this->buscar))) {
            $buscarSaneado = trim($this->buscar);
            $buscarSaneado = str_replace(
                ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ä', 'ë', 'ï', 'ö', 'ü', 'Ä', 'Ë', 'Ï', 'Ö', 'Ü'],
                ['a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u'],
                $buscarSaneado
            );
            $termino = '%'.$buscarSaneado.'%';

            $queryUsuarios->where(function ($q) use ($termino) {
                $q->where('identificacion', 'like', $termino)
                    ->orWhereRaw("LOWER(translate(CONCAT_WS(' ', primer_nombre, segundo_nombre, primer_apellido, segundo_apellido), 'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ', 'aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", [$termino])
                    ->orWhereRaw("LOWER(translate(CONCAT_WS(' ', primer_apellido, segundo_apellido, primer_nombre, segundo_nombre), 'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ', 'aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", [$termino])
                    ->orWhereRaw("LOWER(translate(CONCAT_WS(' ', primer_nombre, primer_apellido), 'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ', 'aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", [$termino])
                    ->orWhereRaw("LOWER(translate(CONCAT_WS(' ', primer_apellido, primer_nombre), 'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ', 'aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", [$termino]);
            });
        }

        $usuarios = $queryUsuarios->orderBy('primer_apellido')->orderBy('primer_nombre')->get();

        $filasProcesadas = collect();
        $contadorCompletados = 0;
        $contadorEnCurso = 0;
        $contadorSinAvance = 0;
        $sumaPromedios = 0;
        $totalConPromedio = 0;

        foreach ($usuarios as $usuario) {
            $historialUsuario = $aprobacionesPorUsuario[$usuario->id] ?? [];

            $detalleItems = [];
            $aprobadas = 0;
            $creditosAprobados = 0;
            $sumaNotasAlumno = 0;
            $cantidadNotasAlumno = 0;

            foreach ($itemsEvaluados as $item) {
                $registro = $historialUsuario[$item->id] ?? null;

                if ($registro) {
                    $estadoNum = $registro['aprobado']; // 1: Aprobado, 2: En proceso, 0: Reprobado
                    $notaFinal = $registro['nota_final'];
                    $esHomologacion = $registro['es_homologacion'];

                    if ($estadoNum === 1) {
                        $aprobadas++;
                        if ($notaFinal !== null && is_numeric($notaFinal)) {
                            $sumaNotasAlumno += (float) $notaFinal;
                            $cantidadNotasAlumno++;
                        }
                        if ($this->modo === 'materias') {
                            $creditosItem = $registro['creditos_aprobados'] ?? $item->creditos ?? 0;
                            $creditosAprobados += (int) $creditosItem;
                        }
                    }

                    $detalleItems[$item->id] = [
                        'estado' => $estadoNum,
                        'nota' => $notaFinal !== null ? number_format((float) $notaFinal, 1) : null,
                        'es_homologacion' => $esHomologacion,
                    ];
                } else {
                    $detalleItems[$item->id] = [
                        'estado' => null, // Pendiente / No cursada
                        'nota' => null,
                        'es_homologacion' => false,
                    ];
                }
            }

            $faltantes = max(0, $totalItems - $aprobadas);
            $promedioAlumno = $totalItems > 0 ? round($sumaNotasAlumno / $totalItems, 1) : 0;
            $porcentajeAvance = $totalItems > 0 ? round(($aprobadas / $totalItems) * 100, 1) : 0;

            // Acumular métricas KPI
            if ($porcentajeAvance >= 100) {
                $contadorCompletados++;
            } elseif ($porcentajeAvance > 0) {
                $contadorEnCurso++;
            } else {
                $contadorSinAvance++;
            }

            $sumaPromedios += $promedioAlumno;
            $totalConPromedio++;

            $filasProcesadas->push([
                'usuario_id' => $usuario->id,
                'identificacion' => $usuario->identificacion,
                'nombre_completo' => trim("{$usuario->primer_apellido} {$usuario->segundo_apellido} {$usuario->primer_nombre} {$usuario->segundo_nombre}"),
                'apellidos' => trim("{$usuario->primer_apellido} {$usuario->segundo_apellido}"),
                'nombres' => trim("{$usuario->primer_nombre} {$usuario->segundo_nombre}"),
                'dado_de_baja' => (bool) $usuario->deleted_at,
                'detalle_items' => $detalleItems,
                'aprobadas' => $aprobadas,
                'total_evaluados' => $totalItems,
                'creditos_aprobados' => $creditosAprobados,
                'faltantes' => $faltantes,
                'promedio' => $promedioAlumno,
                'porcentaje_avance' => $porcentajeAvance,
            ]);
        }

        $promedioGeneralEscuela = $totalConPromedio > 0 ? round($sumaPromedios / $totalConPromedio, 1) : 0;
        $totalCreditosPensum = $this->modo === 'materias' ? $itemsEvaluados->sum('creditos') : 0;

        return [
            'estudiantes' => $filasProcesadas,
            'metricas' => [
                'total' => $filasProcesadas->count(),
                'completados' => $contadorCompletados,
                'en_curso' => $contadorEnCurso,
                'sin_avance' => $contadorSinAvance,
                'promedio_general' => $promedioGeneralEscuela,
                'total_creditos_pensum' => $totalCreditosPensum,
            ],
        ];
    }

    /**
     * Dispara la exportación del consolidado en formato Excel (.xlsx) con memoria y timeout ampliados.
     */
    public function exportarExcel()
    {
        if (empty($this->escuelaId)) {
            $this->dispatch('notificacion', ['tipo' => 'warning', 'mensaje' => 'Selecciona una escuela antes de exportar.']);

            return;
        }

        // Ampliación segura de recursos para exportaciones masivas
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');

        $escuela = Escuela::find($this->escuelaId);
        $nombreEscuela = $escuela ? $escuela->nombre : 'Consolidado';
        $nombreArchivo = 'consolidado_academico_'.\Illuminate\Support\Str::slug($nombreEscuela).'_'.now()->format('Ymd_His').'.xlsx';

        $itemsEvaluados = $this->obtenerItemsEvaluados();
        $resultado = $this->procesarMatrizCompleta();

        return Excel::download(new ConsolidadoAcademicoExport(
            escuelaNombre: $nombreEscuela,
            modo: $this->modo,
            filtroTipoMaterias: $this->filtroTipoMaterias,
            filtroEstadoUsuario: $this->filtroEstadoUsuario,
            items: $itemsEvaluados,
            estudiantes: $resultado['estudiantes'],
            metricas: $resultado['metricas']
        ), $nombreArchivo);
    }

    public function render()
    {
        $escuelaActual = ! empty($this->escuelaId) ? Escuela::find($this->escuelaId) : null;
        $itemsEvaluados = $this->obtenerItemsEvaluados();
        $matriz = $this->procesarMatrizCompleta();

        // Paginación manual de la colección procesada
        $paginaActual = LengthAwarePaginator::resolveCurrentPage();
        $elementosPorPagina = (int) $this->perPage;
        $estudiantesPaginados = new LengthAwarePaginator(
            $matriz['estudiantes']->forPage($paginaActual, $elementosPorPagina),
            $matriz['estudiantes']->count(),
            $elementosPorPagina,
            $paginaActual,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        return view('livewire.escuelas.consolidado-academico', [
            'escuelaActual' => $escuelaActual,
            'itemsEvaluados' => $itemsEvaluados,
            'estudiantesPaginados' => $estudiantesPaginados,
            'metricas' => $matriz['metricas'],
            'consultado' => $this->consultado,
            'filtroEstadoUsuario' => $this->filtroEstadoUsuario,
        ]);
    }
}
