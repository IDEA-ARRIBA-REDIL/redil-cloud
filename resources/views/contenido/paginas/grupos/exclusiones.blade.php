@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Grupos')

<!-- Page -->
@section('page-style')
@vite([
'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
])
@endsection

@section('page-script')
<script type="module">
  window.addEventListener('msn', event => {
    Swal.fire({
      title: event.detail.msnTitulo,
      html: event.detail.msnTexto,
      icon: event.detail.msnIcono,
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    });
  });
</script>
@endsection

@section('content')

<h4 class="mb-1 fw-semibold text-primary">Excluir grupos</h4>
<p class="mb-4 text-black">Crea aquí las relaciones de exclusión entre usuarios y grupos, para que los usuarios no puedan visualizarlos, aunque estén bajo su cobertura.</p>

@include('layouts.status-msn')


@livewire('Grupos.listado-exclusiones-grupo',
  ['queUsuariosCargar' => $queUsuariosCargar ]
)


@endsection
