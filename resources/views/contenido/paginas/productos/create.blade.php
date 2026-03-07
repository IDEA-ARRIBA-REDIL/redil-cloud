@extends('layouts/layoutMaster')

@section('title', 'Juan Pablos')

@section('content')
<div class="container">
  <h1>Crear Producto</h1>
  <button class="btn btn-info"><a class="" href="{{ route('productos.index') }}">Regresar</a></button>

  <form action="{{ route('productos.create') }}" method="POST">
    @csrf
    <label>Nombre:</label>
    <input type="text" name="nombre" class="form-control" required>

    <label>Descripción:</label>
    <textarea name="descripcion" class="form-control"></textarea>

    <label>Precio:</label>
    <input type="number" name="precio" class="form-control" required step="0.01">

    <button type="submit" class="btn btn-success mt-3">Guardar</button>
  </form>
</div>
@endsection
