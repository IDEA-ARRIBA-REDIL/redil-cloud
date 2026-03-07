@extends('layouts.layoutMaster')

@section('title', 'Informe de Compras')

<!-- Page -->
@section('page-style')
@vite([ 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/pickr/pickr-themes.scss', 'resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css', 'resources/assets/vendor/libs/apex-charts/apex-charts.scss'])


@endsection


@section('vendor-script')
@vite(['resources/js/app.js',  'resources/assets/vendor/libs/pickr/pickr.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js', 'resources/assets/vendor/libs/apex-charts/apexcharts.js'])

@endsection


@section('content')

<div class="row">
    <h4 class=" mb-1 fw-semibold text-primary ms-7">Informe de compras </h4>
    <div class="col-12">
        <livewire:informes.informe-compras />
    </div>
</div>

<script>
$(".fecha-picker").flatpickr({
            dateFormat: "Y-m-d",
            disableMobile: true
        });

</script>
@endsection
