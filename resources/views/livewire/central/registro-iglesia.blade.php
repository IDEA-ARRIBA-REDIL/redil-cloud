<div class="container-xxl">
  <div class="authentication-wrapper authentication-basic container-p-y" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem 0;">
    <div class="authentication-inner" style="width: 100%; max-width: 600px;">
      <div class="card">
        <div class="card-body">
          <div class="app-brand justify-content-center text-center mb-4">
            <h3 class="app-brand-text demo text-body fw-bolder text-uppercase text-primary">REDIL Cloud</h3>
          </div>
          <h4 class="mb-2">Registro de Nueva Iglesia ⛪</h4>
          <p class="mb-4">Ingresa los datos para configurar tu entorno en la nube.</p>

          @if (session()->has('success'))
              <div class="alert alert-success">
                  {{ session('success') }}
              </div>
          @endif

          <form wire:submit.prevent="register">
            
            <h6 class="fw-bold">Datos de la Iglesia</h6>
            <div class="row">
                <div class="col-md-12 mb-3">
                  <label class="form-label">Nombre de la Iglesia</label>
                  <input type="text" class="form-control" wire:model.defer="church_name" placeholder="Ej. Templo Cristiano">
                  @error('church_name') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Pastor Principal</label>
                  <input type="text" class="form-control" wire:model.defer="pastor_name">
                  @error('pastor_name') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Membresía Estimada</label>
                  <input type="number" class="form-control" wire:model.defer="estimated_members" placeholder="100">
                  @error('estimated_members') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label">Ciudad</label>
                  <input type="text" class="form-control" wire:model.defer="city">
                  @error('city') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">País</label>
                  <input type="text" class="form-control" wire:model.defer="country">
                  @error('country') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label">WhatsApp</label>
                  <input type="text" class="form-control" wire:model.defer="whatsapp" placeholder="+57 300 0000000">
                  @error('whatsapp') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>

            <hr>
            <h6 class="fw-bold">Detalles del Entorno (SaaS)</h6>
            <div class="mb-3">
              <label class="form-label d-block">Tipo de Plan</label>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" wire:model="plan" value="basico" id="basico">
                <label class="form-check-label" for="basico">Plan Básico (Subdominio)</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" wire:model="plan" value="premium" id="premium">
                <label class="form-check-label" for="premium">Plan Premium (Dominio Propio)</label>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Dominio / Subdominio Deseado</label>
              <input type="text" class="form-control" wire:model.lazy="domain" placeholder="{{ $plan == 'basico' ? 'minegocio' : 'midominio.com' }}">
              @if($full_domain_preview)
                <small class="text-success mt-1 d-block">Tu enlace será: <strong>{{ $full_domain_preview }}</strong></small>
              @endif
              @error('domain') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            <hr>
            <h6 class="fw-bold">Cuenta de Administrador</h6>
            <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Correo Electrónico</label>
                  <input type="email" class="form-control" wire:model.defer="admin_email">
                  @error('admin_email') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Contraseña</label>
                  <input type="password" class="form-control" wire:model.defer="admin_password">
                  @error('admin_password') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-4">
              <button class="btn btn-primary d-grid w-100" type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="register">Registrar y Crear Entorno</span>
                <span wire:loading wire:target="register">Configurando Base de Datos...</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
