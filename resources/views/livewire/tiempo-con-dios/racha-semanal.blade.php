<div>
  @if($formato == 'basico')
  <div class="d-flex flex-column">
    <div class="d-flex flex-row justify-content-center ">
      <h5 class="fw-semibold text-black my-auto">Semanas en racha</h5>
      <i class="ms-1 ti ti-flame fs-4 text-warning"></i>
    </div>
    <div class="mt-2 mb-4 mt-md-2 mb-md-0">
      <center>
        <div class="rounded-circle border border-warning d-flex justify-content-center align-items-center" style="width: {{ $tamaño }}; height: {{ $tamaño }}; border-width: 8px !important;">
          <h3 class="m-0 fw-bold text-black">{{ $rachaSemanal }}</h3>
        </div>
      </center>
    </div>
  </div>
  @elseif($formato == 'compacto')
  <div class="d-flex flex-row justify-content-between p-2">
    <div class="d-flex justify-content-end ms-1 ms-md-5">
      <h5 class="fw-semibold text-black my-auto">Semanas en racha </h5>      
    </div>
    <div class="d-flex justify-content-center me-1 me-md-5">
      <div class="rounded-circle border border-warning d-flex justify-content-center align-items-center" style="width: {{ $tamaño }}; height: {{ $tamaño }}; border-width: 8px !important;">
        <h3 class="m-0 fw-bold text-black">{{ $rachaSemanal }}<i class="ti ti-flame fs-4 text-warning"></i></h3>
      </div>
    </div>
  </div>
  @endif
</div>
