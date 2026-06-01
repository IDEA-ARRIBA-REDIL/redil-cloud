<x-mail::message>
# ¡Hola, {{ $tenant->pastor_name }}!

Te escribimos para recordarte que la licencia de REDIL Cloud para tu iglesia **{{ $tenant->church_name }}** vencerá en **30 días** (el {{ \Carbon\Carbon::parse($tenant->license_ends_at)->format('d/m/Y') }}).

Queremos asegurarnos de que tu iglesia continúe operando sin interrupciones. Por favor, ponte en contacto con nosotros para gestionar la renovación de tu plan actual.

<x-mail::panel>
**Detalles de la Licencia:**
- **Iglesia:** {{ $tenant->church_name }}
- **Plan:** {{ $tenant->plan->nombre ?? 'Básico' }}
- **Fecha de vencimiento:** {{ \Carbon\Carbon::parse($tenant->license_ends_at)->format('d/m/Y') }}
</x-mail::panel>

Para renovar o realizar el pago, puedes responder directamente a este correo o contactarnos a través de los canales de soporte habituales.

Bendiciones,<br>
El equipo de {{ config('app.name') }}
</x-mail::message>
