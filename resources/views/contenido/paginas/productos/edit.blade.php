@extends('layouts/layoutMaster')

@section('title', 'Juan Pablos')

@section('content')
<div class="container">
  <h1>Editar Producto</h1>

  <form action="{{ route('productos.update', $producto) }}" method="POST">
    @csrf
    @method('PATCH')
    <label>Nombre:</label>
    <input type="text" name="nombre" class="form-control" value="{{ $producto->nombre }}" required>

    <label>Descripción:</label>
    <textarea name="descripcion" class="form-control">{{ $producto->descripcion }}</textarea>

    <label>Precio:</label>
    <input type="number" name="precio" class="form-control" value="{{ $producto->precio }}" required step="0.01">

    <button type="submit" class="btn btn-primary mt-3">Actualizar</button>
  </form>
</div>
@endsection
