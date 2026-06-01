<div class="authentication-wrapper authentication-cover">
  <!-- Logo -->
  <a href="/" class="app-brand auth-cover-brand">
    <span class="app-brand-text demo text-heading fw-bolder text-uppercase text-primary fs-3">REDIL Cloud</span>
  </a>
  <!-- /Logo -->

  <div class="authentication-inner row m-0">
    <!-- /Left Text -->
    <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center justify-content-center p-5 pb-2" style="background-color: #f8f9fa;">
      <img src="https://images.unsplash.com/photo-1497215728101-856f4ea42174?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80"
           class="auth-cover-illustration rounded-3 shadow-lg w-100"
           alt="auth-illustration"
           style="max-width: 800px; max-height: 80vh; object-fit: cover;" />
    </div>
    <!-- /Left Text -->

    <!-- Login -->
    <div class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg p-sm-5 p-4 bg-white">
      <div class="w-px-400 mx-auto">
        <!-- Logo -->
        <div class="app-brand mb-4 d-lg-none d-flex">
          <a href="/" class="app-brand-link gap-2 mb-2">
            <span class="app-brand-text demo text-heading fw-bolder text-uppercase text-primary">REDIL Cloud</span>
          </a>
        </div>
        <!-- /Logo -->

        <h4 class="mb-2">¡Bienvenido Administrador! 👋</h4>
        <p class="mb-4">Ingresa a tu cuenta central para gestionar todas las iglesias y configuraciones del SaaS.</p>

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
              <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
            </div>
            @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
          </div>

          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="remember-me" wire:model="remember">
              <label class="form-check-label" for="remember-me">Recordar sesión</label>
            </div>
          </div>

          <button class="btn btn-primary d-grid w-100" type="submit">
            Iniciar Sesión
          </button>
        </form>
      </div>
    </div>
    <!-- /Login -->
  </div>
</div>
