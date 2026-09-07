<?php

namespace App\Livewire\Escuelas;

use App\Exports\InformeEstadoMateriaNivelesExport;
use App\Models\Escuela;
use App\Models\Materia;
use App\Models\MateriaAprobadaUsuario;
use App\Models\NivelAprobadoUsuario;
use App\Models\NivelEscuela;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class InformeEstadoMateriaNiveles extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // --- FILTROS DE SELECCIÓN ---
    public $escuelaSeleccionadaId = '';

    public $escuelaId = '';

    public $itemSeleccionadoId = '';

    public $itemId = '';

    public $modo = 'materias'; // 'materias' o 'niveles'

    public $rangoFechas = '';

    public $filtroEstadoAcademico = 'todos'; // 'todos', 'aprobados', 'en_curso', 'reprobados'

    public $filtroTipoRegistro = 'todos'; // 'todos', 'regular', 'homologacion'

    public $filtroEstadoUsuario = 'activos'; // 'activos', 'todos', 'dados_de_baja'

    public $buscar = '';

    public $perPage = 25;

    public bool $consultado = false;

    // --- LISTAS AUXILIARES ---
    public $escuelas = [];

    public $itemsDisponibles = [];

    public function mount()
    {
        $this->escuelas = Escuela::orderBy('nombre')->get();
        $this->escuelaSeleccionadaId = '';
        $this->escuelaId = '';
        $this->itemSeleccionadoId = '';
        $this->itemId = '';
        $this->itemsDisponibles = [];
        $this->consultado = false;
    }

    /**
     * Al cambiar de escuela en el select, determina el modo y carga las materias o niveles en cascada.
     */
    public function updatedEscuelaSeleccionadaId($value)
    {
        $this->itemSeleccionadoId = '';
        $this->itemsDisponibles = [];

        if (empty($value)) {
            $this->modo = 'materias';

            return;
        }

        $escuela = Escuela::find($value);
        if ($escuela) {
            if ($escuela->tipo_matricula === 'niveles_agrupados' || ($escuela->tipo_matricula === null && $escuela->niveles()->exists())) {
                $this->modo = 'niveles';
                $this->itemsDisponibles = NivelEscuela::where('escuela_id', $value)
                    ->select('id', 'nombre', 'caracter_obligatorio', 'orden')
                    ->orderBy('orden')
                    ->orderBy('nombre')
                    ->get();
            } else {
                $this->modo = 'materias';
                $this->itemsDisponibles = Materia::where('escuela_id', $value)
                    ->select('id', 'nombre', 'caracter_obligatorio', 'creditos', 'orden')
                    ->orderBy('orden')
                    ->orderBy('nombre')
                    ->get();
            }
        }
    }

    /**
     * Ejecuta la consulta al presionar el botón de confirmación de filtros.
     */
    public function consultar()
    {
        if (empty($this->escuelaSeleccionadaId)) {
            $this->dispatch('notificacion', [
                'tipo' => 'warning',
                'mensaje' => 'Por favor selecciona una escuela.',
            ]);

            return;
        }

        if (empty($this->itemSeleccionadoId)) {
            $this->dispatch('notificacion', [
                'tipo' => 'warning',
                'mensaje' => 'Por favor selecciona una materia o nivel.',
            ]);

            return;
        }

        if (empty(trim($this->rangoFechas))) {
            $this->dispatch('notificacion', [
                'tipo' => 'warning',
                'mensaje' => 'Por favor selecciona un rango de fechas.',
            ]);

            return;
        }

        $this->escuelaId = $this->escuelaSeleccionadaId;
        $this->itemId = $this->itemSeleccionadoId;
        $this->consultado = true;
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
     * Procesa la consulta del informe por materia o nivel de forma ultra-optimizada.
     */
    protected function procesarInforme()
    {
        if (! $this->consultado || empty($this->escuelaId) || empty($this->itemId)) {
            return [
                'estudiantes' => collect(),
                'metricas' => [
                    'total' => 0,
                    'aprobados' => 0,
                    'en_curso' => 0,
                    'reprobados' => 0,
                    'promedio_general' => 0,
                ],
                'itemActivo' => null,
            ];
        }

        // 1. Obtener la materia o nivel activo
        if ($this->modo === 'materias') {
            $itemActivo = Materia::find($this->itemId);
        } else {
            $itemActivo = NivelEscuela::find($this->itemId);
        }

        if (! $itemActivo) {
            return [
                'estudiantes' => collect(),
                'metricas' => [
                    'total' => 0,
                    'aprobados' => 0,
                    'en_curso' => 0,
                    'reprobados' => 0,
                    'promedio_general' => 0,
                ],
                'itemActivo' => null,
            ];
        }

        // 2. Parsear rango de fechas si se definió
        $fechaInicio = null;
        $fechaFin = null;

        if (! empty(trim($this->rangoFechas))) {
            $partesFechas = preg_split('/\s+(a|-)\s+/', trim($this->rangoFechas));
            if (count($partesFechas) >= 2) {
                try {
                    $fechaInicio = Carbon::parse(trim($partesFechas[0]))->startOfDay();
                    $fechaFin = Carbon::parse(trim($partesFechas[1]))->endOfDay();
                } catch (\Exception $e) {
                }
            } elseif (count($partesFechas) === 1 && ! empty($partesFechas[0])) {
                try {
                    $fechaInicio = Carbon::parse(trim($partesFechas[0]))->startOfDay();
                    $fechaFin = Carbon::parse(trim($partesFechas[0]))->endOfDay();
                } catch (\Exception $e) {
                }
            }
        }

        // 3. Consultar registros de aprobaciones de la materia o nivel
        if ($this->modo === 'materias') {
            $queryAprobaciones = MateriaAprobadaUsuario::where('materia_id', $this->itemId)
                ->select('id', 'user_id', 'materia_id', 'aprobado', 'nota_final', 'creditos_aprobados', 'total_asistencias', 'es_homologacion', 'fecha_homologacion', 'fecha_homologacion_aprobacion', 'created_at');
        } else {
            $queryAprobaciones = NivelAprobadoUsuario::where('nivel_id', $this->itemId)
                ->select('id', 'user_id', 'nivel_id', 'aprobado', 'nota_final', 'es_homologacion', 'fecha_homologacion', 'fecha_homologacion_aprobacion', 'created_at');
        }

        // Filtro por rango de fechas
        if ($fechaInicio && $fechaFin) {
            $queryAprobaciones->where(function ($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('created_at', [$fechaInicio, $fechaFin])
                    ->orWhereBetween('fecha_homologacion_aprobacion', [$fechaInicio, $fechaFin])
                    ->orWhereBetween('fecha_homologacion', [$fechaInicio, $fechaFin]);
            });
        }

        // Filtro por estado académico
        if ($this->filtroEstadoAcademico === 'aprobados') {
            $queryAprobaciones->where('aprobado', 1);
        } elseif ($this->filtroEstadoAcademico === 'en_curso') {
            $queryAprobaciones->where('aprobado', 2);
        } elseif ($this->filtroEstadoAcademico === 'reprobados') {
            $queryAprobaciones->where('aprobado', 0);
        }

        // Filtro por tipo de registro (Regular vs Homologación)
        if ($this->filtroTipoRegistro === 'regular') {
            $queryAprobaciones->where(function ($q) {
                $q->where('es_homologacion', false)->orWhereNull('es_homologacion');
            });
        } elseif ($this->filtroTipoRegistro === 'homologacion') {
            $queryAprobaciones->where('es_homologacion', true);
        }

        $registros = $queryAprobaciones->get();

        if ($registros->isEmpty()) {
            return [
                'estudiantes' => collect(),
                'metricas' => [
                    'total' => 0,
                    'aprobados' => 0,
                    'en_curso' => 0,
                    'reprobados' => 0,
                    'promedio_general' => 0,
                ],
                'itemActivo' => $itemActivo,
            ];
        }

        // Mapear aprobaciones por user_id
        $aprobacionesPorUsuario = [];
        $userIdsMap = [];

        foreach ($registros as $reg) {
            $uId = (int) $reg->user_id;
            $userIdsMap[$uId] = true;
            $fechaReg = $reg->fecha_homologacion_aprobacion ?? $reg->fecha_homologacion ?? $reg->created_at;

            $aprobacionesPorUsuario[$uId] = [
                'aprobado' => (int) $reg->aprobado,
                'nota_final' => $reg->nota_final,
                'creditos_aprobados' => $this->modo === 'materias' ? ($reg->creditos_aprobados ?? $itemActivo->creditos ?? 0) : 0,
                'total_asistencias' => $this->modo === 'materias' ? $reg->total_asistencias : null,
                'es_homologacion' => (bool) $reg->es_homologacion,
                'fecha_registro' => $fechaReg ? Carbon::parse($fechaReg)->format('d/m/Y') : 'S/D',
            ];
        }

        $userIds = array_keys($userIdsMap);

        // 4. Consultar usuarios asociados
        $queryUsuarios = User::query()
            ->whereIn('id', $userIds)
            ->select('id', 'identificacion', 'primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido', 'email', 'deleted_at');

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
                    ->orWhere('email', 'like', $termino)
                    ->orWhereRaw("LOWER(translate(CONCAT_WS(' ', primer_nombre, segundo_nombre, primer_apellido, segundo_apellido), 'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ', 'aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", [$termino])
                    ->orWhereRaw("LOWER(translate(CONCAT_WS(' ', primer_apellido, segundo_apellido, primer_nombre, segundo_nombre), 'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ', 'aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", [$termino])
                    ->orWhereRaw("LOWER(translate(CONCAT_WS(' ', primer_nombre, primer_apellido), 'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ', 'aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", [$termino])
                    ->orWhereRaw("LOWER(translate(CONCAT_WS(' ', primer_apellido, primer_nombre), 'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ', 'aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", [$termino]);
            });
        }

        $usuarios = $queryUsuarios->orderBy('primer_apellido')->orderBy('primer_nombre')->get();

        $filasProcesadas = collect();
        $contadorAprobados = 0;
        $contadorEnCurso = 0;
        $contadorReprobados = 0;
        $sumaSoloNotas = 0;
        $totalConNota = 0;

        foreach ($usuarios as $usuario) {
            $reg = $aprobacionesPorUsuario[$usuario->id] ?? null;
            if (! $reg) {
                continue;
            }

            $estadoNum = $reg['aprobado'];
            $notaFinal = $reg['nota_final'];

            if ($estadoNum === 1) {
                $contadorAprobados++;
            } elseif ($estadoNum === 2) {
                $contadorEnCurso++;
            } elseif ($estadoNum === 0) {
                $contadorReprobados++;
            }

            if ($notaFinal !== null && is_numeric($notaFinal)) {
                $sumaSoloNotas += (float) $notaFinal;
                $totalConNota++;
            }

            $filasProcesadas->push([
                'usuario_id' => $usuario->id,
                'identificacion' => $usuario->identificacion,
                'nombres' => trim("{$usuario->primer_nombre} {$usuario->segundo_nombre}"),
                'apellidos' => trim("{$usuario->primer_apellido} {$usuario->segundo_apellido}"),
                'email' => $usuario->email,
                'dado_de_baja' => (bool) $usuario->deleted_at,
                'estado' => $estadoNum,
                'es_homologacion' => $reg['es_homologacion'],
                'nota' => $notaFinal !== null ? number_format((float) $notaFinal, 1) : null,
                'creditos' => $reg['creditos_aprobados'],
                'asistencias' => $reg['total_asistencias'],
                'fecha_registro' => $reg['fecha_registro'],
            ]);
        }

        $promedioGeneral = $totalConNota > 0 ? round($sumaSoloNotas / $totalConNota, 1) : 0;

        return [
            'estudiantes' => $filasProcesadas,
            'metricas' => [
                'total' => $filasProcesadas->count(),
                'aprobados' => $contadorAprobados,
                'en_curso' => $contadorEnCurso,
                'reprobados' => $contadorReprobados,
                'promedio_general' => $promedioGeneral,
            ],
            'itemActivo' => $itemActivo,
        ];
    }

    /**
     * Dispara la exportación del informe a Excel (.xlsx).
     */
    public function exportarExcel()
    {
        if (empty($this->escuelaId) || empty($this->itemId)) {
            $this->dispatch('notificacion', [
                'tipo' => 'warning',
                'mensaje' => 'Selecciona la escuela y materia/nivel antes de exportar.',
            ]);

            return;
        }

        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');

        $escuela = Escuela::find($this->escuelaId);
        $nombreEscuela = $escuela ? $escuela->nombre : 'Escuela';

        $resultado = $this->procesarInforme();
        $nombreItem = $resultado['itemActivo'] ? $resultado['itemActivo']->nombre : 'Materia';
        $nombreArchivo = 'informe_estado_'.\Illuminate\Support\Str::slug($nombreEscuela).'_'.\Illuminate\Support\Str::slug($nombreItem).'_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(new InformeEstadoMateriaNivelesExport(
            escuelaNombre: $nombreEscuela,
            itemNombre: $nombreItem,
            modo: $this->modo,
            rangoFechas: $this->rangoFechas,
            filtroEstadoAcademico: $this->filtroEstadoAcademico,
            filtroTipoRegistro: $this->filtroTipoRegistro,
            filtroEstadoUsuario: $this->filtroEstadoUsuario,
            itemActivo: $resultado['itemActivo'],
            estudiantes: $resultado['estudiantes'],
            metricas: $resultado['metricas']
        ), $nombreArchivo);
    }

    public function render()
    {
        $escuelaActual = ! empty($this->escuelaId) ? Escuela::find($this->escuelaId) : null;
        $informe = $this->procesarInforme();

        // Paginación manual
        $paginaActual = LengthAwarePaginator::resolveCurrentPage();
        $elementosPorPagina = (int) $this->perPage;
        $estudiantesPaginados = new LengthAwarePaginator(
            $informe['estudiantes']->forPage($paginaActual, $elementosPorPagina),
            $informe['estudiantes']->count(),
            $elementosPorPagina,
            $paginaActual,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        return view('livewire.escuelas.informe-estado-materia-niveles', [
            'escuelaActual' => $escuelaActual,
            'itemActivo' => $informe['itemActivo'],
            'estudiantesPaginados' => $estudiantesPaginados,
            'metricas' => $informe['metricas'],
            'consultado' => $this->consultado,
            'modo' => $this->modo,
        ]);
    }
}
