<div>
    @once
        {{-- @lottiefiles/dotlottie-wc: soporta segment="[start,end]" como atributo HTML nativo --}}
        <script type="module" src="https://unpkg.com/@lottiefiles/dotlottie-wc@0.3.0/dist/dotlottie-wc.js"></script>
    @endonce

    {{--
        fruto.lottie: 11 frames totales (0-10) a 2fps
        seca.lottie:   3 frames totales (0-2)  a 2fps

        Lógica de frames según racha:
          - Sin registros nunca (cantidadTotal === 0): fruto, frame 0-1  (inicio, apenas brota)
          - Racha rota / negativa:                     seca,  frame 0-2  (animación completa)
          - Racha 1-3 días:                            fruto, frame 0-6  (planta joven)
          - Racha 4-7 días:                            fruto, frame 0-8  (creciendo)
          - Racha > 7 días:                            fruto, frame 0-10 (fruto maduro completo)
    --}}
    @php
        $src = $cantidadTotalTiempoConDios === 0
            ? asset('global_media/fruto.lottie')
            : ($cantidadRachaDiaria <= 0
                ? asset('global_media/seca.lottie')
                : asset('global_media/fruto.lottie'));

        $frameInicio = 1;

        if ($cantidadTotalTiempoConDios === 0) {
            $frameFin = 1;
        } elseif ($cantidadRachaDiaria <= 0) {
            $frameFin = 3;
        } elseif ($cantidadRachaDiaria <= 3) {
            $frameFin = 6;
        } elseif ($cantidadRachaDiaria <= 7) {
            $frameFin = 8;
        } else {
            $frameFin = 10;
        }
    @endphp

    <div class="d-flex flex-column align-items-center my-2">
        <dotlottie-wc
            src="{{ $src }}"
            autoplay
            playCount="2"
            segment="[{{ $frameInicio }},{{ $frameFin }}]"
            speed="1"
            style="width: {{ $ancho }}; height: {{ $alto }};"
        ></dotlottie-wc>
    </div>
</div>
