<?php

namespace App\Livewire\Dashboard;

use App\Models\Actividad;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CalendarioActividades extends Component
{
    public bool $tienePermiso = false;

    public array $arrayActividades = [];

    public array $tagsGenerales = [];

    public string $claseColumnas = 'col-12 col-lg-12 mt-3';

    /**
     * Inicializa el componente validando permisos y cargando actividades del evento.
     */
    public function mount(string $claseColumnas = 'col-12 col-lg-12 mt-3'): void
    {
        $this->claseColumnas = $claseColumnas;

        // 1. Obtener usuario autenticado y su rol activo
        $usuario = auth()->user();
        if (! $usuario) {
            $this->tienePermiso = false;

            return;
        }

        $rolActivo = $usuario->roles()->wherePivot('activo', true)->first();

        // 2. Verificar permiso para ver el calendario en dashboard
        if (! $rolActivo || ! $rolActivo->hasPermissionTo('dashboard.dashboard_mostrar_calendario')) {
            $this->tienePermiso = false;

            return;
        }

        $this->tienePermiso = true;

        // 3. Cargar y formatear actividades basándose estrictamente en fecha_inicio y fecha_finalizacion
        $this->cargarActividades($usuario);
    }

    /**
     * Consulta las actividades vigentes y elegibles para el usuario autenticado
     * basándose estrictamente en las fechas del evento (fecha_inicio y fecha_finalizacion)
     * y NO en las fechas de inscripción (fecha_visualizacion y fecha_cierre).
     */
    public function cargarActividades($usuario): void
    {
        // 1. Tomamos como referencia el inicio del mes actual para que el usuario pueda ver
        // todos los eventos del mes en curso en FullCalendar, más todos los eventos futuros.
        $inicioMesActual = now()->startOfMonth()->toDateString();

        // 2. Consultar actividades activas comparando con fecha_inicio y fecha_finalizacion del evento
        $actividades = Actividad::with(['tags', 'tipo'])
            ->where('activa', true)
            ->whereNotNull('fecha_inicio')
            ->where(function ($query) use ($inicioMesActual) {
                // Eventos que finalicen en o después del inicio de este mes
                $query->where('fecha_finalizacion', '>=', $inicioMesActual)
                    // O si no tienen fecha de finalización, cuya fecha de inicio sea en o después del inicio de este mes
                    ->orWhere(function ($sub) use ($inicioMesActual) {
                        $sub->whereNull('fecha_finalizacion')
                            ->where('fecha_inicio', '>=', $inicioMesActual);
                    });
            })
            ->orderBy('fecha_inicio', 'asc')
            ->get();

        // 3. Filtrar actividades según elegibilidad del usuario (validarAccesoGlobal / requisitos)
        $permitidasIds = Actividad::filtrarActividadesPermitidas($usuario, $actividades);
        $actividadesPermitidas = $actividades->whereIn('id', $permitidasIds);

        $this->procesarActividades($actividadesPermitidas);
    }

    /**
     * Procesa la colección de actividades para estructurar FullCalendar y los tags permitidos.
     */
    public function procesarActividades($actividades): void
    {
        // 1. Extraer ÚNICAMENTE las categorías/tags presentes en las actividades permitidas
        $this->tagsGenerales = $actividades
            ->pluck('tags')
            ->flatten()
            ->unique('id')
            ->values()
            ->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'nombre' => $tag->nombre,
                ];
            })
            ->toArray();

        // 2. Mapear actividades en el formato requerido por FullCalendar
        $this->arrayActividades = $actividades->map(function ($acti) {
            return [
                'id' => $acti->id,
                'title' => $acti->nombre,
                'start' => $acti->fecha_inicio,
                'end' => $acti->fecha_finalizacion,
                'backgroundColor' => $acti->fondo ?: '#7367f0',
                'textColor' => $acti->color ?: '#ffffff',
                'borderColor' => '#000000',
                'url' => route('actividades.perfil', $acti->id),
                'extendedProps' => [
                    'tags' => $acti->tags ? $acti->tags->pluck('id')->toArray() : [],
                ],
            ];
        })->values()->toArray();
    }

    public function render(): View
    {
        return view('livewire.dashboard.calendario-actividades');
    }
}
