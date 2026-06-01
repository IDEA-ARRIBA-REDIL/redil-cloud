<x-mail::message>
# Informe Semanal de REDIL Cloud

Estimado Administrador, a continuación se presenta el resumen del estado actual de la plataforma central y sus inquilinos (tenants).

<x-mail::panel>
**Métricas Generales:**
- **Inquilinos Activos:** {{ $data['active_count'] }}
- **Registros Nuevos (últimos 7 días):** {{ $data['new_count'] }}
- **Pendientes de Aprobación:** {{ $data['pending_count'] }}
</x-mail::panel>

## ⚠️ Licencias Vencidas / En Período de Gracia
@if(count($data['in_grace']) > 0)
@foreach($data['in_grace'] as $tenant)
- **{{ $tenant->church_name }}** (Vence: {{ \Carbon\Carbon::parse($tenant->license_ends_at)->format('d/m/Y') }} | Gracia termina: {{ \Carbon\Carbon::parse($tenant->grace_ends_at)->format('d/m/Y') }})
@endforeach
@else
*No hay inquilinos en período de gracia.*
@endif

## 📅 Licencias por Vencer (Próximos 30 días)
@if(count($data['expiring_soon']) > 0)
@foreach($data['expiring_soon'] as $tenant)
- **{{ $tenant->church_name }}** (Vence el: {{ \Carbon\Carbon::parse($tenant->license_ends_at)->format('d/m/Y') }})
@endforeach
@else
*No hay vencimientos próximos en los siguientes 30 días.*
@endif

## 📈 Exceso de Cuota de Miembros (>90% de su plan)
@if(count($data['quota_alerts']) > 0)
@foreach($data['quota_alerts'] as $alert)
- **{{ $alert['tenant']->church_name }}** (Uso: {{ $alert['miembros'] }} / {{ $alert['max_miembros'] }} | {{ number_format($alert['ratio'] * 100, 1) }}%)
@endforeach
@else
*Todos los inquilinos se encuentran dentro de sus cuotas de miembros permitidas.*
@endif

Para más información, puedes acceder al panel de administración general.

<x-mail::button :url="config('app.url') . '/admin/dashboard'">
Ir al Panel de Control
</x-mail::button>

Bendiciones,<br>
El equipo de {{ config('app.name') }}
</x-mail::message>
