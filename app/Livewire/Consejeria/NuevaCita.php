<?php

namespace App\Livewire\Consejeria;

use App\Events\CitaAgendadaConsejeria;
use App\Mail\DefaultMail;
use App\Mail\NotificacionCitaConsejero;
use App\Mail\NotificacionCitaPaciente;
use App\Models\CitaConsejeria;
use App\Models\TipoConsejeria;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Consejero;
use App\Models\Configuracion;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Collection;

use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\GoogleCalendar\Event as GoogleEvent;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use \stdClass;

class NuevaCita extends Component
{

    public $pacienteId;
    public ?User $paciente = null; // Para mostrar el nombre en la vista
    public $configuracion;

    // Modelados a los campos del formulario
    public $tipoDeCita = '';
    public $medioDeLaCita = '';
    public $consejeroId = null;
    public $horarioSeleccionado = '';
    public $notas_paciente = '';

    // Datos para poblar los campos
    public $tiposDeConsejeria = [];
    public $horariosDisponibles = [];

    /**
     * Hook de montaje (mount).
     * Se ejecuta cuando el componente se inicializa.
     * Perfecto para cargar datos iniciales.
     */
    public function mount($pacienteId = null)
    {
      $this->configuracion = Configuracion::first();
      $this->pacienteId = $pacienteId;

      // 2. (Opcional) Carga el objeto paciente para mostrar su nombre
      if ($this->pacienteId) {
          $this->paciente = User::find($this->pacienteId);
      }
      // Cargamos los tipos de consejería desde la BD
      $this->tiposDeConsejeria = TipoConsejeria::orderBy('nombre')->get();
    }

    // Búsqueda de Consejeros
    public $busquedaConsejero = '';
    public $consejerosEncontrados = [];
    public $consejeroSeleccionado = null; // Objeto completo del consejero para mostrar en la vista
    public $cantidadConsejeros = 10; // Límite inicial de carga

    /**
     * Realiza la búsqueda de consejeros.
     */
    public function updatedBusquedaConsejero()
    {
        $this->cantidadConsejeros = 10; // Resetear paginación al buscar
        $this->obtenerConsejeros();
    }

    /**
     * Carga más consejeros (Scroll Infinito)
     */
    public function cargarMasConsejeros()
    {
        $this->cantidadConsejeros += 10;
        $this->obtenerConsejeros();
    }

    /**
     * Método centralizado para obtener consejeros
     */
    public function obtenerConsejeros()
    {
        // Si no hay medio seleccionado, no buscamos nada
        if (!$this->medioDeLaCita) {
            $this->consejerosEncontrados = [];
            return;
        }

        $busqueda = $this->busquedaConsejero;
        $tipoConsejeriaId = $this->tipoDeCita;
        $sedeId = $this->paciente ? $this->paciente->sede_id : null;

        $query = User::query()
            ->whereHas('consejero', function ($q) use ($tipoConsejeriaId, $sedeId) {
                $q->where('activo', true);
                
                // Filtrar por medio de cita
                if ($this->medioDeLaCita == 1) { // Presencial
                    $q->where('atencion_presencial', true);
                } elseif ($this->medioDeLaCita == 2) { // Virtual
                    $q->where('atencion_virtual', true);
                }

                // 1. Filtro por Tipo de Consejería
                // Si se seleccionó un tipo, mostrar consejeros que lo tengan asignado O que no tengan ninguno (atienden todos)
                if ($tipoConsejeriaId) {
                    $q->where(function($sub) use ($tipoConsejeriaId) {
                        $sub->whereHas('tipoConsejerias', function($tc) use ($tipoConsejeriaId) {
                            $tc->where('tipo_consejerias.id', $tipoConsejeriaId);
                        })
                        ->orWhereDoesntHave('tipoConsejerias');
                    });
                }

                // 2. Filtro por Sede del Paciente
                // Si el paciente tiene sede, mostrar consejeros de esa sede O que no tengan ninguna (atienden todas)
                if ($sedeId) {
                    $q->where(function($sub) use ($sedeId) {
                        $sub->whereHas('sedes', function($s) use ($sedeId) {
                            $s->where('sedes.id', $sedeId);
                        })
                        ->orWhereDoesntHave('sedes');
                    });
                }
            });

        // Aplicar filtro de texto si existe
        if (strlen($busqueda) >= 2) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('primer_nombre', 'like', "%{$busqueda}%")
                  ->orWhere('segundo_nombre', 'like', "%{$busqueda}%")
                  ->orWhere('primer_apellido', 'like', "%{$busqueda}%")
                  ->orWhere('segundo_apellido', 'like', "%{$busqueda}%")
                  ->orWhere('email', 'like', "%{$busqueda}%")
                  ->orWhere('identificacion', 'like', "%{$busqueda}%");
            });
        }

        $this->consejerosEncontrados = $query->limit($this->cantidadConsejeros)->get();
    }

    /**
     * Selecciona un consejero de la lista.
     */
    public function seleccionarConsejero($id)
    {
        $this->consejeroId = $id;
        $this->consejeroSeleccionado = User::find($id);
        
        // Limpiar búsqueda
        $this->busquedaConsejero = '';
        $this->consejerosEncontrados = [];

        // Cargar horarios
        $this->cargarHorarios();
    }

    /**
     * Quita el consejero seleccionado.
     */
    public function limpiarConsejero()
    {
        // Limpiamos las variables
        $this->reset(['consejeroId', 'consejeroSeleccionado', 'horarioSeleccionado', 'horariosDisponibles']);
        
        // CORRECCIÓN: Volvemos a llenar la lista predeterminada
        // para que esté lista si el usuario da clic en el input de nuevo.
        $this->obtenerConsejeros(); 
    }

    public function cargarHorarios()
    {
        // 1. Reseteo y Validación Inicial
        $this->reset(['horarioSeleccionado']);
        $this->horariosDisponibles = [];

        if (!$this->consejeroId || !$this->medioDeLaCita) {
            return;
        }

        // El 'consejeroId' que recibimos es un 'user_id'
        $consejero = Consejero::where('user_id', $this->consejeroId)->first();

        // Si no se encuentra el perfil de consejero o no está activo
        if (!$consejero || !$consejero->activo) {
            return;
        }

        // Validar si el consejero atiende por el medio seleccionado
        if ($this->medioDeLaCita === 'presencial' && !$consejero->atencion_presencial) {
            return;
        }
        if ($this->medioDeLaCita === 'virtual' && !$consejero->atencion_virtual) {
            return;
        }

        // 2. Definir Reglas y Rango de Búsqueda
        $duracionCita = $consejero->duracion_cita_minutos;
        $buffer = $consejero->buffer_entre_citas_minutos;
        $duracionSlot = $duracionCita + $buffer; // Duración total de un "bloque" de cita

        $ahora = Carbon::now();
        $fechaInicio = $ahora->copy()->addDays($consejero->dias_minimos_antelacion)->startOfDay();
        $fechaFin = $ahora->copy()->addDays($consejero->dias_maximos_futuro)->endOfDay();

        // 3. Obtener TODA la data necesaria en pocas consultas
        $horariosHabituales = $consejero->horariosHabituales()->get()->groupBy('dia_semana');

        $horariosAdicionales = $consejero->horariosAdicionales()
            ->where('fecha_inicio', '<=', $fechaFin)
            ->where('fecha_fin', '>=', $fechaInicio)
            ->get();

        $horariosBloqueados = $consejero->horariosBloqueados()
            ->where('fecha_inicio', '<=', $fechaFin)
            ->where('fecha_fin', '>=', $fechaInicio)
            ->get();

        // --- INICIO DEL ESPACIO PARA CITAS FUTURAS ---
        // TODO: Cuando tengas el modelo 'Cita', descomenta y ajusta esta consulta.
         $citasAgendadas = CitaConsejeria::where('consejero_id', $consejero->id)
            ->where('fecha_hora_inicio', '<=', $fechaFin)
            ->where('fecha_hora_fin', '>=', $fechaInicio)
            ->get();
        //$citasAgendadas = new Collection(); // De momento, una colección vacía
        // --- FIN DEL ESPACIO PARA CITAS FUTURAS ---


        // 4. Procesar el rango día por día
        $periodo = CarbonPeriod::create($fechaInicio, '1 day', $fechaFin);
        $slotsDisponibles = [];

        foreach ($periodo as $dia) {
            $diaSemana = $dia->dayOfWeekIso; // 1 = Lunes, 7 = Domingo
            $diaStr = $dia->format('Y-m-d');
            $slotsDelDia = [];

            // a) Obtener todos los bloques de "Disponibilidad" (Habitual + Adicional)
            $potencialesBloques = new Collection();

            // Añadir bloques habituales
            if (isset($horariosHabituales[$diaSemana])) {
                foreach ($horariosHabituales[$diaSemana] as $hab) {
                    $potencialesBloques->push([
                        'inicio' => $dia->copy()->setTimeFromTimeString($hab->hora_inicio),
                        'fin' => $dia->copy()->setTimeFromTimeString($hab->hora_fin)
                    ]);
                }
            }

            // Añadir bloques adicionales (y "clamp" al día actual)
            foreach ($horariosAdicionales as $add) {
                $addInicio = Carbon::parse($add->fecha_inicio);
                $addFin = Carbon::parse($add->fecha_fin);
                if ($addInicio <= $dia->copy()->endOfDay() && $addFin >= $dia->copy()->startOfDay()) {
                    $potencialesBloques->push([
                        'inicio' => $addInicio->isAfter($dia->copy()->startOfDay()) ? $addInicio : $dia->copy()->startOfDay(),
                        'fin' => $addFin->isBefore($dia->copy()->endOfDay()) ? $addFin : $dia->copy()->endOfDay()
                    ]);
                }
            }

            if ($potencialesBloques->isEmpty()) {
                continue; // Siguiente día
            }

            // b) Obtener todos los bloques "Ocupados" (Bloqueos + Citas)
            $todosLosBloqueos = new Collection();

            foreach ($horariosBloqueados as $block) {
                $blockInicio = Carbon::parse($block->fecha_inicio);
                $blockFin = Carbon::parse($block->fecha_fin);
                if ($blockInicio <= $dia->copy()->endOfDay() && $blockFin >= $dia->copy()->startOfDay()) {
                    $todosLosBloqueos->push([
                        'inicio' => $blockInicio->isAfter($dia->copy()->startOfDay()) ? $blockInicio : $dia->copy()->startOfDay(),
                        'fin' => $blockFin->isBefore($dia->copy()->endOfDay()) ? $blockFin : $dia->copy()->endOfDay()
                    ]);
                }
            }

            // TODO: Procesar $citasAgendadas de forma similar
            foreach ($citasAgendadas as $cita) {
                // IMPORTANTE: Una cita bloquea el tiempo de (duracion + buffer)
                // Asumimos que 'fecha_hora_fin' NO incluye el buffer.
                $citaInicio = Carbon::parse($cita->fecha_hora_inicio);
                $citaFin = Carbon::parse($cita->fecha_hora_fin)->addMinutes($buffer); // Añadimos buffer

                if ($citaInicio <= $dia->copy()->endOfDay() && $citaFin >= $dia->copy()->startOfDay()) {
                    $todosLosBloqueos->push([
                        'inicio' => $citaInicio->isAfter($dia->copy()->startOfDay()) ? $citaInicio : $dia->copy()->startOfDay(),
                        'fin' => $citaFin->isBefore($dia->copy()->endOfDay()) ? $citaFin : $dia->copy()->endOfDay()
                    ]);
                }
            }

            // 5. Generar Slots y Filtrar
            foreach ($potencialesBloques as $bloque) {
                $slotActual = $bloque['inicio']->copy();
                $finBloque = $bloque['fin']->copy();

                while ($slotActual->copy()->addMinutes($duracionCita) <= $finBloque) {
                    $slotInicio = $slotActual->copy();
                    $slotFin = $slotInicio->copy()->addMinutes($duracionCita);

                    // Check 1: ¿El slot está en el futuro? (Respecto a la hora actual)
                    if ($slotInicio < $ahora) {
                        $slotActual->addMinutes($duracionSlot); // Siguiente slot
                        continue;
                    }

                    // Check 2: ¿El slot (cita + buffer) choca con algún bloqueo?
                    $slotFinConBuffer = $slotInicio->copy()->addMinutes($duracionSlot);
                    $estaBloqueado = false;
                    foreach ($todosLosBloqueos as $bloqueo) {
                        // Comprobar solapamiento: (InicioA < FinB) y (FinA > InicioB)
                        if ($slotInicio < $bloqueo['fin'] && $slotFinConBuffer > $bloqueo['inicio']) {
                            $estaBloqueado = true;
                            break;
                        }
                    }

                    // Si pasa los filtros, lo añadimos
                    if (!$estaBloqueado) {
                        $horaStr = $slotInicio->format('H:i');
                        if (!in_array($horaStr, $slotsDelDia)) {
                            $slotsDelDia[] = $horaStr;
                        }
                    }

                    // Avanzamos al siguiente slot
                    $slotActual->addMinutes($duracionSlot);
                }
            }

            if (!empty($slotsDelDia)) {
                sort($slotsDelDia); // Ordenar las horas
                $slotsDisponibles[$diaStr] = $slotsDelDia;
            }
        }

        // 6. Asignar resultados
        $this->horariosDisponibles = $slotsDisponibles;

        $this->reset('horarioSeleccionado');

        // 2. Auto-seleccionamos el primer día disponible (si existe)
        if (!empty($slotsDisponibles)) {
            $this->diaSeleccionado = array_key_first($this->horariosDisponibles);
        } else {
            $this->diaSeleccionado = null;
        }
    }

    // --- HOOKS DE ACTUALIZACIÓN ---

    /**
     * Se ejecuta cada vez que $tipoDeCita cambia.
     * Usamos esto para resetear los pasos siguientes.
     */
    public function updatedTipoDeCita($value)
    {
        $this->reset(['medioDeLaCita', 'consejeroId', 'consejeroSeleccionado', 'busquedaConsejero', 'consejerosEncontrados', 'horarioSeleccionado', 'horariosDisponibles']);
    }

    /**
     * Se ejecuta cada vez que $medioDeLaCita cambia.
     */
    public function updatedMedioDeLaCita($value)
    {
        $this->reset(['consejeroId', 'consejeroSeleccionado', 'busquedaConsejero', 'consejerosEncontrados', 'horarioSeleccionado', 'horariosDisponibles']);
        $this->cantidadConsejeros = 10; // Resetear cantidad
        $this->obtenerConsejeros(); // Carga inicial

        // Si ya había un consejero seleccionado, recargamos horarios
        // (porque el medio 'presencial' o 'virtual' puede afectar la disponibilidad)
        if ($this->consejeroId) {
            $this->cargarHorarios();
        }
    }


    public $diaSeleccionado = null;

    public function seleccionarDia($fecha)
    {
        $this->diaSeleccionado = $fecha;
        // Reseteamos la hora si cambian de día
        $this->reset('horarioSeleccionado');
    }

    /**
     * Método que se llama al enviar el formulario.
     */
    public function guardarCita()
    {
        // 1. Validación de datos
        $validated = $this->validate([
            'pacienteId' => 'required|exists:users,id',
            'tipoDeCita' => 'required|exists:tipo_consejerias,id',
            'medioDeLaCita' => 'required',
            'consejeroId' => 'required|exists:users,id',
            'horarioSeleccionado' => 'required|string',
        ]);

        $nuevaCita = null;

        // --- INICIO BLOQUE CRÍTICO ---
        // (Guardar la cita en la base de datos)
        try {
            $consejero = Consejero::where('user_id', $this->consejeroId)->firstOrFail();
            $fechaHoraInicio = Carbon::parse($this->horarioSeleccionado);
            $fechaHoraFin = $fechaHoraInicio->copy()
                                ->addMinutes($consejero->duracion_cita_minutos);

            // 3. Crear la cita en la BD
            $nuevaCita = new CitaConsejeria;
            $nuevaCita->user_id = $this->pacienteId;
            $nuevaCita->consejero_id = $consejero->id;
            $nuevaCita->tipo_consejeria_id = $this->tipoDeCita;
            $nuevaCita->medio = $this->medioDeLaCita;
            
            // Lógica para Google Meet si es virtual
            $enlaceVirtual = null;
            if ($this->medioDeLaCita == 2) {
                 Log::info('INICIO de guardarCita. Hora: ' . now());
                try {
                    $googleEvent = new GoogleEvent;
                    $googleEvent->name = "Cita Consejería: " . TipoConsejeria::find($this->tipoDeCita)->nombre;
                    $googleEvent->startDateTime = $fechaHoraInicio;
                    $googleEvent->endDateTime = $fechaHoraFin;
                    
                    // Asistentes
                    $googleEvent->addAttendee([
                        'email' => User::find($this->pacienteId)->email,
                        'comment' => 'Paciente'
                    ]);
                    $googleEvent->addAttendee([
                        'email' => $consejero->usuario->email,
                        'comment' => 'Consejero'
                    ]);
                    
                    // 1. Prepara la estructura de datos para Meet (Esto ya lo tenías, está bien)
                    $googleEvent->addMeetLink(); 
                    
                    // 2. AQUÍ ESTÁ LA SOLUCIÓN:
                    // Debes pasarle parámetros al método save()
                    $savedEvent = $googleEvent->save('insertEvent', ['conferenceDataVersion' => 1]);
                    
                    // Obtener el link generado
                    $enlaceVirtual = $savedEvent->hangoutLink;

                    Log::info('Enlace virtual generado: ' . $enlaceVirtual);
                    
                } catch (\Exception $e) {
                    Log::error('Error al crear evento en Google Calendar: ' . $e->getMessage());
                    //dd($e->getMessage());
                    $enlaceVirtual = null;
                }
            }

            $nuevaCita->enlace_virtual = $enlaceVirtual;
            $nuevaCita->fecha_hora_inicio = $fechaHoraInicio;
            $nuevaCita->fecha_hora_fin = $fechaHoraFin;
            $nuevaCita->notas_paciente = $this->notas_paciente;

            $nuevaCita->save();

        } catch (\Exception $e) {

            Log::error('Error CRÍTICO al guardar cita: ' . $e->getMessage());
            session()->flash('error', 'Hubo un problema al guardar su cita. Por favor, intente de nuevo.');
            return; // Detenemos la ejecución aquí
        }
        // --- FIN BLOQUE CRÍTICO ---


        // --- INICIO BLOQUE NO CRÍTICO ---
        // (Enviar correos. Si esto falla, no es grave)
        try {
            // Cargamos las relaciones que necesitamos para los correos
            $nuevaCita->load('user', 'consejero.usuario', 'tipoConsejeria');

            // Generar el contenido del ICS
            $icsContenido = $this->generarContenidoIcs($nuevaCita);

            // Enviar correo al Paciente
            Mail::to($nuevaCita->user->email)->send(
                new NotificacionCitaPaciente($nuevaCita, $icsContenido)
            );

            // Enviar correo al Consejero
            Mail::to($nuevaCita->consejero->usuario->email)->send(
                new NotificacionCitaConsejero($nuevaCita, $icsContenido)
            );

        } catch (\Exception $e) {
            // ¡ERROR NO CRÍTICO! Solo lo registramos, pero no molestamos al usuario.
            // La cita se guardó, pero los correos fallaron.
            Log::warning('Error NO CRÍTICO al enviar correos para Cita ID ' . $nuevaCita->id . ': ' . $e->getMessage());
        }
        // --- FIN BLOQUE NO CRÍTICO ---


        // 4. Redireccionar a la página de éxito
        return $this->redirect(route('consejeria.mensajeExitoso', [
            'cita' => $nuevaCita->id
        ]), navigate: true);


    }

    private function generarContenidoIcs(CitaConsejeria $cita): string
    {
        $paciente = $cita->user;
        $consejero = $cita->consejero->usuario; 

        // 1. Preparamos la descripción base en una variable
        $descripcionTexto = "Detalles de la cita:\n" .
                            "Paciente: {$paciente->nombre(3)}\n" .
                            "Consejero: {$consejero->nombre(3)}\n" .
                            "Notas: {$cita->notas_paciente}";

        // 2. Si hay enlace virtual, lo agregamos al principio del texto
        // (Hacemos esto ANTES de crear el objeto Event)
        if ($cita->medio === 'virtual' || $cita->medio === 1) { // Ajusta según tu lógica de IDs (1 suele ser virtual o presencial, revisa tu BD)
            // Ojo: En tu código anterior usabas "virtual" string y luego "1" int. 
            // Asegúrate de usar la comparación correcta. 
            // Asumiremos que si existe enlace_virtual, lo mostramos:
            
            if ($cita->enlace_virtual) {
                $descripcionTexto = "Enlace de la reunión: {$cita->enlace_virtual}\n\n" . $descripcionTexto;
            }
        }

        // 3. Creamos el evento y asignamos la descripción completa de una vez
        // 3. Creamos el evento 
        // IMPORTANTE: El 'organizer' debe coincidir con el email que envía el correo (MAIL_FROM_ADDRESS)
        // para que Gmail muestre los botones de "Añadir al calendario" correctamente.
        $evento = Event::create()
            ->uniqueIdentifier('cita_consejeria_' . $cita->id)
            ->name("Cita Consejería: {$cita->tipoConsejeria->nombre}")
            ->description($descripcionTexto)
            ->startsAt($cita->fecha_hora_inicio)
            ->endsAt($cita->fecha_hora_fin)
            ->organizer(config('mail.from.address'), config('mail.from.name')) 
            ->attendee($paciente->email, $paciente->nombre(3))
            ->attendee($consejero->email, $consejero->nombre(3)); // Añadimos al consejero también como asistente

        // 4. Añadir ubicación
        if ($cita->medio == 1) { 
            if ($cita->consejero->direccion) {
                $evento->address($cita->consejero->direccion);
            }
        } else {
            $evento->addressName("Reunión Virtual");
        }

        // 5. Crear el Calendario y devolver
        $calendario = Calendar::create()
            ->event($evento);

        return $calendario->get();
    }
}
