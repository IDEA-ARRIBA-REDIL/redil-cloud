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
<div class="card app-calendar-wrapper">


  <div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Configuración /</span> Tema
    </h4>

    <div class="row">
        <div class="col-12">
            @livewire('Theme.theme-manager')
        </div>
    </div>
</div>

</div>
@endsection
