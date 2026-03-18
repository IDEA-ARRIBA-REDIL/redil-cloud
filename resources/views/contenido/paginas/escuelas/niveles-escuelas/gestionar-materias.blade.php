@section('isEscuelasModule', true)

@extends('layouts/layoutMaster')

@section('title', 'Gestionar Materias del Grado')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/editor.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/quill/quill.js'])
@endsection

@section('page-script')
    <script type="module">
        // Inicialización de Quill para la descripción en el Offcanvas
        const editor = new Quill('#editor-materia', {
            bounds: '#editor-materia',
            placeholder: 'Descripción de la materia',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{
                        'list': 'ordered'
                    }, {
                        'list': 'bullet'
                    }],
                    ['clean']
                ],
            },
            theme: 'snow'
        });

        editor.on('text-change', () => {
            $('#descripcion-materia').val(editor.root.innerHTML);
        });

        // Confirmación de eliminación
        window.confirmarEliminacionMateria = function(url, nombre) {
            Swal.fire({
                title: '¿Eliminar materia?',
                text: `¿Estás seguro de que deseas eliminar "${nombre}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ea5455',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-danger me-2',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    let form = document.createElement('form');
                    form.action = url;
                    form.method = 'POST';
                    form.innerHTML = `@csrf @method('DELETE')`;
                    document.body.appendChild(form);
                    form.submit();
                }
            })
        }
    </script>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0"> <span class="text-primary">Materias de: {{ $nivel->nombre }}</span></h4>
            <small class="text-black">Gestiona el bloque de materias que componen este grado.</small>
        </div>
        <button class="btn btn-primary rounded-pill shadow-sm" data-bs-toggle="offcanvas"
            data-bs-target="#offcanvasAddMateria">
            <i class="ti ti-plus me-1"></i> Nueva materia
        </button>
    </div>

    @include('layouts.status-msn')

    <div class="row">
        @forelse($materiasAgrupadas as $materia)
            <div class="col-12 col-md-6 col-lg-4 mb-4">
                <div class="card h-100 border-0 shadow-sm transition-all" style="border-radius: 12px;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex align-items-center">
                                <div>
                                    <h5 class="mb-0 fw-bold">{{ $materia->nombre }}</h5>
                                    <small class="text-muted">ID: #{{ $materia->id }}</small>
                                </div>
                            </div>
                            <div class="d-flex gap-1">
                                <a href="{{ route('materias.gestionar', $materia->id) }}" class="btn btn-sm btn-icon btn-text-secondary rounded-pill" title="Datos principales">
                                    <i class="ti ti-info-hexagon"></i>
                                </a>
                                <a href="{{ route('materias.horarios', $materia->id) }}" class="btn btn-sm btn-icon btn-text-secondary rounded-pill" title="Horarios">
                                    <i class="ti ti-clock"></i>
                                </a>
                                <a href="{{ route('materias.modelo', $materia->id) }}" class="btn btn-sm btn-icon btn-text-secondary rounded-pill" title="Modelo de calificación">
                                    <i class="ti ti-template"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-icon btn-text-danger rounded-pill"
                                    onclick="confirmarEliminacionMateria('{{ route('niveles-escuelas.eliminar-materia', [$escuela, $nivel, $materia]) }}', '{{ $materia->nombre }}')">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mt-3 text-black" style="font-size: 0.9rem;">
                            {!! $materia->description ?? $materia->descripcion !!}
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="card  border shadow-none py-5">
                    <div class="card-body">
                        <i class="ti ti-info-circle fs-1 mb-2"></i>
                        <h5>No hay materias registradas para este grado</h5>
                        <p>Haz clic en "Nueva materia" para comenzar a poblar este nivel.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        <a href="{{ route('escuelas.niveles', $escuela) }}" class="btn btn-outline-secondary rounded-pill">
            <i class="ti ti-arrow-left me-1"></i> Volver a Grados
        </a>
    </div>

    <!-- Offcanvas para nueva materia -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddMateria" aria-labelledby="offcanvasAddMateriaLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasAddMateriaLabel" class="offcanvas-title">Nueva Materia para {{ $nivel->nombre }}</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 pt-0 h-100">
            <form class="pt-3" action="{{ route('niveles-escuelas.guardar-materia', [$escuela, $nivel]) }}"
                method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="nombre-materia">Nombre de la materia</label>
                    <input type="text" class="form-control" id="nombre-materia" name="nombre"
                        placeholder="Ej: Teología I" required />
                </div>
                <div class="mb-4">
                    <label class="form-label">Descripción</label>
                    <div id="editor-materia" style="height: 200px;"></div>
                    <input type="hidden" id="descripcion-materia" name="descripción">
                </div>
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill  me-2"
                        data-bs-dismiss="offcanvas">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill ">Crear Materia</button>

                </div>
            </form>
        </div>
    </div>
@endsection
