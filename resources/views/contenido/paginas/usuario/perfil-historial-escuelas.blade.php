@extends('layouts/layoutMaster')

@section('title', 'User Profile - Profile')

@section('vendor-style')

@vite([
'resources/assets/vendor/scss/pages/page-profile.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
])
@endsection

<!-- Page -->
@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/apex-charts/apexcharts.js',
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',

])
@endsection

@section('page-script')
  <script>
    function darBajaAlta(usuarioId, $tipo)
    {
      Livewire.dispatch('abrirModalBajaAlta', { usuarioId: usuarioId, tipo: $tipo });
    }

    function comprobarSiTieneRegistros(usuarioId)
    {
      Livewire.dispatch('comprobarSiTieneRegistros', { usuarioId: usuarioId });
    }

    function eliminacionForzada(usuarioId)
    {
      Livewire.dispatch('confirmarEliminacion', { usuarioId: usuarioId });
    }
  </script>
@endsection



@section('content')
  @include('layouts.status-msn')

  @if($configuracion->vista_perfil_usuario_clasica==false)
  <!-- Navbar pills -->
  <div class="row">
    <div class="col-md-12">
      <div class="card mb-4 p-1 border-0 shadow-sm">
         <ul class="nav nav-pills justify-content-start flex-column flex-md-row gap-2">
            @can('verPerfilUsuarioPolitica', [$usuario, 'principal'])
            <li class="nav-item flex-fill"><a id="tap-principal" href="{{ route('usuario.perfil', $usuario) }}" class="nav-link p-3 waves-effect waves-light" data-tap="principal"><i class='ti-xs ti ti-user-check me-2'></i> Principal</a></li>
            @endcan

            @can('verPerfilUsuarioPolitica', [$usuario, 'familia'])
            <li class="nav-item flex-fill"><a id="tap-familia" href="{{ route('usuario.perfil.familia', $usuario) }}" class="nav-link p-3 waves-effect waves-light"  data-tap="familia"><i class='ti-xs ti ti-home-heart me-2'></i> Familia</a></li>
            @endcan

            @can('verPerfilUsuarioPolitica', [$usuario, 'congregacion'])
            <li class="nav-item flex-fill"><a id="tap-congregacion" href="{{ route('usuario.perfil.congregacion', $usuario) }}" class="nav-link p-3 waves-effect waves-light" data-tap="congregacion"><i class='ti-xs ti ti-building-church me-2'></i> Congregación</a></li>
            @endcan

            <li class="nav-item flex-fill"><a id="tap-otro1" href="{{ route('usuario.historial-escuelas', $usuario) }}" class="nav-link p-3 waves-effect waves-light active" data-tap="otro1"><i class='ti-xs ti ti-school me-2'></i> Escuelas</a></li>
            <li class="nav-item flex-fill"><a id="tap-otro2" href="javascript:void(0);" class="nav-link p-3 waves-effect waves-light" data-tap="otro2"><i class='ti-xs ti ti-report-money me-2'></i> Financiera</a></li>
            <li class="nav-item flex-fill"><a id="tap-otro3" href="javascript:void(0);" class="nav-link p-3 waves-effect waves-light" data-tap="otro3"><i class='ti-xs ti ti-album me-2'></i> Hitos</a></li>
          </ul>
      </div>
    </div>
  </div>
  <!--/ Navbar pills -->
  @endif

  <div class="row">
    <div class="col-12">
      <div class="accordion" id="accordionEscuelas">
        @foreach($escuelas as $escuela)
          <div class="accordion-item card mb-3 border-0 shadow-sm overflow-hidden">
            <h2 class="accordion-header" id="heading{{ $escuela->id }}">
              <button class="accordion-button collapsed px-4 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $escuela->id }}" aria-expanded="false" aria-controls="collapse{{ $escuela->id }}">
                <div class="d-flex flex-column w-100 me-3">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="h5 mb-0 fw-bold"><i class="ti ti-bookmark me-2 text-primary"></i>{{ $escuela->nombre }}</span>
                    <span class="badge bg-label-primary px-3">{{ $escuela->aprobadas_obligatorias }} / {{ $escuela->total_obligatorias }} Obligatorias</span>
                  </div>
                  <div class="progress" style="height: 10px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: {{ $escuela->progreso }}%;" aria-valuenow="{{ $escuela->progreso }}" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                  <small class="text-black mt-1">Avance académico: {{ $escuela->progreso }}%</small>
                </div>
              </button>
            </h2>
            <div id="collapse{{ $escuela->id }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $escuela->id }}" data-bs-parent="#accordionEscuelas">
              <div class="accordion-body p-4">
                <div class="row g-3">
                  @forelse($escuela->materias as $materia)
                    <div class="col-12 col-xl-4 col-md-6 mb-4 ">
                        <div class="h-100 card border shadow">
                            <div class="card-header">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <h5 class="mb-0 fw-semibold text-black lh-sm">{{ $materia->nombre }}</h5>
                                    </div>
                                    @if($materia->caracter_obligatorio)
                                        <span class="badge bg-label-primary">Obligatoria</span>
                                    @else
                                        <span class="badge bg-label-warning">Opcional</span>
                                    @endif
                                </div>

                                @if($materia->resultado)
                                    @if($materia->resultado->aprobado)
                                        <span class="badge bg-label-success rounded-pill my-2">Aprobada</span>
                                    @else
                                        <span class="badge bg-label-danger rounded-pill my-2">Reprobada</span>
                                    @endif
                                @else
                                    <span class="badge bg-label-secondary rounded-pill my-2">Pendiente</span>
                                @endif

                                <div class="d-flex flex-row align-items-center mt-3">
                                    <div class="d-flex flex-column">
                                        <small class="text-muted"><i class="ti ti-bookmark text-black me-2"></i>Estado:</small>
                                        <small class="fw-semibold text-black">
                                            @if($materia->resultado)
                                                {{ $materia->resultado->aprobado ? 'Aprobado satisfactoriamente' : 'No aprobado' }}
                                            @else
                                                Disponible para cursar
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                @if($materia->resultado)
                                    <div class="row justify-content-between mb-2">
                                        <div class="col-12 col-md-6 align-items-center">
                                            <div class="d-flex flex-column">
                                                <small class="text-muted"><i class="ti ti-checklist text-black me-2"></i>Nota Final:</small>
                                                <small class="fw-semibold text-black">
                                                    {{ $materia->resultado->nota_final }}
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6 align-items-center">
                                            <div class="d-flex flex-column text-start">
                                                <small class="text-muted"><i class="ti ti-calendar-event text-black me-2"></i>Fecha:</small>
                                                <small class="fw-semibold text-black">
                                                    @if($materia->resultado->es_homologacion)
                                                        {{ \Carbon\Carbon::parse($materia->resultado->fecha_homologacion)->format('d/m/Y') }}
                                                    @else
                                                        {{ $materia->resultado->created_at->format('d/m/Y') }}
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row justify-content-between mb-2">
                                        <div class="col-12 align-items-center">
                                            @if($materia->resultado->es_homologacion)
                                                <div class="d-flex flex-column">
                                                    <small class="text-muted"><i class="ti ti-certificate text-black me-2"></i>Observación:</small>
                                                    <small class="fw-semibold text-black" title="{{ $materia->resultado->observacion_homologacion }}">
                                                        Homologación
                                                    </small>
                                                </div>
                                            @else
                                                <div class="d-flex flex-column">
                                                    <small class="text-muted"><i class="ti ti-user-check text-black me-2"></i>Asistencias:</small>
                                                    <small class="fw-semibold text-black">{{ $materia->resultado->total_asistencias }}</small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-label-secondary mb-0 p-3 text-center">
                                        <i class="ti ti-clock mb-2 fs-4"></i><br>
                                        <small class="fw-semibold">Aún no has cursado esta materia.</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                  @empty
                    <div class="col-12">
                        <div class="alert alert-info py-4 text-center">
                            <i class="ti ti-info-circle fs-1 mb-2"></i>
                            <p class="mb-0">No hay materias registradas para esta escuela.</p>
                        </div>
                    </div>
                  @endforelse
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

<style>
  .shadow-hover:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
    transform: translateY(-2px);
  }
  .transition-all {
    transition: all 0.3s ease;
  }
  .accordion-button:not(.collapsed) {
    background-color: transparent !important;
    color: inherit !important;
    box-shadow: none !important;
  }
  .accordion-button::after {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%235d596c'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e") !important;
  }
</style>
@endsection
