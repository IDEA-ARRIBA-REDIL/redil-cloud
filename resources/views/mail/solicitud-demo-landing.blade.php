<x-mail::message>
# Nueva solicitud de información desde la Landing

Un líder o pastor ha solicitado una videollamada para conocer **REDIL Cloud**.

<x-mail::panel>
- **Iglesia / Ministerio:** {{ $solicitud['nombreIglesia'] }}
- **Pastor / Líder:** {{ $solicitud['nombreContacto'] }}
- **Correo electrónico:** {{ $solicitud['email'] }}
- **WhatsApp / Teléfono:** {{ $solicitud['whatsapp'] }}
- **País:** {{ $solicitud['pais'] }}
- **Tamaño estimado:** {{ $solicitud['tamanoCongregacion'] }}
- **Área de mayor interés:** {{ $solicitud['moduloInteres'] }}
</x-mail::panel>

@if($solicitud['mensaje'] !== '')
**Mensaje adicional:**

> {{ $solicitud['mensaje'] }}
@endif

<x-mail::button :url="$solicitud['whatsappUrl']">
Responder por WhatsApp
</x-mail::button>

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
