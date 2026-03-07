@extends('layouts.layoutMaster')
@section('isEscuelasModule', true)


@section('title', 'Mis Recursos de la Escuela')

@section('content')
    <h4 class="mb-4 fw-semibold">Mis Recursos Generales</h4>

    <div class="row g-4">
        @forelse ($recursos as $recurso)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    @php
                        // Lógica para asignar un ícono y color según el tipo de recurso
                        $icon = 'ti-file';
                        $iconColor = 'text-secondary';
                        if ($recurso->tipo == 'Video') {
                            $icon = 'ti-brand-youtube';
                            $iconColor = 'text-danger';
                        } elseif ($recurso->tipo == 'Enlace') {
                            $icon = 'ti-link';
                            $iconColor = 'text-info';
                        } elseif ($recurso->tipo == 'Documento') {
                            $icon = 'ti-file-text';
                            $iconColor = 'text-primary';
                        }
                    @endphp

                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            
                            <div>
                                <h5 class="card-title mb-0">{{ $recurso->nombre }}</h5>
                                
                                <span class="badge bg-label-primary mt-1">{{ $recurso->tipo }}</span>
                            </div>
                        </div>
                    </div>
                    <p class="card-text text-muted">
                        {{ $recurso->descripcion }}
                    </p>
                </div>

                <div class="card-footer pt-0 border-0 bg-transparent">
                     <div class="row">
                              
                                {{-- Se muestra un botón diferente dependiendo del contenido del recurso --}}
                                @if ($recurso->ruta_archivo)
                                <div class="col-xs-12 col-md-12">
                                    <a href="{{ Storage::disk('public')->url($recurso->ruta_archivo) }}" class="btn btn-outline-primary waves-effect w-100 m-2" target="_blank" download>
                                        <i class="ti ti-download me-1"></i> Ver/Descargar Archivo
                                    </a>
                                </div>
                                @endif

                                @if ($recurso->link_youtube)
                                <div class="col-xs-12 col-md-6 py-3">
                                    <a style="text-decoration:underline"  href="{{ $recurso->link_youtube }}" target="_blank" class="link-underline-secondary">
                                        <i class="ti ti-external-link me-1"></i> Ver Video 
                                    </a>
                                </div>
                                @endif

                                @if ($recurso->link_externo) 
                                <div class="col-xs-12 col-md-6 py-3">
                                    <a style="text-decoration:underline" href="{{ $recurso->link_externo }}" class="link-underline-secondary" target="_blank">
                                        <i class="ti ti-external-link me-1"></i> Abrir Enlace
                                    </a>
                                </div>
                                @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center p-5">
                <i class="ti ti-folder-off ti-lg text-muted"></i>
                <h5 class="mt-3">No hay recursos disponibles para ti</h5>
                <p class="text-muted">Actualmente no tienes recursos asignados a tu rol activo.</p>
            </div>
        </div>
        @endforelse
    </div>
@endsection