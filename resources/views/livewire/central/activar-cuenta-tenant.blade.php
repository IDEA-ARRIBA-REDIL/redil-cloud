<div class="container py-5" style="max-width: 600px">
    <h1 class="h4">Establecer acceso inicial</h1>
    @if(session('success'))<p class="alert alert-success">{{ session('success') }}</p>@endif
    <form wire:submit="activar">
        <label>Código recibido por correo</label><input wire:model="codigo" class="form-control" autocomplete="off" maxlength="64">
        <label>Contraseña individual (mínimo 14 caracteres)</label><input type="password" wire:model="password" class="form-control" autocomplete="new-password">
        <label>Confirma la contraseña</label><input type="password" wire:model="password_confirmation" class="form-control" autocomplete="new-password">
        @foreach($errors->all() as $error)<p class="text-danger">{{ $error }}</p>@endforeach
        <button class="btn btn-primary rounded-pill mt-3" wire:loading.attr="disabled">Establecer contraseña</button>
    </form>
</div>
