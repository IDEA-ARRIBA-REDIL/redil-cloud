<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row g-4 mb-4">
        <!-- Total -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Total Iglesias</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ $tenants->count() }}</h4>
                            </div>
                            <small>SaaS Registradas</small>
                        </div>
                        <span class="badge bg-label-primary rounded p-2">
                            <i class="bx bx-buildings bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Activas -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Activas</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ $tenants->where('status', 'active')->count() }}</h4>
                            </div>
                            <small>Cuentas en regla</small>
                        </div>
                        <span class="badge bg-label-success rounded p-2">
                            <i class="bx bx-check-shield bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Pendientes -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Pendientes</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ $tenants->where('status', 'pending_review')->count() }}</h4>
                            </div>
                            <small>Requieren aprobación</small>
                        </div>
                        <span class="badge bg-label-warning rounded p-2">
                            <i class="bx bx-time-five bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Suspendidas/Expiradas -->
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>Inactivas</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2">{{ $tenants->whereIn('status', ['suspended', 'expired'])->count() }}</h4>
                            </div>
                            <small>Suspendidas o Expiradas</small>
                        </div>
                        <span class="badge bg-label-danger rounded p-2">
                            <i class="bx bx-error-circle bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center flex-column flex-sm-row">
            <h5 class="card-title mb-0">Iglesias Registradas</h5>
            <div class="mt-3 mt-sm-0 d-flex gap-2">
                <a href="{{ url('/admin/super-admins') }}" class="btn btn-label-secondary">
                    <span class="tf-icons bx bx-group me-1"></span>Gestionar Admins
                </a>

                <form action="{{ url('/admin/logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <span class="tf-icons bx bx-log-out me-1"></span>Cerrar Sesión
                    </button>
                </form>
            </div>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table border-top">
                <thead>
                    <tr>
                        <th>Iglesia / Contacto</th>
                        <th>Subdominio</th>
                        <th>Plan</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($tenants as $tenant)
                    <tr>
                        <td>
                            <div class="d-flex justify-content-start align-items-center">
                                <div class="avatar-wrapper">
                                    <div class="avatar avatar-sm me-3">
                                        <span class="avatar-initial rounded-circle bg-label-primary">{{ strtoupper(substr($tenant->church_name, 0, 2)) }}</span>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <a href="{{ url('/admin/tenants/' . $tenant->id) }}" class="text-body text-truncate"><span class="fw-medium">{{ $tenant->church_name }}</span></a>
                                    <small class="text-muted"><i class="bx bx-user me-1" style="font-size: 12px;"></i>{{ $tenant->pastor_name }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($tenant->domains->isNotEmpty())
                                <a href="https://{{ $tenant->domains->first()->domain }}" target="_blank" class="fw-medium text-primary">
                                    {{ $tenant->domains->first()->domain }}
                                </a>
                            @else
                                <span class="text-muted">Sin dominio</span>
                            @endif
                            <div class="mt-1">
                                <small class="text-muted"><i class="bx bx-calendar me-1" style="font-size: 12px;"></i>{{ $tenant->created_at->format('d/m/Y') }}</small>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-label-info">{{ $tenant->plan_id ? $tenant->plan->nombre ?? 'N/A' : 'Sin Plan' }}</span>
                        </td>
                        <td>
                            @if($tenant->status === 'active')
                                <span class="badge bg-label-success">Activa</span>
                            @elseif($tenant->status === 'pending_review')
                                <span class="badge bg-label-warning">Pendiente</span>
                            @elseif($tenant->status === 'suspended')
                                <span class="badge bg-label-danger">Suspendida</span>
                            @elseif($tenant->status === 'expired')
                                <span class="badge bg-label-secondary">Expirada</span>
                            @else
                                <span class="badge bg-label-dark">{{ $tenant->status }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i> Opciones
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ url('/admin/tenants/' . $tenant->id) }}">
                                        <i class="bx bx-show me-1"></i> Ver Detalles / Gestionar
                                    </a>
                                    @if($tenant->domains->isNotEmpty())
                                        <a class="dropdown-item" href="https://{{ $tenant->domains->first()->domain }}" target="_blank">
                                            <i class="bx bx-link-external me-1"></i> Abrir Sistema
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No hay inquilinos registrados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
