<div class="container py-4">
    <h1 class="h4">Invitaciones de nuevas iglesias</h1>
    <p>Emite un código únicamente después de verificar el pago. Vence en {{ config('admin_global.invitation_days') }} días y sirve para un solo registro.</p>
    <a href="{{ url('/admin/dashboard') }}">Volver al panel</a>
    <form wire:submit="emitir" class="card card-body my-3">
        <label>Correo del administrador del cliente</label>
        <input type="email" wire:model="email" class="form-control" required>
        <label>Plan pagado</label>
        <select wire:model="planId" class="form-select" required>
            <option value="">Seleccionar</option>
            @foreach($planes as $plan)<option value="{{ $plan->id }}">{{ $plan->nombre }}</option>@endforeach
        </select>
        <label>Referencia única del pago comprobado</label>
        <input wire:model="referenciaPago" class="form-control" maxlength="150" required>
        <label class="my-3"><input type="checkbox" wire:model="pagoConfirmado"> Confirmo que comprobé el pago de este cliente</label>
        @foreach($errors->all() as $error)<p class="text-danger">{{ $error }}</p>@endforeach
        <button type="submit" wire:loading.attr="disabled" class="btn btn-primary rounded-pill">Emitir código</button>
    </form>
    @if($codigoGenerado)
        <div class="alert alert-warning">Copia y entrega este código de forma privada. No podrás recuperarlo al salir:
            <code class="d-block text-break">{{ $codigoGenerado }}</code>
        </div>
    @endif
    <table class="table">
        <thead><tr><th>Correo</th><th>Vence</th><th>Estado</th><th>Acción</th></tr></thead>
        <tbody>
            @foreach($invitaciones as $invitacion)
                <tr wire:key="invitacion-{{ $invitacion->id }}">
                    <td>{{ $invitacion->email }}</td><td>{{ $invitacion->expires_at }}</td>
                    <td>{{ $invitacion->used_at ? 'Usada' : ($invitacion->disponible() ? 'Disponible' : 'No disponible') }}</td>
                    <td>@if($invitacion->disponible())<button wire:click="revocar({{ $invitacion->id }})" class="btn btn-outline-danger rounded-pill">Revocar</button>@endif</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
