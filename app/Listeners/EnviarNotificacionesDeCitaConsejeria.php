<?php

namespace App\Listeners;

use App\Events\CitaAgendadaConsejeria;
use App\Mail\NotificacionCitaConsejero;
use App\Mail\NotificacionCitaPaciente;
use App\Models\CitaConsejeria;
use Illuminate\Queue\InteractsWithQueue;

use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\IcalendarGenerator\Properties\TextProperty;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;


class EnviarNotificacionesDeCitaConsejeria
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(CitaAgendadaConsejeria $event): void
    {
        try {

            $cita = $event->cita;

            // Cargamos las relaciones para no hacer N+1 queries
            $cita->load('user', 'consejero.usuario', 'tipoConsejeria');

            // 1. Generar el contenido del ICS usando el paquete Spatie
            $icsContenido = $this->generarContenidoIcs($cita);

            // 2. Enviar correo al Paciente
            Mail::to("idea.arriba@gmail.com")->send(
                new NotificacionCitaPaciente($cita, $icsContenido)
            );

            // 3. Enviar correo al Consejero
            Mail::to("softjuancarlos@gmail.com")->send(
                new NotificacionCitaConsejero($cita, $icsContenido)
            );


        } catch (\Exception $e) {
            // Si algo falla, lo registramos para depurar
            Log::error("Error al enviar notificaciones de cita {$cita->id}: " . $e->getMessage());
        }
    }

    /**
     * Genera el contenido de un archivo .ics (iCalendar)
     * usando spatie/icalendar-generator.
     */
    private function generarContenidoIcs(CitaConsejeria $cita): string
    {
        $paciente = $cita->user;
        $consejero = $cita->consejero->usuario; // El usuario del consejero

        // 1. Crear el evento
        $evento = Event::create()
            ->name("Cita Consejería: {$cita->tipoConsejeria->nombre}")
            ->description(
                "Detalles de la cita:\n" .
                "Paciente: {$paciente->nombre(3)}\n" .
                "Consejero: {$consejero->nombre(3)}\n" .
                "Notas: {$cita->notas_paciente}"
            )
            ->startsAt($cita->fecha_hora_inicio)
            ->endsAt($cita->fecha_hora_fin)
            ->organizer($consejero->email, $consejero->nombre(3))
            ->attendee($paciente->email, $paciente->nombre(3));

        // 2. Añadir ubicación (presencial o virtual)
        if ($cita->medio == 1) {
            if ($cita->consejero->direccion) {
                $evento->address($cita->consejero->direccion);
            }
        } else {
            // Si es virtual, podemos poner el enlace (si lo tuvieras)
            // o simplemente una descripción.
            $evento->addressName("Reunión Virtual");

            // Si tuvieras el $cita->enlace_virtual, puedes añadirlo así:
            // $evento->description("Enlace de la reunión: {$cita->enlace_virtual}\n\n" . $evento->getDescription());
        }

        // 3. Crear el Calendario y añadir el evento
        $calendario = Calendar::create()
            ->event($evento);

        // 4. Devolver el contenido como string
        return $calendario->get();
    }
}
