<x-mail::message>
# ¡Hola, {{ $tenant->pastor_name }}!

Te informamos que la licencia de REDIL Cloud para tu iglesia **{{ $tenant->church_name }}** ha vencido oficialmente.

Para evitar suspender el acceso de inmediato, hemos activado un **período de gracia de 7 días** que terminará el {{ \Carbon\Carbon::parse($tenant->grace_ends_at)->format('d/m/Y') }}. Durante este lapso tu sistema seguirá funcionando normalmente, pero es de carácter urgente realizar la renovación.

<x-mail::panel>
**Estado de la Suscripción:**
- **Iglesia:** {{ $tenant->church_name }}
- **Fecha de Vencimiento Original:** {{ \Carbon\Carbon::parse($tenant->license_ends_at)->format('d/m/Y') }}
- **Fin del período de gracia (Cierre del acceso):** {{ \Carbon\Carbon::parse($tenant->grace_ends_at)->format('d/m/Y') }}
</x-mail::panel>

Evita la suspensión automática del sistema respondiendo de inmediato a este correo o contactando a soporte para registrar tu renovación.

Bendiciones,<br>
El equipo de {{ config('app.name') }}
</x-mail::message>
