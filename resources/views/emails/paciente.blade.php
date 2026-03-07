@component('mail::message')
# ¡Tu cita está confirmada!

Hola, **{{ $cita->user->nombre(3) }}**:

@if($esReprogramacion)
Nos alegra confirmarte que tu cita de consejería ha sido reagendada con éxito. Por favor, revisa los detalles a continuación.
@else
Nos alegra confirmarte que tu cita de consejería ha sido agendada con éxito. Por favor, revisa los detalles a continuación.
@endif

Hemos adjuntado una invitación de calendario (.ics) a este correo. La mayoría de los clientes de correo te permitirán añadirla a tu calendario personal (Google, Outlook, etc.) con un solo clic.

---

### Detalles de la Cita

@component('mail::table')
| Detalle | Información |
| :--- | :--- |
| **Consejero(a)** | {{ $cita->consejero->usuario->nombre(3) }} |
| **Motivo** | {{ $cita->tipoConsejeria->nombre }} |
| **Fecha** | {{ $cita->fecha_hora_inicio->isoFormat('dddd, D [de] MMMM [de] YYYY') }} |
| **Hora** | {{ $cita->fecha_hora_inicio->format('g:i A') }} (Hora de Colombia) |
| **Modalidad** | {{ $cita->medio == 1 ? 'Presencial' : 'Virtual' }} |
@endcomponent

@if ($cita->medio == 1)
**Ubicación:**
<br>
{{ $cita->consejero->direccion ?? 'Dirección no especificada. Por favor, contacta al consejero.' }}
@endif

@if ($cita->medio != 1)
**Enlace Virtual:**
<br>
@if($cita->enlace_virtual)
[{{ $cita->enlace_virtual }}]({{ $cita->enlace_virtual }})
@else
Enlace pendiente de generación.
@endif
@endif

---
@if($esReprogramacion)
Si has agregado la cita anterior en tu calendario personal, te recomendamos eliminarla.
@else
ogramar o cancelar tu cita, por favor hazlo con al menos 24 horas de antelación.
@endif
@endcomponent
