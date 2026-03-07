@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Dashboard consolidación')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-1 fw-semibold text-primary">Dashboard consolidación</h4>

  <form id="formFiltros" method="GET" action="{{ route('consolidacion.dashboard') }}" class="d-flex gap-2">
    <div>
      <select name="anio" id="anio" class="form-select form-select-sm" onchange="this.form.submit()">
        @foreach($anios as $a)
          <option value="{{ $a }}" {{ $anio == $a ? 'selected' : '' }}>Año {{ $a }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <select name="semana" id="semana" class="form-select form-select-sm" onchange="this.form.submit()">
        @foreach($semanas as $s)
          <option value="{{ $s }}" {{ $semana == $s ? 'selected' : '' }}>Semana {{ $s }}</option>
        @endforeach
      </select>
    </div>
  </form>
</div>

<div class="row pt-5">
  <div class="col-12">
    <div class="nav-align-top">
      <ul class="nav nav-pills flex-column flex-sm-row mb-6 gap-2 gap-lg-0 justify-content-center gap-2" role="tablist">
        <li class="nav-item">
          <button type="button" class="nav-link waves-effect waves-light active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-anual" aria-controls="navs-anual" aria-selected="true"><i class="ti ti-calendar ti-lg me-1"></i> Anual </button>
        </li>
        <li class="nav-item">
          <button type="button" class="nav-link waves-effect waves-light" role="tab" data-bs-toggle="tab" data-bs-target="#navs-semestral" aria-controls="navs-semestral" aria-selected="false"><i class="ti ti-calendar-event ti-lg me-1"></i> Semestral</button>
        </li>
        <li class="nav-item">
          <button type="button" class="nav-link waves-effect waves-light" role="tab" data-bs-toggle="tab" data-bs-target="#navs-trimestral" aria-controls="navs-trimestral" aria-selected="false"><i class="ti ti-calendar-stats ti-lg me-1"></i> Trimestral</button>
        </li>
        <li class="nav-item">
          <button type="button" class="nav-link waves-effect waves-light" role="tab" data-bs-toggle="tab" data-bs-target="#navs-semanal" aria-controls="navs-semanal" aria-selected="false"><i class="ti ti-calendar-time ti-lg me-1"></i> Semanal</button>
        </li>
      </ul>
      <div class="tab-content border-0  mx-1 p-2">
        <div class="tab-pane fade show active" id="navs-anual" role="tabpanel">
            <div class="py-4 border rounded mt-2 px-2">
              <center>
                <h6>Contenido Anual</h6>
              </center>
            </div>
        </div>
        <div class="tab-pane fade" id="navs-semestral" role="tabpanel">
            <div class="py-4 border rounded mt-2 px-2">
              <center>
                <h6>Contenido Semestral</h6>
              </center>
            </div>
        </div>
        <div class="tab-pane fade" id="navs-trimestral" role="tabpanel">
            <div class="py-4 border rounded mt-2 px-2">
              <center>
                <h6>Contenido Trimestral</h6>
              </center>
            </div>
        </div>
        <div class="tab-pane fade" id="navs-semanal" role="tabpanel">
            <div class="row">
              <div class="col-12 mt-3">
                <center>
                  <h6 class="mb-0 text-uppercase fw-bold">Cosecha {{ $anio }}</h6>
                  <small class="text-black">Desde el <b>{{ $fechaInicioSemana }}</b> hasta el <b>{{ $fechaFinSemana }}</b></small>
                </center>
              </div>
              <div class="col-12 mt-4">
                <div class="table-responsive border rounded">
                  <table class="table table-hover mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Tipo de vinculación</th>
                        <th class="text-center">Cantidad de usuarios</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($vinculacionesSemanales as $vinculacion)
                        <tr>
                          <td>
                            <i class="ti ti-link me-2 text-primary"></i>
                            <span class="fw-medium">{{ $vinculacion->nombre }}</span>
                          </td>
                          <td class="text-center">
                            <span class="badge bg-label-primary fs-6">{{ $vinculacion->usuarios_count }}</span>
                          </td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="2" class="text-center py-4">No hay datos de vinculación para esta semana.</td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>




@endsection
