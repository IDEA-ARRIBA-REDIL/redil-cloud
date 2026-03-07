@section('isEscuelasModule', true)

@php
// Suponiendo que tienes un Helper para las clases de la plantilla, si no, puedes eliminar estas líneas.
// $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Mi Materia - Escuelas')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/apex-charts/apex-charts.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/apex-charts/apexcharts.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('page-style')
{{-- Estilos para mejorar la visualización y responsividad --}}
<style>
    .card-item-calificacion {
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }

    .card-item-calificacion .badge {
        font-size: 0.75rem;
    }

    /* Colores de borde según el estado del ítem */
    .status-calificado {
        border-left-color: var(--bs-success);
    }

    .status-entregado {
        border-left-color: var(--bs-info);
    }

    .status-pendiente {
        border-left-color: var(--bs-warning);
    }

    .nota-valor {
        font-size: 1.5rem;
        font-weight: 600;
    }

    .recurso-card {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .recurso-card .card-body {
        flex-grow: 1;
    }

</style>
@endsection


@section('content')
<h4 class="mb-4 text-primary">Perfil de la materia: <span class="text-black fw-normal">{{ $materia->nombre }}</span>
</h4>

@include('layouts.status-msn')

<div class="row">
    <div class="col-12">
        <ul class="nav nav-pills flex-column flex-md-row mb-4" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pills-resumen-tab" data-bs-toggle="pill" data-bs-target="#pills-resumen" type="button" role="tab" aria-controls="pills-resumen" aria-selected="true"><i class="mdi mdi-home-outline me-1"></i> Resumen</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-calificaciones-tab" data-bs-toggle="pill" data-bs-target="#pills-calificaciones" type="button" role="tab" aria-controls="pills-calificaciones" aria-selected="false"><i class="mdi mdi-calculator-variant-outline me-1"></i> Calificaciones por Corte</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-recursos-tab" data-bs-toggle="pill" data-bs-target="#pills-recursos" type="button" role="tab" aria-controls="pills-recursos" aria-selected="false"><i class="mdi mdi-book-open-variant-outline me-1"></i> Recursos</button>
            </li>
        </ul>
        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-resumen" role="tabpanel" aria-labelledby="pills-resumen-tab">
                <div class="row g-4">
                    <div class="col-lg-7 col-12">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                                    <h5 class="card-title mb-0"><i class="mdi mdi-information-outline me-2"></i>
                                        Información General</h5>
                                   {{-- CÓDIGO CORREGIDO --}}
                                    <div class="text-end">
                                        <h6 class="mb-0">Promedio Actual</h6>
                                        {{--
                                        Aquí está la magia: Usamos un operador ternario para decidir la clase.
                                        Si el promedio es mayor o igual a la nota mínima, usa 'text-success' (verde).
                                        Si no, usa 'text-danger' (rojo).
                                        --}}
                                        <h4 class="{{ $materia->promedio_actual >= $notaMinimaAprobacion ? 'text-success' : 'text-danger' }} mb-0 fw-bold">
                                            {{ $materia->promedio_actual }}
                                        </h4>
                                    </div>
                                </div>

                                <ul class="list-unstyled">
                                    <li class="mb-3 d-flex align-items-center">
                                        <i class="mdi mdi-account-tie-outline mdi-24px text-primary me-3"></i>
                                        <div>
                                            <h6 class="mb-0">Maestro</h6>
                                             @foreach ($materia->maestros as $maestro)

                                                <div class="d-flex align-items-center {{ !$loop->last ? 'mb-2' : '' }}">
                                                    {{-- UX: Las imágenes de perfil humanizan la interfaz y crean una conexión visual. --}}
                                                    @if($maestro->imagen == "default-m.png" || $maestro->imagen == "default-f.png")
                                                    <div class="avatar avatar-xl">
                                                        <span class="avatar-initial rounded-circle border border-3 border-white bg-info">{{$maestro->iniciales}} </span>
                                                    </div>
                                                    @else
                                                    <div class="avatar avatar-xl">
                                                        <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$maestro->imagen) : $configuracion->ruta_almacenamiento.'/img/usuarios/foto-usuario/'.$maestro->imagen }}" alt="{{ $maestro->imagen }}" class="avatar-initial rounded-circle border border-3 border-white bg-info">
                                                    </div>
                                                    @endif
                                                    <span>{{ $maestro->nombre }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </li>
                                    <li class="d-flex align-items-center">
                                        <i class="mdi mdi-calendar-clock-outline mdi-24px text-primary me-3"></i>
                                        <div>
                                            <h6 class="mb-0">Horario</h6>
                                            <span>{{ $materia->horario }}</span>
                                        </div>
                                    </li>
                                </ul>

                                <hr>
                                <h6 class="mt-4 mb-3">Actividades Calificables del Semestre</h6>
                               <div class="table-responsive text-nowrap">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Actividad</th>
                                                <th>Corte</th>
                                                <th>Nota</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                            @foreach ($materia->items_tabla_resumen as $item)
                                                <tr>
                                                    <td><strong>{{ $item->nombre }}</strong></td>
                                                    <td><small class="text-muted">{{ $item->corte }}</small></td>
                                                    <td>
                                                        @if (is_numeric($item->nota))
                                                            <span class="badge {{ $item->nota >= 3.0 ? 'bg-label-success' : 'bg-label-danger' }}">{{ $item->nota }}</span>
                                                        @else
                                                            <span class="badge bg-label-secondary">Pendiente</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5 col-12">
                        <div class="card h-100">
                            <div class="card-body">
                                {{-- Parte del gráfico (sin cambios) --}}
                                <div class="text-center">
                                    <h5 class="card-title mb-2"><i class="mdi mdi-calendar-check me-2"></i> Asistencia</h5>
                                    <div id="asistenciaChart"></div>
                                    <p class="mt-3 mb-0 fs-5">
                                        <span class="fw-bold">{{ $asistencia->asistencias_alumno }}</span> de
                                        <span class="fw-bold">{{ $asistencia->total_clases }}</span> clases
                                    </p>
                                    <small class="text-muted">Progreso de asistencia</small>
                                </div>
                                <hr>

                                {{-- ========================================================== --}}
                                {{-- === INICIO DE LA SECCIÓN MODIFICADA CON ALPINE.JS       === --}}
                                {{-- ========================================================== --}}

                                {{-- 1. Contenedor principal de Alpine.js --}}
                                {{-- x-data="{ expanded: false }" inicializa la "memoria" de nuestro componente.
                                    'expanded' empieza como 'false' (contraído). --}}
                                <div x-data="{ expanded: false }">
                                    <h6 class="mb-3">Historial Reciente</h6>

                                    {{-- 2. Contenedor de la tabla con estilos dinámicos --}}
                                    {{-- :style="..." aplica estilos condicionalmente.
                                        Si 'expanded' es falso, aplica un alto máximo y oculta el desbordamiento.
                                        Si es verdadero, no aplica ningún estilo, permitiendo que crezca. --}}
                                    <div
                                        class="table-responsive text-nowrap"
                                        :style="expanded ? '' : 'max-height: 250px; overflow: hidden; position: relative;'">

                                        {{-- La tabla de historial no cambia --}}
                                        <table class="table table-sm">
                                            {{-- ... (<thead> sin cambios) ... --}}
                                            <tbody class="table-border-bottom-0">
                                                @foreach ($asistencia->historial as $registro)
                                                    <tr>
                                                        <td>{{ $registro->fecha }}</td>
                                                        <td>
                                                            @if ($registro->estado == 'Asistió')
                                                                <span class="badge bg-label-success">{{ $registro->estado }}</span>
                                                            @else
                                                                <span class="badge bg-label-danger">{{ $registro->estado }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <small>{{ $registro->motivo ?? 'N/A' }}</small>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>

                                        {{-- 3. Sombra inferior (opcional pero mejora la UX) --}}
                                        {{-- x-show="!expanded" hace que este elemento solo sea visible
                                            cuando la lista está contraída, indicando que hay más contenido. --}}
                                        @if(count($asistencia->historial) > 5) {{-- Un umbral para mostrar el efecto --}}
                                        <div x-show="!expanded" style="position: absolute; bottom: 0; left: 0; right: 0; height: 50px; background: linear-gradient(to top, rgba(255,255,255,1), rgba(255,255,255,0)); pointer-events: none;"></div>
                                        @endif
                                    </div>

                                    {{-- 4. El botón "Ver más / Ver menos" --}}
                                    {{-- Solo se muestra si hay más de 5 registros (puedes ajustar este número) --}}
                                    @if(count($asistencia->historial) > 5)
                                    <div class="text-end mt-3">
                                        {{-- @click="expanded = !expanded" es la acción. Invierte el valor de 'expanded' (de true a false y viceversa) --}}
                                        <a style="text-decoration:underline"  @click="expanded = !expanded" class="link-underline-secondary ">
                                            {{-- x-text="..." cambia el texto del botón dinámicamente --}}
                                            <span class="link-underline-secondary " x-text="expanded ? 'Ver menos' : 'Ver más'"></span>
                                            <i class="ti" :class="expanded ? 'ti-caret-up' : 'ti-caret-down'"></i>
                                        </a>
                                    </div>
                                    @endif
                                </div>
                                {{-- ========================================================== --}}
                                {{-- === FIN DE LA SECCIÓN MODIFICADA                         === --}}
                                {{-- ========================================================== --}}

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="pills-calificaciones" role="tabpanel" aria-labelledby="pills-calificaciones-tab">
                  {{-- Llamamos a nuestro nuevo componente y le pasamos el horario actual --}}
                <livewire:alumno.calificacionesAlumno :horario="$horario" />

            </div>
                <!-- ========================================================== -->
            <!-- === SECCIÓN DE RECURSOS COMPLETAMENTE ACTUALIZADA        === -->
            <!-- ========================================================== -->
            <div class="tab-pane fade" id="pills-recursos" role="tabpanel" aria-labelledby="pills-recursos-tab">
                <div class="row g-4">
                    @forelse ($recursos as $recurso)
                    <div class="col-md-4 col-lg-4 col-xs-12">
                        <div class="card recurso-card">
                            <div class="card-body">
                                @php
                                    $icon = 'mdi-link-variant'; // Icono por defecto
                                    if ($recurso->tipo == 'Video') $icon = 'mdi-youtube text-danger';
                                    if ($recurso->tipo == 'Libro') $icon = 'mdi-book-open-page-variant-outline text-info';
                                    if ($recurso->nombre_archivo) $icon = 'mdi-file-download-outline text-primary';
                                @endphp

                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-center">

                                        <div>
                                        <h5 class="card-title mb-0">{{ $recurso->nombre }}</h5><br>
                                        <span class="badge bg-label-primary mt-1"><i class="mdi {{ $icon }} me-1"></i>{{ $recurso->tipo }}</span>
                                        </div>
                                    </div>
                                </div>
                                <p class="card-text text-muted">
                                    {{ $recurso->descripcion }}
                                </p>
                            </div>

                            <div class="card-footer pt-0 border-0">

                                {{-- Lógica condicional corregida con IF separados --}}
                                <div class="row">

                                    @if ($recurso->nombre_archivo)
                                        <div class="col-xs-12 col-md-12 ">
                                        <a href="{{ Storage::disk('public')->url($recurso->ruta_archivo) }}" class="btn btn-outline-primary waves-effect w-100 m-2" target="_blank" download>
                                            <i class="ti ti-clipboard-text"></i> Ver Archivo
                                        </a>
                                        </div>
                                    @endif

                                    @if ($recurso->link_youtube)
                                      <div class="col-xs-12 col-md-6">
                                            <a  style="text-decoration:underline" href="{{$recurso->link_youtube}}" target="_blank" class="link-underline-secondary  m-2 w-100"  ">
                                                <i class="ti ti-external-link"></i> Ver Video
                                            </a>
                                      </div>
                                    @endif

                                    @if ($recurso->link_externo)
                                      <div class="col-xs-12 col-md-6">
                                        <a style="text-decoration:underline"  href="{{ $recurso->link_externo }}" class="link-underline-secondary m-2 w-100" target="_blank">
                                           <i class="ti ti-external-link"></i> Abrir Enlace
                                        </a>
                                      </div>
                                    @endif
                                      </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="text-center p-5 bg-light rounded">
                            <i class="mdi mdi-folder-open-outline mdi-48px text-muted"></i>
                            <h5 class="mt-3">No hay recursos disponibles</h5>
                            <p class="text-muted">El maestro aún no ha añadido materiales de apoyo para esta materia.</p>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="responderItemModal" tabindex="-1" aria-labelledby="responderItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="responderItemModalLabel">Responder a: <span id="modal-item-titulo"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="respuestaTexto" class="form-label">Tu respuesta:</label>
                    {{-- UX: Se recomienda reemplazar este textarea por un editor de texto enriquecido como Quill o TinyMCE --}}
                    <textarea class="form-control" id="respuestaTexto" rows="8" placeholder="Escribe tu respuesta aquí..."></textarea>
                </div>
                <div class="mb-3">
                    <label for="adjuntarArchivo" class="form-label">Adjuntar archivo (opcional):</label>
                    <input class="form-control" type="file" id="adjuntarArchivo">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-btn-outline-secondary rounded-pill " data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary rounded-pill">Guardar Respuesta</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="verRespuestaModal" tabindex="-1" aria-labelledby="verRespuestaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verRespuestaModalLabel">Revisión de: <span id="modal-ver-item-titulo"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <h6><i class="mdi mdi-account-circle-outline me-2"></i>Tu Entrega:</h6>
                    <div class="p-3 bg-light rounded">
                        <p id="modal-ver-respuesta-alumno"></p>
                    </div>
                </div>
                <div>
                    <h6><i class="mdi mdi-account-tie-outline me-2"></i>Feedback del Maestro:</h6>
                    <div class="p-3 bg-light rounded">
                        <p class="mb-0" id="modal-ver-feedback-maestro"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {




        // Lógica para el gráfico de asistencia
        const asistenciaChartEl = document.querySelector('#asistenciaChart');
        if (asistenciaChartEl) {
            const options = {
                chart: {
                    height: 250
                    , type: 'radialBar'
                }
                , series: @json([$asistencia->porcentaje])

                , plotOptions: {
                    radialBar: {
                        hollow: {
                            size: '70%'
                        }
                        , dataLabels: {
                            name: {
                                show: false
                            }
                            , value: {
                                fontSize: '2rem'
                                , fontWeight: '600'
                                , offsetY: 8
                                , formatter: function(val) {
                                    return val + '%';
                                }
                            }
                        }
                    }
                }
                , stroke: {
                    lineCap: 'round'
                }
                , labels: ['Asistencia']
            };
            const chart = new ApexCharts(asistenciaChartEl, options);
            chart.render();
        }

        // Lógica para poblar los modales dinámicamente
        function decodeHTML(html) {
            var txt = document.createElement("textarea");
            txt.innerHTML = html;
            let decoded = txt.value;
            // Intentar decodificar recursivamente si sigue teniendo escapes
            // Esto maneja doble escape '&lt;' -> '<'
            for(let i=0; i<3; i++) {
                 if (decoded && (decoded.includes('&lt;') || decoded.includes('&gt;') || decoded.includes('&amp;'))) {
                      txt.innerHTML = decoded;
                      decoded = txt.value;
                 } else {
                      break;
                 }
            }
            return decoded;
        }

        const responderItemModal = document.getElementById('responderItemModal');
        responderItemModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const titulo = button.getAttribute('data-item-titulo');

            document.getElementById('modal-item-titulo').textContent = titulo;
        });

        const verRespuestaModal = document.getElementById('verRespuestaModal');
        verRespuestaModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const titulo = button.getAttribute('data-item-titulo');
            const respuestaAlumno = button.getAttribute('data-respuesta-alumno');
            const feedbackMaestro = button.getAttribute('data-feedback-maestro');

            document.getElementById('modal-ver-item-titulo').textContent = titulo;
            document.getElementById('modal-ver-respuesta-alumno').innerHTML = decodeHTML(respuestaAlumno ||
                'No se encontró una respuesta de texto.');
            document.getElementById('modal-ver-feedback-maestro').innerHTML = decodeHTML(feedbackMaestro ||
                'El maestro aún no ha dejado un feedback.');
        });
    });



</script>
@endpush
