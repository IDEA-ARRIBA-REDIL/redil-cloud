@extends('layouts/layoutMaster')

@section('title', 'Plantilla')

@section('vendor-style')
<style>
body{
  font-family: poppins !important;
}
</style>
@vite([
'resources/assets/vendor/libs/fullcalendar/fullcalendar.scss',
'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css'

])
@endsection


@section('page-style')
@vite([
'resources/assets/vendor/scss/pages/app-calendar.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/fullcalendar/fullcalendar.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/moment/moment.js',
'resources/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js',
'resources/js/app.js',
])
@endsection

@section('page-script')



<script type="module">

  $(".fecha-picker").flatpickr({
    dateFormat: "Y-m-d",
    disableMobile:true
  });


  $(document).ready(function() {
    $('.select2').select2({
      width: '100px',
      allowClear: true,
      placeholder: 'Ninguno'
    });
    $('.select2').select2({
      dropdownParent: $('#eventForm')
    });

  });
</script>

<script type="text/javascript">
  $('#eventForm').submit(function(){
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
</script>


@endsection

@section('content')
  <!-- Header de Navegación y Acciones -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
      <div class="d-flex align-items-center gap-2 mb-1">
        <a href="{{ route('configuracion.index') }}" class="btn btn-sm btn-icon btn-outline-primary rounded-pill me-2 waves-effect waves-light shadow-sm" title="Volver a Configuración">
          <i class="ti ti-arrow-left"></i>
        </a>
        <h4 class="mb-0 fw-semibold text-primary">Plantilla y tema</h4>
      </div>
      <p class="text-black mb-0 small">
        Personaliza los colores, fuentes y estilos visuales de la aplicación.
      </p>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      @livewire('Theme.theme-manager')
    </div>
  </div>
@endsection
