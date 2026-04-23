@extends('layouts/layoutMaster')

@section('title', 'Configuración de Notificaciones')

@section('content')
    @livewire('notificaciones.admin-tipos-notificaciones')
@endsection

@section('page-script')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('abrir-modal-tipo-notificacion', () => {
            const el = document.getElementById('modalTipoNotificacion');
            if (el) bootstrap.Modal.getOrCreateInstance(el).show();
        });

        Livewire.on('cerrar-modal-tipo-notificacion', () => {
            const el = document.getElementById('modalTipoNotificacion');
            const modal = bootstrap.Modal.getInstance(el);
            if (modal) modal.hide();
        });
    });
</script>
@endsection
