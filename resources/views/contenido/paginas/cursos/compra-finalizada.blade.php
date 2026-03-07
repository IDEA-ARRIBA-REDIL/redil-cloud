@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', '¡Compra Exitosa! - CRECER')

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-profile.scss'])
@endsection

@section('content')
<div class="container-fluid py-5" style="background-color: #f8f8fb; min-height: 100vh;">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-xl-5 mx-auto mt-5">
            <div class="card p-4 shadow-sm border-0 rounded-4 mb-5 text-center">
                <div class="card-header border-0 bg-transparent pt-4 pb-2">
                    <div style="width: 120px; height: 120px; background-color: #e8fadf; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto;">
                        <i class="ti ti-check text-success" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h2 class="text-black fw-bold mb-0 lh-sm mt-3">¡Felicidades, {{ Auth::user()->primer_nombre ?? 'Estudiante' }}!</h2>
                    <h4 class="fw-bold mt-2 mb-1 text-primary">Inscripción Completada Exitosamente</h4>
                    <p class="text-muted px-4">Tu pago ha sido registrado y tus cursos ya están activos. También hemos enviado un recibo a tu correo electrónico.</p>
                </div>
                
                <div class="card-body">
                    <div class="bg-light p-4 rounded-3 text-start mb-4 shadow-sm">
                        <h5 class="fw-bold mb-3 text-dark">Resumen de Cursos Inscritos:</h5>
                        <ul class="list-group list-group-flush bg-transparent">
                            @foreach($carrito->items as $item)
                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 border-light border-bottom">
                                <span class="fw-medium text-dark"><i class="ti ti-book me-2 text-primary"></i> {{ $item['nombre'] }}</span>
                                <span class="fw-bold text-dark">${{ number_format($item['precio'], 0, ',', '.') }}</span>
                            </li>
                            @endforeach
                        </ul>
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2">
                            <span class="fw-bold text-dark">Total Pagado:</span>
                            <span class="fw-bold fs-5 text-primary">${{ number_format($carrito->total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-3 mt-4">
                        <a href="{{ route('cursos.gestionar') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2">
                            <i class="ti ti-arrow-left me-1"></i> Volver al Catálogo
                        </a>
                        <a href="{{ route('cursos.gestionar') }}" class="btn btn-primary rounded-pill px-4 py-2">
                            <i class="ti ti-player-play me-1"></i> Ir a mis Cursos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
