@extends('layouts.layoutMaster')

@section('title', 'Tipos de Hitos')

<meta name="csrf-token" content="{{ csrf_token() }}">

@section('vendor-style')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('content')
<div class="container-fluid mt-4">
    {{-- Header --}}
    <div class="row g-4 mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1 fw-bold text-dark d-flex align-items-center">
                    <i class="ti ti-trophy me-2 text-primary"></i> Tipos de Hitos
                </h4>
                <p class="text-muted mb-0">Administra las clasificaciones, comportamientos y capacidades de los hitos congregacionales.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('configuracion.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="ti ti-arrow-left me-1"></i> Configuración
                </a>
                <a href="{{ route('tipo-hitos.creacionTipoHitos') }}" class="btn btn-primary rounded-pill px-4">
                    <i class="ti ti-plus me-1"></i> Crear nuevo
                </a>
            </div>
        </div>
    </div>

    @include('layouts.status-msn')

    {{-- Cards Grid --}}
    <div class="row g-4 mb-4">
        @forelse ($tiposHitos as $tipo)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border shadow-sm">
                <div class="card-body">
                    {{-- Header de la Card: Icono + Nombre + Acciones --}}
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-md rounded d-flex align-items-center justify-content-center text-white"
                                 style="background-color: {{ $tipo->color ?? '#7c5cfc' }};">
                                <i class="{{ $tipo->icono ?? 'ti ti-award' }} fs-4"></i>
                            </div>
                            <div>
                                <h5 class="card-title text-primary mb-0 fw-bold">{{ $tipo->nombre }}</h5>
                                <span class="badge text-white px-2 py-1 mt-1" style="background-color: {{ $tipo->color ?? '#7c5cfc' }}; font-size: 0.72rem;">
                                    {{ $tipo->slug }}
                                </span>
                            </div>
                        </div>

                        <div class="d-flex gap-1">
                            <a href="{{ route('tipo-hitos.actualizacionTipoHitos', $tipo->id) }}"
                               class="btn btn-icon btn-text-secondary p-0 rounded-circle bg-transparent border-0 shadow-none"
                               data-bs-toggle="tooltip"
                               title="Editar tipo de hito">
                                <i class="ti ti-edit ti-md text-dark"></i>
                            </a>

                            @if(!in_array($tipo->slug, ['general', 'automatico', 'actividad', 'manual']))
                            <form action="{{ route('tipo-hitos.eliminarTipoHitos', $tipo->id) }}" method="POST" class="delete-form m-0" onsubmit="return confirmDelete(event, this)">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-text-secondary p-0 rounded-circle bg-transparent border-0 shadow-none" data-bs-toggle="tooltip" title="Eliminar">
                                    <i class="ti ti-trash ti-md text-danger"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>

                    {{-- Descripción --}}
                    <p class="text-muted small mb-3">
                        {{ $tipo->descripcion ?: 'Sin descripción detallada para este tipo de hito.' }}
                    </p>

                    {{-- Hitos Vinculados --}}
                    <div class="mb-3 py-2 border-top border-bottom d-flex align-items-center justify-content-between">
                        <span class="small text-muted">Hitos registrados:</span>
                        <span class="badge bg-primary text-white">
                            <i class="ti ti-trophy me-1"></i> {{ $tipo->hitos_count ?? 0 }}
                        </span>
                    </div>

                    {{-- Flags y Badges de Capacidades --}}
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        {{-- Botón de estado (AJAX) --}}
                        <button type="button"
                            id="btn-estado-{{ $tipo->id }}"
                            onclick="confirmarCambioEstado({{ $tipo->id }}, '{{ $tipo->nombre }}')"
                            class="btn btn-sm badge {{ $tipo->activo ? 'bg-success' : 'bg-danger' }} text-white border-0"
                            style="cursor: pointer;">
                            {{ $tipo->activo ? 'Activo' : 'Inactivo' }}
                        </button>

                        @if($tipo->requiere_trigger)
                            <span class="badge bg-primary text-white" title="Se dispara por logros o pasos">
                                <i class="ti ti-cpu me-1"></i> Triggers
                            </span>
                        @endif

                        @if($tipo->requiere_actividad)
                            <span class="badge bg-info text-white" title="Vinculado a eventos o actividades">
                                <i class="ti ti-ticket me-1"></i> Actividad
                            </span>
                        @endif

                        @if($tipo->slug === 'manual')
                            <span class="badge bg-warning text-white" title="Reconocimiento pastoral manual">
                                <i class="ti ti-award me-1"></i> Manual
                            </span>
                        @endif

                        @if($tipo->permite_fotos_usuario)
                            <span class="badge bg-secondary text-white" title="Permite fotos de usuarios">
                                <i class="ti ti-photo me-1"></i> Fotos
                            </span>
                        @endif

                        @if($tipo->permite_likes)
                            <span class="badge bg-danger text-white" title="Permite reacciones Me Gusta">
                                <i class="ti ti-heart me-1"></i> Likes
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border">
                <div class="card-body text-center py-5">
                    <i class="ti ti-trophy fs-1 pb-2 text-muted"></i>
                    <h6 class="text-center text-muted">No hay tipos de hitos registrados en el sistema.</h6>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    {{-- Paginación --}}
    <div class="row">
        <div class="col-12 d-flex justify-content-end">
            {{ $tiposHitos->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@push('scripts')
<script>
    // 1. Funciones para cambiar estado (AJAX)
    function confirmarCambioEstado(id, nombre) {
        Swal.fire({
            title: '¿Cambiar estado?',
            text: `Vas a cambiar el estado de "${nombre}"`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#7367f0',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, cambiar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                ejecutarCambioEstado(id);
            }
        });
    }

    function ejecutarCambioEstado(id) {
        const url = `/tipo-hitos/cambiar-estado/${id}`;
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const boton = document.getElementById(`btn-estado-${id}`);
                if (data.nuevo_estado) {
                    boton.classList.remove('bg-danger');
                    boton.classList.add('bg-success');
                    boton.innerText = 'Activo';
                } else {
                    boton.classList.remove('bg-success');
                    boton.classList.add('bg-danger');
                    boton.innerText = 'Inactivo';
                }
                Swal.fire({
                    icon: 'success',
                    title: '¡Actualizado!',
                    text: data.mensaje,
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'No se pudo cambiar el estado', 'error');
        });
    }

    function confirmDelete(event, form) {
        event.preventDefault();
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡No podrás revertir esta acción!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#7367f0',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
        return false;
    }

    // 2. Notificaciones automáticas de sesión
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: "{{ session('success') }}",
            timer: 3000,
            showConfirmButton: false
        });
        @endif

        @if(session('status_error'))
        Swal.fire({
            icon: 'error',
            title: 'Atención',
            text: "{{ session('status_error') }}",
            confirmButtonText: 'Entendido',
            customClass: { confirmButton: 'btn btn-primary' },
            buttonsStyling: false
        });
        @endif
    });
</script>
@endpush
