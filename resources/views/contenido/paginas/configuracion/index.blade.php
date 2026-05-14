@extends('layouts/contentNavbarLayout')

@section('title', 'Configuración')

@section('content')

<h4 class=" mb-1 fw-semibold text-primary">Configuración</h4>

<div class="row g-4 mt-10">
  @foreach($items as $item)
  <div class="col-6 col-sm-4 col-md-3 col-lg-2">
    <a href="{{ route($item['route']) }}" class="text-body">
      <div class="card h-100 text-center border-0 shadow-sm card-hover">
        <div class="card-body d-flex flex-column align-items-center justify-content-center p-4">
          <div class="avatar avatar-lg mb-3">
            <span class="avatar-initial rounded-circle {{ $item['color'] }}">
              <i class="ti {{ $item['icon'] }} ti-md"></i>
            </span>
          </div>
          <p class="mb-0 fw-semibold text-black text-wrap">{{ $item['title'] }}</p>
        </div>
      </div>
    </a>
  </div>
  @endforeach
</div>

<style>
.card-hover {
  transition: all 0.3s ease-in-out;
  cursor: pointer;
}
.card-hover:hover {
  transform: translateY(-5px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
.avatar-initial {
  display: flex;
  align-items: center;
  justify-content: center;
}
/* Asegurar que el texto no se corte */
.text-wrap {
    white-space: normal !important;
    word-wrap: break-word;
}
</style>
@endsection
