<div class="container-xxl">
  <div class="authentication-wrapper authentication-basic container-p-y" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem 0;">
    <div class="authentication-inner" style="width: 100%; max-width: 850px;">

      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Registro de Nueva Iglesia ⛪</h5>
            <small class="text-muted float-end">Formulario de creación de Entorno SaaS</small>
        </div>
        <div class="card-body">
          <div class="app-brand justify-content-center text-center mb-4">
            <h3 class="app-brand-text demo text-body fw-bolder text-uppercase text-primary">REDIL Cloud</h3>
          </div>

          <div class="alert alert-primary d-flex align-items-center" role="alert">
            <span class="alert-icon text-primary me-2">
              <i class="bx bx-info-circle bx-sm"></i>
            </span>
            <span>Todos los campos marcados con <strong class="text-danger">*</strong> son obligatorios para garantizar la creación y configuración correcta de su entorno.</span>
          </div>

          <form wire:submit.prevent="register" onsubmit="showLoading()">

            <div class="divider text-start mt-5 mb-4">
              <div class="divider-text fw-bold text-primary fs-6"><i class="bx bx-church"></i> Datos de la Iglesia</div>
            </div>

            <div class="row g-3">
                <div class="col-md-8">
                  <label class="form-label">Nombre de la Iglesia <span class="text-danger">*</span></label>
                  <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-buildings"></i></span>
                    <input type="text" class="form-control" wire:model.defer="church_name" placeholder="Ej. Templo Cristiano">
                  </div>
                  @error('church_name') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-4">
                  <label class="form-label">Membresía Estimada <span class="text-danger">*</span></label>
                  <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-group"></i></span>
                    <input type="number" class="form-control" wire:model.defer="estimated_members" placeholder="100">
                  </div>
                  @error('estimated_members') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-6">
                  <label class="form-label">Nombre del Pastor Principal <span class="text-danger">*</span></label>
                  <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-user"></i></span>
                    <input type="text" class="form-control" wire:model.defer="pastor_name" placeholder="Ej. Pastor Juan Pérez">
                  </div>
                  @error('pastor_name') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">Teléfono del Pastor Principal <span class="text-danger">*</span></label>
                  <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-phone"></i></span>
                    <input type="text" class="form-control" wire:model.defer="pastor_phone" placeholder="Ej. +57 300 1234567">
                  </div>
                  @error('pastor_phone') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-6">
                  <label class="form-label">Encargado Administrativo (Software) <span class="text-danger">*</span></label>
                  <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-headphone"></i></span>
                    <input type="text" class="form-control" wire:model.defer="admin_contact_name" placeholder="Ej. María Gómez">
                  </div>
                  @error('admin_contact_name') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">Teléfono del Encargado Administrativo <span class="text-danger">*</span></label>
                  <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-phone-call"></i></span>
                    <input type="text" class="form-control" wire:model.defer="admin_contact_phone" placeholder="Ej. +57 300 7654321">
                  </div>
                  @error('admin_contact_phone') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-4">
                  <label class="form-label">Ciudad <span class="text-danger">*</span></label>
                  <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-map-pin"></i></span>
                    <input type="text" class="form-control" wire:model.defer="city" placeholder="Ej. Tuluá">
                  </div>
                  @error('city') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-4">
                  <label class="form-label">País <span class="text-danger">*</span></label>
                  <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-globe"></i></span>
                    <input type="text" class="form-control" wire:model.defer="country" placeholder="Ej. Colombia">
                  </div>
                  @error('country') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-4">
                  <label class="form-label">WhatsApp <span class="text-danger">*</span></label>
                  <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bxl-whatsapp"></i></span>
                    <input type="text" class="form-control" wire:model.defer="whatsapp" placeholder="+57 300 0000000">
                  </div>
                  @error('whatsapp') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="divider text-start mt-5 mb-4">
              <div class="divider-text fw-bold text-primary fs-6"><i class="bx bx-cloud"></i> Detalles del Entorno (SaaS)</div>
            </div>

            <div class="row g-3">
                <div class="col-md-12 mb-3">
                  <label class="form-label d-block fw-bold">Tipo de Plan Asignado <span class="text-danger">*</span></label>
                  <span class="badge bg-label-primary fs-6 p-2 rounded"><i class="bx bx-badge-check me-1"></i> Plan Básico Activo (Subdominio)</span>
                  <input type="hidden" wire:model="plan" value="basico-350">
                  @error('plan') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-12">
                  <label class="form-label fw-bold">Subdominio Deseado <span class="text-danger">*</span></label>
                  <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-link"></i></span>
                    <input type="text" class="form-control" wire:model.lazy="domain" placeholder="miiglesia" aria-describedby="domain-addon">
                    <span class="input-group-text" id="domain-addon">.redil.co</span>
                  </div>
                  <div class="form-text mt-2">Escribe solo el identificador deseado para el subdominio. Por ejemplo, si deseas obtener <strong>miiglesia.redil.co</strong> escribe únicamente <strong>miiglesia</strong> (sin puntos ni extensiones .com).</div>
                  @if($full_domain_preview)
                    <div class="form-text text-success mt-2">✨ Enlace de acceso definitivo: <strong class="fs-6">{{ $full_domain_preview }}</strong></div>
                  @endif
                  @error('domain') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="divider text-start mt-5 mb-4">
              <div class="divider-text fw-bold text-primary fs-6"><i class="bx bx-user-circle"></i> Cuenta de Administrador</div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                  <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                    <input type="email" class="form-control" wire:model.defer="admin_email" placeholder="ejemplo@correo.com">
                  </div>
                  @error('admin_email') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-6 form-password-toggle">
                  <label class="form-label">Contraseña <span class="text-danger">*</span></label>
                  <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-lock"></i></span>
                    <input type="password" class="form-control" wire:model.defer="admin_password" placeholder="············">
                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                  </div>
                  <div class="form-text">Mínimo 6 caracteres</div>
                  @error('admin_password') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-5">
              <button class="btn btn-primary btn-lg d-grid w-100" type="submit">
                <i class="bx bx-paper-plane me-2"></i> Registrar y Crear Entorno
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('tenant-created', (event) => {
            Swal.fire({
                title: '¡Registro Exitoso!',
                text: event[0]?.message || 'Tu solicitud ha sido recibida y se encuentra en estado Pendiente de Aprobación.',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });
        });

        Livewire.hook('commit', ({ succeed, fail }) => {
            succeed(() => {
                if (Swal.isVisible() && Swal.getTitle().textContent === 'Procesando...') {
                    Swal.close();
                }
            });
            fail(() => {
                if (Swal.isVisible() && Swal.getTitle().textContent === 'Procesando...') {
                    Swal.close();
                }
            });
        });
    });

    function showLoading() {
        Swal.fire({
            title: 'Procesando...',
            html: 'Estamos creando la base de datos y configurando tu entorno.<br><b>Por favor no cierres ni actualices la ventana.</b>',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });
    }
  </script>
</div>
