<?php

namespace App\Livewire\Consejeria;

use App\Mail\NotificacionCitaConsejero;
use App\Mail\NotificacionCitaPaciente;
use App\Models\CitaConsejeria;
use App\Models\Consejero;
use App\Models\TipoConsejeria;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\GoogleCalendar\Event as GoogleEvent;

class ReprogramarCita extends Component
{
    public $citaId;
    public $origen; // URL to redirect back to
    public ?CitaConsejeria $cita = null;

    // Form fields
    public $horarioSeleccionado = '';
    public $notas_paciente = ''; // Optional: allow updating notes? Plan said read-only details, but maybe notes can be appended. Let's stick to plan: read-only details.

    // Data for display/logic
    public $horariosDisponibles = [];
    public $diaSeleccionado = null;
    public $guardando = false;

    public function mount($citaId, $origen = null)
    {
        $this->citaId = $citaId;
        $this->origen = $origen ?? url()->previous();
        
        $this->cita = CitaConsejeria::with(['user', 'consejero.usuario', 'tipoConsejeria'])->find($citaId);

        if (!$this->cita) {
            abort(404, 'Cita no encontrada');
        }

        // Load available slots for the counselor
        $this->cargarHorarios();
    }

    public function cargarHorarios()
    {
        $this->reset(['horarioSeleccionado']);
        $this->horariosDisponibles = [];

        $consejero = $this->cita->consejero;

        if (!$consejero || !$consejero->activo) {
            return;
        }

        // Validate medium availability (though we are keeping the same medium)
        if ($this->cita->medio === 'presencial' && !$consejero->atencion_presencial) {
            return;
        }
        if (($this->cita->medio === 'virtual' || $this->cita->medio == 2) && !$consejero->atencion_virtual) {
            return;
        }

        // Define Rules and Search Range
        $duracionCita = $consejero->duracion_cita_minutos;
        $buffer = $consejero->buffer_entre_citas_minutos;
        $duracionSlot = $duracionCita + $buffer;

        $ahora = Carbon::now();
        $fechaInicio = $ahora->copy()->addDays($consejero->dias_minimos_antelacion)->startOfDay();
        $fechaFin = $ahora->copy()->addDays($consejero->dias_maximos_futuro)->endOfDay();

        // Fetch Data
        $horariosHabituales = $consejero->horariosHabituales()->get()->groupBy('dia_semana');

        $horariosAdicionales = $consejero->horariosAdicionales()
            ->where('fecha_inicio', '<=', $fechaFin)
            ->where('fecha_fin', '>=', $fechaInicio)
            ->get();

        $horariosBloqueados = $consejero->horariosBloqueados()
            ->where('fecha_inicio', '<=', $fechaFin)
            ->where('fecha_fin', '>=', $fechaInicio)
            ->get();

        // Fetch existing appointments to block slots (EXCLUDING the current appointment being rescheduled if it falls in range, though usually we reschedule to a DIFFERENT time, so the old time is technically "free" if we move it. But for simplicity, let's just fetch all OTHER appointments)
        $citasAgendadas = CitaConsejeria::where('consejero_id', $consejero->id)
            ->where('id', '!=', $this->citaId) // Exclude current appointment
            ->where('fecha_hora_inicio', '<=', $fechaFin)
            ->where('fecha_hora_fin', '>=', $fechaInicio)
            ->whereNull('deleted_at') // Ignore cancelled
            ->get();

        // Process day by day
        $periodo = CarbonPeriod::create($fechaInicio, '1 day', $fechaFin);
        $slotsDisponibles = [];

        foreach ($periodo as $dia) {
            $diaSemana = $dia->dayOfWeekIso;
            $diaStr = $dia->format('Y-m-d');
            $slotsDelDia = [];

            // a) Availability Blocks
            $potencialesBloques = new Collection();

            if (isset($horariosHabituales[$diaSemana])) {
                foreach ($horariosHabituales[$diaSemana] as $hab) {
                    $potencialesBloques->push([
                        'inicio' => $dia->copy()->setTimeFromTimeString($hab->hora_inicio),
                        'fin' => $dia->copy()->setTimeFromTimeString($hab->hora_fin)
                    ]);
                }
            }

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
                continue;
            }

            // b) Occupied Blocks
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

            foreach ($citasAgendadas as $cita) {
                $citaInicio = Carbon::parse($cita->fecha_hora_inicio);
                $citaFin = Carbon::parse($cita->fecha_hora_fin)->addMinutes($buffer);

                if ($citaInicio <= $dia->copy()->endOfDay() && $citaFin >= $dia->copy()->startOfDay()) {
                    $todosLosBloqueos->push([
                        'inicio' => $citaInicio->isAfter($dia->copy()->startOfDay()) ? $citaInicio : $dia->copy()->startOfDay(),
                        'fin' => $citaFin->isBefore($dia->copy()->endOfDay()) ? $citaFin : $dia->copy()->endOfDay()
                    ]);
                }
            }

            // Generate Slots
            foreach ($potencialesBloques as $bloque) {
                $slotActual = $bloque['inicio']->copy();
                $finBloque = $bloque['fin']->copy();

                while ($slotActual->copy()->addMinutes($duracionCita) <= $finBloque) {
                    $slotInicio = $slotActual->copy();
                    $slotFin = $slotInicio->copy()->addMinutes($duracionCita);

                    if ($slotInicio < $ahora) {
                        $slotActual->addMinutes($duracionSlot);
                        continue;
                    }

                    $slotFinConBuffer = $slotInicio->copy()->addMinutes($duracionSlot);
                    $estaBloqueado = false;
                    foreach ($todosLosBloqueos as $bloqueo) {
                        if ($slotInicio < $bloqueo['fin'] && $slotFinConBuffer > $bloqueo['inicio']) {
                            $estaBloqueado = true;
                            break;
                        }
                    }

                    if (!$estaBloqueado) {
                        $horaStr = $slotInicio->format('H:i');
                        if (!in_array($horaStr, $slotsDelDia)) {
                            $slotsDelDia[] = $horaStr;
                        }
                    }

                    $slotActual->addMinutes($duracionSlot);
                }
            }

            if (!empty($slotsDelDia)) {
                sort($slotsDelDia);
                $slotsDisponibles[$diaStr] = $slotsDelDia;
            }
        }

        $this->horariosDisponibles = $slotsDisponibles;
        $this->reset('horarioSeleccionado');

        if (!empty($slotsDisponibles)) {
            $this->diaSeleccionado = array_key_first($this->horariosDisponibles);
        } else {
            $this->diaSeleccionado = null;
        }
    }

    public function seleccionarDia($fecha)
    {
        $this->diaSeleccionado = $fecha;
        $this->reset('horarioSeleccionado');
    }

    public function reprogramar()
    {
        $this->validate([
            'horarioSeleccionado' => 'required|string',
        ]);

        $this->guardando = true;

        try {
            $consejero = $this->cita->consejero;
            $fechaHoraInicio = Carbon::parse($this->horarioSeleccionado);
            $fechaHoraFin = $fechaHoraInicio->copy()->addMinutes($consejero->duracion_cita_minutos);

            // Update Appointment
            $this->cita->fecha_hora_inicio = $fechaHoraInicio;
            $this->cita->fecha_hora_fin = $fechaHoraFin;
            
            // Update Google Meet link if virtual
            if (($this->cita->medio === 'virtual' || $this->cita->medio == 2)) {
                try {
                    $googleEvent = new GoogleEvent;
                    $googleEvent->name = "Cita reprogramada: " . $this->cita->tipoConsejeria->nombre;
                    $googleEvent->startDateTime = $fechaHoraInicio;
                    $googleEvent->endDateTime = $fechaHoraFin;
                    
                    $googleEvent->addAttendee([
                        'email' => $this->cita->user->email,
                        'comment' => 'Paciente'
                    ]);
                    $googleEvent->addAttendee([
                        'email' => $consejero->usuario->email,
                        'comment' => 'Consejero'
                    ]);
                    
                    $googleEvent->addMeetLink(); 
                    
                    $savedEvent = $googleEvent->save('insertEvent', ['conferenceDataVersion' => 1]);
                    
                    $this->cita->enlace_virtual = $savedEvent->hangoutLink;
                    
                } catch (\Exception $e) {
                    Log::error('Error al generar nuevo link de Meet al reprogramar: ' . $e->getMessage());
                }
            }

            $this->cita->save();

        } catch (\Exception $e) {
            Log::error('Error al reprogramar cita: ' . $e->getMessage());
            session()->flash('error', 'Hubo un problema al reprogramar la cita.');
            $this->guardando = false;
            return;
        }

        // Send Emails
        try {
            $this->cita->load('user', 'consejero.usuario', 'tipoConsejeria');
            $icsContenido = $this->generarContenidoIcs($this->cita);

            // Notify Patient
            Mail::to($this->cita->user->email)->send(
                new NotificacionCitaPaciente($this->cita, $icsContenido, true)
            );

            // Notify Counselor
            Mail::to($this->cita->consejero->usuario->email)->send(
                new NotificacionCitaConsejero($this->cita, $icsContenido, true)
            );

        } catch (\Exception $e) {
            Log::warning('Error enviando correos de reprogramación: ' . $e->getMessage());
        }

        // Redirect back
        return redirect($this->origen)->with('success', 'Cita reprogramada exitosamente.');
    }

    private function generarContenidoIcs(CitaConsejeria $cita): string
    {
        // Reuse logic from NuevaCita or extract to a Helper/Service.
        // For now, duplicating for speed as per "NuevaCita" logic.
        $paciente = $cita->user;
        $consejero = $cita->consejero->usuario; 

        $descripcionTexto = "Cita Reprogramada.\n" .
                            "Detalles de la cita:\n" .
                            "Paciente: {$paciente->nombre(3)}\n" .
                            "Consejero: {$consejero->nombre(3)}\n" .
                            "Notas: {$cita->notas_paciente}";

        if ($cita->medio === 'virtual' || $cita->medio == 2) {
            if ($cita->enlace_virtual) {
                $descripcionTexto = "Enlace de la reunión: {$cita->enlace_virtual}\n\n" . $descripcionTexto;
            }
        }

        $evento = Event::create()
            ->name("Cita Consejería: {$cita->tipoConsejeria->nombre}")
            ->description($descripcionTexto)
            ->startsAt($cita->fecha_hora_inicio)
            ->endsAt($cita->fecha_hora_fin)
            ->organizer($consejero->email, $consejero->nombre(3))
            ->attendee($paciente->email, $paciente->nombre(3));

        if ($cita->medio == 1) { 
            if ($cita->consejero->direccion) {
                $evento->address($cita->consejero->direccion);
            }
        } else {
            $evento->addressName("Reunión Virtual");
        }

        $calendario = Calendar::create()->event($evento);

        return $calendario->get();
    }

    public function render()
    {
        return view('livewire.consejeria.reprogramar-cita');
    }
}
