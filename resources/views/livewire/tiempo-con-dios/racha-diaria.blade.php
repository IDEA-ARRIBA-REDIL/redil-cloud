<div>
  <style>

    .lineaActiva {
        width: {{ $largoLinea }};
        height: 2px;
        background: #0043CE
    }

    .lineaBase {
        width: {{ $largoLinea }};
        height: 2px;
        background:#C6C6C6
    }

    @media (max-width: 768px) {
      .lineaActiva {
          width: 15px;
          height: 2px;
          background: #0043CE
      }

      .lineaBase {
          width: 15px;
          height: 2px;
          background:#C6C6C6
      }
     }

    .step.activo {
        background-color: #DF6C01 !important;
    }

    .step {
      background-color:rgb(255, 255, 255);
      color: #fff;
      font-size: 14px;
      width: 25px;
      height: 25px;
      border-radius: 50%
    }
  </style>

   <div id="rachaSemanal" class="d-flex justify-content-center align-items-center my-8">
      @foreach ($rachaSemanal as $dia => $infoDia)
        <div class="d-flex align-items-center">
          @if($infoDia['estado'])
          <div class="step activo d-flex flex-column justify-content-center align-items-center">
            <span><i class="fa fa-check mt-12"></i></span>
            <div class="d-flex align-items-center text-black pt-2">
              <p class="d-none d-md-block">{{ $dia }}</p>
              <p class="d-block d-md-none ">{{ $infoDia['nombreCorto']  }}</p>
            </div>
          </div>
          @else
          <div class="step border d-flex flex-column justify-content-center align-items-center">
            <span><i class="fa fa-check mt-12"></i></span>
            <div class="d-flex align-items-center text-black pt-2">
              <p class="d-none d-lg-block">{{ $dia }}</p>
              <p class="d-block d-lg-none ">{{ $infoDia['nombreCorto']  }}</p>
            </div>
          </div>
          @endif
          @if(!$loop->last)
          <span class="{{ $infoDia['dia'] < $diaDeLaSemana ? 'lineaActiva' : 'lineaBase' }}"></span>
          @endif
        </div>
      @endforeach
    </div>
</div>
