<div class="container-xxl py-5">
  <div class="row g-4 align-items-stretch">
   <div class="col-lg-5 d-flex align-items-center">
    <section class="w-100 p-4 p-xl-5">
    <div class="redil-eyebrow mb-3">BIENVENIDO A REDIL CLOUD</div>
    <h1 class="h2 mb-4">Tu comunidad.<br>Todo en un lugar.</h1>
    <p class="text-muted mb-4">Acceso exclusivo para administradores de la plataforma.</p>
    @if(session()->has('admin_challenge'))
        <p>Solicitamos el envío de un código al correo de tu cuenta. El procesamiento puede tardar alrededor de un minuto; revisa también la carpeta de spam. El código vence {{ config('admin_global.mfa_minutes') }} minutos después de solicitarlo.</p>
        <form wire:submit="verificar">
            <label for="codigo">Código de seguridad</label>
            <input id="codigo" wire:model="codigo" class="form-control" inputmode="numeric" autocomplete="one-time-code" maxlength="6">
            @error('codigo')<p class="text-danger">{{ $message }}</p>@enderror
            <button class="btn btn-primary my-3" wire:loading.attr="disabled">Verificar e ingresar</button>
        </form>
        <button type="button" wire:click="cancelar" class="btn btn-link">Volver al inicio</button>
    @else
        <form wire:submit="login">
            <label for="email">Correo</label>
            <input id="email" type="email" wire:model="email" class="form-control" autocomplete="username" required>
            @error('email')<p class="text-danger">{{ $message }}</p>@enderror
            <label for="password">Contraseña</label>
            <input id="password" type="password" wire:model="password" class="form-control" autocomplete="current-password" required>
            @error('password')<p class="text-danger">{{ $message }}</p>@enderror
            <button class="btn btn-primary my-3" wire:loading.attr="disabled">Continuar</button>
        </form>
    @endif
    </section>
   </div>
   <div class="col-lg-7">
    <aside class="redil-hero h-100 d-flex flex-column justify-content-center" style="min-height: 420px">
        <div class="redil-eyebrow">PASTOREO INTELIGENTE</div>
        <h2 class="display-3 fw-bold my-4">Crecer.<br>Conectar.<br>Juntos.</h2>
        <p>Más cerca de cada iglesia.<br>Más tiempo para lo que realmente importa.</p>
        <span class="badge bg-white text-dark align-self-start">REDIL · Administración central</span>
    </aside>
   </div>
  </div>
</div>
