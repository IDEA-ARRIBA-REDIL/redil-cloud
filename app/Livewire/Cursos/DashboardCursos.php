<?php

namespace App\Livewire\Cursos;

use App\Models\Carrera;
use App\Models\Curso;
use App\Models\CursoUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DashboardCursos extends Component
{
    // Filtros
    public $fechaInicio;

    public $fechaFin;

    public $carreraId = '';

    /**
     * Inicialización del componente con valores por defecto.
     */
    public function mount(): void
    {
        // Por defecto, mostramos el último mes
        $this->fechaInicio = Carbon::now()->subMonth()->format('Y-m-d');
        $this->fechaFin = Carbon::now()->format('Y-m-d');
    }

    /**
     * Método llamado por wire:init para inicializar los gráficos.
     */
    public function loadCharts(): void
    {
        $this->dispatch('contentChanged');
    }

    /**
     * Renderiza la vista del dashboard con los datos procesados.
     */
    public function render()
    {
        // Obtener el query base de inscripciones según el rango de fecha
        $queryInscripciones = CursoUser::whereBetween('fecha_inscripcion', [
            $this->fechaInicio.' 00:00:00',
            $this->fechaFin.' 23:59:59',
        ]);

        // Filtrar por carrera si se selecciona una
        if ($this->carreraId) {
            $queryInscripciones->whereHas('curso', function ($q) {
                $q->where('carrera_id', $this->carreraId);
            });
        }

        // 1. Total de nuevos inscritos
        $totalInscritos = $queryInscripciones->count();

        // 2. Promedio de avance
        $promedioAvance = $queryInscripciones->avg('porcentaje_progreso') ?? 0;

        // 3. Datos por Género (0 = Masculino, 1 = Femenino)
        // Necesitamos unir con la tabla users
        $datosGenero = (clone $queryInscripciones)
            ->join('users', 'curso_users.user_id', '=', 'users.id')
            ->select('users.genero', DB::raw('count(*) as total'))
            ->groupBy('users.genero')
            ->get();

        // 4. Datos por Roles
        // Nota: Los roles suelen estar en model_has_roles.
        // Vamos a obtener los nombres de los roles de los usuarios inscritos.
        $datosRoles = (clone $queryInscripciones)
            ->join('model_has_roles', 'curso_users.user_id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('roles.name as rol', DB::raw('count(*) as total'))
            ->groupBy('roles.name')
            ->get();

        // 5. Desglose por curso
        $inscritosPorCurso = (clone $queryInscripciones)
            ->join('cursos', 'curso_users.curso_id', '=', 'cursos.id')
            ->select('cursos.nombre', DB::raw('count(*) as total'))
            ->groupBy('cursos.id', 'cursos.nombre')
            ->orderBy('total', 'desc')
            ->get();

        // 6. Usuarios por Entidad (Global)
        $datosEntidad = User::query()
            ->leftJoin('entidades_relacionadas', 'users.entidad_relacionada_id', '=', 'entidades_relacionadas.id')
            ->select(DB::raw('COALESCE(entidades_relacionadas.nombre, "Sin Entidad") as entidad'), DB::raw('count(*) as total'))
            ->groupBy('entidades_relacionadas.id', 'entidades_relacionadas.nombre')
            ->orderBy('total', 'desc')
            ->get();

        // 7. Inscritos por Entidad (En el rango de fecha seleccionado)
        $inscritosPorEntidad = (clone $queryInscripciones)
            ->join('users', 'curso_users.user_id', '=', 'users.id')
            ->leftJoin('entidades_relacionadas', 'users.entidad_relacionada_id', '=', 'entidades_relacionadas.id')
            ->select(DB::raw('COALESCE(entidades_relacionadas.nombre, "Sin Entidad") as entidad'), DB::raw('count(*) as total'))
            ->groupBy('entidades_relacionadas.id', 'entidades_relacionadas.nombre')
            ->orderBy('total', 'desc')
            ->get();

        return view('livewire.cursos.dashboard-cursos', [
            'totalInscritos' => $totalInscritos,
            'promedioAvance' => round($promedioAvance, 2),
            'datosGenero' => $datosGenero,
            'datosRoles' => $datosRoles,
            'inscritosPorCurso' => $inscritosPorCurso,
            'datosEntidad' => $datosEntidad,
            'inscritosPorEntidad' => $inscritosPorEntidad,
            'carreras' => Carrera::orderBy('nombre')->get(),
        ]);
    }
}
