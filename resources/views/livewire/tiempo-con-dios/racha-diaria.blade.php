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

   <div class="d-flex flex-column align-items-center my-2" x-data="{ loops: 0 }" x-init="
      $refs.player.addEventListener('complete', () => {
          loops++;
          if (loops < 2) {
              $refs.player.play();
          }
      });
  ">
      <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>
      <dotlottie-player 
          x-ref="player"
          src="{{ $cantidadTotalTiempoConDios === 0 ? asset('global_media/fruto.lottie') : ($cantidadRachaDiaria <= 0 ? asset('global_media/seca.lottie') : asset('global_media/fruto.lottie')) }}" 
          background="transparent" 
          speed="1" 
          autoplay
          @if($cantidadTotalTiempoConDios === 0)
          segment="[0, 2]"
          @elseif($cantidadRachaDiaria > 0 && $cantidadRachaDiaria <= 3)
          segment="[0, 3]"
          @endif
          style="width: 180px; height: 180px;"
      ></dotlottie-player>
   </div>

   <div id="rachaSemanal" class="d-flex justify-content-center align-items-center mb-2 border rounded-3 pt-5 py-10">
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
