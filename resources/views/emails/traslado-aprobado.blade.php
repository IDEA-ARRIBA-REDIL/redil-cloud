@component('mail::message')
# Solicitud de Traslado Aprobada

Hola **{{ $solicitud->user->primer_nombre }}**,

Tu solicitud de traslado para la materia **{{ $solicitud->matricula->horarioMateriaPeriodo->materiaPeriodo->materia->nombre }}** ha sido **APROBADA**.

**Detalles del cambio:**

*   **Nuevo Grupo:** {{ $solicitud->horarioDestino->horarioBase->dia_semana }} {{ $solicitud->horarioDestino->horarioBase->hora_inicio_formato }}
*   **Sede:** {{ $solicitud->horarioDestino->horarioBase->aula->sede->nombre }}

Ya puedes asistir a tu nuevo horario. Tu registro de notas y asistencia ha sido actualizado.

Gracias,<br>
{{ config('app.name') }}
@endcomponent
