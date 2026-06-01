<x-mail::message>
# ¡Hola, {{ $tenant->pastor_name }}!

Lamentamos informarte que el período de gracia para la renovación de tu licencia de REDIL Cloud ha finalizado. Por este motivo, el acceso al sistema para tu iglesia **{{ $tenant->church_name }}** ha sido **suspendido temporalmente**.

Toda la información y datos de tu congregación se encuentran seguros e intactos en la base de datos, pero el acceso permanecerá restringido para todos los usuarios hasta que se procese el pago de renovación.

<x-mail::panel>
**Detalles del Estado de la Cuenta:**
- **Iglesia:** {{ $tenant->church_name }}
- **Fecha de Suspensión:** {{ now()->format('d/m/Y') }}
- **Plan Asignado:** {{ $tenant->plan->nombre ?? 'Básico' }}
</x-mail::panel>

Para reactivar el acceso inmediatamente, por favor ponte en contacto con nosotros respondiendo a este correo o vía telefónica/WhatsApp.

Bendiciones,<br>
El equipo de {{ config('app.name') }}
</x-mail::message>
