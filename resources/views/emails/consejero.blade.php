@component('mail::message')
@if($esReprogramacion)
# Reagendamiento de cita
@else
# Nueva cita agendada
@endif

Hola, **{{ $cita->consejero->usuario->nombre(3) }}**:

@if($esReprogramacion)
Se ha reagendado una cita en tu calendario. Un archivo `.ics` ha sido adjuntado para que puedas actualizar tu calendario principal.
@else
Se ha agendado una nueva cita en tu calendario. Un archivo `.ics` ha sido adjuntado para que puedas añadirlo a tu calendario principal.
@endif

---

### Detalles de la Cita

@component('mail::table')
| Detalle | Información |
| :--- | :--- |
| **Paciente** | {{ $cita->user->nombre(3) }} |
| **Email Paciente** | {{ $cita->user->email }} |
| **Motivo** | {{ $cita->tipoConsejeria->nombre }} |
| **Fecha** | {{ $cita->fecha_hora_inicio->isoFormat('dddd, D [de] MMMM [de] YYYY') }} |
| **Hora** | {{ $cita->fecha_hora_inicio->format('g:i A') }} (Hora de Colombia) |
| **Modalidad** | {{ $cita->medio == 1 ? 'Presencial' : 'Virtual' }} |
@endcomponent

@if ($cita->medio == 1)
**Ubicación:**
<br>
{{ $cita->consejero->direccion ?? 'Tu dirección (presencial)' }}
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

### Detalles
@component('mail::panel')
{{ $cita->notas_paciente ?? 'El paciente no dejó notas.' }}
@endcomponent

@if($esReprogramacion)
Si has agregado la cita anterior en tu calendario personal, te recomendamos eliminarla.
@endif


Saludos,<br>
El equipo de {{ config('app.name') }}
@endcomponent
