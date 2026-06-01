<x-mail::message>
# ¡Hola, {{ $tenant->pastor_name }}!

Hemos recibido exitosamente el registro para tu iglesia **{{ $tenant->church_name }}** en REDIL Cloud.

En este momento, tu cuenta se encuentra en estado **Pendiente de Aprobación**.

Estamos procesando tu solicitud y preparando todo el entorno. Un asesor de nuestro equipo se pondrá en contacto contigo muy pronto (al WhatsApp o correo que proporcionaste) para coordinar los detalles finales y la activación de tu plan.

<x-mail::panel>
**Detalles de tu cuenta:**
- **Iglesia:** {{ $tenant->church_name }}
- **Subdominio solicitado:** {{ $tenant->domains->first()->domain ?? $tenant->id . '.redilcloud.com' }}
- **Miembros estimados:** {{ $tenant->estimated_members }}
</x-mail::panel>

Si tienes alguna pregunta urgente, puedes responder directamente a este correo.

Bendiciones,<br>
El equipo de {{ config('app.name') }}
</x-mail::message>
