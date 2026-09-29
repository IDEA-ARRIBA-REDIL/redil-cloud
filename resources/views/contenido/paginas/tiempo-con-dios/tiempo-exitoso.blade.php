@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Tiempo con Dios Completado')

@section('vendor-style')
@endsection

@section('vendor-script')
@endsection

@section('page-script')
@endsection

@section('content')

<div class="d-flex align-items-center min-vh-100 py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 col-lg-12 d-flex align-items-center">
                <div class="mx-auto my-auto text-center" style="max-width: 600px;">
                  
                    <img style="width: 220px; height: 220px; object-fit: contain;" src="{{ Storage::disk('global_media')->url('Inscipcion-exitosa.png') }}" alt="Inscripción exitosa" class="p-0">
                    <h2 class="text-black fw-bold mb-0 lh-sm mt-3">{{ $cantidadRachaSemanal > 1 ? '¡Felicidades!' : '¡Felicidades andas en racha!'}}</h2>
                    <p class="text-black mt-1 mb-3">
                      {{ $cantidadRachaSemanal > 1 ? 'Completaste tu tiempo con Dios, sigue así, y entra en racha.' : 'Completaste tu tiempo con Dios'}}
                    </p>

                    <!-- ======================================================= -->
                    <!-- RECOMPENSAS DE GAMIFICACIÓN -->
                    <!-- ======================================================= -->
                    @if(isset($resultadoGamificacion) && $resultadoGamificacion['puntosGanados'] > 0)
                      <div class="d-inline-flex align-items-center gap-2 px-4 py-2 mb-3 rounded-pill bg-warning-subtle text-warning-emphasis fw-bold shadow-sm" style="font-size: 1.05rem; border: 1px solid #fde047;">
                        <span style="font-size: 1.25rem;">🪙</span>
                        <span>+{{ number_format($resultadoGamificacion['puntosGanados']) }} Puntos Ganados</span>
                      </div>
                    @endif

                    @if(isset($resultadoGamificacion) && !empty($resultadoGamificacion['insigniasDesbloqueadas']))
                      <div class="mb-4 p-3 rounded-4 bg-success-subtle text-success-emphasis border border-success d-inline-block text-center mx-auto shadow-sm w-100" style="max-width: 420px;">
                        <div class="small fw-bold text-uppercase text-success mb-2">🎉 ¡Nueva Insignia Desbloqueada!</div>
                        @foreach($resultadoGamificacion['insigniasDesbloqueadas'] as $ins)
                          <div class="d-flex align-items-center justify-content-center gap-3 p-2 bg-white rounded-3 shadow-xs">
                            @if($ins->tipo_icono === 'imagen' && $ins->imagen_url)
                              <img src="{{ $ins->imagen_url }}" alt="{{ $ins->nombre }}" style="width: 44px; height: 44px; object-fit: contain;">
                            @else
                              <div style="width: 44px; height: 44px; border-radius: 50%; background: #e8f5e9; display: flex; align-items: center; justify-content: center;">
                                <i class="ti {{ $ins->icono_clase ?: 'ti-award' }} fs-3 text-success"></i>
                              </div>
                            @endif
                            <div class="text-start">
                              <div class="fw-bold text-dark fs-6">{{ $ins->nombre }}</div>
                              <small class="text-muted d-block">{{ $ins->descripcion ?: '¡Logro completado con éxito!' }}</small>
                            </div>
                          </div>
                        @endforeach
                      </div>
                    @endif

                    <div class="my-3">
                      @livewire('TiempoConDios.racha-diaria', [
                        'largoLinea' => '50px'
                      ])
                    </div>

                    <div class="d-grid gap-2 d-sm-flex justify-content-center mt-4">
                      <a href="{{ route('dashboard') }}" type="button" class="btn btn-primary rounded-pill px-8 py-2 fw-semibold">
                        <span class="align-middle me-sm-1 me-0">Salir</span>
                      </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection
