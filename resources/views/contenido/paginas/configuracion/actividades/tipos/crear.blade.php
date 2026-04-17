@extends('layouts/layoutMaster')

@section('title', 'Crear tipo de actividad')

@section('content')
<h4 class="fw-semibold text-primary mb-1">Nuevo tipo de actividad</h4>
<p class="text-muted">Configura los parámetros básicos para este tipo de actividad.</p>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('gestionar-tipos-de-actividad.crear') }}" method="POST">
          @csrf
          @include('contenido.paginas.configuracion.actividades.tipos.formulario')
          
          <div class="pt-4 d-flex justify-content-end gap-2">
            <a href="{{ route('gestionar-tipos-de-actividad.index') }}" class="btn btn-secondary rounded-pill">Cancelar</a>
            <button type="submit" class="btn btn-primary px-5 rounded-pill">Guardar </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
