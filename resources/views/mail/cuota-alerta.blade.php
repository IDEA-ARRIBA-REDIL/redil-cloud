<x-mail::message>
# ¡Hola, {{ $tenant->pastor_name }}!

Te escribimos del soporte de REDIL Cloud para informarte que tu iglesia **{{ $tenant->church_name }}** está alcanzando o ha superado el límite de miembros activos permitido en tu plan actual.

<x-mail::panel>
**Estado de Consumo de Tu Plan:**
- **Miembros registrados:** {{ number_format($miembros) }}
- **Miembros máximos de tu plan:** {{ number_format($maxMiembros) }}
- **Uso actual:** {{ number_format($ratio * 100, 1) }}%
</x-mail::panel>

@if($ratio >= 1.0)
## ⚠️ Límite del plan excedido (100% o más)
Has superado la capacidad máxima de tu plan. Para garantizar que todos tus nuevos miembros y líderes puedan registrarse y acceder al sistema normalmente, te recomendamos subir de categoría tu plan a la brevedad.
@else
## 📅 Límite de miembros próximo (90% o más)
Estás muy cerca de completar el cupo máximo de tu plan. Te sugerimos ponerte en contacto con soporte para evaluar una ampliación de tu plan actual y evitar restricciones de registros a futuro.
@endif

Por favor, responde a este correo o escríbenos a nuestro canal de soporte técnico para asistirte con el cambio de plan.

Bendiciones,<br>
El equipo de {{ config('app.name') }}
</x-mail::message>
