@extends('layouts/layoutMaster')

@section('title', 'Juan Pablos')

@section('content')
<div class="container">
  <h1>Detalle del Producto</h1>

  <p><strong>Nombre:</strong> {{ $producto->nombre }}</p>
  <p><strong>Descripción:</strong> {{ $producto->descripcion }}</p>
  <p><strong>Precio:</strong> ${{ $producto->precio }}</p>

  <a href="{{ route('productos.index') }}" class="btn btn-secondary">Volver</a>
</div>
@endsection