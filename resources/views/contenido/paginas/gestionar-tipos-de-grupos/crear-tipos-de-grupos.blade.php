@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Crear tipo de grupo')

{{-- Estilos y scripts (sin cambios) --}}
@section('page-style')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
@vite([
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css'
])
<style>
    .img-container {
        min-height: 300px;
        max-height: 80vh;
        background-color: #f7f7f7;
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
    }
    .img-container img {
        display: block;
        max-width: 100%;
    }
</style>
@endsection

@section('vendor-script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
@vite([
'resources/js/app.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js'
])
@endsection

@section('page-script')
<script type="module">
  $(function() {
    $('#formulario').submit(function(){
      $('.btnGuardar').attr('disabled','disabled');

      Swal.fire({
        title: "Espera un momento",
        text: "Ya estamos guardando...",
        icon: "info",
        showCancelButton: false,
        showConfirmButton: false,
        showDenyButton: false
      });
    });

  });
</script>
@endsection


@push('scripts')
{{-- (Tus scripts para manejo de archivos van aquí, sin cambios) --}}
@endpush

@section('content')

<h4 class=" mb-1 fw-semibold text-primary">Crear tipo de grupo</h4>
<p class="mb-4 text-black">Registra un nuevo tipo de grupo en el sistema.</p>

@include('layouts.status-msn')

<form id="formulario" role="form" method="POST" action="{{ route('gestionar-tipos-de-grupos.crearTipoDeGrupo') }}" enctype="multipart/form-data">
  @csrf
  <div class="row">
    <div class="col-md-12 mt-10">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">
           Información principal
        </h5>
        <div class="card-body">
          <div class="row">
            <!-- nombre --> 
            <div class="mb-3 col-12 col-md-6 col-md-3">
              <label class="form-label">Nombre</label>
              <input type="text" name="nombre" class="form-control" placeholder="Nombre del tipo de grupo" value="{{ old('nombre') }}">
              @error('nombre')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <!-- nombre -->

            <!-- nombre_plural --> 
            <div class="mb-3 col-12 col-md-6 col-md-3">
              <label class="form-label">Nombre plural</label>
              <input type="text" name="nombre_plural" class="form-control" placeholder="Nombre del tipo de grupo" value="{{ old('nombre_plural') }}">
              @error('nombre_plural')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <!-- nombre_plural -->

            <!-- descripcion -->
            <div class="mb-3 col-12">
              <label class="form-label">Descripción</label>
              <input type="text" name="descripcion" class="form-control" placeholder="Descripción breve" value="{{ old('descripcion') }}">
              @error('descripcion')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <!-- descripcion -->

            <!-- imagen -->
            <div class="mb-3 col-12 col-md-6">
              <label for="imagen" class="form-label">Icono</label>
              <div id="contenedor-input-imagen">
                <div class="input-group">
                  <input type="file" id="imagen" name="imagen" class="form-control" accept="image/*">
                </div>
                <div class="form-text">El icono se recortará a 100x100 px.</div>
                <input type="hidden" id="imagen_recortada" name="imagen_recortada" value="{{ old('imagen_recortada') }}">
                @error('imagen')
                <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
                @enderror
              </div>
            </div>
            <!-- imagen -->

            <!-- portada -->
            <div class="mb-3 col-12 col-md-6">
              <label for="portada" class="form-label">Portada</label>
              <div id="contenedor-input-portada">
                <div class="input-group">
                  <input type="file" id="portada" name="portada" class="form-control" accept="image/*">
                </div>
                <div class="form-text">La portada se recortará a 1700x400 px.</div>
                <input type="hidden" id="portada_recortada" name="portada_recortada" value="{{ old('portada_recortada') }}">
                @error('portada')
                <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
                @enderror
              </div>
            </div>
            <!-- portada -->      

            <!-- geo_icono -->
            <div class="mb-3 col-12 col-md-3">
              <label class="form-label">Icono del mapa</label>
              <input type="text" name="geo_icono" class="form-control" value="{{ old('geo_icono') }}">
              @error('geo_icono')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <!-- geo_icono -->

            <!-- color -->
            <div class="mb-3 col-12 col-md-3">
              <label class="form-label">Color principal</label>
              <input type="color" name="color" id="color" class="form-control form-control-color" value="{{ old('color', '#00A3FF') }}">
              @error('color')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <!-- color -->
            
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">
           Texto para los reportes
        </h5>
        <div class="card-body">
          <div class="row">
             
            <div class="col-12 mb-3 col-md-6">
              <label class="form-label">Título principal</label>
              <input type="text" name="titulo1_finalizar_reporte" class="form-control" value="{{ old('titulo1_finalizar_reporte', 'Confirmar asistencia') }}">
              @error('titulo1_finalizar_reporte')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>

            <div class="col-12 mb-3 col-md-6">
              <label class="form-label">Subtítulo encargados</label>
              <input type="text" name="subtitulo_encargados_finalizar_reporte" class="form-control" value="{{ old('subtitulo_encargados_finalizar_reporte', 'Encargados') }}">
              @error('subtitulo_encargados_finalizar_reporte')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>

            <div class="col-12 mb-3 col-md-6">
              <label class="form-label">Subtítulo personas nuevas</label>
              <input type="text" name="subtitulo_sumatorias_adiccionales_finalizar_reporte" class="form-control" value="{{ old('subtitulo_sumatorias_adiccionales_finalizar_reporte', 'Personas nuevas') }}">
              @error('subtitulo_sumatorias_adiccionales_finalizar_reporte')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>

            <div class="col-12 mb-3 col-md-6">
              <label class="form-label">Subtítulo miembros</label>
              <input type="text" name="subtitulo_miembros_finalizar_reporte" class="form-control" value="{{ old('subtitulo_miembros_finalizar_reporte', 'Miembros del grupo') }}">
              @error('subtitulo_miembros_finalizar_reporte')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>

            <div class="col-12 mb-3 col-md-6">
              <label class="form-label">Subtítulo ofrendas</label>
              <input type="text" name="subtitulo_ofrendas_finalizar_reporte" class="form-control" value="{{ old('subtitulo_ofrendas_finalizar_reporte', 'Ofrendas') }}">
              @error('subtitulo_ofrendas_finalizar_reporte')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">
           Configuraciones numéricas
        </h5>
        <div class="card-body">
          <div class="row">
            <div class="col-md-3 col-12 mb-3">
              <label class="form-label">Orden</label>
              <input type="number" name="orden" class="form-control" value="{{ old('orden') }}">
              @error('orden')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-3 col-12 mb-3">
              <label class="form-label">Metros de cobertura</label>
              <input type="number" name="metros_cobertura" class="form-control" value="{{ old('metros_cobertura', 500) }}">
              @error('metros_cobertura')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-3 col-12 mb-3">
              <label class="form-label">Máx. reportes/semana</label>
              <input type="number" name="cantidad_maxima_reportes_semana" class="form-control" value="{{ old('cantidad_maxima_reportes_semana', 1) }}">
              @error('cantidad_maxima_reportes_semana')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-3 col-12 mb-3">
              <label class="form-label">Días para inactividad</label>
              <input type="number" name="tiempo_para_definir_inactivo_grupo" class="form-control" value="{{ old('tiempo_para_definir_inactivo_grupo', 30) }}">
              @error('tiempo_para_definir_inactivo_grupo')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-3 col-12 mb-3">
              <label class="form-label">Horas link asistencia</label>
              <input type="number" name="horas_disponiblidad_link_asistencia" class="form-control" value="{{ old('horas_disponiblidad_link_asistencia', 0) }}">
              @error('horas_disponiblidad_link_asistencia')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>            
            
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">
           Opciones y permisos 
        </h5>
        <div class="card-body">
          <div class="row">
             <div class="col-md-3 col-12 mb-3">
              <div class="form-check form-switch mt-3"><input class="form-check-input" type="checkbox" name="seguimiento_actividad" value="1" {{ old('seguimiento_actividad') ? 'checked' : '' }}><label class="form-check-label">Seguimiento actividad</label></div>
            </div>
            <div class="col-md-3 col-12 mb-3">
              <div class="form-check form-switch mt-3"><input class="form-check-input" type="checkbox" name="contiene_servidores" value="1" {{ old('contiene_servidores') ? 'checked' : '' }}><label class="form-check-label">Contiene servidores</label></div>
            </div>
            <div class="col-md-3 col-12 mb-3">
              <div class="form-check form-switch mt-3"><input class="form-check-input" type="checkbox" name="posible_grupo_sede" value="1" {{ old('posible_grupo_sede') ? 'checked' : '' }}><label class="form-check-label">Posible grupo sede</label></div>
            </div>
            <div class="col-md-3 col-12 mb-3">
              <div class="form-check form-switch mt-3"><input class="form-check-input" type="checkbox" name="ingresos_individuales_discipulos" value="1" {{ old('ingresos_individuales_discipulos', '1') == '1' ? 'checked' : '' }}><label class="form-check-label">Ingresos individuales discípulos</label></div>
            </div>
            <div class="col-md-3 col-12 mb-3">
              <div class="form-check form-switch mt-3"><input class="form-check-input" type="checkbox" name="ingresos_individuales_lideres" value="1" {{ old('ingresos_individuales_lideres', '1') == '1' ? 'checked' : '' }}><label class="form-check-label">Ingresos individuales líderes</label></div>
            </div>
            <div class="col-md-3 col-12 mb-3">
              <div class="form-check form-switch mt-3"><input class="form-check-input" type="checkbox" name="registra_datos_planeacion" value="1" {{ old('registra_datos_planeacion') ? 'checked' : '' }}><label class="form-check-label">Registra datos planeación</label></div>
            </div>
            <div class="col-md-3 col-12 mb-3">
              <div class="form-check form-switch mt-3"><input class="form-check-input" type="checkbox" name="servidores_solo_discipulos" value="1" {{ old('servidores_solo_discipulos', '1') == '1' ? 'checked' : '' }}><label class="form-check-label">Servidores solo discípulos</label></div>
            </div>
            <div class="col-md-3 col-12 mb-3">
              <div class="form-check form-switch mt-3"><input class="form-check-input" type="checkbox" name="visible_mapa_asignacion" value="1" {{ old('visible_mapa_asignacion', '1') == '1' ? 'checked' : '' }}><label class="form-check-label">Visible en mapa</label></div>
            </div>
            <div class="col-md-3 col-12 mb-3">
              <div class="form-check form-switch mt-3"><input class="form-check-input" type="checkbox" name="tipo_evangelistico" value="1" {{ old('tipo_evangelistico') ? 'checked' : '' }}><label class="form-check-label">Tipo evangelístico</label></div>
            </div>
            <div class="col-md-3 col-12 mb-3">
              <div class="form-check form-switch mt-3"><input class="form-check-input" type="checkbox" name="enviar_mensaje_bienvenida" value="1" {{ old('enviar_mensaje_bienvenida') ? 'checked' : '' }}><label class="form-check-label">Enviar mensaje bienvenida</label></div>
            </div>
            <div class="col-md-3 col-12 mb-3">
              <div class="form-check form-switch mt-3"><input class="form-check-input" type="checkbox" name="sumar_encargado_asistencia_grupo" value="1" {{ old('sumar_encargado_asistencia_grupo') ? 'checked' : '' }}><label class="form-check-label">Sumar encargado a asistencia</label></div>
            </div>
            <div class="col-md-3 col-12 mb-3">
              <div class="form-check form-switch mt-3"><input class="form-check-input" type="checkbox" name="registrar_inasistencia" value="1" {{ old('registrar_inasistencia') ? 'checked' : '' }}><label class="form-check-label">Registrar inasistencia</label></div>
            </div>
            <div class="col-md-3 col-12 mb-3">
              <div class="form-check form-switch mt-3"><input class="form-check-input" type="checkbox" name="inasistencia_obligatoria" value="1" {{ old('inasistencia_obligatoria') ? 'checked' : '' }}><label class="form-check-label">Inasistencia obligatoria</label></div>
            </div>
            
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header text-black fw-semibold">
           Descripciones adicionales
        </h5>
        <div class="card-body">
          <div class="row">           

            <div class="col-12 mb-3">
              <label class="form-label">Mensaje de bienvenida</label>
              <textarea class="form-control" name="mensaje_bienvenida" rows="3">{{ old('mensaje_bienvenida') }}</textarea>
              @error('mensaje_bienvenida')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>

            <div class="col-12 mb-3">
              <label class="form-label">Descripción principal (Finalizar reporte)</label>
              <textarea class="form-control" name="descripcion1_finalizar_reporte" rows="3">{{ old('descripcion1_finalizar_reporte', 'Gestiona aquí las asistencias de los miembros del grupo.') }}</textarea>
              @error('descripcion1_finalizar_reporte')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>

            <div class="col-12 mb-3">
              <label class="form-label">Descripción de ofrendas (Finalizar reporte)</label>
              <textarea class="form-control" name="descripcion_ofrendas_finalizar_reporte" rows="3">{{ old('descripcion_ofrendas_finalizar_reporte', 'Ingresa el valor de las ofrendas recolectadas en el grupo.') }}</textarea>
              @error('descripcion_ofrendas_finalizar_reporte')
              <div class="text-danger ti-12px mt-2"><i class="ti ti-circle-x"></i> {{ $message }}</div>
              @enderror
            </div>
            
            
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- botonera -->
  <div class="d-flex mb-1 mt-5">
    <div class="me-auto">
      <button type="submit" class="btn btnGuardar btn-primary rounded-pill px-12 py-2" >Guardar</button>
    </div>
  </div>
  <!-- /botonera -->
    
</form>

<div class="modal fade" id="modalRecorte" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header pb-4">
        <h5 class="modal-title" id="modalRecorteTitle">Recortar imagen</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
         <div class="img-container">
            <img src="" id="croppingImage" alt="Imagen para recortar">
        </div>
      </div>
      <div class="modal-footer pt-5">
        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-outline-primary crop-btn rounded-pill">Recortar</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var croppingImage = document.getElementById('croppingImage');
    var cropBtn = document.querySelector('.crop-btn');
    var uploadImagen = document.getElementById('imagen');
    var uploadPortada = document.getElementById('portada');
    var modalRecorteEl = document.getElementById('modalRecorte');
    var inputResultadoImagen = document.getElementById('imagen_recortada');
    var inputResultadoPortada = document.getElementById('portada_recortada');
    var modalRecorteTitle = document.getElementById('modalRecorteTitle');
    var cropper = null;
    var currentTarget = null;

    function handleFileSelect(e, target) {
        if (e.target.files.length) {
            var file = e.target.files[0];
            var fileType = file.type;

            if (fileType === 'image/gif' || fileType === 'image/jpeg' || fileType === 'image/png' || fileType === 'image/webp') {
                var reader = new FileReader();
                reader.onload = function(e) {
                    croppingImage.src = e.target.result;
                    if(cropper) {
                        cropper.destroy();
                        cropper = null;
                    }
                    currentTarget = target;
                    if (target === 'imagen') {
                        modalRecorteTitle.innerText = 'Recortar Icono (100x100 px)';
                    } else {
                        modalRecorteTitle.innerText = 'Recortar Portada (1700x400 px)';
                    }
                    var modalRecorte = bootstrap.Modal.getOrCreateInstance(modalRecorteEl);
                    modalRecorte.show();
                };
                reader.readAsDataURL(file);
            } else {
                Swal.fire('Error', 'Formato de archivo no soportado', 'error');
            }
        }
    }

    if(uploadImagen) {
        uploadImagen.addEventListener('change', function(e) {
            handleFileSelect(e, 'imagen');
        });
    }

    if(uploadPortada) {
        uploadPortada.addEventListener('change', function(e) {
            handleFileSelect(e, 'portada');
        });
    }

    modalRecorteEl.addEventListener('shown.bs.modal', function () {
        var aspect = currentTarget === 'imagen' ? 1 : 1700 / 400;
        cropper = new Cropper(croppingImage, {
            zoomable: false,
            viewMode: 1,
            aspectRatio: aspect,
            autoCropArea: 1,
            responsive: true,
            restore: false,
            checkCrossOrigin: false,
        });
    });

    modalRecorteEl.addEventListener('hidden.bs.modal', function () {
        if(cropper){
            cropper.destroy();
            cropper = null;
        }
        if(currentTarget === 'imagen' && inputResultadoImagen.value === ""){
            uploadImagen.value = "";
        } else if(currentTarget === 'portada' && inputResultadoPortada.value === ""){
            uploadPortada.value = "";
        }
    });

    cropBtn.addEventListener('click', function() {
        if(!cropper) return;
        var width = currentTarget === 'imagen' ? 100 : 1700;
        var height = currentTarget === 'imagen' ? 100 : 400;

        var canvas = cropper.getCroppedCanvas({
            width: width,
            height: height,
        });

        var imgSrc = canvas.toDataURL('image/png');

        if(currentTarget === 'imagen') {
            inputResultadoImagen.value = imgSrc;
        } else {
            inputResultadoPortada.value = imgSrc;
        }

        Swal.fire({
            position: 'top-end',
            icon: 'success',
            title: 'Imagen recortada',
            showConfirmButton: false,
            timer: 1500
        });

        var modalRecorte = bootstrap.Modal.getInstance(modalRecorteEl);
        modalRecorte.hide();
    });
});
</script>

@endsection