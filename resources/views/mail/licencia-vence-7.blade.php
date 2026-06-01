<x-mail::message>
# ¡Hola, {{ $tenant->pastor_name }}!

Este es un recordatorio importante. La licencia de REDIL Cloud para tu iglesia **{{ $tenant->church_name }}** vencerá en **7 días** (el {{ \Carbon\Carbon::parse($tenant->license_ends_at)->format('d/m/Y') }}).

Evita contratiempos y cortes en el acceso de tus líderes y miembros renovando tu suscripción antes del vencimiento.

<x-mail::panel>
**Detalles de la Licencia:**
- **Iglesia:** {{ $tenant->church_name }}
- **Plan:** {{ $tenant->plan->nombre ?? 'Básico' }}
- **Fecha de vencimiento:** {{ \Carbon\Carbon::parse($tenant->license_ends_at)->format('d/m/Y') }}
</x-mail::panel>

Por favor, contáctanos hoy mismo respondiendo a este correo o vía WhatsApp para procesar la renovación de forma inmediata.

Bendiciones,<br>
El equipo de {{ config('app.name') }}
</x-mail::message>
