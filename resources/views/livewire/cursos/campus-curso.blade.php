@push('styles')
    <style>
        .loading-overlay-campus {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            z-index: 10000;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: all 0.4s ease-in-out;
        }

        .loading-card {
            background: rgba(255, 255, 255, 0.9);
            padding: 2.5rem;
            border-radius: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            max-width: 90%;
            width: 320px;
        }

        .spinner-campus {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(25, 119, 229, 0.1);
            border-top: 4px solid #1977E5;
            border-radius: 50%;
            animation: spin-campus 1s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite;
        }

        @keyframes spin-campus {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .loading-text-animation {
            animation: pulse-loading 1.5s ease-in-out infinite;
        }

        @keyframes pulse-loading {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
    </style>
@endpush

<div>
    <div wire:loading wire:target="seleccionarItem, avanzarSiguiente, marcarCompletado, cargarProgreso, procesarEnvioEvaluacion, irAPregunta, preguntaAnterior, siguientePregunta">
        <div class="loading-overlay-campus">
            <div class="loading-card">
                <div class="position-relative">
                    <div class="spinner-campus"></div>
                    <div class="position-absolute top-50 start-50 translate-middle">
                        <i class="ti ti-book-2 text-primary fs-3"></i>
                    </div>
                </div>
                <div class="text-center">
                    <h5 class="fw-bold mb-1 text-primary loading-text-animation">Cargando lección</h5>
                    <p class="text-muted small mb-0">Preparando tu contenido...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Encabezado / Breadcrumbs -->
    <div class="d-flex align-items-center mb-4">
        <h4 class="mb-0 text-primary fw-bold">
            {{ $curso->nombre }}
        </h4>
    </div>

    <!-- Contenedor Principal (Video Izquierda / Playlist Derecha) -->
    <div class="row g-4 mb-5">
        <!-- COLUMNA IZQUIERDA: CONTENIDO PRINCIPAL -->
        <div class="col-lg-8">

            <!-- Título de la Lección Actual -->
            <h3 class="fw-semibold  text-black mb-4">
                {{ $itemActivo ? $itemActivo->titulo : 'Sin contenido seleccionado' }}</h3>

            <!-- Visualizador de Contenido -->
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                @if ($itemActivo)
                    @if ($itemActivo->tipo->codigo == 'video')
                        <!-- TIPO VIDEO -->
                        <div class="ratio ratio-16x9 bg-dark position-relative">
                            @if ($itemActivo->itemable && $itemActivo->itemable->video_url)
                                @if ($itemActivo->itemable->video_plataforma === 'youtube')
                                    <!-- Agregamos ID y enablejsapi=1 para poder conectarnos al reproductor por JS -->
                                    <iframe id="youtube-player-{{ $itemActivo->itemable->video_id }}"
                                        src="https://www.youtube.com/embed/{{ $itemActivo->itemable->video_id }}?enablejsapi=1&rel=0"
                                        allowfullscreen></iframe>
                                @elseif($itemActivo->itemable->video_plataforma === 'vimeo')
                                    <!-- ID para Vimeo -->
                                    <iframe id="vimeo-player-{{ $itemActivo->itemable->video_id }}"
                                        src="https://player.vimeo.com/video/{{ $itemActivo->itemable->video_id }}"
                                        allowfullscreen></iframe>
                                @else
                                    <div
                                        class="d-flex align-items-center justify-content-center text-white h-100 flex-column">
                                        <a href="{{ $itemActivo->itemable->video_url }}" target="_blank"
                                            class="text-white text-decoration-none">
                                            <i class="ti ti-external-link me-2 mb-2" style="font-size: 3rem;"></i><br>
                                            Ver video externo
                                        </a>
                                    </div>
                                @endif
                            @else
                                <div
                                    class="d-flex align-items-center justify-content-center h-100 text-white flex-column">
                                    <i class="ti ti-video text-black mb-2" style="font-size: 3rem;"></i>
                                    <p>Video no disponible</p>
                                </div>
                            @endif
                        </div>
                    @elseif($itemActivo->tipo->codigo == 'lectura' || $itemActivo->tipo->codigo == 'texto')
                        <!-- TIPO TEXTO/LECTURA -->
                        <div class="p-5 text-center bg-light border-bottom">
                            <i class="ti ti-book text-primary mb-3" style="font-size: 4rem;"></i>
                            <h4 class="text-primary fw-bold">Material de lectura</h4>
                            <p class="text-muted mb-0">Por favor, lee el contenido detallado en la sección de
                                descripción abajo.</p>
                        </div>
                    @elseif($itemActivo->tipo->codigo == 'iframe')
                        <!-- TIPO IFRAME -->
                        <div class="ratio ratio-16x9 bg-light position-relative border-bottom">
                            @if ($itemActivo->itemable && $itemActivo->itemable->iframe_code)
                                {!! $itemActivo->itemable->iframe_code !!}
                            @else
                                <div
                                    class="d-flex align-items-center justify-content-center h-100 text-muted flex-column">
                                    <i class="ti ti-code mb-2" style="font-size: 3rem;"></i>
                                    <p>Código embebido no disponible</p>
                                </div>
                            @endif
                        </div>
                    @elseif($itemActivo->tipo->codigo == 'recurso' || $itemActivo->tipo->codigo == 'archivo')
                        <!-- TIPO RECURSO -->
                        <div class="bg-light border-bottom"
                            style="height: 70vh; min-height: 500px; display: flex; flex-direction: column;">
                            @if ($itemActivo->itemable && $itemActivo->itemable->archivo_path)
                                @php
                                    $archivoRuta = $itemActivo->itemable->archivo_path;
                                    $esPdf = Str::endsWith(strtolower($archivoRuta), '.pdf');
                                @endphp

                                @if ($esPdf)
                                    <!-- Solución para scroll de PDF en móviles (especialmente iOS/Safari) -->
                                    <div class="pdf-container w-100 h-100"
                                        style="overflow-y: auto; -webkit-overflow-scrolling: touch; background-color: #525659; flex-grow: 1;">
                                        <object
                                            data="{{ route('cursos.recurso.preview', $itemActivo->itemable->id) }}?t={{ $itemActivo->itemable->updated_at?->timestamp }}"
                                            type="application/pdf" class="w-100 h-100"
                                            style="min-height: 100%; border: none; display: block;">
                                            <!-- Fallback si el navegador móvil no soporta object PDF -->
                                            <div
                                                class="d-flex align-items-center justify-content-center h-100 text-white flex-column p-4 text-center">
                                                <i class="ti ti-file-text mb-3" style="font-size: 3rem;"></i>
                                                <p>Tu navegador no soporta la visualización integrada de PDF.</p>
                                                <a href="{{ route('cursos.recurso.preview', $itemActivo->itemable->id) }}"
                                                    target="_blank" class="btn btn-primary mt-2 rounded-pill">
                                                    Abrir PDF en nueva pestaña <i class="ti ti-external-link ms-1"></i>
                                                </a>
                                            </div>
                                        </object>
                                    </div>
                                @else
                                    <!-- Otros tipos de archivos (Word, Excel, PPT) que se abren con Google Docs Viewer -->
                                    <div class="ratio ratio-16x9 w-100 h-100 position-relative" style="flex-grow: 1;">
                                        <iframe
                                            src="{{ route('cursos.recurso.preview', $itemActivo->itemable->id) }}?t={{ $itemActivo->itemable->updated_at?->timestamp }}"
                                            class="w-100 h-100" style="border: none;" allowfullscreen
                                            title="Visor de Recursos"></iframe>
                                    </div>
                                @endif

                                <!-- Botón inferior móvil para forzar apertura / recargas -->
                                <div class="p-3 bg-white border-top d-md-none text-center">
                                    <h6 class="text-muted small mb-3">Opciones del documento</h6>

                                    <div class="d-flex flex-column gap-2">
                                        <a href="{{ route('cursos.recurso.preview', $itemActivo->itemable->id) }}"
                                            target="_blank" class="btn btn-outline-primary rounded-pill w-100">
                                            <i class="ti ti-external-link me-1"></i> Leer en pantalla completa
                                        </a>

                                        <a href="{{ route('cursos.recurso.preview', $itemActivo->itemable->id) }}?download=1"
                                            target="_blank" download
                                            wire:click="marcarCompletado({{ $itemActivo->id }})"
                                            class="btn btn-primary rounded-pill w-100 fw-bold shadow-sm d-flex justify-content-center align-items-center gap-2">
                                            <i class="ti ti-download fs-5"></i> Descargar
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div
                                    class="d-flex align-items-center justify-content-center h-100 text-muted flex-column flex-grow-1">
                                    <i class="ti ti-file-download mb-2" style="font-size: 3rem;"></i>
                                    <p>Recurso no disponible</p>
                                </div>
                            @endif
                    @elseif(in_array($itemActivo->tipo->codigo, ['evaluacion', 'quiz', 'final']))
                            <!-- TIPO EVALUACION (Examen) -->
                            <div class="bg-white position-relative rounded-top"
                                style="min-height: 400px;"
                                @if ($evaluacionConfig && $evaluacionConfig->limite_tiempo > 0 && !empty($preguntasEvaluacion) && ($itemsProgreso[$itemActivo->id] ?? '') !== 'completado') x-data="{
                                        timeLeft: {{ $evaluacionConfig->limite_tiempo * 60 - (now()->timestamp - $inicioExamen) }},
                                        isTimeUp: false,
                                        formatTime(seconds) {
                                            if (seconds <= 0) return '0:00';
                                            const m = Math.floor(seconds / 60);
                                            const s = seconds % 60;
                                            return `${m}:${s.toString().padStart(2, '0')}`;
                                        }
                                    }"
                                    x-init="
                                        const timer = setInterval(() => {
                                            if (timeLeft > 0) {
                                                timeLeft--;
                                            } else {
                                                clearInterval(timer);
                                                isTimeUp = true;
                                                Livewire.dispatch('tiempo-agotado');
                                            }
                                        }, 1000);
                                    "
                                @else
                                    x-data="{ isTimeUp: false }" @endif>

                                

                                <div class="p-4 p-md-5 pt-0">
                                    @if ($evaluacionBloqueada)
                                        <!-- ESTADO BLOQUEADO POR INTENTOS / DILATACIÓN -->
                                        <div class="text-center py-5">
                                            <div class="mb-4">
                                                <i class="ti ti-lock-square-rounded text-black"
                                                    style="font-size: 5rem;"></i>
                                            </div>
                                            <h3 class="fw-bold text-dark">Evaluación Bloqueada Temporalmente</h3>
                                            <p class="text-muted fs-5 mb-4">
                                                Has alcanzado el límite de
                                                <strong>{{ $itemActivo->itemable->cantidad_repeticiones }}
                                                    intentos</strong> permitidos.<br>
                                                Debes esperar a que pase el tiempo de dilatación para intentarlo de nuevo.
                                            </p>

                                            <div class="d-inline-block bg-label-warning p-3 rounded-pill px-5 mb-4">
                                                <h4 class="mb-0 fw-bold text-white">
                                                    <i class="ti ti-alarm me-2 text-white"></i> Podrás reintentar en:
                                                    ~{{ ceil($horasRestantesDilatacion) }} Horas
                                                </h4>
                                            </div>

                                            <div class="mt-2 d-flex flex-column gap-3 align-items-center">
                                                @if ($puedeVerRespuestasActual)
                                                    <button wire:click="verRespuestas"
                                                        class="btn btn-outline-success rounded-pill px-5 fw-bold shadow-sm">
                                                        <i class="ti ti-eye me-1"></i> Ver Respuestas Correctas
                                                    </button>
                                                @endif

                                                <button wire:click="seleccionarItem({{ $itemActivo->id }}, true)"
                                                    class="btn btn-primary rounded-pill px-4">
                                                    <i class="ti ti-refresh me-1"></i> Verificar Disponibilidad
                                                </button>
                                            </div>
                                        </div>
                                    @elseif($itemActivo->itemable && ($itemsProgreso[$itemActivo->id] ?? '') === 'completado')
                                        <!-- YA COMPLETADO (Mostrar nota) -->
                                        <div class="text-center py-5">
                                            <div class="mb-4">
                                                <i class="ti ti-discount-check-filled btn-text-success"
                                                    style="font-size: 5rem;"></i>
                                            </div>
                                            <h3 class="fw-bold text-black">
                                                @if($evaluacionResultado && $evaluacionResultado->aprobado)
                                                    ¡Evaluación Aprobada!
                                                @else
                                                    Evaluación Finalizada
                                                @endif
                                            </h3>
                                            <p class="text-dark fs-5 mb-4">
                                                @if($evaluacionResultado && $evaluacionResultado->aprobado)
                                                    Has completado esta evaluación exitosamente.
                                                @else
                                                    Has finalizado todos los intentos de esta evaluación.
                                                @endif
                                            </p>

                                            @if ($evaluacionResultado)
                                                <div class="d-inline-block bg-label-success p-3 rounded-pill px-5 mb-4">
                                                    <h4 class="mb-0 fw-bold text-white">
                                                        Nota Final: {{ round($evaluacionResultado->nota, 2) }}%
                                                    </h4>
                                                </div>
                                            @endif

                                            <div class="mt-2 d-flex flex-column gap-3 align-items-center">
                                                @if ($puedeVerRespuestasActual)
                                                    <button wire:click="verRespuestas"
                                                        class="btn btn-outline-success rounded-pill px-5 fw-bold shadow-sm">
                                                        <i class="ti ti-eye me-1"></i> Ver Respuestas Correctas
                                                    </button>
                                                @endif

                                                <button wire:click="avanzarSiguiente"
                                                    class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                                                    Continuar al Siguiente Item <i class="ti ti-chevron-right ms-1"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @elseif (!empty($preguntasEvaluacion))
                                        <!-- NAVEGADOR SUPERIOR: Stepper de Progreso -->
                                        <div class="d-flex align-items-center justify-content-between mb-5 overflow-auto pb-2">
                                            @foreach ($preguntasEvaluacion as $index => $pregunta)
                                                @php
                                                    $respondida = !empty($respuestasEvaluacion[$pregunta->id]);
                                                    $esActiva = $index === $preguntaActualIndex;
                                                    
                                                    // Estilos para el círculo
                                                    $circuloEstilo = "width: 35px; height: 35px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border-radius: 50%; border: 2px solid; cursor: pointer; transition: all 0.3s;";
                                                    
                                                    if ($respondida || $esActiva) {
                                                        $circuloEstilo .= " background-color: #198754; border-color: #198754; color: white;";
                                                    } else {
                                                        $circuloEstilo .= " background-color: white; border-color: #dee2e6; color: #adb5bd;";
                                                    }

                                                    // Estilos para la línea (si no es el último)
                                                    $mostrarLinea = $index < count($preguntasEvaluacion) - 1;
                                                @endphp

                                                <div class="d-flex align-items-center {{ $mostrarLinea ? 'flex-grow-1' : '' }}">
                                                    <!-- Círculo -->
                                                    <div wire:click="irAPregunta({{ $index }})" 
                                                         style="{{ $circuloEstilo }}"
                                                         class="{{ $esActiva ? 'shadow-sm fw-bold' : '' }}">
                                                        {{ $index + 1 }}
                                                    </div>

                                                    <!-- Línea -->
                                                    @if ($mostrarLinea)
                                                        @php
                                                            $siguientePregunta = $preguntasEvaluacion[$index + 1] ?? null;
                                                            $siguienteRespondida = $siguientePregunta ? !empty($respuestasEvaluacion[$siguientePregunta->id]) : false;
                                                            $lineaVerde = $respondida && ($index + 1 <= $preguntaActualIndex || $siguienteRespondida);
                                                        @endphp
                                                        <div style="height: 3px; flex-grow: 1; margin: 0 8px; background-color: {{ $lineaVerde ? '#198754' : '#dee2e6' }};"></div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>

                                        <!-- CUADRO DE LA PREGUNTA ACTUAL -->
                                        @php
                                            $preguntaEnPantalla = $preguntasEvaluacion[$preguntaActualIndex];
                                        @endphp

                                        <div class="mb-4">
                                            <h5 class="fw-bold text-black mb-1">
                                                {{ $preguntaActualIndex + 1 }}. {{ $preguntaEnPantalla->pregunta }}
                                            </h5>
                                            <p class="text-muted small">
                                                @if ($preguntaEnPantalla->tipo_respuesta == 'multiple')
                                                    Selecciona todas las opciones que correspondan
                                                @elseif($preguntaEnPantalla->tipo_respuesta == 'unica')
                                                    Selecciona la opción correcta
                                                @else
                                                    Selecciona si es verdadero o falso
                                                @endif
                                            </p>

                                            <!-- Opciones Disponibles -->
                                            <div class="d-flex flex-column gap-3 mt-4">
                                                @foreach ($preguntaEnPantalla->opciones as $opcion)
                                                    @php
                                                        $isChecked = in_array(
                                                            $opcion->id,
                                                            $respuestasEvaluacion[$preguntaEnPantalla->id],
                                                        );
                                                        $inputType = $preguntaEnPantalla->tipo_respuesta === 'multiple' ? 'checkbox' : 'radio';
                                                        $opcionId = "opcion_{$opcion->id}";
                                                    @endphp

                                                    <label wire:key="opcion-{{ $opcion->id }}"
                                                        for="{{ $opcionId }}"
                                                        class="d-flex align-items-center justify-content-between p-3 border rounded cursor-pointer shadow-sm transition-all {{ $isChecked ? 'border-primary' : 'bg-transparent border-light' }}"
                                                        wire:click.prevent="seleccionarRespuesta({{ $preguntaEnPantalla->id }}, {{ $opcion->id }}, '{{ $preguntaEnPantalla->tipo_respuesta }}')"
                                                        style="background: white;">
                                                        
                                                        <span class="fs-6 {{ $isChecked ? 'text-black' : 'text-dark' }}">
                                                            {{ $opcion->opcion }}
                                                        </span>

                                                        <div class="form-check mb-0">
                                                            <input
                                                                class="form-check-input {{ $inputType === 'radio' ? 'rounded-circle' : '' }}"
                                                                type="{{ $inputType }}"
                                                                id="{{ $opcionId }}"
                                                                {{ $isChecked ? 'checked' : '' }} readonly
                                                                style="transform: scale(1.3); pointer-events: none;">
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>

                                        <!-- INFO BOX: TIEMPO LÍMITE -->
                                        @if ($evaluacionConfig && $evaluacionConfig->limite_tiempo > 0)
                                            <div class="p-3 mb-4 rounded-4 rounded" style="border: 1px dashed #dee2e6; color: #555;">
                                                <p class="mb-0 small text-black">
                                                    Examen con tiempo límite, tienes {{ $evaluacionConfig->limite_tiempo }} minutos para finalizar y enviar tu respuesta.
                                                </p>
                                            </div>
                                        @endif

                                        <!-- BOTONES DE NAVEGACIÓN INFERIOR (Anterior / Siguiente) -->
                                        <div class="d-flex justify-content-between mt-4">
                                            <button wire:click="preguntaAnterior"
                                                class="btn btn-outline-primary px-4 rounded-pill fw-bold {{ $preguntaActualIndex == 0 ? 'invisible' : '' }}"
                                               >
                                                <i class="ti ti-chevron-left me-1"></i> Anterior
                                            </button>

                                            <button wire:click="siguientePregunta"
                                                class="btn  px-4 rounded-pill btn-outline-primary fw-bold shadow-sm {{ $preguntaActualIndex == count($preguntasEvaluacion) - 1 ? 'invisible' : '' }}"
                                               >
                                                Siguiente <i class="ti ti-chevron-right ms-1"></i>
                                            </button>
                                        </div>

                                        <!-- FOOTER DE LA EVALUACIÓN -->
                                        <div class="mt-5 p-4 bg-light rounded-bottom mx-n4 mx-md-n5 mb-n4 mb-md-n5 d-flex align-items-center flex-wrap gap-4">
                                            <!-- Timer -->
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ti ti-clock-hour-4 fs-4 text-black"></i>
                                                <span class="text-black fw-semibold">Tiempo restante: <span x-text="formatTime(timeLeft)"></span></span>
                                            </div>

                                            <!-- Intentos -->
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ti ti-file-text fs-4 text-black"></i>
                                                <span class="text-black fw-semibold">Intento: {{ $intentosRealizados + 1 }}/{{ 1 + ($evaluacionConfig->cantidad_repeticiones ?? 0) }}</span>
                                            </div>

                                            <!-- Botón Finalizar -->
                                            <div class="ms-auto">
                                                <button wire:click="validarYEnviarEvaluacion" 
                                                    :disabled="isTimeUp || @js(!$this->evaluacionEstaCompleta)"
                                                    wire:loading.attr="disabled"
                                                    class="btn btn-success fw-bold px-4 py-2 rounded-pill shadow-sm"
                                                    style="background-color: #198754; border: none;">
                                                    Finalizar evaluación
                                                </button>
                                            </div>
                                        </div>

                                    @else
                                        <div
                                            class="d-flex align-items-center justify-content-center h-100 text-muted flex-column py-5">
                                            <i class="ti ti-file-unknown mb-2" style="font-size: 3rem;"></i>
                                            <h5 class="mt-2 text-heading">Evaluación Vacía</h5>
                                            <p>Esta evaluación no tiene preguntas configuradas aún.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                           
                    @else
                            <!-- OTRO CONTENIDO -->
                            <div class="p-5 text-center bg-light border-bottom">
                                <i class="ti ti-file text-black mb-3" style="font-size: 4rem;"></i>
                                <h5 class="text-black">Desarrollo de contenido en proceso</h5>
                            </div>
                    @endif
                @else
                    <div class="p-5 text-center bg-light border-bottom">
                        <h5 class="text-black">Por favor selecciona una lección del temario.</h5>
                    </div>
                @endif
            </div>

            <!-- Footer del Contenido -->
            @php
                $autor = $curso->equipo->first()->user ?? null;
                $estadoItem = $itemsProgreso[$itemActivo->id] ?? 'bloqueado';
            @endphp

            <div class="row align-items-center mb-4 g-3">
                <!-- Autor -->
                <div class="col-12 col-sm-auto mb-2 mb-sm-0">
                    <div class="d-flex align-items-center gap-3">
                        @if ($autor && $autor->avatar)
                            <img src="{{ Storage::url($autor->avatar) }}" alt="Autor"
                                class="rounded-circle border" width="44" height="44"
                                style="object-fit: cover;">
                        @else
                            <div class="btn-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold border"
                                style="width: 44px; height: 44px;">
                                {{ $autor ? $autor->inicialesNombre() : 'AA' }}
                            </div>
                        @endif
                        <h6 class="mb-0 fw-bold" style="color: #2b2b4d;">
                            {{ $autor->primer_nombre ?? 'Autor' }}
                            {{ $autor->apellidos ?? '' }}
                        </h6>
                    </div>
                </div>

                <!-- Info adicional + Badge -->
                <div
                    class="col-12 col-sm d-flex flex-wrap align-items-center justify-content-between justify-content-sm-end gap-4">
                    <div class="d-flex align-items-center gap-4 text-muted">
                        <span class="d-flex align-items-center gap-1 me-5 p-2">
                            <i class="ti ti-category fs-5"></i>
                            <span class="small fw-semibold">{{ $curso->carrera->nombre ?? 'Crecimiento' }}</span>
                        </span>
                        <span class="d-flex align-items-center gap-1 me-5 p-2">
                            <i class="ti ti-clock fs-5"></i>
                            <span class="small fw-semibold">{{ $curso->duracion_estimada ?? '2 Meses' }}</span>
                        </span>
                    </div>

                    @if ($estadoItem === 'completado')
                        <div>
                            <span
                                class="badge bg-success d-flex align-items-center gap-1 px-3 py-2 rounded-3 shadow-sm">
                                <i class="ti ti-check fs-6"></i>
                                <span class="fw-bold text-white">Completado</span>
                            </span>
                        </div>
                    
                    @endif
                </div>
            </div>

            <!-- BLOQUE DE PROGRESO (SOLO MÓVIL) -->
            <div class="d-lg-none mb-5">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body p-4">
                        <!-- Botón Siguiente (Top Móvil) -->
                        <div class="mb-4 text-end">
                            @if ($itemActivo && isset($itemsProgreso[$itemActivo->id]))
                                @if ($itemsProgreso[$itemActivo->id] === 'completado')
                                    <button wire:click="avanzarSiguiente"
                                        class="btn btn-success w-100 rounded-pill shadow-sm py-2">
                                        Siguiente lección <i class="ti ti-chevron-right ms-1"></i>
                                    </button>
                                @elseif($itemsProgreso[$itemActivo->id] === 'iniciado' && !in_array($itemActivo->tipo->codigo, ['evaluacion', 'quiz', 'final']))
                                    <button wire:click="marcarCompletado({{ $itemActivo->id }})"
                                        class="btn btn-success w-100 rounded-pill shadow-sm d-flex align-items-center justify-content-center gap-2 py-2 btn-marcar-hecho-class"
                                        disabled>
                                        Siguiente <i class="ti ti-chevron-right ms-1"></i>
                                    </button>
                                @endif
                            @endif
                        </div>

                        <h5 class="fw-bold mb-3" style="color: #2b2b4d;">Tu progreso</h5>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-muted">{{ $progresoPorcentaje }}% completado</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success rounded" role="progressbar"
                                    style="width: {{ $progresoPorcentaje }}%"
                                    aria-valuenow="{{ $progresoPorcentaje }}" aria-valuemin="0"
                                    aria-valuemax="100"></div>
                            </div>
                        </div>

                        <div class="accordion accordion-header-primary" id="accordionProgresoMobile">
                            @forelse($curso->modulos as $index => $modulo)
                                @php
                                    $moduloCompletado = $this->isModuloCompletado($modulo);
                                @endphp
                                <div class="accordion-item border-0 mb-2 shadow-none bg-transparent">
                                    <h2 class="accordion-header" id="headingMobile{{ $modulo->id }}">
                                        <button class="accordion-button collapsed px-0 bg-transparent shadow-none"
                                            type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapseMobile{{ $modulo->id }}"
                                            aria-expanded="false"
                                            aria-controls="collapseMobile{{ $modulo->id }}">
                                            <div class="d-flex align-items-center w-100">
                                                @if ($moduloCompletado)
                                                    <i class="ti ti-circle-check-filled btn-text-success me-2 fs-5"></i>
                                                @else
                                                    <i class="ti ti-circle btn-text-success me-2 fs-5"></i>
                                                @endif
                                                <div class="flex-grow-1">
                                                    <span class="fw-bold d-block text-dark small">Módulo {{ $index + 1 }}:</span>
                                                    <span class="text-muted" style="font-size: 0.75rem;">
                                                        <i class="ti ti-clock me-1"></i> {{ count($modulo->items) }} Lecciones
                                                    </span>
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapseMobile{{ $modulo->id }}" class="accordion-collapse collapse"
                                        aria-labelledby="headingMobile{{ $modulo->id }}"
                                        data-bs-parent="#accordionProgresoMobile">
                                        <div class="accordion-body px-0 py-2">
                                            <div class="list-group list-group-flush gap-2">
                                                @foreach ($modulo->items as $item)
                                                    @php
                                                        $estadoItem = $itemsProgreso[$item->id] ?? 'bloqueado';
                                                        $esActivo = $itemActivoId == $item->id;
                                                    @endphp
                                                    <a href="javascript:void(0)"
                                                        wire:click="seleccionarItem({{ $item->id }})"
                                                        class="list-group-item list-group-item-action border rounded-3 p-3 {{ $esActivo ? 'border-primary ' : 'border-light' }} {{ $estadoItem === 'bloqueado' ? 'opacity-50' : '' }}">
                                                        <div class="d-flex align-items-center">
                                                            @if ($estadoItem === 'completado')
                                                                <i class="ti ti-circle-check-filled btn-text-success me-3 fs-5"></i>
                                                            @elseif($estadoItem === 'bloqueado')
                                                                <i class="ti ti-lock text-secondary me-3 fs-5"></i>
                                                            @else
                                                                <i class="ti ti-circle text-primary me-3 fs-5"></i>
                                                            @endif

                                                            <div class="flex-grow-1">
                                                                <span class="d-block fw-semibold small {{ $esActivo ? 'text-primary' : 'text-dark' }}">
                                                                    {{ $item->itemable->titulo ?? 'Sin título' }}
                                                                </span>
                                                            </div>
                                                            <i class="ti ti-player-play-filled ms-2 text-primary small"></i>
                                                        </div>
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-center text-muted small">No hay módulos disponibles</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>




            <!-- Mensajes Flash -->
            @if (session()->has('successItems'))
                <div class="alert alert-success alert-dismissible mt-3" role="alert">
                    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">¡Excelente progreso!</h6>
                    <p class="mb-0">{{ session('successItems') }}</p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>

            @endif

        <!-- MODAL REVISIÓN DE RESPUESTAS -->
        @if($mostrarRespuestas && $evaluacionResultado)
        <div class="modal fade show" id="modalRevisionRespuestas" tabindex="-1" aria-hidden="true" style="display: block; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-bottom px-4 py-3">
                        <h5 class="modal-title fw-bold text-primary">
                            <i class="ti ti-list-check me-2"></i> Revisión de Evaluación
                        </h5>
                        <button type="button" class="btn-close" wire:click="cerrarRespuestas" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0 rounded-4 mb-4">
                            <h6 class="fw-bold mb-1"><i class="ti ti-info-circle me-1"></i> Información importante</h6>
                            <p class="small mb-0">Esta revisión es por única vez. Una vez que cierres este modal, no podrás volver a consultar las respuestas correctas de este intento.</p>
                        </div>

                        @foreach($preguntasEvaluacion as $idx => $pregunta)
                            @php
                                $respuestasUsuario = $evaluacionResultado->respuestas_json[$pregunta->id] ?? [];
                            @endphp
                            <div class="mb-5 p-3 rounded-4 bg-light border">
                                <h6 class="fw-bold text-dark mb-3">
                                    {{ $idx + 1 }}. {{ $pregunta->pregunta }}
                                </h6>
                                <div class="list-group list-group-flush gap-2">
                                    @foreach($pregunta->opciones as $opcion)
                                        @php
                                            $respondioEsta = in_array($opcion->id, $respuestasUsuario);
                                            $esCorrecta = $opcion->es_correcta;
                                            $claseBorde = '';
                                            $icon = '';
                                            
                                            if ($esCorrecta) {
                                                $claseBorde = 'border-success bg-label-success';
                                                $icon = '<i class="ti ti-circle-check-filled text-success ms-2"></i>';
                                            } elseif ($respondioEsta && !$esCorrecta) {
                                                $claseBorde = 'border-danger bg-label-danger';
                                                $icon = '<i class="ti ti-circle-x-filled text-danger ms-2"></i>';
                                            }
                                        @endphp
                                        <div class="list-group-item border rounded-3 p-3 d-flex align-items-center justify-content-between {{ $claseBorde }}">
                                            <div class="d-flex align-items-center">
                                                <div class="form-check me-2">
                                                    <input class="form-check-input" type="checkbox" disabled {{ $respondioEsta ? 'checked' : '' }}>
                                                </div>
                                                <span class="{{ $esCorrecta ? 'fw-bold text-white' : ($respondioEsta ? 'text-white' : 'text-dark') }}">
                                                    {{ $opcion->opcion }}
                                                </span>
                                            </div>
                                            {!! $icon !!}
                                        </div>
                                    @endforeach
                                </div>
                                @if($pregunta->explicacion)
                                    <div class="mt-3 p-3 bg-white rounded border-start border-4 border-info">
                                        <p class="small mb-0"><strong>Explicación:</strong> {{ $pregunta->explicacion }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="modal-footer border-top p-4">
                        <button type="button" class="btn btn-primary rounded-pill px-5  fw-bold" wire:click="cerrarRespuestas">
                            Aceptar y Finalizar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

                <!-- Columna Descripción -->
                <div class="mb-4">
                    <h3 class="fw-regular text-black mb-3">Descripción</h3>
                    <div class="text-black py-4" style="line-height: 1.5;">
                        @if ($itemActivo && $itemActivo->itemable && $itemActivo->itemable->contenido_html)
                            {!! $itemActivo->itemable->contenido_html !!}
                        @else
                            <p class="text-muted">No hay descripción adicional provista para esta clase.</p>
                        @endif
                    </div>
                </div>

                <!-- Columna Foro (Opcional según diseño) -->
                @if ($itemActivo && isset($itemActivo->tipo) && !in_array($itemActivo->tipo->codigo, ['evaluacion', 'quiz', 'final']))
                    <div class="card border-0 mb-5 shadow-sm rounded-4 mt-4 overflow-hidden"
                        style="background-color: #e8f5e9;">
                        <div class="row g-0 align-items-center">
                            <!-- Banner Image -->
                            <div class="col-4 col-sm-3 col-md-2 h-100">
                                <img src="{{ asset('img/forum_banner.png') }}" alt="Foro de dudas"
                                    class="img-fluid h-100" style="object-fit: cover; min-height: 160px; max-height: 190px;">
                            </div>
                            <!-- Content -->
                            <div class="col-8 col-sm-9 col-md-10">
                                <div class="card-body p-3 p-md-4">
                                    <div class="row align-items-center">
                                        <!-- Texto -->
                                        <div class="col-12 col-lg-7 mb-3 mb-lg-0">
                                            <h5 class="fw-bold mb-1 text-dark">Foro de dudas</h5>
                                            <p class="text-dark small mb-0">¿Tienes dudas sobre algún tema? Consulta lo que otros compañeros han discutido.</p>
                                        </div>
                                        <!-- Icono y Botón -->
                                        <div class="col-12 col-lg-5">
                                            <div class="d-flex align-items-center justify-content-between justify-content-lg-end gap-4">
                                                <!-- Icon & Badge -->
                                                <div class="position-relative">
                                                    <i class="ti ti-messages fs-2 text-black"></i>
                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill"
                                                        style="font-size: 0.6rem;background-color:#1977E5; color:white !important">
                                                        {{ $hilosForo->count() }}
                                                    </span>
                                                </div>
                                                <!-- Action Button -->
                                                <button type="button"
                                                    class="btn btn-outline-dark rounded-pill px-4 d-flex align-items-center gap-2"
                                                    data-bs-toggle="modal" data-bs-target="#modalForoCompleto">
                                                    <span class="small fw-regular text-black">Ver foro</span>
                                                    <i class="ti ti-chevron-right fs-6 text-black"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div
                        class="alert alert-warning mt-4 rounded-4 shadow-sm border-0 bg-label-warning text-dark d-flex align-items-center gap-3 p-3">
                        <i class="ti ti-info-circle fs-4 text-white"></i>
                        <div>
                            <h6 class="mb-1 fw-bold">Foro deshabilitado</h6>
                            <small>No se permiten preguntas públicas en la sección de evaluación para proteger la
                                integridad de las respuestas.</small>
                        </div>
                    </div>
        @endif
    </div>
    <!-- Fin Sección Inferior -->


        <!-- FIN COLUMNA IZQUIERDA -->

        <!-- COLUMNA DERECHA: PROGRESO Y TEMARIO (Escritorio) -->
        <div class="col-lg-4 d-none d-lg-block">
            <div class="card shadow-sm border-0 rounded-4 sticky-top" style="top: 80px;">
                <div class="card-body p-4">

                    <h5 class="fw-bold mb-3" style="color: #2b2b4d;">Tu progreso</h5>

                    <!-- Barra de Progreso -->
                    <div class="mb-4">
                        <small class="text-black d-block mb-1">{{ $progresoPorcentaje }}% completado</small>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar"
                                style="width: {{ $progresoPorcentaje }}%;" aria-valuenow="{{ $progresoPorcentaje }}"
                                aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                    </div>

                    <!-- Acordeón del Temario -->
                    <div class="accordion accordion-flush" id="temarioAccordion">

                        @forelse($curso->modulos as $index => $modulo)
                            <div class="accordion-item {{ $index !== 0 ? 'mt-2' : '' }} border rounded"
                                style="background-color: #f8f9fa;">
                                <h2 class="accordion-header" id="headingModulo-{{ $modulo->id }}">
                                    <!-- Si el módulo actual contene el ítem activo, desplegar por defecto, de lo contrario, contraido -->
                                    <button
                                        class="accordion-button rounded {{ collect($modulo->items)->pluck('id')->contains($itemActivoId) ? '' : 'collapsed' }} bg-transparent fw-bold"
                                        type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseModulo-{{ $modulo->id }}"
                                        aria-expanded="{{ collect($modulo->items)->pluck('id')->contains($itemActivoId) ? 'true' : 'false' }}"
                                        aria-controls="collapseModulo-{{ $modulo->id }}"
                                        style="color: #2b2b4d; box-shadow: none;">

                                        <!-- Ícono de progreso del módulo -->
                                        @if ($this->isModuloCompletado($modulo))
                                            <i class="ti ti-circle-check-filled btn-text-success me-2 fs-5"></i>
                                        @else
                                            <i class="ti ti-circle btn-text-success me-2 fs-5"></i>
                                        @endif

                                        <div class="d-flex flex-column ms-1">
                                            <span>Módulo {{ $index + 1 }}: {{ $modulo->titulo }}</span>
                                            <small class="text-black fw-normal" style="font-size: 0.75rem;">
                                                <i class="ti ti-clock me-1"></i> {{ count($modulo->items) }}
                                                Lecciones
                                            </small>
                                        </div>
                                    </button>
                                </h2>

                                <div id="collapseModulo-{{ $modulo->id }}"
                                    class="accordion-collapse collapse {{ collect($modulo->items)->pluck('id')->contains($itemActivoId) ? 'show' : '' }}"
                                    aria-labelledby="headingModulo-{{ $modulo->id }}"
                                    data-bs-parent="#temarioAccordion">
                                    <div class="accordion-body p-0">

                                        <ul class="list-group list-group-flush bg-transparent p-1">
                                            @forelse($modulo->items as $itemIndex => $item)
                                                <!-- Lógica de restricciones LMS desde BD -->
                                                @php
                                                    $isActivo = $item->id == $itemActivoId;
                                                    $estadoItem = $itemsProgreso[$item->id] ?? 'bloqueado';

                                                    // Clases base para el item
                                                    $itemClass =
                                                        'list-group-item list-group-item-action d-flex align-items-center py-3 border-0 bg-transparent text-start';
                                                    if ($isActivo) {
                                                        $itemClass .=
                                                            ' fw-bold text-dark bg-white shadow-sm rounded-3 my-1 ms-2 me-2 border-start border-4 border-primary';
                                                    } else {
                                                        $itemClass .= ' ms-3';
                                                        if ($estadoItem === 'bloqueado') {
                                                            $itemClass .= ' text-muted';
                                                        } else {
                                                            $itemClass .= ' text-black';
                                                        }
                                                    }
                                                @endphp

                                                <!-- Si está bloqueado, se aplican estilos extra para impedir clics evidentes -->
                                                <button wire:click="seleccionarItem({{ $item->id }})"
                                                    class="{{ $itemClass }}"
                                                    style="{{ $estadoItem === 'bloqueado' ? 'cursor: not-allowed; opacity: 0.6;' : 'cursor: pointer;' }} {{ $isActivo ? 'transform: translateX(-5px);' : '' }} width: 97%;"
                                                    {{ $estadoItem === 'bloqueado' ? 'disabled' : '' }}>

                                                    <!-- Icono de Estado -->
                                                    @if ($estadoItem === 'completado')
                                                        <i
                                                            class="ti ti-circle-check-filled btn-text-success me-3 fs-5"></i>
                                                    @elseif($estadoItem === 'bloqueado')
                                                        <i class="ti ti-lock text-secondary me-3 fs-5"></i>
                                                    @else
                                                        <!-- Estado iniciado / disponible -->
                                                        @if ($item->tipo->codigo == 'video')
                                                            <i
                                                                class="ti ti-player-play-filled text-primary me-3 fs-5"></i>
                                                        @elseif($item->tipo->codigo == 'lectura' || $item->tipo->codigo == 'texto')
                                                            <i class="ti ti-book text-info me-3 fs-5"></i>
                                                        @else
                                                            <i class="ti ti-file text-primary me-3 fs-5"></i>
                                                        @endif
                                                    @endif

                                                    <!-- Titulo -->
                                                    <span class="{{ $isActivo ? 'fw-bold' : '' }}"
                                                        style="font-size: 0.9rem;">
                                                        Clase {{ $itemIndex + 1 }}:
                                                        {{ Str::limit($item->titulo, 35) }}
                                                    </span>

                                                    <!-- Indicador a la derecha si es la lección activa -->
                                                    @if ($isActivo)
                                                        <i class="ti ti-player-play-filled ms-auto text-primary"
                                                            style="font-size: 0.8rem;"></i>
                                                    @endif

                                                </button>
                                            @empty
                                                <div class="p-3 text-black small text-center">Este módulo aún no
                                                    tiene
                                                    lecciones.</div>
                                            @endforelse
                                        </ul>

                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-warning">Aún no hay módulos para este curso.</div>
                        @endforelse

                    </div>
                    <!-- Botones de Acción (Restricciones LMS) -->
                    <div class="col-12">
                        @if ($itemActivo && isset($itemsProgreso[$itemActivo->id]))
                            @if ($itemsProgreso[$itemActivo->id] === 'completado')
                                <button style="float: right;" wire:click="avanzarSiguiente"
                                    class="btn btn-success px-4 rounded-pill shadow-sm">
                                    Siguiente lección <i class="ti ti-chevron-right ms-1"></i>
                                </button>
                            @elseif(
                                $itemsProgreso[$itemActivo->id] === 'iniciado' &&
                                    !in_array($itemActivo->tipo->codigo, ['evaluacion', 'quiz', 'final']))
                                <!-- Está en curso. Mostramos botón de HECHO pero bloqueado hasta que el JS lo habilite -->
                                <!-- El JS habilitará este botón basado en el tiempo de video o Scroll del texto -->
                                <button style="float:right;" wire:click="marcarCompletado({{ $itemActivo->id }})"
                                    id="btn-marcar-hecho"
                                    class="btn btn-success px-4 rounded-pill shadow-sm d-flex align-items-center gap-2 btn-marcar-hecho-class"
                                    disabled>
                                    Siguiente <i class="ti ti-chevron-right ms-1"></i>
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <!-- FIN COLUMNA DERECHA -->



        <!-- MODAL FORO COMPLETO -->
        <div class="modal fade" id="modalForoCompleto" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header btn-primary px-4 py-3">
                        <h5 class="modal-title text-white d-flex align-items-center gap-2">
                            <i class="ti ti-message-circle-2"></i> Foro del curso: {{ $curso->nombre }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <!-- Cuerpo del Modal incrustando el componente Livewire ForoCursoEstudiante -->
                    <div class="modal-body p-3 " style="min-height: 60vh;">
                        @livewire('cursos.foro.foro-curso-estudiante', ['cursoId' => $curso->id])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    @push('scripts')
        <script>
            document.addEventListener('livewire:navigated', () => {
                let intervalProgresoYT = null;

                function habilitarBotonesHecho() {
                    let botones = document.querySelectorAll('.btn-marcar-hecho-class');
                    botones.forEach(btn => btn.removeAttribute('disabled'));
                    
                    // Compatibilidad con el ID original por si queda algo
                    let btnOriginal = document.getElementById('btn-marcar-hecho');
                    if(btnOriginal) btnOriginal.removeAttribute('disabled');
                }

                function crearReproductorYoutube() {
                    let iframes = document.querySelectorAll('iframe[id^="youtube-player-"]');
                    if (iframes.length === 0) return;

                    if (intervalProgresoYT) clearInterval(intervalProgresoYT);

                    let player = new YT.Player(iframes[0].id, {
                        events: {
                            'onStateChange': function(event) {
                                if (event.data == 1) { // 1 = Reproduciendo
                                    intervalProgresoYT = setInterval(function() {
                                        let duration = player.getDuration();
                                        let current = player.getCurrentTime();
                                        if (duration > 0 && (current / duration) >= 0.95) {
                                            habilitarBotonesHecho();
                                            clearInterval(intervalProgresoYT);
                                        }
                                    }, 2000); // 2 segundos
                                } else {
                                    if (intervalProgresoYT) clearInterval(intervalProgresoYT);
                                }
                            }
                        }
                    });
                }

                function crearReproductorVimeo() {
                    let iframes = document.querySelectorAll('iframe[id^="vimeo-player-"]');
                    if (iframes.length === 0) return;

                    let player = new Vimeo.Player(iframes[0]);

                    player.on('timeupdate', function(data) {
                        if (data.percent >= 0.95) {
                            habilitarBotonesHecho();
                            player.off('timeupdate');
                        }
                    });
                }

                function inicializarOyentesProgreso() {
                    let btnHecho = document.querySelector('.btn-marcar-hecho-class');
                    // Si no hay botones de marcar hecho, verificamos si es que ya está completado
                    if (!btnHecho) return;

                    let tipoActual = '{{ $itemActivo->tipo->codigo ?? '' }}';

                    if (tipoActual === 'video') {
                        let plataforma = '{{ $itemActivo->itemable->video_plataforma ?? '' }}';

                        if (plataforma === 'youtube') {
                            if (typeof YT === 'undefined' || typeof YT.Player === 'undefined') {
                                let tag = document.createElement('script');
                                tag.src = "https://www.youtube.com/iframe_api";
                                let firstScriptTag = document.getElementsByTagName('script')[0] || document.body
                                    .lastChild;
                                firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

                                window.onYouTubeIframeAPIReady = function() {
                                    crearReproductorYoutube();
                                };
                            } else {
                                crearReproductorYoutube();
                            }
                        } else if (plataforma === 'vimeo') {
                            if (typeof Vimeo === 'undefined') {
                                let tag = document.createElement('script');
                                tag.src = "https://player.vimeo.com/api/player.js";
                                tag.onload = function() {
                                    crearReproductorVimeo();
                                };
                                let firstScriptTag = document.getElementsByTagName('script')[0] || document.body
                                    .lastChild;
                                firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
                            } else {
                                crearReproductorVimeo();
                            }
                        } else {
                            setTimeout(() => {
                                habilitarBotonesHecho();
                            }, 15000);
                        }
                    } else if (tipoActual === 'lectura' || tipoActual === 'texto') {
                        let checkScroll = function() {
                            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 50) {
                                habilitarBotonesHecho();
                                window.removeEventListener('scroll', checkScroll);
                            }
                        };
                        window.addEventListener('scroll', checkScroll);
                        checkScroll();
                    } else if (tipoActual === 'recurso' || tipoActual === 'archivo') {
                        setTimeout(() => {
                            habilitarBotonesHecho();
                        }, 5000);
                    } else {
                        setTimeout(() => {
                            habilitarBotonesHecho();
                        }, 3000);
                    }
                }

                inicializarOyentesProgreso();

                Livewire.on('item-cambiado', () => {
                    if (intervalProgresoYT) clearInterval(intervalProgresoYT);
                    // Permitimos que re-renderice el DOM de livewire.
                    setTimeout(inicializarOyentesProgreso, 500);
                });

                // Eventos SweetAlert para Validación de Evaluación
                Livewire.on('evaluacion-incompleta', (data) => {
                    Swal.fire({
                        title: 'Evaluación Incompleta',
                        text: 'Por favor responde a todas las preguntas antes de enviar tu evaluación.',
                        icon: 'warning',
                        confirmButtonText: 'Revisar',
                        customClass: {
                            confirmButton: 'btn btn-warning'
                        },
                        buttonsStyling: false
                    });
                });

                Livewire.on('confirmar-envio-evaluacion', (data) => {
                    Swal.fire({
                        title: '¿Terminaste tu evaluación?',
                        text: "¿Estás seguro de enviar tus respuestas? Verificaste que todo esté contestado de acuerdo al temario.",
                        icon: 'question',
                        showCancelButton: true,

                        confirmButtonText: 'Sí, enviar ahora',
                        cancelButtonText: 'Cancelar y revisar',
                        customClass: {
                            confirmButton: 'btn btn-primary rounded-pill me-3',
                            cancelButton: 'btn btn-outline-secondary rounded-pill'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            console.log('Confirmación aceptada. Ejecutando lógica de servidor...');
                            @this.procesarEnvioEvaluacion();
                        }
                    });
                });

                Livewire.on('evaluacion-aprobada', (data) => {
                    Swal.fire({
                        title: '¡Felicidades!',
                        text: 'Has aprobado la evaluación con una nota de ' + data.nota + '%.',
                        icon: 'success',
                        showCancelButton: data.puedeVerRespuestas,
                        confirmButtonText: 'Siguiente Clase',
                        cancelButtonText: 'Ver Respuestas',
                        customClass: {
                            confirmButton: 'btn btn-success',
                            cancelButton: 'btn btn-outline-success ms-2'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.avanzarSiguiente();
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            @this.verRespuestas();
                        }
                    });
                });

                Livewire.on('evaluacion-reprobada', (data) => {
                    Swal.fire({
                        title: 'No has aprobado',
                        text: 'Tu nota fue de ' + data.nota + '%. Te quedan ' + data.restantes +
                            ' intento(s). ¡Inténtalo de nuevo!',
                        icon: 'error',
                        confirmButtonText: 'Reintentar',
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        },
                        buttonsStyling: false
                    });
                });

                Livewire.on('evaluacion-reprobada-finalizar-quiz', (data) => {
                    Swal.fire({
                        title: 'Quiz Finalizado',
                        text: 'No has aprobado (Nota: ' + data.nota + '%). Has agotado tus intentos, pero puedes continuar con el siguiente contenido.' + (data.puedeVerRespuestas ? ' Haz clic en "Ver Respuestas" para revisar tus fallos antes de seguir.' : ''),
                        icon: 'warning',
                        confirmButtonText: 'Continuar',
                        customClass: {
                            confirmButton: 'btn btn-warning'
                        },
                        buttonsStyling: false
                    });
                });

                Livewire.on('evaluacion-reprobada-bloqueada', (data) => {
                    let text = 'No has aprobado (Nota: ' + data.nota + '%). Has agotado tus intentos. ';
                    if (data.esFinal) {
                        text += 'Siendo una evaluación final, serás redirigido al catálogo.';
                    } else {
                        text += 'Podrás reintentar en ' + data.horas + ' horas.';
                    }
                    
                    if (data.puedeVerRespuestas) {
                        text += ' Antes de salir, puedes revisar las respuestas correctas.';
                    }

                    Swal.fire({
                        title: data.esFinal ? 'Evaluación Finalizada' : 'Intentos Agotados',
                        text: text,
                        icon: 'warning',
                        confirmButtonText: 'Entendido',
                        customClass: {
                            confirmButton: 'btn btn-warning'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed && data.esFinal && !data.puedeVerRespuestas) {
                            window.location.href = "{{ route('cursos.campus'),$curso->slug }}";
                        }
                    });
                });

                Livewire.on('tiempo-agotado', () => {
                    Swal.fire({
                        title: '¡Tiempo Agotado!',
                        text: 'El tiempo para realizar esta evaluación ha finalizado.',
                        icon: 'error',
                        confirmButtonText: 'Entendido',
                        allowOutsideClick: false,
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    });
                });
            });
        </script>
    @endpush
