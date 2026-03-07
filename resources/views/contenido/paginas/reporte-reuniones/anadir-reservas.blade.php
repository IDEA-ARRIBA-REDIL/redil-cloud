@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Iglesia')

<!-- Page -->
@section('page-style')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss'])
@endsection

@section('vendor-script')
@vite([
'resources/js/app.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js'])
@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    // Listener de Livewire para reanudar el escaneo
    Livewire.on('msn', ({ msnIcono, msnTitulo, msnTexto, timer }) => {
      Swal.fire({
        icon: msnIcono ?? 'info',
        title: msnTitulo ?? 'Información',
        text: msnTexto ?? '',
        timer: timer ?? 3000,
        showConfirmButton: false,
        timerProgressBar: true,
      }).then(() => {
        if (html5Qrcode && isScanning) {
          try {
            html5Qrcode.resume();
          } catch (e) {
            console.error("No se pudo reanudar el escáner.", e);
          }
        }
      });
    });

  });
</script>
@endsection

@section('content')


<div class="row mt-10">

  @livewire('ReporteReuniones.reservas', [
    'reporteReunion' => $reporteReunion,
  ])

</div>


@endsection
