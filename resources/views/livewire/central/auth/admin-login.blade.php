<div class="container-xxl">
  <div class="authentication-wrapper authentication-basic container-p-y" style="min-height: 100vh; display: flex; align-items: center; justify-content: center;">
    <div class="authentication-inner" style="width: 100%; max-width: 450px;">
      <div class="card">
        <div class="card-body">
          <div class="app-brand justify-content-center text-center mb-4">
            <h3 class="app-brand-text demo text-body fw-bolder text-uppercase text-primary">REDIL Cloud</h3>
          </div>
          <h4 class="mb-2">Acceso a Super Admin</h4>
          <p class="mb-4">Ingresa a tu cuenta central para gestionar a los inquilinos.</p>

          <form wire:submit.prevent="login" class="mb-3">
            <div class="mb-3">
              <label for="email" class="form-label">Correo Electrónico</label>
              <input type="text" class="form-control" wire:model.defer="email" id="email" placeholder="Ingresa tu email" autofocus>
              @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
            <div class="mb-3 form-password-toggle">
              <div class="d-flex justify-content-between">
                <label class="form-label" for="password">Contraseña</label>
              </div>
              <div class="input-group input-group-merge">
                <input type="password" id="password" wire:model.defer="password" class="form-control" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
              </div>
              @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
            <div class="mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember-me" wire:model="remember">
                <label class="form-check-label" for="remember-me">Recordarme</label>
              </div>
            </div>
            <div class="mb-3">
              <button class="btn btn-primary d-grid w-100" type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="login">Iniciar Sesión</span>
                <span wire:loading wire:target="login">Iniciando...</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
