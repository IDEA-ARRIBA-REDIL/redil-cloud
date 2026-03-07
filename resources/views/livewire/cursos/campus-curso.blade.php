<div>
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
            <h5 class="fw-semibold  text-black mb-4" >{{ $itemActivo ? $itemActivo->titulo : 'Sin contenido seleccionado' }}</h5>

            <!-- Visualizador de Contenido -->
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                @if($itemActivo)
                    @if($itemActivo->tipo->codigo == 'video')
                        <!-- TIPO VIDEO -->
                        <div class="ratio ratio-16x9 bg-dark position-relative">
                            @if($itemActivo->itemable && $itemActivo->itemable->video_url)
                                @if($itemActivo->itemable->video_plataforma === 'youtube')
                                    <!-- Agregamos ID y enablejsapi=1 para poder conectarnos al reproductor por JS -->
                                    <iframe id="youtube-player-{{ $itemActivo->itemable->video_id }}" src="https://www.youtube.com/embed/{{ $itemActivo->itemable->video_id }}?enablejsapi=1&rel=0" allowfullscreen></iframe>
                                @elseif($itemActivo->itemable->video_plataforma === 'vimeo')
                                    <!-- ID para Vimeo -->
                                    <iframe id="vimeo-player-{{ $itemActivo->itemable->video_id }}" src="https://player.vimeo.com/video/{{ $itemActivo->itemable->video_id }}" allowfullscreen></iframe>
                                @else
                                    <div class="d-flex align-items-center justify-content-center text-white h-100 flex-column">
                                        <a href="{{ $itemActivo->itemable->video_url }}" target="_blank" class="text-white text-decoration-none">
                                            <i class="ti ti-external-link me-2 mb-2" style="font-size: 3rem;"></i><br>
                                            Ver video externo
                                        </a>
                                    </div>
                                @endif
                            @else
                                <div class="d-flex align-items-center justify-content-center h-100 text-white flex-column">
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
                            <p class="text-muted mb-0">Por favor, lee el contenido detallado en la sección de descripción abajo.</p>
                        </div>
                    @elseif($itemActivo->tipo->codigo == 'iframe')
                        <!-- TIPO IFRAME -->
                        <div class="ratio ratio-16x9 bg-light position-relative border-bottom">
                            @if($itemActivo->itemable && $itemActivo->itemable->iframe_code)
                                {!! $itemActivo->itemable->iframe_code !!}
                            @else
                                <div class="d-flex align-items-center justify-content-center h-100 text-muted flex-column">
                                    <i class="ti ti-code mb-2" style="font-size: 3rem;"></i>
                                    <p>Código embebido no disponible</p>
                                </div>
                            @endif
                        </div>
                    @elseif($itemActivo->tipo->codigo == 'recurso' || $itemActivo->tipo->codigo == 'archivo')
                        <!-- TIPO RECURSO -->
                        <div class="bg-light border-bottom" style="height: 70vh; min-height: 500px; display: flex; flex-direction: column;">
                            @if($itemActivo->itemable && $itemActivo->itemable->archivo_path)
                                @php
                                    $archivoRuta = $itemActivo->itemable->archivo_path;
                                    $esPdf = Str::endsWith(strtolower($archivoRuta), '.pdf');
                                @endphp

                                @if($esPdf)
                                    <!-- Solución para scroll de PDF en móviles (especialmente iOS/Safari) -->
                                    <div class="pdf-container w-100 h-100" style="overflow-y: auto; -webkit-overflow-scrolling: touch; background-color: #525659; flex-grow: 1;">
                                        <object data="{{ route('cursos.recurso.preview', $itemActivo->itemable->id) }}?t={{ $itemActivo->itemable->updated_at?->timestamp }}"
                                                type="application/pdf"
                                                class="w-100 h-100"
                                                style="min-height: 100%; border: none; display: block;">
                                            <!-- Fallback si el navegador móvil no soporta object PDF -->
                                            <div class="d-flex align-items-center justify-content-center h-100 text-white flex-column p-4 text-center">
                                                <i class="ti ti-file-text mb-3" style="font-size: 3rem;"></i>
                                                <p>Tu navegador no soporta la visualización integrada de PDF.</p>
                                                <a href="{{ route('cursos.recurso.preview', $itemActivo->itemable->id) }}" target="_blank" class="btn btn-primary mt-2 rounded-pill">
                                                    Abrir PDF en nueva pestaña <i class="ti ti-external-link ms-1"></i>
                                                </a>
                                            </div>
                                        </object>
                                    </div>
                                @else
                                    <!-- Otros tipos de archivos (Word, Excel, PPT) que se abren con Google Docs Viewer -->
                                    <div class="ratio ratio-16x9 w-100 h-100 position-relative" style="flex-grow: 1;">
                                        <iframe src="{{ route('cursos.recurso.preview', $itemActivo->itemable->id) }}?t={{ $itemActivo->itemable->updated_at?->timestamp }}" class="w-100 h-100" style="border: none;" allowfullscreen title="Visor de Recursos"></iframe>
                                    </div>
                                @endif

                                <!-- Botón inferior móvil para forzar apertura / recargas -->
                                <div class="p-3 bg-white border-top d-md-none text-center">
                                    <h6 class="text-muted small mb-3">Opciones del documento</h6>

                                    <div class="d-flex flex-column gap-2">
                                        <a href="{{ route('cursos.recurso.preview', $itemActivo->itemable->id) }}" target="_blank"
                                           class="btn btn-outline-primary rounded-pill w-100">
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
                                <div class="d-flex align-items-center justify-content-center h-100 text-muted flex-column flex-grow-1">
                                    <i class="ti ti-file-download mb-2" style="font-size: 3rem;"></i>
                                    <p>Recurso no disponible</p>
                                </div>
                            @endif
                        </div>
                    @elseif(in_array($itemActivo->tipo->codigo, ['evaluacion', 'quiz', 'final']))
                        <!-- TIPO EVALUACION (Examen) -->
                        <div class="p-4 p-md-5 bg-white position-relative border-bottom rounded-top" style="min-height: 400px;">
                            @if(!empty($preguntasEvaluacion))
                                <!-- NAVEGADOR SUPERIOR: Círculos de Progreso -->
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-4 pb-3 border-bottom">
                                    <div class="d-flex flex-wrap gap-2 me-auto">
                                        @foreach($preguntasEvaluacion as $index => $pregunta)
                                            @php
                                                $respondida = !empty($respuestasEvaluacion[$pregunta->id]);
                                                $esActiva = ($index === $preguntaActualIndex);

                                                // Estilos dinámicos estilo Plantilla Sneat (Bootstrap)
                                                $circuloClase = "btn btn-icon rounded-circle fw-bold border-2 transition-all ";
                                                if ($esActiva) {
                                                    // Activa (Outline Primary o Solid Primary)
                                                    $circuloClase .= "btn-primary shadow-sm border-primary scale-105";
                                                } elseif ($respondida) {
                                                    // Ya respondida (Outline Success o Info)
                                                    $circuloClase .= "btn-outline-primary border-primary bg-label-primary";
                                                } else {
                                                    // Pendiente (Gris / Secondary)
                                                    $circuloClase .= "btn-outline-secondary";
                                                }
                                            @endphp

                                            <!-- Botón Círculo de Navegación -->
                                            <button wire:click="irAPregunta({{ $index }})"
                                                    class="{{ $circuloClase }}"
                                                    style="width: 40px; height: 40px; {{ $esActiva ? 'transform: scale(1.1);' : '' }}"
                                                    title="Pregunta {{ $index + 1 }}">
                                                {{ $index + 1 }}
                                            </button>
                                        @endforeach
                                    </div>

                                    <!-- Botón Enviar -->
                                    <button wire:click="validarYEnviarEvaluacion" class="btn btn-success fw-bold px-4 rounded-pill shadow-sm d-flex align-items-center gap-2">
                                        Enviar Evaluación <i class="ti ti-brand-telegram"></i>
                                    </button>
                                </div>

                                <!-- CUADRO DE LA PREGUNTA ACTUAL -->
                                @php
                                    $preguntaEnPantalla = $preguntasEvaluacion[$preguntaActualIndex];
                                @endphp

                                <div class="mb-5" style="min-height: 250px;">
                                    <!-- Enunciado de la Pregunta -->
                                    <div class="mb-4">
                                        <div class="d-flex align-items-start gap-3">
                                            <span class="badge bg-label-primary rounded-pill p-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; min-width: 35px;">
                                                <span class="fs-5">{{ $preguntaActualIndex + 1 }}</span>
                                            </span>
                                            <div>
                                                <h4 class="fw-bold mb-1 text-heading">
                                                    {{ $preguntaEnPantalla->pregunta }}
                                                </h4>
                                                @if($preguntaEnPantalla->tipo_respuesta == 'multiple')
                                                    <small class="text-muted"><i class="ti ti-checkbox text-primary me-1"></i>Pregunta de selección múltiple (elige todas las que apliquen).</small>
                                                @elseif($preguntaEnPantalla->tipo_respuesta == 'unica')
                                                    <small class="text-muted"><i class="ti ti-circle-dot text-primary me-1"></i>Pregunta de selección única.</small>
                                                @else
                                                    <small class="text-muted"><i class="ti ti-adjustments-horizontal text-primary me-1"></i>Verdadero o falso.</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Opciones Disponibles -->
                                    <div class="d-flex flex-column gap-3 ps-md-5">
                                        @foreach($preguntaEnPantalla->opciones as $opcion)
                                            <!-- Determinar si la opción está checked en nuestro estado local -->
                                            @php
                                                $isChecked = in_array($opcion->id, $respuestasEvaluacion[$preguntaEnPantalla->id]);
                                                $inputType = ($preguntaEnPantalla->tipo_respuesta === 'multiple') ? 'checkbox' : 'radio';
                                                $inputName = "pregunta_" . $preguntaEnPantalla->id;
                                            @endphp

                                            <label class="d-flex align-items-center p-3 border rounded-3 cursor-pointer transition-all {{ $isChecked ? 'border-primary bg-label-primary shadow-sm' : 'bg-transparent border-secondary-subtle hover-bg-light' }}">
                                                <div class="form-check me-3 mb-0">
                                                    <input class="form-check-input {{ $inputType === 'radio' ? 'rounded-circle' : '' }}"
                                                           type="{{ $inputType }}"
                                                           name="{{ $inputName }}"
                                                           value="{{ $opcion->id }}"
                                                           wire:click="seleccionarRespuesta({{ $preguntaEnPantalla->id }}, {{ $opcion->id }}, '{{ $preguntaEnPantalla->tipo_respuesta }}')"
                                                           {{ $isChecked ? 'checked' : '' }}
                                                           style="transform: scale(1.1);">
                                                </div>
                                                <span class="fs-5 {{ $isChecked ? 'fw-bold text-primary' : 'text-body' }}">{{ $opcion->opcion }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- BOTONES DE NAVEGACIÓN INFERIOR -->
                                <div class="d-flex justify-content-between pt-4 border-top">
                                    <button wire:click="preguntaAnterior"
                                            class="btn btn-outline-primary px-4 rounded-pill fw-bold {{ $preguntaActualIndex == 0 ? 'disabled' : '' }}"
                                            >
                                        <i class="ti ti-chevron-left me-1"></i> Anterior
                                    </button>

                                    <button wire:click="siguientePregunta"
                                            class="btn btn-primary px-4 rounded-pill fw-bold shadow-sm"
                                            {{ $preguntaActualIndex == (count($preguntasEvaluacion) - 1) ? 'disabled' : '' }}>
                                        Siguiente <i class="ti ti-chevron-right ms-1"></i>
                                    </button>
                                </div>
                            @else
                                <div class="d-flex align-items-center justify-content-center h-100 text-muted flex-column py-5">
                                    <i class="ti ti-file-unknown mb-2" style="font-size: 3rem;"></i>
                                    <h5 class="mt-2 text-heading">Evaluación Vacía</h5>
                                    <p>Esta evaluación no tiene preguntas configuradas aún.</p>
                                </div>
                            @endif
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
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center pb-4 border-bottom">

                @php
                    $autor = $curso->equipo->first()->user ?? null;
                @endphp

                <!-- Autor Info -->
                <div class="d-flex align-items-center mb-3 mb-sm-0">
                    @if($autor && $autor->avatar)
                        <img src="{{ Storage::url($autor->avatar) }}" alt="Autor" class="rounded-circle" width="48" height="48" style="object-fit: cover;">
                    @else
                        <div class="btn-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width: 48px; height: 48px;">
                            {{ $autor->inicialesNombre()  }}
                        </div>
                    @endif
                    <div class="ms-3">
                        <h6 class="mb-0 fw-bold" style="color: #2b2b4d;">{{ $autor->primer_nombre ?? 'Autor' }} {{ $autor->apellidos ?? '' }}</h6>
                        <small class="text-black d-flex align-items-center gap-3 mt-1">
                            <span><i class="ti ti-category me-1"></i> Crecimiento</span>
                            <span><i class="ti ti-clock me-1"></i> {{ $curso->duracion_estimada ?? '2 Meses' }}</span>
                        </small>
                    </div>
                </div>

                <!-- Botones de Acción (Restricciones LMS) -->
                <div class="d-flex gap-2">
                    @if($itemActivo && isset($itemsProgreso[$itemActivo->id]))
                        @if($itemsProgreso[$itemActivo->id] === 'completado')
                            <!-- Ya completado, solo mostramos el indicativo y avanzar -->
                            <button class="btn btn-outline-success px-4 rounded-pill shadow-sm d-flex align-items-center gap-2" disabled>
                                <i class="ti ti-check"></i> Completado
                            </button>
                            <button wire:click="avanzarSiguiente" class="btn btn-primary px-4 rounded-pill shadow-sm">
                                Siguiente lección <i class="ti ti-chevron-right ms-1"></i>
                            </button>
                        @elseif($itemsProgreso[$itemActivo->id] === 'iniciado' && !in_array($itemActivo->tipo->codigo, ['evaluacion', 'quiz', 'final']))
                            <!-- Está en curso. Mostramos botón de HECHO pero bloqueado hasta que el JS lo habilite -->
                            <!-- El JS habilitará este botón basado en el tiempo de video o Scroll del texto -->
                            <button wire:click="marcarCompletado({{ $itemActivo->id }})" id="btn-marcar-hecho" class="btn btn-success px-4 rounded-pill shadow-sm d-flex align-items-center gap-2" disabled>
                                <i class="ti ti-check"></i> Hecho
                            </button>
                        @endif
                    @endif
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

            <!-- SECCIÓN INFERIOR: DESCRIPCIÓN Y FORO -->
            <div class="mt-5">

                <!-- Columna Descripción -->
                <div class="mb-4">
                    <h5 class="fw-semibold text-black mb-3">Descripción</h5>
                    <div class="text-black border p-4 rounded-4 bg-white shadow-sm" style="line-height: 1.7;">
                        @if($itemActivo && $itemActivo->itemable && $itemActivo->itemable->contenido_html)
                            {!! $itemActivo->itemable->contenido_html !!}
                        @else
                            <p class="text-muted">No hay descripción adicional provista para esta clase.</p>
                        @endif
                    </div>
                </div>

                <!-- Columna Foro (Opcional según diseño) -->
                @if($itemActivo && isset($itemActivo->tipo) && !in_array($itemActivo->tipo->codigo, ['evaluacion', 'quiz', 'final']))
                <div class="border-primary border-2  card  mb-5 shadow-sm rounded-4 mt-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0 text-primary d-flex align-items-center gap-2">
                                <i class="ti ti-messages"></i> Foro de dudas
                            </h5>
                        </div>

                        <p class="text-black small mb-3">¿Tienes alguna pregunta sobre esta clase? Consulta lo que otros compañeros han discutido o inicia una nueva conversación.</p>

                        @if($hilosForo->count() > 0)
                            <div class="list-group list-group-flush mb-3 rounded">
                                @foreach($hilosForo as $hilo)
                                    <div class="list-group-item bg-white px-3 py-2 border-start border-4 border-primary mb-2 rounded shadow-sm">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="fw-bold text-dark text-truncate d-block" style="max-width: 80%;">{{ $hilo->titulo }}</small>
                                            <span class="badge bg-label-{{ $hilo->estado == 'resuelto' ? 'success' : 'warning' }} rounded-pill" style="font-size: 0.6rem;">
                                                {{ ucfirst($hilo->estado) }}
                                            </span>
                                        </div>
                                        <small class="text-black d-block mt-1"><i class="ti ti-user me-1"></i>{{ $hilo->user->primer_nombre }}</small>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="bg-white p-3 rounded mb-3 text-center border">
                                <small class="  text-black">Sé el primero en hacer una pregunta.</small>
                            </div>
                        @endif

                        <div class="d-grid mt-2">
                            <button type="button" class="btn btn-outline-primary rounded-pill d-flex justify-content-center align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalForoCompleto">
                                Ver foro completo <i class="ti ti-external-link"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @else
                <div class="alert alert-warning mt-4 rounded-4 shadow-sm border-0 bg-label-warning text-dark d-flex align-items-center gap-3 p-3">
                    <i class="ti ti-info-circle fs-4 text-warning"></i>
                    <div>
                        <h6 class="mb-1 fw-bold">Foro deshabilitado</h6>
                        <small>No se permiten preguntas públicas en la sección de evaluación para proteger la integridad de las respuestas.</small>
                    </div>
                </div>
                @endif

            </div>
            <!-- Fin Sección Inferior -->

        </div>
        <!-- FIN COLUMNA IZQUIERDA -->

        <!-- COLUMNA DERECHA: PROGRESO Y TEMARIO -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 sticky-top" style="top: 80px;">
                <div class="card-body p-4">

                    <h5 class="fw-bold mb-3" style="color: #2b2b4d;">Tu progreso</h5>

                    <!-- Barra de Progreso -->
                    <div class="mb-4">
                        <small class="text-black d-block mb-1">{{ $progresoPorcentaje }}% completado</small>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progresoPorcentaje }}%;" aria-valuenow="{{ $progresoPorcentaje }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <!-- Acordeón del Temario -->
                    <div class="accordion accordion-flush" id="temarioAccordion">

                        @forelse($curso->modulos as $index => $modulo)
                            <div class="accordion-item {{ $index !== 0 ? 'mt-2' : '' }} border rounded" style="background-color: #f8f9fa;">
                                <h2 class="accordion-header" id="headingModulo-{{ $modulo->id }}">
                                    <!-- Si el módulo actual contene el ítem activo, desplegar por defecto, de lo contrario, contraido -->
                                    <button class="accordion-button rounded {{ collect($modulo->items)->pluck('id')->contains($itemActivoId) ? '' : 'collapsed' }} bg-transparent fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseModulo-{{ $modulo->id }}" aria-expanded="{{ collect($modulo->items)->pluck('id')->contains($itemActivoId) ? 'true' : 'false' }}" aria-controls="collapseModulo-{{ $modulo->id }}" style="color: #2b2b4d; box-shadow: none;">

                                        <!-- Simulamos ícono de progreso del módulo (Verde si todo está completado) -->
                                        @if($index == 0)
                                            <i class="ti ti-circle-check-filled text-success me-2 fs-5"></i>
                                        @else
                                            <i class="ti ti-circle text-success me-2 fs-5"></i> <!-- Circulo verde vacio? Segun diseño -->
                                        @endif

                                        <div class="d-flex flex-column ms-1">
                                            <span>Módulo {{ $index + 1 }}: {{ $modulo->titulo }}</span>
                                            <small class="text-black fw-normal" style="font-size: 0.75rem;">
                                                <i class="ti ti-clock me-1"></i> {{ count($modulo->items) }} Lecciones
                                            </small>
                                        </div>
                                    </button>
                                </h2>

                                <div id="collapseModulo-{{ $modulo->id }}" class="accordion-collapse collapse {{ collect($modulo->items)->pluck('id')->contains($itemActivoId) ? 'show' : '' }}" aria-labelledby="headingModulo-{{ $modulo->id }}" data-bs-parent="#temarioAccordion">
                                    <div class="accordion-body p-0">

                                        <ul class="list-group list-group-flush bg-transparent p-1">
                                            @forelse($modulo->items as $itemIndex => $item)

                                                <!-- Lógica de restricciones LMS desde BD -->
                                                @php
                                                    $isActivo = $item->id == $itemActivoId;
                                                    $estadoItem = $itemsProgreso[$item->id] ?? 'bloqueado';

                                                    // Clases base para el item
                                                    $itemClass = "list-group-item list-group-item-action d-flex align-items-center py-3 border-0 bg-transparent text-start";
                                                    if($isActivo) {
                                                        $itemClass .= " fw-bold text-dark bg-white shadow-sm rounded-3 my-1 ms-2 me-2 border-start border-4 border-primary";
                                                    } else {
                                                        $itemClass .= " ms-3";
                                                        if($estadoItem === 'bloqueado') {
                                                            $itemClass .= " text-muted";
                                                        } else {
                                                            $itemClass .= " text-black";
                                                        }
                                                    }
                                                @endphp

                                                <!-- Si está bloqueado, se aplican estilos extra para impedir clics evidentes -->
                                                <button wire:click="seleccionarItem({{ $item->id }})" class="{{ $itemClass }}" style="{{ $estadoItem === 'bloqueado' ? 'cursor: not-allowed; opacity: 0.6;' : 'cursor: pointer;' }} {{ $isActivo ? 'transform: translateX(-5px);' : '' }} width: 97%;" {{ $estadoItem === 'bloqueado' ? 'disabled' : '' }}>

                                                    <!-- Icono de Estado -->
                                                    @if($estadoItem === 'completado')
                                                        <i class="ti ti-circle-check-filled text-success me-3 fs-5"></i>
                                                    @elseif($estadoItem === 'bloqueado')
                                                        <i class="ti ti-lock text-secondary me-3 fs-5"></i>
                                                    @else
                                                        <!-- Estado iniciado / disponible -->
                                                        @if($item->tipo->codigo == 'video')
                                                            <i class="ti ti-player-play-filled text-primary me-3 fs-5"></i>
                                                        @elseif($item->tipo->codigo == 'lectura' || $item->tipo->codigo == 'texto')
                                                            <i class="ti ti-book text-info me-3 fs-5"></i>
                                                        @else
                                                            <i class="ti ti-file text-primary me-3 fs-5"></i>
                                                        @endif
                                                    @endif

                                                    <!-- Titulo -->
                                                    <span class="{{ $isActivo ? 'fw-bold' : '' }}" style="font-size: 0.9rem;">
                                                        Clase {{ $itemIndex + 1 }}: {{ Str::limit($item->titulo, 35) }}
                                                    </span>

                                                    <!-- Indicador a la derecha si es la lección activa -->
                                                    @if($isActivo)
                                                        <i class="ti ti-player-play-filled ms-auto text-primary" style="font-size: 0.8rem;"></i>
                                                    @endif

                                                </button>
                                            @empty
                                                <div class="p-3 text-black small text-center">Este módulo aún no tiene lecciones.</div>
                                            @endforelse
                                        </ul>

                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-warning">Aún no hay módulos para este curso.</div>
                        @endforelse

                    </div>

                </div>
            </div>
        </div>
        <!-- FIN COLUMNA DERECHA -->

    </div>

    <!-- MODAL FORO COMPLETO -->
    <div class="modal fade" id="modalForoCompleto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header btn-primary px-4 py-3">
                    <h5 class="modal-title text-white d-flex align-items-center gap-2">
                        <i class="ti ti-message-circle-2"></i> Foro del curso: {{ $curso->nombre }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- Cuerpo del Modal incrustando el componente Livewire ForoCursoEstudiante -->
                <div class="modal-body p-3 " style="min-height: 60vh;">
                    @livewire('cursos.foro.foro-curso-estudiante', ['cursoId' => $curso->id])
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:navigated', () => {
        let intervalProgresoYT = null;

        function crearReproductorYoutube(btnHecho) {
            let iframes = document.querySelectorAll('iframe[id^="youtube-player-"]');
            if(iframes.length === 0) return;

            if(intervalProgresoYT) clearInterval(intervalProgresoYT);

            let player = new YT.Player(iframes[0].id, {
                events: {
                    'onStateChange': function(event) {
                        if (event.data == 1) { // 1 = Reproduciendo
                            intervalProgresoYT = setInterval(function() {
                                let duration = player.getDuration();
                                let current = player.getCurrentTime();
                                if (duration > 0 && (current / duration) >= 0.95) {
                                    btnHecho.removeAttribute('disabled');
                                    clearInterval(intervalProgresoYT);
                                }
                            }, 2000); // 2 segundos
                        } else {
                            if(intervalProgresoYT) clearInterval(intervalProgresoYT);
                        }
                    }
                }
            });
        }

        function crearReproductorVimeo(btnHecho) {
            let iframes = document.querySelectorAll('iframe[id^="vimeo-player-"]');
            if(iframes.length === 0) return;

            let player = new Vimeo.Player(iframes[0]);

            player.on('timeupdate', function(data) {
                if (data.percent >= 0.95) {
                    btnHecho.removeAttribute('disabled');
                    player.off('timeupdate');
                }
            });
        }

        function inicializarOyentesProgreso() {
            let btnHecho = document.getElementById('btn-marcar-hecho');
            if(!btnHecho) return;

            let tipoActual = '{{ $itemActivo->tipo->codigo ?? '' }}';

            if (tipoActual === 'video') {
                let plataforma = '{{ $itemActivo->itemable->video_plataforma ?? "" }}';

                if (plataforma === 'youtube') {
                    if (typeof YT === 'undefined' || typeof YT.Player === 'undefined') {
                        let tag = document.createElement('script');
                        tag.src = "https://www.youtube.com/iframe_api";
                        let firstScriptTag = document.getElementsByTagName('script')[0] || document.body.lastChild;
                        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

                        window.onYouTubeIframeAPIReady = function() {
                            crearReproductorYoutube(btnHecho);
                        };
                    } else {
                        crearReproductorYoutube(btnHecho);
                    }
                }
                else if (plataforma === 'vimeo') {
                    if (typeof Vimeo === 'undefined') {
                        let tag = document.createElement('script');
                        tag.src = "https://player.vimeo.com/api/player.js";
                        tag.onload = function() {
                            crearReproductorVimeo(btnHecho);
                        };
                        let firstScriptTag = document.getElementsByTagName('script')[0] || document.body.lastChild;
                        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
                    } else {
                        crearReproductorVimeo(btnHecho);
                    }
                }
                else {
                    setTimeout(() => { btnHecho.removeAttribute('disabled'); }, 15000);
                }
            }
            else if (tipoActual === 'lectura' || tipoActual === 'texto') {
                let checkScroll = function() {
                    if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 50) {
                        btnHecho.removeAttribute('disabled');
                        window.removeEventListener('scroll', checkScroll);
                    }
                };
                window.addEventListener('scroll', checkScroll);
                checkScroll();
            }
            else if (tipoActual === 'recurso' || tipoActual === 'archivo') {
                setTimeout(() => { btnHecho.removeAttribute('disabled'); }, 5000);
            }
            else {
                setTimeout(() => { btnHecho.removeAttribute('disabled'); }, 3000);
            }
        }

        inicializarOyentesProgreso();

        Livewire.on('item-cambiado', () => {
             if(intervalProgresoYT) clearInterval(intervalProgresoYT);
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
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, enviar ahora',
                cancelButtonText: 'Cancelar y revisar',
                customClass: {
                    confirmButton: 'btn btn-success me-3',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Llamamos a la función de confirmación en el componente
                    // Usando Livewire 3 format dispatch
                    Livewire.dispatch('procesarEnvioEvaluacion');
                }
            });
        });
    });
</script>
@endpush
