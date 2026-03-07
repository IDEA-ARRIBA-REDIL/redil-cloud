@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp


@extends('layouts/blankLayout')

@section('title', 'Mi asistencia')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
])
@endsection


@section('page-script')

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const errorNoExiste = '{{ session('errors') && session('errors')->first('no_existe') ?? '' }}';

    if (errorNoExiste) {
      Swal.fire({
        title: '',
        text: 'El usuario con información "{{ old('buscar') }}" no se encuentra en nuestra base de datos ¿Deseas crearlo?',
        icon: 'info',
        confirmButtonText: 'Si, quiero crearlo',
        showCancelButton: true, // Opcional: mostrar un botón de cancelar
        cancelButtonText: 'No, cancelar', // Opcional: texto del botón de cancelar
      }).then((result) => {
        if (result.isConfirmed) {
          // Redirigir a la otra ventana
          window.open('{{ route('usuario.nuevoExteriorConGrupo', [ "formulario" => $formulario, "grupoId" => $reporte->grupo_id ]) }}', '_blank');
        }
        // Si se hace clic en "No, cancelar" (o se cierra la alerta), no se hace nada
      });

      // Limpiar el error de la sesión para que no se muestre de nuevo
      @php
        session()->forget('errors.no_existe');
      @endphp
    }
  });
</script>
@endsection

@section('content')

<div class="d-flex align-items-center min-vh-100">
    <div class="container">
        <div class="row">
            <div class="col-12 col-lg-12 d-flex align-items-center">
                <div class=" mx-auto my-auto text-center">

                  <form id="formulario" role="form" class="forms-sample" method="POST" action="{{ route('reporteGrupo.reportarMiAsistancia', $reporte) }}" enctype="multipart/form-data">
                    @csrf
                    <img src="{{ Storage::url('generales/img/otros/dibujo_auto_asistencia.png') }}" class="img-fluid w-50 p-0">

                    @if($puedeReportar == false)
                    <h2 class="text-black fw-bold mb-0 lh-sm mt-3">Link de asistencia</h2>
                    <p class="text-black mt-1 mb-5">
                      ¡Ups! El link de asistencia ha caducado
                    </p>

                    <div class="p-3 d-flex mb-3" style="color:black; font-size:12px;border: solid 2px #95CDDF;border-radius: 14px;">
                      <i class="ti ti-bulb text-secondary me-2"></i>
                      <p class="m-0"> Si no alcanzaste a reportar asistencia, debes pedir al encargado del grupo que registre tu asistencia.</p>
                    </div>
                    @else
                    <h2 class="text-black fw-bold mb-0 lh-sm">Link de asistencia</h2>
                    <p class="text-black mt-1 mb-5">
                      Ingresa tu número de documento
                    </p>

                    <div class="row text-start mt-3 ">
                      <!-- buscar -->
                      <div class="mb-3 col-12 offset-md-2 col-md-8">
                        <input id="buscar" name="buscar" value="{{ old('buscar') }}" type="text" placeholder="Ingresa el número" class="form-control" />
                        @if($errors->has('error'))
                        <div class="text-danger ti-12px mt-2"> <i class="ti ti-circle-x"></i>{{ $errors->first('error') }}</div>
                        @endif

                         @if($errors->has('success')  )
                        <div class="text-success ti-12px mt-2"> <i class="ti ti-circle-check"></i>{{$errors->first('success')}}</div>
                        @endif
                      </div>
                      <!-- buscar -->
                    </div>

                    <div class="d-grid gap-2 d-sm-flex justify-content-center mt-3">
                      <button type="submit" class="btn btn-primary rounded-pill px-10 py-3" >
                        <span class="align-middle me-sm-1 me-0 ">Confirmar</span>
                      </button>
                    </div>
                    @endif

                  </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
