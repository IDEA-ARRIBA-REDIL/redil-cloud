@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Relaciones Familiares')

<!-- Page -->
@section('page-style')
@vite([
'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss',
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js',
])
@endsection

@section('page-script')
<script type="module">

$(document).ready(function() {
    $('.select2').select2({
        placeholder: 'Filtrar por tipo de petición',
      }
    );
  });

  $(document).ready(function() {
    $('.select2GeneradorExcel').select2({
      dropdownParent: $('#modalGeneradorExcel')
    });
  });

  // Eso arragle un error en los select2 con el scroll cuando esta dentro de un modal
  $('#modalGeneradorExcel').on('scroll', function(event) {
    $(this).find(".select2GeneradorExcel").each(function() {
      $(this).select2({
        dropdownParent: $(this).parent()
      });
    });
  });

  $(".clearAllItems").click(function() {
   var value = $(this).data('select');
    $('#' + value).val(null).trigger('change');
  });

  $(".selectAllItems").click(function() {
   var value = $(this).data('select');
    $("#" + value + " > option").prop("selected", true);
    $("#" + value).trigger("change");
  });

</script>
@endsection

@section('content')

<h4 class="mb-1 fw-semibold text-primary">Informe de relaciones familiares</h4>
<p class="mb-4 text-black">Conoce las relaciones familiares de tu congregación.</p>

@include('layouts.status-msn')

<form class="forms-sample" method="GET" action="{{ route('familias.informes') }}">
  <div class="row">
      <!-- Familiar principal -->
        @livewire('Usuarios.usuarios-para-busqueda', [
          'id' => 'buscador_usuario',
          'tipoBuscador' => 'unico',
          'conDadosDeBaja' => 'no',
          'class' => 'col-12 col-md-3 mb-3',
          'placeholder' => 'Selecciona un usuario',
          'queUsuariosCargar'=>'todos',
          'modulo' => 'familiar-principal',
          'estiloSeleccion' => 'pequeno',
          'usuarioSeleccionadoId'=>$userId ? $userId : ''
        ])
      <!--/ Familiar principal -->

      <!--/ Buscar Grupo -->
      @livewire('Grupos.grupos-para-busqueda', [
        'id' => 'inputGruposIds',
        'class' => 'col-12 col-md-3 mb-3',
        'estiloSeleccion' => 'pequeno',
        'placeholder' => 'Selecciona un grupo',
        'conDadosDeBaja' => 'no',
        'grupoSeleccionadoId'=>$grupoId ? $grupoId : '',
        'unico' => TRUE
      ])

      <!-- Por tipo ministerio -->
      <div class="col-7 col-md-4 mb-2">
        <select id="filtroTipoMinisterio" name="filtroTipoMinisterio" class="select2BusquedaAvanzada form-select">
            <option value="0" {{ !$tipoMinisterioSeleccionado || $tipoMinisterioSeleccionado == 0 ? 'selected' : '' }}>Ministerio completo</option>
            <option value="1" {{ $tipoMinisterioSeleccionado == 1 ? 'selected' : '' }}>Ministerio directo</option>
        </select>
      </div>

      <div class="col-5 col-md-2 mb-2">
        <div class="input-group" >
          <button class="btn btn-outline-primary px-2 px-md-3" type="submit" id="button-addon2"><i class="ti ti-search"></i></button>
          @if(count($parientes) > 0)
          <a href="{{ route('familias.informes') }}" class="btn btn-outline-danger btn-sm " type="submit"><i class="ti ti-x"></i></a>
          @endif
        </div>
      </div>

      <div class="col-12 mt-2 d-flex justify-content-end">

         @if(count($parientes) > 0)
          <button type="button" class="btn btn-outline-secondary waves-effect px-2 px-md-3" data-bs-toggle="modal" data-bs-target="#modalGeneradorExcel">
            <i class="ti ti-file-download"></i> <span class="d-none d-md-block">Descargar excel</span></span>
          </button>
          @endif
      </div>
  </div>
</form>

<div class="row my-3 g-4">
  @if(count($parientes) > 0)
    @foreach($parientes as $pariente)
    <div class="col-lg-4 col-md-6 col-12">
      <div class="card border rounded p-4">
        <div class="card-body">

          <div class="d-flex justify-content-center align-items-center user-name">
            <div class="avatar-wrapper">
              <div class="avatar avatar-lg me-4"><img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$pariente->foto) : $configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$pariente->foto }}" alt="foto {{ $pariente->primer_nombre }}" class="rounded-circle"></div>
            </div>
            <div class="d-flex flex-column">
              <h6 class="mb-1 fw-bold">{{ $pariente->primer_nombre }}</h6>
              <small>¿Responsable?
                @if($pariente->es_el_responsable)
                <span class="mx-2 badge bg-label-success">Si</span>
                @else
                <span class="mx-2 badge bg-label-secondary">No</span>
                @endif
              </small>
            </div>
          </div>

          <div class="my-3 gap-2 divider">
            <div class="divider-text">
              <span class="pb-1 "><b>Relación:</b> {{ $pariente->genero == 0 ? $pariente->nombre_masculino : $pariente->nombre_femenino }} de </span>
            </div>
          </div>

          <div class="d-flex justify-content-center align-items-center user-name">
            <div class="avatar-wrapper">
              <div class="avatar avatar-lg me-4"><img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$pariente->foto) : $configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$pariente->foto }}" alt="foto {{ $pariente->primer_nombre }}" class="rounded-circle"></div>
            </div>
            <div class="d-flex flex-column">
              <h6 class="mb-1 fw-bold">{{ $pariente->nombreParienteSecundario }}</h6>
              <small>¿Responsable?
                @if($pariente->responsableParienteSecundario)
                <span class="mx-2 badge bg-label-success">Si</span>
                @else
                <span class="mx-2 badge bg-label-secondary">No</span>
                @endif
              </small>
            </div>
          </div>

        </div>
      </div>
    </div>
    @endforeach
  @else
  <div class="py-4">
    <center>
      <i class="ti ti-home-heart fs-1 pb-1"></i>
      <h6 class="text-center">No hay relaciones familiares</h6>
    </center>
  </div>
  @endif
</div>

 <!-- Modal generador de excel -->
 <div class="modal fade modalSelect2" id="modalGeneradorExcel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <form class="forms-sample" method="POST"  action="{{ route('familias.generarExcel') }}">
      @csrf
      <textarea id="parametros-busqueda" name="parametrosBusqueda" class="d-none">{{json_encode(request()->input())}}</textarea>
      <div class="modal-content">
        <div class="modal-header d-flex flex-column">
          <h4 class="modal-title">Generador de excel</h4>
          <p class="modal-subtitle text-center">Selecciona los campos que deseas exportar en el archivo Excel.</p>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">

            <!-- Informacion personal -->
            <div class="col-12 mb-3">
              <label for="informacionPersonal" class="form-label">Información personal pariente principal
                (<a href="javascript:;" data-select="informacionPersonal" class="selectAllItems"><span class="fw-medium">Seleccionar todos</span></a> | <a href="javascript:;" data-select="informacionPersonal" class="clearAllItems"><span class="fw-medium">Quitar todos</span></a>)
              </label>
              <select id="informacionPersonal" name="informacionPersonal[]" class="select2GeneradorExcel form-select" multiple>
                @foreach($camposInformeExcel->where('selector_id',1) as $campo)
                <option value="{{ $campo->id }}">{{ $campo->nombre_campo_informe }}</option>
                @endforeach
              </select>
            </div>

            <!-- Informacion ministerial -->
            <div class="col-12 mb-3">
              <label for="informacionMinisterial" class="form-label">Información ministerial pariente principal
                (<a href="javascript:;" data-select="informacionMinisterial" class="selectAllItems"><span class="fw-medium">Seleccionar todos</span></a> | <a href="javascript:;" data-select="informacionMinisterial" class="clearAllItems"><span class="fw-medium">Quitar todos</span></a>)
              </label>
              <select id="informacionMinisterial" name="informacionMinisterial[]" class="select2GeneradorExcel form-select" multiple>
                @foreach($pasosCrecimiento as $pasoCrecimiento)
                <option value="{{ $pasoCrecimiento->id }}">{{ $pasoCrecimiento->nombre }}</option>
                @endforeach
              </select>
            </div>

            <!-- Informacion congregacional -->
            <div class="col-12 mb-3">
              <label for="informacionCongregacional" class="form-label">Información congregacional pariente principal
                (<a href="javascript:;" data-select="informacionCongregacional" class="selectAllItems"><span class="fw-medium">Seleccionar todos</span></a> | <a href="javascript:;" data-select="informacionCongregacional" class="clearAllItems"><span class="fw-medium">Quitar todos</span></a>)
              </label>
              <select id="informacionCongregacional" name="informacionCongregacional[]" class="select2GeneradorExcel form-select" multiple>
                @foreach($camposInformeExcel->where('selector_id',2) as $campo)
                <option value="{{ $campo->id }}">{{ $campo->nombre_campo_informe }}</option>
                @endforeach
              </select>
            </div>

            @if($configuracion->visible_seccion_campos_extra)
            <!-- Informacion congregacional -->
            <div class="col-12 mb-3">
              <label for="informacionCamposExtras" class="form-label">Información {{$configuracion->label_seccion_campos_extra}}
                (<a href="javascript:;" data-select="informacionCamposExtras" class="selectAllItems"><span class="fw-medium">Seleccionar todos</span></a> | <a href="javascript:;" data-select="informacionCamposExtras" class="clearAllItems"><span class="fw-medium">Quitar todos</span></a>)
              </label>
              <select id="informacionCamposExtras" name="informacionCamposExtras[]" class="select2GeneradorExcel form-select" multiple>
                @foreach($camposExtras as $campo)
                <option value="{{ $campo->id }}">{{ $campo->nombre }}</option>
                @endforeach
              </select>
            </div>
            @endif

            <!-- Información petición-->
            <div class="col-12 mb-3">
              <label for="camposRelacionesUsuarios" class="form-label">Información campos relación familiar
                (<a href="javascript:;" data-select="camposRelacionesUsuarios" class="selectAllItems"><span class="fw-medium">Seleccionar todos</span></a> | <a href="javascript:;" data-select="camposRelacionesUsuarios" class="clearAllItems"><span class="fw-medium">Quitar todos</span></a>)
              </label>
              <select id="camposRelacionesUsuarios" name="camposRelacionesUsuarios[]" class="select2GeneradorExcel form-select" multiple>
                @foreach($camposRelacionesUsuarios as $campoRelacion)
                <option value="{{ $campoRelacion->id }}">{{ $campoRelacion->nombre }}</option>
                @endforeach
              </select>
            </div>

          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-success"><i class="ti ti-donwload ml-3"></i> Generar </button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection
