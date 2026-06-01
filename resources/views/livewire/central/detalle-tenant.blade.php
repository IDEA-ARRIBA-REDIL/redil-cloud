<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4 py-3">
        <h4 class="fw-bold mb-0"><span class="text-muted fw-light">SaaS / Inquilinos /</span> Detalles</h4>
        <a href="{{ url('/admin/dashboard') }}" class="btn btn-label-secondary">
            <span class="tf-icons bx bx-arrow-back me-1"></span>Volver al Dashboard
        </a>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success d-flex align-items-center" role="alert">
            <span class="alert-icon text-success me-2">
                <i class="bx bx-check bx-sm"></i>
            </span>
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
        <!-- User Sidebar -->
        <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
            <!-- User Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="user-avatar-section">
                        <div class="d-flex align-items-center flex-column">
                            <div class="avatar avatar-xl mb-3">
                                <span class="avatar-initial rounded bg-label-primary" style="font-size: 1.5rem;">{{ strtoupper(substr($tenant->church_name, 0, 2)) }}</span>
                            </div>
                            <div class="user-info text-center">
                                <h4 class="mb-2">{{ $tenant->church_name }}</h4>
                                <span class="badge bg-label-secondary">{{ $tenant->domains->first()->domain ?? 'Sin subdominio' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-around flex-wrap mt-4 pb-2 border-bottom">
                        <div class="d-flex align-items-start me-4 mt-3 gap-3">
                            <span class="badge bg-label-primary p-2 rounded"><i class="bx bx-group bx-sm"></i></span>
                            <div>
                                <h5 class="mb-0">{{ $tenant->estimated_members }}</h5>
                                <span>Miembros</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-start mt-3 gap-3">
                            <span class="badge bg-label-primary p-2 rounded"><i class="bx bx-briefcase bx-sm"></i></span>
                            <div>
                                <h5 class="mb-0">ID</h5>
                                <span>{{ $tenant->id }}</span>
                            </div>
                        </div>
                    </div>
                    <h5 class="pb-2 border-bottom mb-4 mt-4">Detalles del Contacto</h5>
                    <div class="info-container">
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <span class="fw-medium text-heading me-2">Pastor:</span>
                                <span>{{ $tenant->pastor_name }}</span>
                            </li>
                            <li class="mb-3">
                                <span class="fw-medium text-heading me-2">Email:</span>
                                <span>{{ $tenant->admin_email }}</span>
                            </li>
                            <li class="mb-3">
                                <span class="fw-medium text-heading me-2">WhatsApp:</span>
                                <span>{{ $tenant->whatsapp }}</span>
                            </li>
                            <li class="mb-3">
                                <span class="fw-medium text-heading me-2">Ubicación:</span>
                                <span>{{ $tenant->city }}, {{ $tenant->country }}</span>
                            </li>
                            <li class="mb-3">
                                <span class="fw-medium text-heading me-2">Url de Acceso:</span>
                                <span>
                                    <a href="https://{{ $tenant->domains->first()->domain ?? '' }}" target="_blank" class="text-primary">
                                        {{ $tenant->domains->first()->domain ?? 'N/A' }}
                                    </a>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gestión de Suscripción -->
        <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
            <div class="card mb-4">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Gestión de Suscripción y Estado</h5>
                </div>
                <div class="card-body mt-3">
                    <form wire:submit.prevent="updateStatus">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Estado Actual</label>
                                <select wire:model="status" class="form-select">
                                    <option value="pending_review">Pendiente de Revisión</option>
                                    <option value="active">Activo</option>
                                    <option value="suspended">Suspendido</option>
                                    <option value="expired">Expirado</option>
                                    <option value="setup_failed">Error en Configuración</option>
                                </select>
                                @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Plan Asignado</label>
                                <select wire:model="plan_id" class="form-select">
                                    <option value="">Seleccione un plan</option>
                                    @foreach($planes as $plan)
                                        <option value="{{ $plan->id }}">{{ $plan->nombre }} (Hasta {{ $plan->max_miembros }} miembros)</option>
                                    @endforeach
                                </select>
                                @error('plan_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Fecha de Vencimiento Licencia</label>
                                <input type="date" wire:model="license_ends_at" class="form-control">
                                @error('license_ends_at') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-12 mt-4 d-flex align-items-center">
                                <button type="submit" class="btn btn-primary me-2">
                                    <span class="tf-icons bx bx-save me-1"></span> Guardar Cambios
                                    <span wire:loading wire:target="updateStatus" class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>
                                </button>
                                <a href="{{ url('/admin/dashboard') }}" class="btn btn-label-secondary">
                                    Volver al listado
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
