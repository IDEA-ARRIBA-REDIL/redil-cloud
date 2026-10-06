@extends('layouts/layoutMaster')

@section('title', 'Gestionar Permisos - ' . $role->name)

@section('content')
 
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <a href="{{ route('configuracion.gestionar-roles') }}" class="btn btn-sm btn-icon btn-outline-primary rounded-pill me-2 waves-effect waves-light shadow-sm" title="Volver a Configuración">
              <i class="ti ti-arrow-left"></i>
            </a>
            <h4 class="mb-0 fw-semibold text-primary">Editar Permisos: {{ $role->name }}</h4>
          </div>
          <p class="text-black mb-0 small">
            Administra los permisos del rol.
          </p>
        </div>

        @livewire('roles-privilegios.editar-permisos', ['role' => $role])


@endsection
