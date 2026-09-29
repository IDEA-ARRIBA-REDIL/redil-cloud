<div class="container py-4" style="max-width: 850px">
    <h1 class="h4">Registro de nueva iglesia REDIL</h1>
    <p>Necesitas el código emitido por REDIL después de comprobar tu pago. El plan está asociado al código y no se elige aquí.</p>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <form wire:submit="register" class="card card-body">
        <label for="codigo">Código de invitación</label>
        <input id="codigo" wire:model="codigo" class="form-control mb-3" autocomplete="off" maxlength="64" required>
        <div class="row g-3">
            @foreach([
                'church_name' => 'Nombre de la iglesia',
                'pastor_name' => 'Nombre del pastor principal',
                'pastor_phone' => 'Teléfono del pastor',
                'admin_contact_name' => 'Encargado administrativo',
                'admin_contact_phone' => 'Teléfono del encargado',
                'city' => 'Ciudad', 'country' => 'País', 'whatsapp' => 'WhatsApp de contacto'
            ] as $campo => $label)
                <div class="col-md-6" wire:key="registro-{{ $campo }}">
                    <label for="{{ $campo }}">{{ $label }}</label>
                    <input id="{{ $campo }}" wire:model="{{ $campo }}" class="form-control" maxlength="100" required>
                </div>
            @endforeach
            <div class="col-md-6">
                <label for="estimated_members">Miembros estimados</label>
                <input id="estimated_members" type="number" min="1" max="1000000" wire:model="estimated_members" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="admin_email">Correo del administrador (el de la invitación)</label>
                <input id="admin_email" type="email" wire:model="admin_email" class="form-control" required>
            </div>
            <div class="col-12">
                <label for="domain">Subdominio solicitado</label>
                <div class="input-group">
                    <input id="domain" wire:model.blur="domain" maxlength="63" class="form-control" required>
                    <span class="input-group-text">.{{ config('admin_global.registration_domain') }}</span>
                </div>
                @if($full_domain_preview)<p>{{ $full_domain_preview }}</p>@endif
            </div>
        </div>
        <p class="mt-3">No solicitamos contraseña aquí. Una vez revisado y activado el entorno recibirás un código por correo para establecer tu acceso individual.</p>
        @foreach($errors->all() as $error)<p class="text-danger">{{ $error }}</p>@endforeach
        <button class="btn btn-primary rounded-pill" wire:loading.attr="disabled" wire:target="register">Enviar solicitud</button>
        <p wire:loading wire:target="register" role="status">Validando tu invitación. No cierres esta ventana.</p>
    </form>
</div>
