<?php

namespace App\Livewire\RuedaDeLaVida;

use App\Models\CampoSeccionRv;
use App\Models\ConfiguracionRv;
use App\Models\SeccionRv;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class GestionarRuedaDeLaVida extends Component
{
    public string $tabActiva = 'secciones';

    public array $seccionesActivas = [];

    // --- Propiedades para Gestión de Sección (Área de Vida) ---
    public ?int $seccionIdEditando = null;

    public bool $modoEdicionSeccion = false;

    public string $nombreSeccion = '';

    public string $subtituloSeccion = '';

    public string $iconoSeccion = 'ti ti-cloud-heart';

    public string $colorSeccion = '#008ffb';

    public float $promedioMinimoSeccion = 6.0;

    // --- Propiedades para Gestión de Hábito (CampoSeccionRv) ---
    public ?int $campoIdEditando = null;

    public ?int $seccionPadreId = null;

    public ?string $seccionPadreNombre = '';

    public bool $modoEdicionCampo = false;

    public string $nombreCampo = '';

    public bool $abiertoCampo = false;

    public string $colorCampo = '#008ffb';

    // --- Propiedades para Configuración General (ConfiguracionRv) ---
    public string $nombreGeneral = 'Rueda de la Vida';

    public string $labelPromedioGeneral = 'Promedio';

    public float $promedioGeneralMinimo = 6.0;

    public string $nombreHabitos = 'Hábitos que debo desarrollar';

    public int $maxMetas = 5;

    public int $maxHabitosPorMeta = 5;

    public int $periodicidad = 30;

    protected function rulesSeccion(): array
    {
        return [
            'nombreSeccion' => ['required', 'string', 'max:100'],
            'subtituloSeccion' => ['nullable', 'string', 'max:255'],
            'iconoSeccion' => ['required', 'string', 'max:100'],
            'colorSeccion' => ['required', 'string', 'max:50'],
            'promedioMinimoSeccion' => ['required', 'numeric', 'min:0', 'max:10'],
        ];
    }

    protected function rulesCampo(): array
    {
        return [
            'nombreCampo' => [$this->abiertoCampo ? 'nullable' : 'required', 'string', 'max:50'],
            'abiertoCampo' => ['boolean'],
            'colorCampo' => ['required', 'string', 'max:50'],
        ];
    }

    protected function rulesConfiguracion(): array
    {
        return [
            'nombreGeneral' => ['required', 'string', 'max:100'],
            'labelPromedioGeneral' => ['required', 'string', 'max:100'],
            'promedioGeneralMinimo' => ['required', 'numeric', 'min:0', 'max:10'],
            'nombreHabitos' => ['required', 'string', 'max:100'],
            'maxMetas' => ['required', 'integer', 'min:1', 'max:20'],
            'maxHabitosPorMeta' => ['required', 'integer', 'min:1', 'max:20'],
            'periodicidad' => ['required', 'integer', 'min:1', 'max:365'],
        ];
    }

    public function mount(): void
    {
        $this->cargarConfiguracion();
    }

    /**
     * Carga la configuración del módulo en las propiedades del componente.
     */
    public function cargarConfiguracion(): void
    {
        $config = ConfiguracionRv::first();
        if ($config) {
            $this->nombreGeneral = $config->nombre_general ?? 'Rueda de la Vida';
            $this->labelPromedioGeneral = $config->label_promedio_general ?? 'Promedio';
            $this->promedioGeneralMinimo = (float) ($config->promedio_general ?? 6.0);
            $this->nombreHabitos = $config->nombre_habitos ?? 'Hábitos que debo desarrollar';
            $this->maxMetas = (int) ($config->max_metas ?? 5);
            $this->maxHabitosPorMeta = (int) ($config->max_habitos_por_meta ?? 5);
            $this->periodicidad = (int) ($config->periodicidad ?? 30);
        }
    }

    /**
     * Alterna la visibilidad del acordeón para una sección.
     */
    public function toggleSeccion(int $seccionId): void
    {
        if (in_array($seccionId, $this->seccionesActivas)) {
            $this->seccionesActivas = array_diff($this->seccionesActivas, [$seccionId]);
        } else {
            $this->seccionesActivas[] = $seccionId;
        }
    }

    // =========================================================================
    //  GESTIÓN DE SECCIONES (ÁREAS DE VIDA)
    // =========================================================================

    /**
     * Prepara el modal para crear una nueva sección de hábitos.
     */
    public function crearSeccion(): void
    {
        $this->resetValidation();
        $this->seccionIdEditando = null;
        $this->modoEdicionSeccion = false;
        $this->nombreSeccion = '';
        $this->subtituloSeccion = 'Llena cada uno de los siguientes hábitos';
        $this->iconoSeccion = 'ti ti-cloud-heart';
        $this->colorSeccion = '#008ffb';
        $this->promedioMinimoSeccion = 6.0;

        $this->dispatch('abrirModal', nombreModal: 'modalSeccionRv');
    }

    /**
     * Prepara el modal para editar una sección existente.
     */
    public function editarSeccion(int $seccionId): void
    {
        $this->resetValidation();
        $seccion = SeccionRv::findOrFail($seccionId);

        $this->seccionIdEditando = $seccion->id;
        $this->modoEdicionSeccion = true;
        $this->nombreSeccion = $seccion->nombre_seccion;
        $this->subtituloSeccion = $seccion->subtitulo_seccion ?? '';
        $this->iconoSeccion = $seccion->icono ?? 'ti ti-cloud-heart';
        $this->colorSeccion = $seccion->color ?? '#008ffb';
        $this->promedioMinimoSeccion = (float) ($seccion->promedio_minimo ?? 6.0);

        $this->dispatch('abrirModal', nombreModal: 'modalSeccionRv');
    }

    /**
     * Guarda la creación o actualización de una sección.
     */
    public function guardarSeccion(): void
    {
        $this->validate($this->rulesSeccion());

        if ($this->modoEdicionSeccion && $this->seccionIdEditando) {
            $seccion = SeccionRv::findOrFail($this->seccionIdEditando);
            $seccion->update([
                'nombre_seccion' => $this->nombreSeccion,
                'subtitulo_seccion' => $this->subtituloSeccion,
                'icono' => $this->iconoSeccion,
                'color' => $this->colorSeccion,
                'promedio_minimo' => $this->promedioMinimoSeccion,
            ]);

            $this->dispatch('cerrarModal', nombreModal: 'modalSeccionRv');
            $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Excelente!', msnTexto: 'El área fue actualizada exitosamente.');
        } else {
            // Calcular el nuevo orden antes de las secciones fijas (tipo 2 y 3)
            $ultimoOrdenContador = SeccionRv::where('tipo_seccion_id', 1)->max('orden') ?? 0;
            $nuevoOrden = $ultimoOrdenContador + 1;

            // Desplazar hacia arriba el orden de las secciones de sistema (resumen y encuesta)
            SeccionRv::where('orden', '>=', $nuevoOrden)->increment('orden');

            $seccion = SeccionRv::create([
                'titulo_barra' => 'Rueda de la vida',
                'tipo_seccion_id' => 1,
                'icono' => $this->iconoSeccion,
                'orden' => $nuevoOrden,
                'titulo_steper' => 'Calcula tus hábitos',
                'nombre_seccion' => $this->nombreSeccion,
                'subtitulo_seccion' => $this->subtituloSeccion,
                'descripcion' => '',
                'label_btn_bienvenida' => '',
                'label_indice_promedio' => 'Promedio',
                'label_superior_atras' => 'Volver',
                'label_superior_adelante' => 'Salir',
                'label_btn_inferior_adelante' => 'Continuar',
                'label_btn_inferior_atras' => 'Volver',
                'min' => 0,
                'max' => 10,
                'color' => $this->colorSeccion,
                'promedio_minimo' => $this->promedioMinimoSeccion,
            ]);

            // Por defecto, agregar 1 hábito abierto libre para que el usuario pueda escribir su hábito
            CampoSeccionRv::create([
                'nombre' => '',
                'abierto' => true,
                'seccion_rv_id' => $seccion->id,
                'orden' => 1,
                'color' => $this->colorSeccion,
            ]);

            $this->seccionesActivas[] = $seccion->id;

            $this->dispatch('cerrarModal', nombreModal: 'modalSeccionRv');
            $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Área creada!', msnTexto: 'El área fue creada con éxito. Ya puedes agregarle hábitos.');
        }
    }

    /**
     * Sube una posición el orden del área entre las secciones de hábitos.
     */
    public function subirSeccion(int $seccionId): void
    {
        $seccionActual = SeccionRv::findOrFail($seccionId);
        $seccionAnterior = SeccionRv::where('tipo_seccion_id', 1)
            ->where('orden', '<', $seccionActual->orden)
            ->orderBy('orden', 'desc')
            ->first();

        if ($seccionAnterior) {
            $ordenTemp = $seccionActual->orden;
            $seccionActual->orden = $seccionAnterior->orden;
            $seccionAnterior->orden = $ordenTemp;

            $seccionActual->save();
            $seccionAnterior->save();
        }
    }

    /**
     * Baja una posición el orden del área entre las secciones de hábitos.
     */
    public function bajarSeccion(int $seccionId): void
    {
        $seccionActual = SeccionRv::findOrFail($seccionId);
        $seccionSiguiente = SeccionRv::where('tipo_seccion_id', 1)
            ->where('orden', '>', $seccionActual->orden)
            ->orderBy('orden', 'asc')
            ->first();

        if ($seccionSiguiente) {
            $ordenTemp = $seccionActual->orden;
            $seccionActual->orden = $seccionSiguiente->orden;
            $seccionSiguiente->orden = $ordenTemp;

            $seccionActual->save();
            $seccionSiguiente->save();
        }
    }

    /**
     * Elimina un área/sección y sus campos.
     */
    public function eliminarSeccion(int $seccionId): void
    {
        $seccion = SeccionRv::findOrFail($seccionId);

        // Protección: Solo se pueden eliminar secciones de hábitos (tipo 1)
        if ($seccion->tipo_seccion_id != 1) {
            $this->dispatch('msn', msnIcono: 'error', msnTitulo: 'Acción no permitida', msnTexto: 'Las secciones de resumen y encuesta son requeridas por el sistema.');

            return;
        }

        DB::transaction(function () use ($seccion) {
            // Eliminar campos relacionados
            $seccion->campos()->delete();
            // Eliminar sección (soft delete)
            $seccion->delete();
        });

        $this->seccionesActivas = array_diff($this->seccionesActivas, [$seccionId]);
        $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Área eliminada!', msnTexto: 'El área y sus hábitos asociados fueron eliminados.');
    }

    // =========================================================================
    //  GESTIÓN DE HÁBITOS (CAMPOS DE SECCIÓN)
    // =========================================================================

    /**
     * Prepara el modal para agregar un nuevo hábito a un área específica.
     */
    public function crearCampo(int $seccionId): void
    {
        $this->resetValidation();
        $seccion = SeccionRv::findOrFail($seccionId);

        $this->seccionPadreId = $seccion->id;
        $this->seccionPadreNombre = $seccion->nombre_seccion;
        $this->campoIdEditando = null;
        $this->modoEdicionCampo = false;
        $this->nombreCampo = '';
        $this->abiertoCampo = false;
        $this->colorCampo = $seccion->color ?? '#008ffb';

        $this->dispatch('abrirModal', nombreModal: 'modalCampoRv');
    }

    /**
     * Prepara el modal para editar un hábito existente.
     */
    public function editarCampo(int $campoId): void
    {
        $this->resetValidation();
        $campo = CampoSeccionRv::with('seccion')->findOrFail($campoId);

        $this->campoIdEditando = $campo->id;
        $this->seccionPadreId = $campo->seccion_rv_id;
        $this->seccionPadreNombre = $campo->seccion?->nombre_seccion ?? '';
        $this->modoEdicionCampo = true;
        $this->nombreCampo = $campo->nombre ?? '';
        $this->abiertoCampo = (bool) $campo->abierto;
        $this->colorCampo = $campo->color ?? '#008ffb';

        $this->dispatch('abrirModal', nombreModal: 'modalCampoRv');
    }

    /**
     * Guarda la creación o edición de un hábito.
     */
    public function guardarCampo(): void
    {
        $this->validate($this->rulesCampo());

        if ($this->modoEdicionCampo && $this->campoIdEditando) {
            $campo = CampoSeccionRv::findOrFail($this->campoIdEditando);
            $campo->update([
                'nombre' => $this->abiertoCampo ? '' : trim($this->nombreCampo),
                'abierto' => $this->abiertoCampo,
                'color' => $this->colorCampo,
            ]);

            $this->dispatch('cerrarModal', nombreModal: 'modalCampoRv');
            $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Hábito actualizado!', msnTexto: 'El hábito fue modificado correctamente.');
        } else {
            $ultimoOrden = CampoSeccionRv::where('seccion_rv_id', $this->seccionPadreId)->max('orden') ?? 0;

            CampoSeccionRv::create([
                'nombre' => $this->abiertoCampo ? '' : trim($this->nombreCampo),
                'abierto' => $this->abiertoCampo,
                'seccion_rv_id' => $this->seccionPadreId,
                'orden' => $ultimoOrden + 1,
                'color' => $this->colorCampo,
            ]);

            // Asegurarse de que el área quede desplegada para ver el nuevo hábito
            if (! in_array($this->seccionPadreId, $this->seccionesActivas)) {
                $this->seccionesActivas[] = $this->seccionPadreId;
            }

            $this->dispatch('cerrarModal', nombreModal: 'modalCampoRv');
            $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Hábito creado!', msnTexto: 'El hábito fue añadido con éxito al área.');
        }
    }

    /**
     * Sube de posición un hábito dentro de su sección.
     */
    public function subirCampo(int $campoId): void
    {
        $campoActual = CampoSeccionRv::findOrFail($campoId);
        $campoAnterior = CampoSeccionRv::where('seccion_rv_id', $campoActual->seccion_rv_id)
            ->where('orden', '<', $campoActual->orden)
            ->orderBy('orden', 'desc')
            ->first();

        if ($campoAnterior) {
            $ordenTemp = $campoActual->orden;
            $campoActual->orden = $campoAnterior->orden;
            $campoAnterior->orden = $ordenTemp;

            $campoActual->save();
            $campoAnterior->save();
        }
    }

    /**
     * Baja de posición un hábito dentro de su sección.
     */
    public function bajarCampo(int $campoId): void
    {
        $campoActual = CampoSeccionRv::findOrFail($campoId);
        $campoSiguiente = CampoSeccionRv::where('seccion_rv_id', $campoActual->seccion_rv_id)
            ->where('orden', '>', $campoActual->orden)
            ->orderBy('orden', 'asc')
            ->first();

        if ($campoSiguiente) {
            $ordenTemp = $campoActual->orden;
            $campoActual->orden = $campoSiguiente->orden;
            $campoSiguiente->orden = $ordenTemp;

            $campoActual->save();
            $campoSiguiente->save();
        }
    }

    /**
     * Elimina un hábito de la sección.
     */
    public function eliminarCampo(int $campoId): void
    {
        $campo = CampoSeccionRv::findOrFail($campoId);
        $seccionId = $campo->seccion_rv_id;
        $campo->delete();

        // Reordenar los hábitos restantes
        $camposRestantes = CampoSeccionRv::where('seccion_rv_id', $seccionId)->orderBy('orden', 'asc')->get();
        foreach ($camposRestantes as $index => $c) {
            $c->orden = $index + 1;
            $c->save();
        }

        $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Eliminado!', msnTexto: 'El hábito fue eliminado correctamente.');
    }

    // =========================================================================
    //  GESTIÓN DE CONFIGURACIÓN GLOBAL (ConfiguracionRv)
    // =========================================================================

    /**
     * Guarda la configuración global de la Rueda de la Vida.
     */
    public function guardarConfiguracion(): void
    {
        $this->validate($this->rulesConfiguracion());

        $config = ConfiguracionRv::first();
        if (! $config) {
            $config = new ConfiguracionRv;
        }

        $config->nombre_general = $this->nombreGeneral;
        $config->label_promedio_general = $this->labelPromedioGeneral;
        $config->promedio_general = $this->promedioGeneralMinimo;
        $config->nombre_habitos = $this->nombreHabitos;
        $config->max_metas = $this->maxMetas;
        $config->max_habitos_por_meta = $this->maxHabitosPorMeta;
        $config->periodicidad = $this->periodicidad;
        $config->save();

        $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Configuración guardada!', msnTexto: 'Los parámetros globales de la Rueda de la Vida fueron actualizados.');
    }

    public function render(): View
    {
        $secciones = SeccionRv::with(['campos' => function ($q) {
            $q->orderBy('orden', 'asc');
        }])->orderBy('orden', 'asc')->get();

        return view('livewire.rueda-de-la-vida.gestionar-rueda-de-la-vida', [
            'secciones' => $secciones,
        ]);
    }
}
