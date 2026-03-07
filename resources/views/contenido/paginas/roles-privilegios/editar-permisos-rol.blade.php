@extends('layouts/layoutMaster')

@section('title', 'Gestionar Permisos - ' . $role->name)

@section('content')
        <h4 class="mb-4 text-primary fw-semibold text-start">
          Editar Permisos: {{ $role->name }}
        </h4>

        <a href="{{ route('configuracion.gestionar-roles') }}" class="btn btn-outline-secondary text-end">
          <i class="ti ti-arrow-left me-1"></i> Volver
        </a>

        @livewire('roles-privilegios.editar-permisos', ['role' => $role])


@endsection
