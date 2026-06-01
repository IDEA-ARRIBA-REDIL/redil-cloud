<x-mail::message>
# Nuevo Registro de Iglesia

Hola Admin,

Se ha registrado una nueva iglesia en REDIL Cloud. La cuenta se encuentra actualmente en estado **Pendiente de Aprobación**.

<x-mail::panel>
**Datos del Registro:**
- **Iglesia:** {{ $tenant->church_name }}
- **Contacto / Pastor:** {{ $tenant->pastor_name }}
- **Email:** {{ $tenant->admin_email }}
- **WhatsApp:** {{ $tenant->whatsapp }}
- **Ciudad:** {{ $tenant->city }}, {{ $tenant->country }}
- **Tamaño Estimado:** {{ $tenant->estimated_members }} miembros
- **Subdominio:** {{ $tenant->domains->first()->domain ?? $tenant->id . '.redilcloud.com' }}
</x-mail::panel>

Por favor, ponte en contacto con el cliente para coordinar el pago o la configuración, y luego procede a aprobar la cuenta desde el panel de administración.

<x-mail::button :url="config('app.url') . '/admin/tenants/' . $tenant->id">
Ver Detalles en el Panel
</x-mail::button>

Gracias,<br>
Sistema Automático de {{ config('app.name') }}
</x-mail::message>
