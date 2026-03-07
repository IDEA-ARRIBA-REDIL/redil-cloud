@extends('layouts/layoutMaster')

@section('title', 'Juan Pablos')

@section('content')
<div class="container">
  <h1>Lista de Productos</h1>
  <button class="btn btn-info"><a href="{{ route('productos.store') }}">Nuevo Producto</a></button>

  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <table class="table mt-3">
    <tr>
      <th>ID</th>
      <th>Nombre</th>
      <th>Precio</th>
      <th>Acciones</th>
    </tr>
    @foreach ($productos as $producto)
    <tr>
      <td>{{ $producto->id }}</td>
      <td>{{ $producto->nombre }}</td>
      <td>${{ $producto->precio }}</td>
      <td>
      <a href="{{ route('productos.show', $producto) }}" class="btn btn-info">Ver</a>
      <a href="{{ route('productos.edit', $producto) }}" class="btn btn-warning">Editar</a>
      <form action="{{ route('productos.destroy', $producto) }}" method="POST" style="display:inline;">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-danger" onclick="return confirm('¿Eliminar producto?')">Eliminar</button>
      </form>
      </td>
    </tr>
  @endforeach
  </table>
</div>
@endsection
