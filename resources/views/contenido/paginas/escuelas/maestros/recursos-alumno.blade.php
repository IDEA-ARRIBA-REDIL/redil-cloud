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
    </style>
@endsection

@section('content')
    @include('layouts.status-msn')

  
      @livewire('Maestros.gestion-recursos', [
            'horarioAsignado' => $horarioAsignado,
            'maestro' => $maestro, // <-- CAMBIO CLAVE: El nombre del parámetro coincide con el controlador
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