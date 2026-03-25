<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">SaaS /</span> Inquilinos (Iglesias)
        </h4>

        <div>
            <a href="{{ route('central.admin.super-admins') }}" class="btn btn-secondary btn-sm me-2">Gestionar Admins</a>
            <form action="{{ route('central.admin.logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">Cerrar Sesión</button>
            </form>
        </div>
    </div>

    <div class="card">
        <h5 class="card-header">Iglesias Registradas</h5>
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID (Prefijo)</th>
                        <th>Fecha Creación</th>
                        <th>Dominio Principal</th>
                        <th>Estado de Suspensión</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($tenants as $tenant)
                    <tr>
                        <td><strong>{{ $tenant->id }}</strong></td>
                        <td>{{ $tenant->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($tenant->domains->isNotEmpty())
                                <a href="http://{{ $tenant->domains->first()->domain }}:8000" target="_blank">{{ $tenant->domains->first()->domain }}</a>
                            @else
                                <span class="text-muted">Sin dominio</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $isSuspended = json_decode($tenant->data)->is_suspended ?? false;
                            @endphp
                            @if($isSuspended)
                                <span class="badge bg-label-danger">Suspendida</span>
                            @else
                                <span class="badge bg-label-success">Activa</span>
                            @endif
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i> Opciones
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="javascript:void(0);"><i class="bx bx-edit-alt me-1"></i> Ver Detalles</a>
                                    <!-- Suspensión Livewire logic here if needed -->
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
