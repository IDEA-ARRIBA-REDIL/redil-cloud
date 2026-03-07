@extends('layouts/layoutMaster')

@section('title', 'Editar Curso')

@section('page-style')
@vite(['resources/assets/vendor/libs/quill/editor.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/pickr/pickr-themes.scss', 'resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css'])
@endsection

@section('vendor-script')
@vite(['resources/js/app.js', 'resources/assets/vendor/libs/quill/quill.js', 'resources/assets/vendor/libs/pickr/pickr.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js'])

<script>
    $(document).ready(function() {

            $('.select2').select2({
                width: '100%',
                allowClear: true,
                placeholder: 'Ninguno'
            });

              $(".fecha-picker").flatpickr({
            dateFormat: "Y-m-d",
            disableMobile: true
        });
    });
</script>

@endsection

@section('content')


<div class="row">
  <div class="col-12">
    @livewire('cursos.editar-curso', ['curso' => $curso])
  </div>
</div>
@endsection
