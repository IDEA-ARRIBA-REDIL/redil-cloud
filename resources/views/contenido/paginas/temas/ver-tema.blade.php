@extends('layouts/layoutMaster')

@section('title', 'Tema - Nuevo')

@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/quill/typography.scss',
    'resources/assets/vendor/libs/quill/editor.scss'
  ])
@endsection

@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/quill/quill.js'
  ])
@endsection

@section('page-script')
<script type="module">
  const editor = new Quill('#editor', {
    bounds: '#editor',
    readOnly: true,
    modules: {
    },
    theme: 'bubble'
  });

  editor.root.innerHTML = '{!! $tema->contenido !!}';
  editor.editor.enable(false);
</script>
@endsection

@section('content')

  @include('layouts.status-msn')

  <div class="card h-100">
    <img class="card-img-top" src="{{ $configuracion->version == 1  ? Storage::url($configuracion->ruta_almacenamiento.'/img/temas/'.$tema->portada) : Storage::url($configuracion->ruta_almacenamiento.'/img/temas/default.jpg')}}" alt="Card imagen {{ $tema->titulo }}" />

    <div class="card-header">
      <div class="d-flex justify-content-between">
        <div class="d-flex align-items-start">
          <div class="me-2 mt-1">
            <h3 class="mb-0 fw-semibold text-black lh-sm">{{ $tema->titulo }}</h3>
          </div>
        </div>
        <div class="ms-auto">
          @if($rolActivo->hasPermissionTo('temas.editar_tema'))
            <div class="dropdown zindex-2 border rounded p-1">
            <button type="button" class="btn dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical text-muted"></i></button>
              <ul class="dropdown-menu dropdown-menu-end">
                @if($rolActivo->hasPermissionTo('temas.editar_tema'))
                <li>
                  <a class="dropdown-item" href="{{route('tema.actualizar', $tema)}}">
                    <span class="me-2">Editar</span>
                  </a>
                </li>
                @endif
                @if($rolActivo->hasPermissionTo('temas.eliminar_tema'))
                <hr class="dropdown-divider">
                <li>
                  <a data-id="{{$tema->id}}"  data-nombre="{{$tema->titulo}}" class="dropdown-item text-danger waves-effect confirmacionEliminar" >
                    <span class="me-2">Eliminar</span>
                  </a>
                </li>
                @endif
              </ul>
            </div>
          @endif
        </div>
      </div>
    </div>

    <div class="card-body">
      <div class="d-flex my-2 mb-5">
        <h6 class="mb-0 mt-1 lh-sm me-3"> Categorias: </h6>
        @if($tema->categorias->count()> 0)
          @foreach($tema->categorias as $categoria)
            <span class="badge rounded-pill bg-label-primary fw-light">{{$categoria->nombre}}</span>
          @endforeach
        @else
          <span class="badge rounded-pill bg-label-info fw-light">Sin categoria </span>
        @endif
      </div>

      @if($tema->url)
      <div class="">
        <a href="{{ $tema->url }}" target="_blank" type="button" class="btn btn-sm btn-text-primary waves-effect waves-light">
          <span class="ti-xs ti ti-link me-2"></span>Abrir link
        </a>
      </div>
      @endif

      <div id="editor" class="mt-5">
      </div>
    </div>
  </div>



@endsection
