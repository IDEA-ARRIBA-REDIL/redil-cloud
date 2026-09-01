<?php

namespace App\Services;

use App\Models\CrecimientoUsuario;
use App\Models\Hito;
use App\Models\HitoUsuario;
use App\Models\Materia;
use App\Models\MateriaAprobadaUsuario;
use App\Models\NivelAprobadoUsuario;
use App\Models\NivelEscuela;
use App\Models\TareaConsolidacionUsuario;
use App\Models\TipoHito;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class HitoTriggerService
{
    /**
     * Trigger: Cambio de estado en Pasos de Crecimiento.
     */
    public function onCrecimientoUsuarioCambio(int $userId, int $pasoCrecimientoId, int $estadoId): array
    {
        return $this->buscarYAsignar(
            'pasos_crecimiento',
            'cambio_estado',
            $userId,
            ['paso_crecimiento_id' => $pasoCrecimientoId, 'estado_id' => $estadoId]
        );
    }

    /**
     * Trigger: Cambio de estado en Tareas de Consolidación.
     */
    public function onTareaConsolidacionCambio(int $userId, int $tareaConsolidacionId, int $estadoId): array
    {
        return $this->buscarYAsignar(
            'tareas_consolidacion',
            'cambio_estado',
            $userId,
            ['tarea_consolidacion_id' => $tareaConsolidacionId, 'estado_id' => $estadoId]
        );
    }

    /**
     * Trigger: Aprobación de materia en Escuelas.
     */
    public function onMateriaAprobada(
        int $userId,
        int $materiaId,
        ?int $escuelaId = null,
        ?int $nivelId = null,
        ?int $origenId = null,
        ?string $fecha = null
    ): array {
        if (! $escuelaId) {
            $materia = Materia::find($materiaId);
            $escuelaId = $materia?->escuela_id;
            $nivelId = $nivelId ?? $materia?->nivel_id;
        }

        $condiciones = ['materia_id' => $materiaId];
        if ($escuelaId) {
            $condiciones['escuela_id'] = $escuelaId;
        }

        return $this->buscarYAsignar(
            'escuelas',
            'aprobacion_materia',
            $userId,
            $condiciones,
            'automatico',
            $origenId,
            $fecha
        );
    }

    /**
     * Trigger: Aprobación de nivel en Escuelas (para escuelas por niveles agrupados).
     */
    public function onNivelAprobado(
        int $userId,
        int $nivelId,
        ?int $escuelaId = null,
        ?int $origenId = null,
        ?string $fecha = null
    ): array {
        if (! $escuelaId) {
            $nivel = NivelEscuela::find($nivelId);
            $escuelaId = $nivel?->escuela_id;
        }

        $condiciones = ['nivel_id' => $nivelId];
        if ($escuelaId) {
            $condiciones['escuela_id'] = $escuelaId;
        }

        return $this->buscarYAsignar(
            'escuelas',
            'aprobacion_nivel',
            $userId,
            $condiciones,
            'automatico',
            $origenId,
            $fecha
        );
    }

    /**
     * Trigger: Asignación a grupo (integrante).
     */
    public function onAsignacionGrupoIntegrante(int $userId, int $tipoGrupoId, int $grupoId): array
    {
        return $this->buscarYAsignar(
            'grupos',
            'asignacion_integrante',
            $userId,
            ['tipo_grupo_id' => $tipoGrupoId],
            'automatico',
            $grupoId
        );
    }

    /**
     * Trigger: Designación como líder/encargado de grupo.
     */
    public function onDesignacionLiderGrupo(int $userId, int $tipoGrupoId, int $grupoId): array
    {
        return $this->buscarYAsignar(
            'grupos',
            'designacion_lider',
            $userId,
            ['tipo_grupo_id' => $tipoGrupoId],
            'automatico',
            $grupoId
        );
    }

    /**
     * Búsqueda y asignación de hitos automáticos activos que cumplan con las condiciones.
     */
    private function buscarYAsignar(
        string $modulo,
        string $tipo,
        int $userId,
        array $condiciones,
        string $origenTipo = 'automatico',
        ?int $origenId = null,
        ?string $fecha = null
    ): array {
        $tipoAutomatico = TipoHito::where('slug', 'automatico')->first();
        if (! $tipoAutomatico) {
            return [];
        }

        $hitos = Hito::where('tipo_hito_id', $tipoAutomatico->id)
            ->where('activo', true)
            ->where('trigger_modulo', $modulo)
            ->where('trigger_tipo', $tipo)
            ->get();

        $asignados = [];
        foreach ($hitos as $hito) {
            if ($this->condicionesCumplen($hito->trigger_config, $condiciones)) {
                if ($this->asignar($hito, $userId, $origenTipo, $origenId, $fecha)) {
                    $asignados[] = $hito;
                }
            }
        }

        if (! empty($asignados)) {
            Log::info("HitoTriggerService: Usuario ID {$userId} recibió ".count($asignados)." hitos en módulo '{$modulo}' tipo '{$tipo}'.");
        }

        return $asignados;
    }

    /**
     * Valida si la configuración esperada del hito coincide con las condiciones del evento.
     */
    private function condicionesCumplen(?array $config, array $condiciones): bool
    {
        if (! $config || empty($config)) {
            return true;
        }

        foreach ($config as $key => $valor) {
            if ($valor !== null && $valor !== '') {
                if (! isset($condiciones[$key]) || (string) $condiciones[$key] !== (string) $valor) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Asignación efectiva en la tabla hito_usuario (evitando duplicados).
     * Garantiza que un usuario reciba un hito específico como máximo 1 sola vez en su vida.
     */
    public function asignar(Hito $hito, int $userId, string $origenTipo = 'automatico', ?int $origenId = null, ?string $fecha = null): bool
    {
        $existe = HitoUsuario::where('hito_id', $hito->id)
            ->where('user_id', $userId)
            ->exists();

        if ($existe) {
            return false;
        }

        HitoUsuario::create([
            'hito_id' => $hito->id,
            'user_id' => $userId,
            'fecha' => $fecha ?? now()->toDateString(),
            'asistio' => true,
            'origen_tipo' => $origenTipo,
            'origen_id' => $origenId,
            'asignado_por' => auth()->id(),
        ]);

        return true;
    }

    /**
     * Migración Retroactiva: Asigna un hito automático a usuarios que ya cumplían los requisitos históricos.
     */
    public function migrarRetroactivo(Hito $hito): int
    {
        $count = 0;

        if ($hito->trigger_modulo === 'pasos_crecimiento') {
            $count = $this->migrarPasosCrecimiento($hito);
        } elseif ($hito->trigger_modulo === 'tareas_consolidacion') {
            $count = $this->migrarTareasConsolidacion($hito);
        } elseif ($hito->trigger_modulo === 'escuelas') {
            $count = $this->migrarEscuelas($hito);
        } elseif ($hito->trigger_modulo === 'grupos') {
            $count = $this->migrarGrupos($hito);
        }

        return $count;
    }

    private function migrarPasosCrecimiento(Hito $hito): int
    {
        $config = $hito->trigger_config ?? [];
        if (empty($config['paso_crecimiento_id']) || empty($config['estado_id'])) {
            return 0;
        }

        $query = CrecimientoUsuario::where('paso_crecimiento_id', $config['paso_crecimiento_id'])
            ->where('estado_id', $config['estado_id']);

        $count = 0;
        foreach ($query->get() as $crecimiento) {
            $fecha = $crecimiento->fecha ? substr((string) $crecimiento->fecha, 0, 10) : now()->toDateString();
            if ($this->asignar($hito, $crecimiento->user_id, 'automatico', $crecimiento->id, $fecha)) {
                $count++;
            }
        }

        return $count;
    }

    private function migrarTareasConsolidacion(Hito $hito): int
    {
        $config = $hito->trigger_config ?? [];
        if (empty($config['tarea_consolidacion_id']) || empty($config['estado_id'])) {
            return 0;
        }

        $query = TareaConsolidacionUsuario::where('tarea_consolidacion_id', $config['tarea_consolidacion_id'])
            ->where('estado_tarea_consolidacion_id', $config['estado_id']);

        $count = 0;
        foreach ($query->get() as $tarea) {
            $fecha = $tarea->created_at ? $tarea->created_at->toDateString() : now()->toDateString();
            if ($this->asignar($hito, $tarea->user_id, 'automatico', $tarea->id, $fecha)) {
                $count++;
            }
        }

        return $count;
    }

    private function migrarEscuelas(Hito $hito): int
    {
        $config = $hito->trigger_config ?? [];
        $count = 0;

        if (! empty($config['materia_id'])) {
            $query = MateriaAprobadaUsuario::where('materia_id', $config['materia_id'])
                ->where('aprobado', MateriaAprobadaUsuario::ESTADO_APROBADO);

            foreach ($query->get() as $materia) {
                $fecha = $materia->fecha_homologacion_aprobacion
                    ? substr((string) $materia->fecha_homologacion_aprobacion, 0, 10)
                    : ($materia->fecha_homologacion
                        ? substr((string) $materia->fecha_homologacion, 0, 10)
                        : ($materia->created_at ? $materia->created_at->toDateString() : now()->toDateString()));
                if ($this->asignar($hito, $materia->user_id, 'automatico', $materia->id, $fecha)) {
                    $count++;
                }
            }
        } elseif (! empty($config['nivel_id'])) {
            $query = NivelAprobadoUsuario::where('nivel_id', $config['nivel_id'])
                ->where('aprobado', NivelAprobadoUsuario::ESTADO_APROBADO);

            foreach ($query->get() as $nivel) {
                $fecha = $nivel->fecha_homologacion_aprobacion
                    ? substr((string) $nivel->fecha_homologacion_aprobacion, 0, 10)
                    : ($nivel->fecha_homologacion
                        ? substr((string) $nivel->fecha_homologacion, 0, 10)
                        : ($nivel->created_at ? $nivel->created_at->toDateString() : now()->toDateString()));
                if ($this->asignar($hito, $nivel->user_id, 'automatico', $nivel->id, $fecha)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    private function migrarGrupos(Hito $hito): int
    {
        $config = $hito->trigger_config ?? [];
        if (empty($config['tipo_grupo_id'])) {
            return 0;
        }

        $tipoGrupoId = $config['tipo_grupo_id'];
        $count = 0;

        if ($hito->trigger_tipo === 'designacion_lider') {
            $usuarios = User::whereHas('gruposEncargados', function ($q) use ($tipoGrupoId) {
                $q->where('tipo_grupo_id', $tipoGrupoId);
            })->get();
        } else {
            $usuarios = User::whereHas('gruposDondeAsiste', function ($q) use ($tipoGrupoId) {
                $q->where('tipo_grupo_id', $tipoGrupoId);
            })->get();
        }

        foreach ($usuarios as $user) {
            if ($this->asignar($hito, $user->id, 'automatico', null)) {
                $count++;
            }
        }

        return $count;
    }
}
