@extends('layouts.layoutMaster')
@section('isEscuelasModule', true)
@section('title', 'Recursos Generales de la Escuela')

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-profile.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js'])
@endsection

@section('content')
    @include('layouts.status-msn')
  
    {{-- Aquí cargamos nuestro componente Livewire --}}
    @livewire('escuelas.gestion-recursos-generales')
@endsection

@push('scripts')
<script>
    // La mayor parte del JS ahora está en el push del componente Livewire,
    // pero dejamos esto para notificaciones globales.
    document.addEventListener('livewire:initialized', function () {
        Livewire.on('notificacion', (event) => {
            const detail = Array.isArray(event) ? event[0] : event;
            Swal.fire({
                icon: 'success',
                title: detail.titulo || '¡Realizado!',
                text: detail.texto,
                timer: detail.timer || 2500,
                showConfirmButton: false,
            });
        });
    });
</script>
@endpush