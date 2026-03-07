@component('mail::message')
# Solicitud de Traslado Rechazada

Hola **{{ $solicitud->user->primer_nombre }}**,

Tu solicitud de traslado para la materia **{{ $solicitud->matricula->horarioMateriaPeriodo->materiaPeriodo->materia->nombre }}** ha sido **RECHAZADA**.

**Motivo:**
> {{ $solicitud->motivo_rechazo }}

Deberás continuar asistiendo a tu horario actual.

Gracias,<br>
{{ config('app.name') }}
@endcomponent
