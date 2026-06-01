@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Iglesia')

<!-- Page -->
@section('page-style')
@vite([
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/js/app.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js'])
@endsection
@section('page-script')
<script type="module">
</script>

<style>
</style>

@endsection

@section('content')



<h4 class=" mb-1 fw-semibold text-primary">Añadir servidores</h4>

<p class="mb-4 text-black">{{ $reporteReunion->reunion->nombre }} | {{  $reporteReunion->fecha }}</p>

@include('layouts.status-msn')




@endsection