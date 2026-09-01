<?php

namespace App\Livewire\Hitos;

use App\Models\Actividad;
use App\Models\Escuela;
use App\Models\EstadoCivil;
use App\Models\EstadoPasoCrecimientoUsuario;
use App\Models\EstadoTareaConsolidacion;
use App\Models\Hito;
use App\Models\HitoFoto;
use App\Models\Materia;
use App\Models\NivelEscuela;
use App\Models\PasoCrecimiento;
use App\Models\RangoEdad;
use App\Models\Sede;
use App\Models\TareaConsolidacion;
use App\Models\TipoGrupo;
use App\Models\TipoHito;
use App\Models\TipoUsuario;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class CrearEditarHito extends Component
{
    use WithFileUploads;

    public $hitoId = null;

    // Panel 1: Información General
    public $tipo_hito_id = null;

    public $titulo = '';

    public $descripcion = '';

    public $mensaje_usuario = '';

    public $fecha_evento = '';

    public $activo = true;

    public $requiere_sesion = true;

    // Panel 2: Multimedia y Fotos
    public $portada;

    public $portadaActual = null;

    public $video_url = '';

    public $permite_fotos_usuario = true;

    public $max_fotos_usuario = 3;

    public $max_peso_kb = 2048;

    public $fotosAdmin = [];

    public $fotosAdminActuales = [];

    // Panel 3: Tipo y Activación / Triggers / Asignación Manual (Solo al Editar)
    public $actividad_id = null;

    public $requiere_asistencia = false;

    public $trigger_modulo = '';

    public $trigger_tipo = '';

    public $trigger_paso_crecimiento_id = null;

    public $trigger_estado_paso_id = null;

    public $trigger_tarea_consolidacion_id = null;

    public $trigger_estado_tarea_id = null;

    public $trigger_escuela_id = null;

    public $trigger_nivel_id = null;

    public $trigger_materia_id = null;

    public $trigger_tipo_grupo_id = null;

    public $usuariosManuales = [];

    // Panel 4: Restricciones y Segmentación (Solo al Editar)
    public $sedesSeleccionadas = [];

    public $estadosCivilesSeleccionados = [];

    public $rangosEdadSeleccionados = [];

    public $tiposUsuariosSeleccionados = [];

    public $grupoTiposSeleccionados = [];

    protected function rules()
    {
        return [
            'titulo' => 'required|string|max:150',
            'tipo_hito_id' => 'required|exists:tipo_hitos,id',
            'descripcion' => 'nullable|string',
            'mensaje_usuario' => 'nullable|string|max:1000',
            'fecha_evento' => 'nullable|date',
            'portada' => 'nullable|image|max:5120',
            'video_url' => 'nullable|url|max:500',
            'max_fotos_usuario' => 'required|integer|min:1|max:10',
            'max_peso_kb' => 'required|integer|min:512|max:10240',
        ];
    }

    public function mount($hitoId = null)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        if ($rolActivo) {
            $rolActivo->verificacionDelPermiso($hitoId ? 'hitos.editar' : 'hitos.crear');
        }

        // Tipo por defecto (general si existe)
        if (! $hitoId) {
            $tipoGeneral = TipoHito::where('slug', 'general')->first();
            $this->tipo_hito_id = $tipoGeneral ? $tipoGeneral->id : TipoHito::first()?->id;
            $this->fecha_evento = now()->toDateString();
        }

        if ($hitoId) {
            $hito = Hito::with(['fotosAdmin', 'sedes', 'estadosCiviles', 'rangosEdad', 'tiposUsuarios', 'grupoTipos', 'usuariosAsignados'])->findOrFail($hitoId);
            $this->hitoId = $hito->id;
            $this->titulo = $hito->titulo;
            $this->tipo_hito_id = $hito->tipo_hito_id;
            $this->descripcion = $hito->descripcion;
            $this->mensaje_usuario = $hito->mensaje_usuario;
            $this->fecha_evento = $hito->fecha_evento ? $hito->fecha_evento->format('Y-m-d') : '';
            $this->video_url = $hito->video_url;
            $this->portadaActual = $hito->portada_path;
            $this->actividad_id = $hito->actividad_id;
            $this->requiere_asistencia = (bool) $hito->requiere_asistencia;
            $this->trigger_modulo = $hito->trigger_modulo ?? '';
            $this->trigger_tipo = $hito->trigger_tipo ?? '';
            $this->permite_fotos_usuario = (bool) $hito->permite_fotos_usuario;
            $this->max_fotos_usuario = $hito->max_fotos_usuario ?? 3;
            $this->max_peso_kb = $hito->max_peso_kb ?? 2048;
            $this->requiere_sesion = (bool) $hito->requiere_sesion;
            $this->activo = (bool) $hito->activo;

            $config = $hito->trigger_config ?? [];
            $this->trigger_paso_crecimiento_id = $config['paso_crecimiento_id'] ?? null;
            $this->trigger_estado_paso_id = $config['estado_id'] ?? null;
            $this->trigger_tarea_consolidacion_id = $config['tarea_consolidacion_id'] ?? null;
            $this->trigger_estado_tarea_id = $config['estado_id'] ?? null;
            $this->trigger_escuela_id = $config['escuela_id'] ?? null;
            $this->trigger_nivel_id = $config['nivel_id'] ?? null;
            $this->trigger_materia_id = $config['materia_id'] ?? null;
            $this->trigger_tipo_grupo_id = $config['tipo_grupo_id'] ?? null;

            $this->sedesSeleccionadas = $hito->sedes->pluck('id')->toArray();
            $this->estadosCivilesSeleccionados = $hito->estadosCiviles->pluck('id')->toArray();
            $this->rangosEdadSeleccionados = $hito->rangosEdad->pluck('id')->toArray();
            $this->tiposUsuariosSeleccionados = $hito->tiposUsuarios->pluck('id')->toArray();
            $this->grupoTiposSeleccionados = $hito->grupoTipos->pluck('id')->toArray();
            $this->fotosAdminActuales = $hito->fotosAdmin;

            // Cargar usuarios asignados manualmente
            $this->usuariosManuales = $hito->usuariosAsignados->map(function ($u) {
                return [
                    'user_id' => $u->id,
                    'nombre' => $u->nombre(3),
                    'foto' => $u->foto,
                    'foto_url' => $u->foto ? tenant_asset('img/usuario/fotos/'.$u->foto) : null,
                    'iniciales' => $u->inicialesNombre(),
                    'fecha' => $u->pivot->fecha ? Carbon::parse($u->pivot->fecha)->toDateString() : now()->toDateString(),
                    'nota' => $u->pivot->nota_personalizada ?? '',
                ];
            })->toArray();
        }
    }

    #[On('usuario-seleccionado')]
    public function onUsuarioSeleccionado($id, $buscadorId = null)
    {
        if ($id && ! in_array($id, array_column($this->usuariosManuales, 'user_id'))) {
            $user = User::find($id);
            if ($user) {
                $this->usuariosManuales[] = [
                    'user_id' => $user->id,
                    'nombre' => $user->nombre(3),
                    'foto' => $user->foto,
                    'foto_url' => $user->foto ? tenant_asset('img/usuario/fotos/'.$user->foto) : null,
                    'iniciales' => $user->inicialesNombre(),
                    'fecha' => now()->toDateString(),
                    'nota' => '',
                ];
            }
        }
    }

    public function eliminarUsuarioManual($index)
    {
        if (isset($this->usuariosManuales[$index])) {
            unset($this->usuariosManuales[$index]);
            $this->usuariosManuales = array_values($this->usuariosManuales);
        }
    }

    public function updatedTipoHitoId()
    {
        $tipo = TipoHito::find($this->tipo_hito_id);
        if ($tipo) {
            $this->permite_fotos_usuario = $tipo->permite_fotos_usuario;
            if (! $tipo->requiere_trigger) {
                $this->trigger_modulo = '';
                $this->trigger_tipo = '';
            }
            if (! $tipo->requiere_actividad) {
                $this->actividad_id = null;
                $this->requiere_asistencia = false;
            }
        }
    }

    public function updatedTriggerModulo()
    {
        $this->trigger_tipo = '';
        $this->trigger_paso_crecimiento_id = null;
        $this->trigger_estado_paso_id = null;
        $this->trigger_tarea_consolidacion_id = null;
        $this->trigger_estado_tarea_id = null;
        $this->trigger_escuela_id = null;
        $this->trigger_nivel_id = null;
        $this->trigger_materia_id = null;
        $this->trigger_tipo_grupo_id = null;

        if ($this->trigger_modulo === 'pasos_crecimiento' || $this->trigger_modulo === 'tareas_consolidacion') {
            $this->trigger_tipo = 'cambio_estado';
        }
    }

    public function updatedTriggerEscuelaId()
    {
        $this->trigger_nivel_id = null;
        $this->trigger_materia_id = null;
    }

    public function eliminarFotoAdmin($fotoId)
    {
        $foto = HitoFoto::find($fotoId);
        if ($foto && $foto->hito_id == $this->hitoId) {
            Storage::disk('public')->delete('img/hitos/fotos/'.$foto->ruta);
            $foto->delete();
            $this->fotosAdminActuales = HitoFoto::where('hito_id', $this->hitoId)->where('es_admin', true)->get();
            $this->dispatch('msn', [
                'msnIcono' => 'success',
                'msnTitulo' => '¡Foto eliminada!',
                'msnTexto' => 'La foto ha sido eliminada correctamente.',
            ]);
        }
    }

    public function guardar()
    {
        $this->validate();

        $tipoSeleccionado = TipoHito::findOrFail($this->tipo_hito_id);
        $esNuevo = $this->hitoId === null;

        $data = [
            'tipo_hito_id' => $this->tipo_hito_id,
            'user_id' => auth()->id() ?? 1,
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'mensaje_usuario' => $this->mensaje_usuario,
            'fecha_evento' => $this->fecha_evento ?: null,
            'video_url' => $this->video_url,
            'permite_fotos_usuario' => $this->permite_fotos_usuario,
            'max_fotos_usuario' => $this->max_fotos_usuario,
            'max_peso_kb' => $this->max_peso_kb,
            'requiere_sesion' => $this->requiere_sesion,
            'activo' => $this->activo,
        ];

        // Lógica según el tipo de hito (se procesa al editar)
        if (! $esNuevo) {
            if ($tipoSeleccionado->requiere_actividad || $tipoSeleccionado->slug === 'actividad') {
                $data['actividad_id'] = $this->actividad_id;
                $data['requiere_asistencia'] = $this->requiere_asistencia;
                $data['trigger_modulo'] = null;
                $data['trigger_tipo'] = null;
                $data['trigger_config'] = null;
            } elseif ($tipoSeleccionado->requiere_trigger || $tipoSeleccionado->slug === 'automatico') {
                $data['actividad_id'] = null;
                $data['requiere_asistencia'] = false;
                $data['trigger_modulo'] = $this->trigger_modulo;
                $data['trigger_tipo'] = $this->trigger_tipo;
                $data['trigger_config'] = $this->armarConfigTrigger();
            } else {
                $data['actividad_id'] = null;
                $data['requiere_asistencia'] = false;
                $data['trigger_modulo'] = null;
                $data['trigger_tipo'] = null;
                $data['trigger_config'] = null;
            }
        }

        // Subida de Portada
        if ($this->portada) {
            $nombrePortada = 'portada-'.Str::slug($this->titulo).'-'.time().'.'.$this->portada->getClientOriginalExtension();
            $this->portada->storeAs('img/hitos/portadas', $nombrePortada, 'public');
            $data['portada_path'] = $nombrePortada;
        }

        if (! $esNuevo) {
            $hito = Hito::findOrFail($this->hitoId);
            if ($this->portada && $hito->portada_path) {
                Storage::disk('public')->delete('img/hitos/portadas/'.$hito->portada_path);
            }
            $hito->update($data);
        } else {
            $hito = Hito::create($data);
        }

        // Sincronizar según el tipo de hito
        if (! $esNuevo) {
            if ($tipoSeleccionado->slug === 'manual') {
                // Sincronizar usuarios asignados con fecha y nota personalizada
                $syncData = [];
                foreach ($this->usuariosManuales as $item) {
                    $syncData[$item['user_id']] = [
                        'fecha' => ! empty($item['fecha']) ? $item['fecha'] : now()->toDateString(),
                        'nota_personalizada' => ! empty($item['nota']) ? $item['nota'] : null,
                        'origen_tipo' => 'manual',
                        'origen_id' => auth()->id(),
                        'asignado_por' => auth()->id(),
                        'asistio' => true,
                    ];
                }
                $hito->usuariosAsignados()->sync($syncData);

                // En hitos manuales se limpian las restricciones demográficas para evitar bloqueos
                $hito->sedes()->detach();
                $hito->estadosCiviles()->detach();
                $hito->rangosEdad()->detach();
                $hito->tiposUsuarios()->detach();
                $hito->grupoTipos()->detach();
            } else {
                // Sincronizar restricciones de segmentación para tipos General, Actividad o Automático
                $hito->sedes()->sync($this->sedesSeleccionadas);
                $hito->estadosCiviles()->sync($this->estadosCivilesSeleccionados);
                $hito->rangosEdad()->sync($this->rangosEdadSeleccionados);
                $hito->tiposUsuarios()->sync($this->tiposUsuariosSeleccionados);
                $hito->grupoTipos()->sync($this->grupoTiposSeleccionados);
            }
        }

        // Subida de fotos administrativas adicionales
        if (! empty($this->fotosAdmin)) {
            $ordenActual = $hito->fotosAdmin()->count();
            foreach ($this->fotosAdmin as $index => $fotoFile) {
                $nombreFoto = 'hito-'.$hito->id.'-admin-'.time().'-'.$index.'.'.$fotoFile->getClientOriginalExtension();
                $fotoFile->storeAs('img/hitos/fotos', $nombreFoto, 'public');
                HitoFoto::create([
                    'hito_id' => $hito->id,
                    'user_id' => auth()->id(),
                    'ruta' => $nombreFoto,
                    'orden' => $ordenActual + $index,
                    'es_admin' => true,
                    'aprobada' => true,
                ]);
            }
        }

        // Si fue una creación: redirigir a la vista de edición para continuar con triggers y restricciones
        if ($esNuevo) {
            session()->flash('msn', [
                'tipo' => 'success',
                'mensaje' => '¡Hito creado exitosamente! Ahora puedes configurar los disparadores (triggers) y las restricciones de audiencia más abajo.',
            ]);

            return redirect()->route('hitos.editar', $hito->id);
        }

        // Si fue una edición: guardar y mostrar mensaje de éxito
        session()->flash('msn', [
            'tipo' => 'success',
            'mensaje' => 'Hito y configuraciones actualizadas exitosamente.',
        ]);

        return redirect()->route('hitos.index');
    }

    private function armarConfigTrigger(): ?array
    {
        $config = [];

        if ($this->trigger_modulo === 'pasos_crecimiento') {
            $this->trigger_tipo = 'cambio_estado';
            $config['paso_crecimiento_id'] = $this->trigger_paso_crecimiento_id;
            $config['estado_id'] = $this->trigger_estado_paso_id;
        } elseif ($this->trigger_modulo === 'tareas_consolidacion') {
            $this->trigger_tipo = 'cambio_estado';
            $config['tarea_consolidacion_id'] = $this->trigger_tarea_consolidacion_id;
            $config['estado_id'] = $this->trigger_estado_tarea_id;
        } elseif ($this->trigger_modulo === 'escuelas') {
            $config['escuela_id'] = $this->trigger_escuela_id;
            $escuela = Escuela::find($this->trigger_escuela_id);
            if ($escuela && $escuela->tipo_matricula === 'niveles_agrupados') {
                $this->trigger_tipo = 'aprobacion_nivel';
                $config['nivel_id'] = $this->trigger_nivel_id;
            } else {
                $this->trigger_tipo = 'aprobacion_materia';
                $config['materia_id'] = $this->trigger_materia_id;
            }
        } elseif ($this->trigger_modulo === 'grupos') {
            $config['tipo_grupo_id'] = $this->trigger_tipo_grupo_id;
        }

        return empty($config) ? null : $config;
    }

    public function render()
    {
        $tiposHito = TipoHito::activos()->orderBy('nombre')->get();
        $tipoSeleccionado = TipoHito::find($this->tipo_hito_id);

        $actividades = Actividad::orderBy('nombre')->get();
        $pasosCrecimiento = PasoCrecimiento::orderBy('nombre')->get();
        $estadosPasos = EstadoPasoCrecimientoUsuario::orderBy('puntaje')->get();
        $tareasConsolidacion = TareaConsolidacion::orderBy('nombre')->get();
        $estadosTareas = EstadoTareaConsolidacion::orderBy('puntaje')->get();
        $escuelas = Escuela::orderBy('nombre')->get();
        $escuelaSeleccionada = $this->trigger_escuela_id ? Escuela::find($this->trigger_escuela_id) : null;
        $niveles = ($escuelaSeleccionada && $escuelaSeleccionada->tipo_matricula === 'niveles_agrupados')
            ? NivelEscuela::where('escuela_id', $this->trigger_escuela_id)->orderBy('orden')->get()
            : collect();
        $materias = ($escuelaSeleccionada && $escuelaSeleccionada->tipo_matricula !== 'niveles_agrupados')
            ? Materia::where('escuela_id', $this->trigger_escuela_id)->orderBy('nombre')->get()
            : collect();
        $tiposGrupo = TipoGrupo::orderBy('nombre')->get();

        // Opciones de segmentación
        $sedes = Sede::orderBy('nombre')->get();
        $estadosCiviles = EstadoCivil::orderBy('nombre')->get();
        $rangosEdad = RangoEdad::orderBy('nombre')->get();
        $tiposUsuario = TipoUsuario::orderBy('nombre')->get();

        return view('livewire.hitos.crear-editar-hito', compact(
            'tiposHito', 'tipoSeleccionado', 'actividades', 'pasosCrecimiento',
            'estadosPasos', 'tareasConsolidacion', 'estadosTareas', 'escuelas',
            'niveles', 'materias', 'tiposGrupo', 'sedes', 'estadosCiviles',
            'rangosEdad', 'tiposUsuario'
        ));
    }
}
