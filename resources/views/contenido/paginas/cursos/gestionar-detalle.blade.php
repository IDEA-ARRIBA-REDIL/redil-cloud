@extends('layouts/layoutMaster')

@section('title', 'Detalle del Contenido del Curso')

@section('page-style')
    @vite([
        'resources/assets/vendor/libs/quill/typography.scss',
        'resources/assets/vendor/libs/quill/katex.scss',
        'resources/assets/vendor/libs/quill/editor.scss'
    ])
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/quill/quill.js'
    ])
@endsection

@section('page-script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script type="module">
    const editorContainer = document.getElementById('editor-container');
    if (editorContainer) {
            const editor = new Quill('#editor-container', {
                bounds: '#editor-container',
                placeholder: 'Escribe aquí la descripción del curso...',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'header': 1 }, { 'header': 2 }],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'align': [] }],
                        [{ 'size': ['small', false, 'large', 'huge'] }],
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        [{ 'font': [] }],
                        [{ 'list': 'ordered' }, { 'list': 'bullet' }, { 'list': 'check' }],
                        [{ 'indent': '-1' }, { 'indent': '+1' }],
                        ['link', 'image', 'video'],
                        ['clean']
                    ],
                    imageResize: {
                        modules: ['Resize', 'DisplaySize']
                    },
                },
                theme: 'snow'
            });

            // Cargar contenido inicial
            @php
                $initialContent = html_entity_decode($curso->descripcion_larga ?? '');
            @endphp
            try {
                const initialContent = @json($initialContent);
                editor.root.innerHTML = initialContent;
            } catch (e) {
                console.error('Error al cargar contenido inicial:', e);
            }

            // Sincronizar con el input hidden al hacer submit del formulario
            const formDescripcion = document.getElementById('form-descripcion');
            if (formDescripcion) {
                formDescripcion.addEventListener('submit', function(e) {
                    document.getElementById('descripcion_larga').value = editor.root.innerHTML;
                });
            }
        }
</script>
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-3 mb-1">
                <span class="text-primary fwsemibold">Detalle del aprendizaje</span>
            </h4>
            <div class="text-black small">Configura la descripción detallada y los puntos clave de aprendizaje para: {{ $curso->nombre }}</div>
        </div>
        <div>
            <a href="{{ route('cursos.gestionar') }}" class="btn btn-outline-primary">
                <i class="ti ti-arrow-left me-1"></i> Volver al listado
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <!-- Formulario de Descripción Larga (Independiente de Livewire) -->
            <div class="card mb-4">
                <div class="card-body">
                    <form id="form-descripcion" action="{{ route('cursos.actualizarDescripcion', $curso) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-12 mb-4">
                                <h5 class="text-black fw-semibold">Descripción avanzada del curso </h5>
                                <div class="form-text mb-2">Detalla de qué trata el curso. Esta información se mostrará en la página principal del mismo.</div>

                                <div id="editor-container" style="height: 200px;"></div>
                                <input type="hidden" name="descripcion_larga" id="descripcion_larga">
                            </div>

                            <div class="col-12 mt-4 mb-3">
                                <h5 class="text-black fw-semibold mb-3">Mensajes para el alumno</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" for="mensaje_bienvenida">Describe tu mensaje de bienvenida</label>
                                        <textarea id="mensaje_bienvenida" name="mensaje_bienvenida" class="form-control" rows="4">{{ $curso->mensaje_bienvenida }}</textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label" for="mensaje_aprobacion">Mensaje de aprobación</label>
                                        <textarea id="mensaje_aprobacion" name="mensaje_aprobacion" class="form-control" rows="4">{{ $curso->mensaje_aprobacion }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-device-floppy me-1"></i> Guardar información avanzada
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Componente Livewire para Syllabus (Qué Aprenderás) -->
            <div class="card">
                <div class="card-body">
                    @livewire('cursos.detalle.gestionar-detalle-curso', ['curso' => $curso])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
