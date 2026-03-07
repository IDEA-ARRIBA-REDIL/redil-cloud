@extends('layouts/layoutMaster')

@section('title', 'Foro del Curso - Panel de Moderación (LMS)')
@section('page-style')
@vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/pickr/pickr-themes.scss', 'resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css'])


@endsection


@section('vendor-script')
@vite(['resources/js/app.js',  'resources/assets/vendor/libs/pickr/pickr.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js'])

<script>
    $(document).ready(function() {
           

            $('.select2').select2({
                width: '100px',
                allowClear: true,
               
               
            });
    });
</script>

@endsection

@section('content')
    @livewire('cursos.foro.panel-foro-asesor')
@endsection
