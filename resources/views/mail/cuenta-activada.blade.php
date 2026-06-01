<x-mail::message>
# ¡Felicidades, {{ $tenant->pastor_name }}!

Tu cuenta para la iglesia **{{ $tenant->church_name }}** en REDIL Cloud ha sido **activada exitosamente**.

Ya puedes acceder a tu panel de control y comenzar a configurar tu iglesia.

<x-mail::panel>
**Enlace de Acceso:**
[https://{{ $domain }}](https://{{ $domain }})

*Nota: Usa el correo electrónico y la contraseña que proporcionaste durante el registro.*
</x-mail::panel>

<x-mail::button :url="'https://' . $domain">
Ingresar al Sistema
</x-mail::button>

Si necesitas ayuda para comenzar, recuerda revisar nuestra documentación o contactar a nuestro equipo de soporte.

Bendiciones,<br>
El equipo de {{ config('app.name') }}
</x-mail::message>
