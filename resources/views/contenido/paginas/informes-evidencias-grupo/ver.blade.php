@extends('layouts.layoutMaster')

@section('title', 'Ver Informe de Evidencia')

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
    'resources/assets/vendor/libs/quill/typography.scss',
    'resources/assets/vendor/libs/quill/editor.scss'
  ])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
    'resources/assets/vendor/libs/quill/quill.js'
  ])
@endsection

@section('page-script')
<script type="module">
  document.addEventListener('DOMContentLoaded', function () {
    const quillConfig = {
      readOnly: true,
      modules: {},
      theme: 'bubble'
    };

    @for($i = 1; $i <= 3; $i++)
      @php
          $habilitar = "habilitar_campo_{$i}_informe_evidencias_grupo";
          $campoName = "campo{$i}";
          $content = $informe->$campoName;
      @endphp
      @if($configuracion->$habilitar && !empty($content))
        const editor{{$i}} = new Quill('#editor{{$i}}', quillConfig);
        editor{{$i}}.root.innerHTML = `{!! $content !!}`;
      @endif
    @endfor
  });
</script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('.form-eliminar');
    forms.forEach(form => {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        Swal.fire({
          title: '¿Estás seguro?',
          text: "¡No podrás revertir esto!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Sí, eliminarlo!',
          cancelButtonText: 'Cancelar'
        }).then((result) => {
          if (result.isConfirmed) {
            this.submit();
          }
        });
      });
    });
  });
</script>
@endsection

@section('content')

  @include('layouts.status-msn')

  <div class="card h-100">

    <div class="card-header">
      <div class="d-flex justify-content-between">
        <div class="d-flex align-items-start">
          <div class="me-2 mt-1">
            <h3 class="mb-0 fw-semibold text-black lh-sm">{{ $informe->nombre }}</h3>
            <p class="text-black mb-0"><i class="ti ti-calendar me-1"></i> {{ $informe->fecha }}</p>
          </div>
        </div>
        <div class="ms-auto">
          <div class="dropdown zindex-2 border rounded p-1">
            <button type="button" class="btn dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical text-black"></i></button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a class="dropdown-item" href="{{ route('grupo.informeEvidencia.editar', [$grupo, $informe]) }}">
                   Editar
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="{{ route('grupo.informeEvidencia.descargar', [$grupo, $informe]) }}">
                  Descargar
                </a>
              </li>
              <hr class="dropdown-divider">
              <li>
                <form action="{{ route('grupo.informeEvidencia.eliminar', [$grupo, $informe]) }}" method="POST" class="form-eliminar">
                  @csrf
                  @method('DELETE')
                  <input type="hidden" name="source" value="{{ str_contains(url()->previous(), 'administrativo') ? 'admin' : 'local' }}">
                  <button type="submit" class="dropdown-item text-danger">
                    <i class="ti ti-trash me-2"></i> Eliminar
                  </button>
                </form>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div class="card-body">
      <div class="row">
        @for($i = 1; $i <= 3; $i++)
          @php
              $habilitar = "habilitar_campo_{$i}_informe_evidencias_grupo";
              $labelConf = "label_campo_{$i}_informe_evidencias_grupo";
              $campoName = "campo{$i}";
              $content = $informe->$campoName;
          @endphp

          @if($configuracion->$habilitar && !empty($content))
            <div class="col-12 mb-4">
              <h5 class="fw-bold mb-2 text-primary">{{ $configuracion->$labelConf ?? "Campo {$i}" }}</h5>
              <div id="editor{{$i}}" class="mt-2 text-black">
              </div>
            </div>
          @endif
        @endfor
      </div>

      <div class="mt-4">
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary rounded-pill waves-effect">
          <i class="ti ti-arrow-left me-1"></i> Volver
        </a>
      </div>
    </div>
  </div>

@endsection
