@extends('layouts.layoutMaster')
@section('isEscuelasModule', true)
@section('title', 'Recursos para Alumnos')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-style')
    {{-- Estilos para mejorar la interfaz --}}
    <style>
        .resource-item {
            transition: background-color 0.2s ease-in-out;
        }
        .resource-item:hover {
            background-color: var(--bs-gray-100);
        }
        .resource-icon {
            font-size: 2rem;
            width: 40px;
            text-align: center;
        }
        .resource-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }
        .module-nav-link {
            font-size: 12px !important;
            padding: 0.6rem 0.8rem !important;
            transition: background-color 0.3s ease, color 0.3s ease;
            border-radius: 0.375rem;
            border: 1px solid transparent;
        }

        .module-nav-link.active {
            background-color: var(--bs-primary) !important;
            color: var(--bs-white) !important;
            border-color: var(--bs-primary) !important;
        }

        .module-nav-link:not(.active):hover {
            background-color: var(--bs-gray-200);
        }
    </style>
@endsection

@section('content')
    @include('layouts.status-msn')

    {{-- Barra de Navegación del Módulo --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card mb-0 p-0 border-0 shadow-sm">
                <ul class="nav nav-pills nav-fill justify-content-start flex-column flex-md-row gap-1 px-2 py-1">
                    @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_dashboard_general'))
                    <li class="nav-item">
                        <a href="{{ route('maestros.dashboardClase', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}"
                            class="nav-link module-nav-link waves-effect waves-light {{ request()->routeIs('maestros.dashboardClase') ? 'active' : '' }}">
                            <i class="mdi mdi-view-dashboard-outline me-1"></i> Dashboard general
                        </a>
                    </li>
                    @endif
                    @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_calificacion_detallada'))
                    <li class="nav-item">
                        <a href="{{ route('maestros.calificacionMultiple', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}"
                            class="nav-link module-nav-link waves-effect waves-light {{ request()->routeIs('maestros.calificacionMultiple') ? 'active' : '' }}">
                            <i class="mdi mdi-table-edit me-1"></i> Calificación detallada
                        </a>
                    </li>
                    @endif
                    @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_reportes_asistencia'))
                    <li class="nav-item">
                        <a href="{{ route('maestros.reporteAsistencia', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}"
                            class="nav-link module-nav-link waves-effect waves-light {{ request()->routeIs('maestros.reporteAsistencia') ? 'active' : '' }}">
                            <i class="mdi mdi-calendar-check-outline me-1"></i> Reportes de asistencia
                        </a>
                    </li>
                    @endif
                    @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_recursos_alumnos'))
                    <li class="nav-item">
                        <a href="{{ route('maestros.recursosAlumnos', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}"
                            class="nav-link module-nav-link waves-effect waves-light {{ request()->routeIs('maestros.recursosAlumnos') ? 'active' : '' }}">
                            <i class="mdi mdi-folder-multiple-outline me-1"></i> Recursos alumnos
                        </a>
                    </li>
                    @endif
                    @if(isset($rolActivo))
                    <li class="nav-item">
                        <a href="{{ route('maestros.gestionarItems', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}"
                            class="nav-link module-nav-link waves-effect waves-light {{ request()->routeIs('maestros.gestionarItems') ? 'active' : '' }}">
                            <i class="mdi mdi-list-box-outline me-1"></i> Gestionar Items
                        </a>
                    </li>
                    @endif
                    @if(isset($rolActivo) && $rolActivo->hasPermissionTo('escuelas.tab_calificacion_grilla'))
                    <li class="nav-item">
                        <a href="{{ route('maestros.calificacionGrilla', ['maestro' => $maestro, 'horarioAsignado' => $horarioAsignado]) }}"
                            class="nav-link module-nav-link waves-effect waves-light {{ request()->routeIs('maestros.calificacionGrilla') ? 'active' : '' }}">
                            <i class="mdi mdi-grid me-1"></i> Calificación Grilla
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    @livewire('Maestros.gestion-recursos', [
        'horarioAsignado' => $horarioAsignado,
        'maestro' => $maestro,
    ])
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const resourceModal = new bootstrap.Modal(document.getElementById('resourceModal'));
    const modalTitle = document.getElementById('resourceModalLabel');
    const modalSaveBtn = document.getElementById('btn-guardar-recurso');

    // --- Lógica para abrir el modal en modo "Crear" ---
    document.getElementById('btn-crear-recurso').addEventListener('click', function () {
        // Resetear el formulario
        document.getElementById('resourceId').value = '';
        document.getElementById('resourceNombre').value = '';
        document.getElementById('resourceDescripcion').value = '';
        document.getElementById('resourceLinkExterno').value = '';
        document.getElementById('resourceLinkYoutube').value = '';
        document.getElementById('resourceArchivo').value = '';

        // Configurar el modal para "Crear"
        modalTitle.textContent = 'Crear Nuevo Recurso';
        modalSaveBtn.textContent = 'Guardar Recurso';
    });

    // --- Lógica para abrir el modal en modo "Editar" ---
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function () {
            const data = this.dataset;

            // Llenar el formulario con los datos del recurso
            document.getElementById('resourceId').value = data.id;
            document.getElementById('resourceNombre').value = data.nombre;
            document.getElementById('resourceDescripcion').value = data.descripcion;
            document.getElementById('resourceLinkExterno').value = data.link_externo;
            document.getElementById('resourceLinkYoutube').value = data.link_youtube;

            // Configurar el modal para "Editar"
            modalTitle.textContent = 'Editar Recurso';
            modalSaveBtn.textContent = 'Guardar Cambios';

            // Mostrar el modal
            resourceModal.show();
        });
    });

    // --- Lógica para el botón de "Eliminar" con confirmación ---
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function () {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡No podrás revertir esta acción!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, ¡eliminar!',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-primary me-1',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Aquí iría la lógica para eliminar el recurso (p.ej. una petición a tu backend)
                    Swal.fire(
                        '¡Eliminado!',
                        'El recurso ha sido eliminado.',
                        'success'
                    );
                }
            });
        });
    });

     Livewire.on('notificacion', (event) => {
                    const detail = Array.isArray(event) ? event[0] :
                        event; // Livewire 3 puede pasar el evento en un array
                    Swal.fire({
                        icon: 'success',
                        title: detail.titulo || '¡Realizado!', // Título del modal
                        text: detail.texto, // Texto del cuerpo del modal
                        timer: detail.timer || 2500, // Duración antes de que se cierre solo (opcional)
                        showConfirmButton: detail.showConfirmButton === undefined ? false : detail
                            .showConfirmButton, // Mostrar botón de confirmación (por defecto no)
                        // Estilos para un modal centrado (por defecto SweetAlert es centrado)
                        // No necesitas 'toast: true' ni 'position: top-end' para un modal centrado
                    });
                });

    // Inicializar tooltips de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
