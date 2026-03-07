@extends('layouts.layoutMaster')

@section('title', isset($informe) ? 'Editar Informe de Evidencia' : 'Nuevo Informe de Evidencia')

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
    'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
    'resources/assets/vendor/libs/quill/typography.scss',
    'resources/assets/vendor/libs/quill/editor.scss'
  ])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
    'resources/assets/vendor/libs/flatpickr/flatpickr.js',
    'resources/assets/vendor/libs/quill/quill.js'
  ])
@endsection

@section('page-script')
<script type="module">
  $(function() {
    // Inicializar Flatpickr
    $('.flatpickr-date').flatpickr({
      dateFormat: 'Y-m-d',
      allowInput: true
    });

    // Configuración común de Quill
    const quillConfig = {
      placeholder: 'Responde aquí',
    modules: {
      toolbar: [
        ['bold', 'italic', 'underline', 'strike'],
        [{ 'header': 1 }, { 'header': 2 }],
        [{ 'color': [] }, { 'background': [] }],
        [{ 'align': [] }],
        [{ 'size': ['small', false, 'large', 'huge'] }],
        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
        [{ 'font': [] }],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }, { 'list': 'check' }],
        [{ 'indent': '-1'}, { 'indent': '+1' }],
        ['link', 'image', 'video'],
        ['clean']
      ],
      imageResize: {
          modules: [ 'Resize', 'DisplaySize']
        },
    },
    theme: 'snow'
    };

    const setupQuill = (selector, inputId, initialContent) => {
      const editor = new Quill(selector, quillConfig);
      editor.root.innerHTML = initialContent;
      
      editor.on('text-change', () => {
        let html = editor.root.innerHTML;
        // Si no hay texto (solo espacios/saltos) y no hay imágenes, lo tomamos como vacío
        if (editor.getText().trim().length === 0 && editor.root.querySelectorAll('img, video, iframe').length === 0) {
          html = '';
        }
        $(inputId).val(html);
      });
      return editor;
    };

    @if($configuracion->habilitar_campo_1_informe_evidencias_grupo)
      setupQuill('#editor1', '#campo1', `{!! old('campo1', isset($informe) ? $informe->campo1 : '') !!}`);
    @endif

    @if($configuracion->habilitar_campo_2_informe_evidencias_grupo)
      setupQuill('#editor2', '#campo2', `{!! old('campo2', isset($informe) ? $informe->campo2 : '') !!}`);
    @endif

    @if($configuracion->habilitar_campo_3_informe_evidencias_grupo)
      setupQuill('#editor3', '#campo3', `{!! old('campo3', isset($informe) ? $informe->campo3 : '') !!}`);
    @endif

    // Manejo de carga en el formulario
    $('#form-informe').submit(function() {
      $('.btn-guardar').attr('disabled', 'disabled');
      Swal.fire({
        title: "Espera un momento",
        text: "Guardando informe...",
        icon: "info",
        showConfirmButton: false,
        allowOutsideClick: false
      });
    });
  });
</script>
@endsection

@section('content')
<h4 class="mb-1 fw-semibold text-primary">{{ isset($informe) ? 'Editar' : 'Crear' }} informe de evidencia</h4>
<p class="mb-4 text-black">Completa los campos para {{ isset($informe) ? 'actualizar' : 'registrar' }} el informe del grupo {{ $grupo->nombre }}.</p>

@include('layouts.status-msn')

<form id="form-informe" method="POST" action="{{ isset($informe) ? route('grupo.informeEvidencia.update', [$grupo, $informe]) : route('grupo.informeEvidencia.store', $grupo) }}">
  @csrf
  @if(isset($informe))
    @method('PATCH')
  @endif

  <div class="row">
    <div class="col-md-12">
      <div class="card mb-4">
        <div class="card-body">
          <div class="row">
            <!-- Nombre -->
            <div class="col-md-6 mb-3">
              <label class="form-label" for="nombre">Nombre del Informe</label>
              <input type="text" id="nombre" name="nombre" class="form-control" value="{{ old('nombre', isset($informe) ? $informe->nombre : '') }}" maxlength="100">
              @error('nombre') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <!-- Fecha -->
            <div class="col-md-6 mb-3">
              <label class="form-label" for="fecha">Fecha</label>
              <input type="text" id="fecha" name="fecha" class="form-control flatpickr-date" value="{{ old('fecha', isset($informe) ? $informe->fecha : date('Y-m-d')) }}">
              @error('fecha') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <!-- Campo 1 -->
            @if($configuracion->habilitar_campo_1_informe_evidencias_grupo)
              <div class="col-12 mb-3">
                <label class="form-label">{{ $configuracion->label_campo_1_informe_evidencias_grupo }} @if($configuracion->campo_1_informe_evidencias_grupo_obligatorio) @endif</label>
                <div id="editor1" style="min-height: 200px;"></div>
                <input type="hidden" id="campo1" name="campo1" value="{{ old('campo1', isset($informe) ? $informe->campo1 : '') }}">
                @error('campo1') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
              </div>
            @endif

            <!-- Campo 2 -->
            @if($configuracion->habilitar_campo_2_informe_evidencias_grupo)
              <div class="col-12 mb-3">
                <label class="form-label">{{ $configuracion->label_campo_2_informe_evidencias_grupo }} @if($configuracion->campo_2_informe_evidencias_grupo_obligatorio) @endif</label>
                <div id="editor2" style="min-height: 200px;"></div>
                <input type="hidden" id="campo2" name="campo2" value="{{ old('campo2', isset($informe) ? $informe->campo2 : '') }}">
                @error('campo2') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
              </div>
            @endif

            <!-- Campo 3 -->
            @if($configuracion->habilitar_campo_3_informe_evidencias_grupo)
              <div class="col-12 mb-3">
                <label class="form-label">{{ $configuracion->label_campo_3_informe_evidencias_grupo }} @if($configuracion->campo_3_informe_evidencias_grupo_obligatorio) @endif</label>
                <div id="editor3" style="min-height: 200px;"></div>
                <input type="hidden" id="campo3" name="campo3" value="{{ old('campo3', isset($informe) ? $informe->campo3 : '') }}">
                @error('campo3') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
              </div>
            @endif
          </div>

          <div class="mt-4">
            <button type="submit" class="btn btn-primary rounded-pill btn-guardar me-2">
              Guardar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>
@endsection
