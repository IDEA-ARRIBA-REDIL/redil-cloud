<?php

namespace App\Livewire\Carrito;

use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Carbon\Carbon;

// --- Importar todos los modelos necesarios ---
use App\Models\Actividad;
use App\Models\Configuracion;
use App\Models\HorarioMateriaPeriodo;
use App\Models\ActividadCategoria;
use App\Models\ActividadCarritoCompra;
use App\Models\Compra;
use App\Models\Pago;
use App\Models\Matricula;
use App\Models\MatriculaHorarioMateriaPeriodo as EstadoAcademico;

class EscuelasCarrito extends Component
{
    // --- PROPIEDADES DEL COMPONENTE ---

    // Datos pasados desde el controlador
    public Actividad $actividad;
    public $compraActual;
    public $primeraVez;
    public $categoriasHabilitadas;

    // Propiedades para la lógica interna y la vista
    public $configuracion;
    public $monedasActividad;
    public int $monedaSeleccionada;

    // Propiedades de selección del usuario (vinculadas con Alpine.js)
    public $selectedMateriaPeriodo = null;
    #[Validate('required', message: 'Debes seleccionar una sede.')]
    public $sedeSeleccionada      = null;
    public $tipoAulaSeleccionado  = null;
    #[Validate('required', message: 'Debes seleccionar un horario.')]
    public $horarioSeleccionado   = null;

    // Propiedades para cargar datos dinámicamente en los selects
    public $sedes = [];
    public $tiposAula = [];
    public $horarios = [];

    // Propiedades para Multistep y Formulario
    public $pasoActual = 1;
    public $totalPasos = 2; // Selección Académica y Formulario (si existe)
    public $elementosFormulario = [];
    public $respuestas = [];

    /**
     * CORRECCIÓN: Se restaura el método mount a su versión completa para recibir
     * todos los parámetros necesarios desde el CarritoController.
     */
    public function mount(Actividad $actividad, $compraActual, $primeraVez, $categoriasHabilitadas)
    {
        $this->configuracion = Configuracion::first();
        $this->actividad = $actividad;
        $this->compraActual = $compraActual;
        $this->primeraVez = $primeraVez;
        $this->categoriasHabilitadas = $categoriasHabilitadas ?? collect();

        // Inicializar propiedades para la vista
        $this->monedasActividad = $actividad->monedas;
        $this->monedaSeleccionada = $this->monedasActividad->isNotEmpty() ? $this->monedasActividad->first()->id : 0;

        // Cargar elementos del formulario
        $this->elementosFormulario = $this->actividad->elementos()->orderBy('orden')->get();
        $this->totalPasos = $this->elementosFormulario->isNotEmpty() ? 2 : 1;

        // Si hay una compra previa, intentar cargar respuestas
        if ($this->compraActual) {
            $respuestasPrevias = \App\Models\RespuestaElementoFormulario::where('compra_id', $this->compraActual->id)->get();
            foreach ($respuestasPrevias as $resp) {
                $elementoId = $resp->elemento_formulario_actividad_id;
                $elemento = \App\Models\ElementoFormularioActividad::find($elementoId);
                if (!$elemento) continue;

                $tipo = $elemento->tipoElemento->getRawOriginal('clase') ?? $elemento->tipoElemento->clase;

                switch ($tipo) {
                    case 'corta': $this->respuestas[$elementoId] = $resp->respuesta_texto_corto; break;
                    case 'larga': $this->respuestas[$elementoId] = $resp->respuesta_texto_largo; break;
                    case 'si_no': $this->respuestas[$elementoId] = $resp->respuesta_si_no; break;
                    case 'unica_respuesta': $this->respuestas[$elementoId] = $resp->respuesta_unica; break;
                    case 'multiple_respuesta': $this->respuestas[$elementoId] = explode(",", $resp->respuesta_multiple); break;
                    case 'fecha': $this->respuestas[$elementoId] = $resp->respuesta_fecha; break;
                    case 'numero': $this->respuestas[$elementoId] = $resp->respuesta_numero; break;
                    case 'moneda': $this->respuestas[$elementoId] = $resp->respuesta_moneda; break;
                }
            }
        }

        // Limpiar selecciones dependientes al inicio
        $this->reset(['sedeSeleccionada', 'tipoAulaSeleccionado', 'horarioSeleccionado', 'tiposAula', 'horarios', 'sedes']);
    }

    public function siguientePaso()
    {
        if ($this->pasoActual == 1) {
            $this->validate(); // Valida sede y horario
            if ($this->totalPasos > 1) {
                $this->pasoActual = 2;
                return;
            }
        }

        $this->crearMatricula();
    }

    public function volverPaso()
    {
        if ($this->pasoActual > 1) {
            $this->pasoActual--;
        }
    }

    // --- MÉTODOS PARA CARGA DINÁMICA DE SELECTS ---

    public function loadSedes($materiaPeriodoId)
    {
        $this->selectedMateriaPeriodo = $materiaPeriodoId;
        $this->reset(['sedeSeleccionada', 'tipoAulaSeleccionado', 'horarioSeleccionado', 'tiposAula', 'horarios']);
        $this->sedes = HorarioMateriaPeriodo::getSedesForMateriaPeriodo($materiaPeriodoId)
            ->map(fn($sede) => ['id' => $sede->id, 'nombre' => $sede->nombre])
            ->all();
    }

    public function updatedSedeSeleccionada($value)
    {
        $this->loadTiposAula($value);
    }

    public function loadTiposAula($sedeId)
    {
        $this->sedeSeleccionada = $sedeId ?: null;
        $this->reset(['tipoAulaSeleccionado', 'horarioSeleccionado', 'horarios']);
        if (!$this->sedeSeleccionada) {
            $this->tiposAula = [];
            return;
        }
        $this->tiposAula = HorarioMateriaPeriodo::query()
            ->where('materia_periodo_id', $this->selectedMateriaPeriodo)
            ->whereHas('horarioBase.aula', fn($q) => $q->where('sede_id', $this->sedeSeleccionada))
            ->with('horarioBase.aula.tipo')
            ->get()
            ->pluck('horarioBase.aula.tipo')
            ->unique('id')
            ->map(fn($tipo) => ['id' => $tipo->id, 'nombre' => $tipo->nombre])
            ->values()->all();
    }

    public function setTipoAula($tipoId)
    {
        $this->tipoAulaSeleccionado = $tipoId;
        $this->reset('horarioSeleccionado');
        $this->horarios = HorarioMateriaPeriodo::query()
            ->where('materia_periodo_id', $this->selectedMateriaPeriodo)
            ->where('cupos_disponibles', '>', 0)
            ->whereHas('horarioBase.aula', function ($q) use ($tipoId) {
                $q->where('sede_id', $this->sedeSeleccionada)
                    ->where('tipo_aula_id', $tipoId);
            })
            ->with(['horarioBase.aula', 'maestros.user'])
            ->get()
            ->map(fn($h) => $this->formatHorarioForAlpine($h))
            ->all();
    }

    public function updatedHorarioSeleccionado($value)
    {
        // La única responsabilidad de este método es actualizar el estado.
        if ($value) {
            $this->horarioSeleccionado = $value;
        }
    }

    // --- MÉTODO PRINCIPAL DE ACCIÓN ---

    public function crearMatricula()
    {
        // 1. VALIDACIÓN INICIAL (Si estamos en el paso 1, ya se validó en siguientePaso)
        if ($this->pasoActual == 2) {
            // --- VALIDACIÓN DE CAMPOS OBLIGATORIOS ---
            foreach ($this->elementosFormulario as $elemento) {
                if ($elemento->required && $elemento->tipo_elemento_id != 1) { // 1 suele ser 'informativo'
                    $valor = $this->respuestas[$elemento->id] ?? null;
                    if (empty($valor)) {
                        $this->dispatch('mostrarMensaje', [
                            'msnTitulo' => 'Campo Obligatorio',
                            'msnTexto' => "El campo \"{$elemento->titulo}\" es obligatorio.",
                            'msnIcono' => 'error'
                        ]);
                        return;
                    }
                }
            }
        }

        // 2. INICIO DE LA TRANSACCIÓN
        DB::beginTransaction();
        try {
            // 3. RECOPILAR DATOS
            $usuario = Auth::user();
            $horario = HorarioMateriaPeriodo::with(['materiaPeriodo.periodo', 'materiaPeriodo.materia'])->findOrFail($this->horarioSeleccionado);
            $periodo = $horario->materiaPeriodo->periodo;
            $categoria = ActividadCategoria::where('materia_periodo_id', $horario->materia_periodo_id)
                ->where('actividad_id', $this->actividad->id)
                ->firstOrFail();
            $valorMatricula = $categoria->monedas()->where('moneda_id', $this->monedaSeleccionada)->first()->pivot->valor ?? 0;

            // 4. VALIDACIÓN DE CUPOS (Solo si es primera vez o ha cambiado el horario)
            // Aquí se podría añadir una lógica más fina, pero por simplicidad:
            if (!$this->compraActual && $horario->cupos_disponibles <= 0) {
                $this->dispatch('mostrarMensaje', [
                    'msnTitulo' => 'Sin Cupos',
                    'msnTexto' => 'Lo sentimos, ya no quedan cupos disponibles para el horario seleccionado.',
                    'msnIcono' => 'error'
                ]);
                DB::rollBack();
                return;
            }

            // 5. CREACIÓN O ACTUALIZACIÓN DE COMPRA (USAR UPDATEORCREATE PARA EVITAR DUPLICADOS)
            $compra = Compra::updateOrCreate([
                'user_id' => $usuario->id,
                'actividad_id' => $this->actividad->id,
            ], [
                'moneda_id' => $this->monedaSeleccionada,
                'fecha' => now(),
                'valor' => $valorMatricula,
                'estado' => 1,
                'nombre_completo_comprador' => $usuario->nombre(3),
                'identificacion_comprador' => $usuario->identificacion,
                'telefono_comprador' => $usuario->telefono_movil ?: '111111111',
                'email_comprador' => $usuario->email,
                'metodo_pago_id' => 0,
                'destinatario_id' => $this->sedeSeleccionada
            ]);

            // 6. CREACIÓN O ACTUALIZACIÓN DEL PAGO
            $pago = Pago::updateOrCreate([
                'compra_id' => $compra->id,
                'estado_pago_id' => 1, // Iniciado
            ], [
                'moneda_id' => $this->monedaSeleccionada,
                'valor' => $valorMatricula,
                'fecha' => now(),
            ]);

            // 7. MATRÍCULA Y ESTADO ACADÉMICO
            $matricula = Matricula::updateOrCreate([
                'user_id' => $usuario->id,
                'periodo_id' => $periodo->id,
                'horario_materia_periodo_id' => $horario->id,
            ], [
                'referencia_pago' => $pago->id,
                'estado_pago_matricula' => 'pendiente',
                'fecha_matricula' => now()->toDateString(),
                'valor_a_pagar' => $valorMatricula,
                'material_sede_id' => $this->sedeSeleccionada,
                'escuela_id' => $periodo->escuela_id,
            ]);

            EstadoAcademico::updateOrCreate([
                'user_id' => $usuario->id,
                'horario_materia_periodo_id' => $horario->id,
                'matricula_id' => $matricula->id,
            ], [
                'periodo_id' => $periodo->id,
                'estado_aprobacion' => 'cursando',
            ]);

            // 8. ÍTEM EN EL CARRITO
            ActividadCarritoCompra::updateOrCreate([
                'compra_id' => $compra->id,
                'user_id' => $usuario->id,
            ], [
                'actividad_id' => $this->actividad->id,
                'actividad_categoria_id' => $categoria->id,
                'cantidad' => 1,
                'precio' => $valorMatricula,
                'pago_id' => $pago->id,
                'fecha' => now()->toDateString(),
            ]);

            // 9. GUARDAR RESPUESTAS DEL FORMULARIO
            foreach ($this->respuestas as $elementoId => $valor) {
                if (empty($valor)) continue;

                $elemento = \App\Models\ElementoFormularioActividad::find($elementoId);
                if (!$elemento) continue;

                $respuesta = \App\Models\RespuestaElementoFormulario::updateOrCreate([
                    'compra_id' => $compra->id,
                    'elemento_formulario_actividad_id' => $elementoId,
                ], [
                    'inscripcion_id' => null, // En escuelas a veces no hay inscripción directa o se liga a la matrícula
                    'user_id' => $usuario->id
                ]);

                // Switch de guardado (simplificado para que quepa en un chunk, basándonos en el tipo de elemento)
                $tipo = $elemento->tipoElemento->getRawOriginal('clase') ?? $elemento->tipoElemento->clase;
                switch ($tipo) {
                    case 'corta': $respuesta->respuesta_texto_corto = $valor; break;
                    case 'larga': $respuesta->respuesta_texto_largo = $valor; break;
                    case 'si_no': $respuesta->respuesta_si_no = $valor; break;
                    case 'unica_respuesta': $respuesta->respuesta_unica = $valor; break;
                    case 'multiple_respuesta': $respuesta->respuesta_multiple = is_array($valor) ? implode(",", $valor) : $valor; break;
                    case 'fecha': $respuesta->respuesta_fecha = $valor; break;
                    case 'numero': $respuesta->respuesta_numero = $valor; break;
                    case 'moneda': $respuesta->respuesta_moneda = $valor; break;
                }
                $respuesta->save();
            }

            // 10. ACTUALIZAR CUPOS (Solo si es registro nuevo)
            if ($matricula->wasRecentlyCreated) {
                $horario->decrement('cupos_disponibles');
            }

            // 11. CONFIRMAR TRANSACCIÓN
            DB::commit();

            // 12. REDIRIGIR
            if ($this->configuracion->envio_material) {
                return redirect()->route('carrito.destinatario', ['actividad' => $this->actividad]);
            } else {
                return redirect()->route('carrito.checkout', ['compra' => $compra, 'actividad' => $this->actividad]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en EscuelasCarrito: ' . $e->getMessage());
            $this->dispatch('mostrarMensaje', [
                'msnTitulo' => 'Error Inesperado',
                'msnTexto' => 'Error técnico: ' . $e->getMessage(),
                'msnIcono' => 'error'
            ]);
        }
    }

    // --- MÉTODOS AUXILIARES ---

    protected function formatHorarioForAlpine($horario): array
    {
        $dias = [1 => 'Lun', 2 => 'Mar', 3 => 'Mié', 4 => 'Jue', 5 => 'Vie', 6 => 'Sáb', 7 => 'Dom'];
        $dia = $dias[$horario->horarioBase->dia] ?? 'N/D';
        $ini = Carbon::parse($horario->horarioBase->hora_inicio)->format('h:i A');
        $fin = Carbon::parse($horario->horarioBase->hora_fin)->format('h:i A');
        $aula = $horario->horarioBase->aula->nombre ?? 'N/D';
        $maestro = $horario->maestros->first()?->user->nombre(2) ?? 'Por asignar';

        $label = "{$dia} | {$ini} - {$fin} | Aula: {$aula} | Maestro: {$maestro}";

        return ['id' => $horario->id, 'label' => $label];
    }

    public function setMoneda(int $monedaId): void
    {
        $this->monedaSeleccionada = $monedaId;
    }

    public function precioCategoria($categoria): float
    {
        return $categoria->monedas->firstWhere('id', $this->monedaSeleccionada)?->pivot->valor ?? 0.0;
    }

    public function render()
    {
        return view('livewire.carrito.escuelas-carrito');
    }
}
