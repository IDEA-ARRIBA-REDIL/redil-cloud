@extends('layouts/layoutMaster')

@section('title', 'Editar tipo de actividad')

@section('content')
<h4 class="fw-semibold text-primary mb-1">Editar tipo de actividad</h4>
<p class="text-muted">Modifica los parámetros de configuración para <b>{{ $tipoActividad->nombre }}</b>.</p>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('gestionar-tipos-de-actividad.actualizar', $tipoActividad->id) }}" method="POST">
          @csrf
          @method('PATCH')
          @include('contenido.paginas.configuracion.actividades.tipos.formulario')
          
          <div class="pt-4 d-flex justify-content-end gap-2">
            <a href="{{ route('gestionar-tipos-de-actividad.index') }}" class="btn btn-secondary rounded-pill">Cancelar</a>
            <button type="submit" class="btn btn-primary px-5 rounded-pill">Guardar Cambios</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
