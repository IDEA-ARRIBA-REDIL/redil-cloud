{{-- Asume que 'layouts.layoutMaster' es tu layout principal --}}
@section('isEscuelasModule', true)
{{-- resources/views/maestros/horarios_asignados.blade.php --}}
@extends('layouts.layoutMaster')

@section('title', 'Gestionar Alumno') {{-- Corregí el typo "almuno" a "Alumno" --}}

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js'])
@endsection

@section('page-script')

    <script type="module">
        $(document).ready(function() {
            // Asegúrate de que el selector sea correcto para tus date pickers
            // Si el id es 'edit_corte_fecha_fin', el selector podría ser #edit_corte_fecha_fin
            // Si usas una clase 'fecha-picker' en varios inputs, está bien.
            $(".fecha-picker").flatpickr({ // Si tienes múltiples con esta clase
                dateFormat: "Y-m-d",
                disableMobile: true,
                allowInput: true // Permite escribir la fecha también
            });

            // Si es un solo elemento con ID específico:
            // $("#edit_corte_fecha_fin").flatpickr({
            //     dateFormat: "Y-m-d",
            //     disableMobile: true,
            //     allowInput: true
            // });
        });
    </script>

@endsection

@section('content')
    @include('layouts.status-msn')

    <div class="row">

        <div class="col-xl-4 col-lg-4 col-md-5 col-12 order-1 order-md-0"> {{-- Ajuste en col-md para mejor responsividad del sidebar --}}
            <div class="card mb-4"> {{-- Cambiado mb-6 a mb-4 para consistencia con Bootstrap --}}
                <div class="card-body pt-12">
                    <div class="user-avatar-section">
                        <div class=" d-flex align-items-center flex-column">
                            @if (isset($alumno) && ($alumno->foto == 'default-m.png' || $alumno->foto == 'default-f.png'))
                                <div class="avatar avatar-xl">
                                    <span class="avatar-initial rounded-circle border border-3 border-white bg-info">
                                        {{ $alumno->inicialesNombre() }} </span>
                                </div>
                            @elseif (isset($alumno))
                                <div class="avatar avatar-xl">
                                    <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $alumno->foto) : $configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $alumno->foto }}"
                                        alt="{{ $alumno->foto }}"
                                        class="avatar-initial rounded-circle border border-3 border-white bg-info">
                                    {{-- El class aquí podría ser solo rounded-circle --}}
                                </div>
                            @else
                                {{-- Fallback si $alumno no está definido, aunque debería estarlo si la vista es para un alumno --}}
                                <div class="avatar avatar-xl">
                                    <span class="avatar-initial rounded-circle border border-3 border-white bg-secondary">
                                        ? </span>
                                </div>
                            @endif
                            <div class="user-info text-center mt-3"> {{-- Añadido mt-3 para espaciado --}}
                                <h5>{{ isset($alumno) ? $alumno->nombre(3) : 'N/A' }}</h5>

                            </div>
                            <div class="user-info text-center mt-3"> {{-- Añadido mt-3 para espaciado --}}
                                <h5>Nota Actual</h5>
                                <span class="badge bg-label-primary">2.5</span>
                            </div>
                        </div>
                    </div>

                    <h5 class="pb-3 pt-4 border-bottom mb-3">Información</h5> {{-- Ajuste de padding y margin --}}
                    <div class="info-container">
                        @if (isset($alumno)) {{-- Buena práctica verificar si $alumno existe --}}
                            <ul class="list-unstyled"> {{-- Removido mb-6, los li ya tienen mb --}}
                                <li class="mb-2">
                                    <span class="fw-medium me-1">Email:</span> {{-- Usar fw-medium para el label como en el template --}}
                                    <span>{{ $alumno->email }}</span>
                                </li>
                                <li class="mb-2">
                                    <span class="fw-medium me-1">Celular:</span>
                                    <span>{{ $alumno->celular ?? 'N/A' }}</span> {{-- Asumiendo que tienes 'celular' y usando el operador null coalescing --}}
                                </li>
                                <li class="mb-2">
                                    <span class="fw-medium me-1">Sede origen:</span>
                                    <span>{{ $alumno->sede->nombre ?? 'N/A' }}</span>
                                </li>
                                <li class="mb-0"> {{-- mb-0 para el último elemento si no hay botones después directamente --}}
                                    <span class="fw-medium me-1">Líder directo:</span>
                                    @if ($alumno->encargadosDirectos()->count() > 0)
                                        @foreach ($alumno->encargadosDirectos() as $encargado)
                                            <span>{{ $encargado->nombre }}</span>
                                            @if (!$loop->last)
                                                ,
                                            @endif
                                        @endforeach
                                    @else
                                        <span>Sin encargados</span>
                                    @endif
                                </li>
                                <li> <span class="fw-medium me-1">Estado:</span>
                                    <span class="badge bg-label-primary">Activo</span>
                                </li>

                            </ul>
                            <div class="d-flex justify-content-center pt-4">
                                <a target="_blank" href="javascript:;" class="btn btn-primary me-3"
                                    data-bs-target="#editUser" {{-- me-3 o me-4 está bien --}} data-bs-toggle="modal">Actualizar</a>
                                <a href="javascript:;" class="btn btn-label-danger suspend-user">Bloquear</a>
                                <a href="{{ route('maestros.gestionarClase', ['horarioAsignado' => $horarioAsignado, 'maestro' => $maestro]) }}"
                                    class="btn ms-3 btn-outline-secondary">Atrás</a>
                            </div>
                        @else
                            <p class="text-center">Información del alumno no disponible.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-8 col-lg-8 col-md-7 col-12 order-0 order-md-1"> {{-- Ajuste en col-md y order --}}

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Reportes de asistencia</h5>

                </div>
                <div class="card-body">
                    <h6 class="mb-3 badge bg-label-primary ">Total 4 de 10 asistencias</h6>
                    <div class="table-responsive text-nowrap"> {{-- text-nowrap previene que el texto se rompa demasiado pronto --}}
                        <table class="table table-hover"> {{-- AÑADIDAS CLASES DE BOOTSTRAP --}}
                            <thead>
                                <tr> {{-- Las cabeceras <th> deben estar dentro de un <tr> --}}
                                    <th>Fecha Reporte</th>
                                    <th>Asistio</th>
                                    <th>Motivo Inasistencia</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0"> {{-- Clase del template para el estilo del tbody --}}
                                <tr>
                                    <td class="text-center">
                                        30 de Abril 2025
                                    </td>
                                    <td><span class="badge bg-label-success"> Sí </span>
                                    </td>
                                    <td><span> N/A</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">
                                        07 de mayo 2025
                                    </td>
                                    <td><span class="badge bg-label-success"> Sí </span>
                                    </td>
                                    <td><span> N/A</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">
                                        14 de mayo 2025
                                    </td>
                                    <td><span class="badge bg-label-danger"> No </span>
                                    </td>
                                    <td><span> Enfermedad </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">
                                        21 de mayo 2025
                                    </td>
                                    <td><span class="badge bg-label-success"> Sí </span>
                                    </td>
                                    <td><span> N/A </span>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div> {{-- Fin de table-responsive --}}

                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Valoración final maestro</h5>

                </div>
                <div class="card-body">
                    <textarea style="width:100%" type="text-area" row="10">Felicidades por aprobar existosamente</textarea>
                </div>

            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 order-0 order-md-1">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Items para Calificación</h5>

                </div>
                <div class="card-body"> {{-- Removido pt-12, el header ya da espacio --}}
                    {{-- ENVOLVEMOS LA TABLA PARA HACERLA RESPONSIVE --}}
                    <div class="table-responsive text-nowrap"> {{-- text-nowrap previene que el texto se rompa demasiado pronto --}}
                        <table class="table table-hover"> {{-- AÑADIDAS CLASES DE BOOTSTRAP --}}
                            <thead>
                                <tr> {{-- Las cabeceras <th> deben estar dentro de un <tr> --}}
                                    <th class="text-center">Nombre</th>
                                    <th class="text-center">Porcentaje</th>
                                    <th class="text-center">Corte</th>
                                    <th class="text-center">Nota</th>
                                    <th class="text-center">Entregable</th>
                                    <th class="text-center">Calificar</th> {{-- Combinar acciones en una columna --}}
                                    <th class="text-center">Fecha Límite Entregable</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0"> {{-- Clase del template para el estilo del tbody --}}
                                {{-- EJEMPLO DE FILA - DEBERÍAS ITERAR SOBRE TUS DATOS AQUÍ --}}
                                @php
                                    // Datos de ejemplo para visualizar, reemplaza con tus datos reales
                                    $itemsCalificacion = [
                                        [
                                            'nombre' => 'CLASE 1 - Tarea',
                                            'porcentaje' => '30%',
                                            'fecha_limite' => now()->addDays(5)->format('Y-m-d'),
                                            'nota' => null,
                                            'corte' => '1',
                                        ],
                                        [
                                            'nombre' => 'CLASE 2 - Examen',
                                            'porcentaje' => '10%',
                                            'fecha_limite' => now()->addDays(12)->format('Y-m-d'),
                                            'nota' => 4.5,
                                            'corte' => '1',
                                        ],
                                        [
                                            'nombre' => 'CLASE 3 - Proyecto',
                                            'porcentaje' => '10%',
                                            'fecha_limite' => now()->addDays(20)->format('Y-m-d'),
                                            'nota' => null,
                                            'corte' => '2',
                                        ],
                                        [
                                            'nombre' => 'CLASE 4 - Expo',
                                            'porcentaje' => '20%',
                                            'fecha_limite' => now()->addDays(20)->format('Y-m-d'),
                                            'nota' => null,
                                            'corte' => '2',
                                        ],
                                        [
                                            'nombre' => 'CLASE 5 - Examen',
                                            'porcentaje' => '30%',
                                            'fecha_limite' => now()->addDays(20)->format('Y-m-d'),
                                            'nota' => null,
                                            'corte' => '3',
                                        ],
                                    ];
                                @endphp

                                @forelse ($itemsCalificacion as $index => $item)
                                    {{-- Reemplaza $itemsCalificacion con tu variable real --}}
                                    <tr>
                                        <td>
                                            <strong>{{ $item['nombre'] }}</strong> {{-- Nombre en negrita --}}
                                        </td>
                                        <td>
                                            <span class="badge bg-label-primary">{{ $item['porcentaje'] }}</span>
                                            {{-- Porcentaje como badge --}}
                                        </td>
                                        <td>
                                            {{ $item['corte'] }}
                                        </td>

                                        <td>
                                            <input type="number" class="form-control form-control-sm"
                                                {{-- form-control-sm para input más pequeño --}} min="0" max="5" step="0.1"
                                                style="width: 80px;" {{-- Ancho fijo pequeño para la nota --}} value="{{ $item['nota'] }}"
                                                placeholder="0.0">
                                        </td>
                                        <td>
                                            <button
                                                class="btn btn-sm  btn-outline-secondary rounded-pill waves-effect waves-light"><i
                                                    class="mdi mdi-upload-outline"></i>Ver Entregable</button>
                                        </td>
                                        <td class="text-center">

                                            <button class="btn btn-sm  btn-primary rounded-pill waves-effect waves-light"><i
                                                    class="mdi mdi-pencil-outline"></i>Calificar</button>

                                        </td>
                                        <td>
                                            {{-- Aquí el input de fecha. El label no es necesario DENTRO del td si el th es claro --}}
                                            <input type="text" class="form-control fecha-picker"
                                                id="fecha_limite_{{ $index }}" {{-- ID único para cada picker --}}
                                                placeholder="YYYY-MM-DD" value="{{ $item['fecha_limite'] }}">
                                            {{-- Si es solo para mostrar y no editar: {{ \Carbon\Carbon::parse($item['fecha_limite'])->format('d/m/Y') }} --}}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No hay items de calificación definidos.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div> {{-- Fin de table-responsive --}}
                </div>
            </div>

        </div>
    </div>

    @php
        // --- DATOS DE EJEMPLO ---
        // Esto vendría de tu controlador, pasado a la vista
        $historialEducativo = [
            [
                'escuela_nombre' => 'Escuela de Liderazgo',
                'escuela_id_nav' => 'liderazgo', // ID para la navegación de pestañas
                'materias' => [
                    [
                        'nombre' => 'Liderazgo 101: Fundamentos',
                        'aprobada' => true,
                        'fecha_aprobacion' => '2023-06-15',
                        'nota_final' => 4.5,
                        'periodo' => 'Semestre 2023-I',
                        'docente' => 'Dr. Carlos Solis',
                        'creditos' => 3,
                        'comentarios' => 'Excelente desempeño en el proyecto final.',
                        'icono' => 'mdi-account-star-outline', // Icono de ejemplo
                        'color_badge' => 'success',
                    ],
                    [
                        'nombre' => 'Comunicación Estratégica',
                        'aprobada' => false,
                        'fecha_aprobacion' => '2023-06-20',
                        'nota_final' => null,
                        'periodo' => 'Semestre 2023-I',
                        'docente' => 'Lic. Ana Torres',
                        'creditos' => 2,
                        'comentarios' => null,
                        'icono' => 'mdi-bullhorn-outline',
                        'color_badge' => 'success',
                    ],
                    [
                        'nombre' => 'Liderazgo Avanzado 201',
                        'aprobada' => false,
                        'fecha_aprobacion' => null,
                        'nota_final' => 0, // O la nota del último intento si no aprobó
                        'periodo' => 'Semestre 2023-II (En Curso)',
                        'docente' => 'Dr. Carlos Solis',
                        'creditos' => 3,
                        'comentarios' => 'Pendiente evaluación final.',
                        'icono' => 'mdi-account-supervisor-outline',
                        'color_badge' => 'warning', // o 'info' si está en curso
                    ],
                ],
            ],
            [
                'escuela_nombre' => 'Escuela de Teología',
                'escuela_id_nav' => 'teologia',
                'materias' => [
                    [
                        'nombre' => 'Antiguo Testamento I',
                        'aprobada' => true,
                        'fecha_aprobacion' => '2024-01-25',
                        'nota_final' => 4.8,
                        'periodo' => 'Trimestre 2023-IV',
                        'docente' => 'PhD. Elena Reyes',
                        'creditos' => 4,
                        'comentarios' => 'Participación destacada.',
                        'icono' => 'mdi-book-open-page-variant-outline',
                        'color_badge' => 'success',
                    ],
                    [
                        'nombre' => 'Nuevo Testamento I',
                        'aprobada' => true,
                        'fecha_aprobacion' => '2024-01-30',
                        'nota_final' => 4.6,
                        'periodo' => 'Trimestre 2023-IV',
                        'docente' => 'Dr. Marco Peña',
                        'creditos' => 4,
                        'comentarios' => null,
                        'icono' => 'mdi-book-open-outline',
                        'color_badge' => 'success',
                    ],
                ],
            ],
            [
                'escuela_nombre' => 'Talleres Complementarios',
                'escuela_id_nav' => 'talleres',
                'materias' => [
                    [
                        'nombre' => 'Taller de Oratoria',
                        'aprobada' => true,
                        'fecha_aprobacion' => '2023-03-10',
                        'nota_final' => 5.0, // A veces los talleres son solo aprobado/reprobado
                        'periodo' => 'Marzo 2023',
                        'docente' => 'Coach Laura Vidal',
                        'creditos' => 1,
                        'comentarios' => 'Finalizado con éxito.',
                        'icono' => 'mdi-microphone-variant',
                        'color_badge' => 'success',
                    ],
                ],
            ],
        ];
    @endphp

    <div class="row">
        <div class="col-12 order-0 order-md-1">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Historial Educativo</h5>
                    {{-- Podrías añadir un botón para descargar un certificado o similar --}}
                    {{-- <a href="#" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-download-outline me-1"></i> Descargar Resumen</a> --}}
                </div>
                <div class="card-body">
                    @if (!empty($historialEducativo))
                        {{-- PESTAÑAS DE NAVEGACIÓN --}}
                        <ul class="nav nav-pills mb-3 flex-column flex-md-row" id="pills-historial-tab" role="tablist">
                            @foreach ($historialEducativo as $index => $escuela)
                                <li class="nav-item border rounded" role="presentation">
                                    <button class="nav-link @if ($loop->first) active @endif"
                                        id="pills-{{ $escuela['escuela_id_nav'] }}-tab" data-bs-toggle="pill"
                                        data-bs-target="#pills-{{ $escuela['escuela_id_nav'] }}" type="button"
                                        role="tab" aria-controls="pills-{{ $escuela['escuela_id_nav'] }}"
                                        aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                        {{ $escuela['escuela_nombre'] }}

                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        {{-- CONTENIDO DE LAS PESTAÑAS --}}
                        <div class="tab-content" id="pills-tabContent">
                            @foreach ($historialEducativo as $index => $escuela)
                                <div class="tab-pane fade @if ($loop->first) show active @endif"
                                    id="pills-{{ $escuela['escuela_id_nav'] }}" role="tabpanel"
                                    aria-labelledby="pills-{{ $escuela['escuela_id_nav'] }}-tab">

                                    <h6 class="text-muted mb-3">Materias de {{ $escuela['escuela_nombre'] }}:</h6>

                                    @if (!empty($escuela['materias']))
                                        <div class="row g-3"> {{-- g-3 para espaciado entre cards --}}
                                            @foreach ($escuela['materias'] as $materia)
                                                <div class="col-md-6 col-lg-4 "> {{-- Ajusta columnas para responsividad --}}
                                                    <div class="card h-100">
                                                        <div class="card-body border rounded m-3">
                                                            <div
                                                                class="d-flex justify-content-between align-items-start mb-2">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="avatar me-2">
                                                                        <span
                                                                            class="avatar-initial rounded bg-label-{{ $materia['aprobada'] ? $materia['color_badge'] : ($materia['nota_final'] === null ? 'secondary' : 'danger') }}">
                                                                            <i class="ti ti-circle-dashed-check"></i>
                                                                        </span>
                                                                    </div>
                                                                    <h6 class="card-title mb-0 text-truncate"
                                                                        style="max-width: 200px;"
                                                                        title="{{ $materia['nombre'] }}">
                                                                        {{ $materia['nombre'] }}
                                                                    </h6>
                                                                </div>
                                                                @if ($materia['aprobada'])
                                                                    <span
                                                                        class="badge bg-light-success text-success">Aprobada</span>
                                                                @elseif ($materia['nota_final'] === null && $materia['fecha_aprobacion'] === null)
                                                                    <span class="badge bg-light-info text-alert">En
                                                                        Curso</span>
                                                                @else
                                                                    <span class="badge bg-light-danger text-danger">No
                                                                        Aprobada</span>
                                                                @endif
                                                            </div>

                                                            <ul class="list-unstyled mb-3">
                                                                <li class="mb-1 d-flex align-items-center">
                                                                    <i
                                                                        class="mdi mdi-calendar-check-outline mdi-18px me-2 text-muted"></i>
                                                                    <small><strong>Nota Final:</strong>
                                                                        {{ $materia['nota_final'] ?? 'N/A' }}</small>
                                                                </li>
                                                                @if ($materia['fecha_aprobacion'])
                                                                    <li class="mb-1 d-flex align-items-center">
                                                                        <i
                                                                            class="mdi mdi-check-decagram-outline mdi-18px me-2 text-muted"></i>
                                                                        <small><strong>Aprobación:</strong>
                                                                            {{ \Carbon\Carbon::parse($materia['fecha_aprobacion'])->isoFormat('LL') }}</small>
                                                                    </li>
                                                                @endif
                                                                <li class="mb-1 d-flex align-items-center">
                                                                    <i
                                                                        class="mdi mdi-timelapse mdi-18px me-2 text-muted"></i>
                                                                    <small><strong>Periodo:</strong>
                                                                        {{ $materia['periodo'] ?? 'N/A' }}</small>
                                                                </li>
                                                                <li class="mb-1 d-flex align-items-center">
                                                                    <i
                                                                        class="mdi mdi-account-tie-outline mdi-18px me-2 text-muted"></i>
                                                                    <small><strong>Docente:</strong>
                                                                        {{ $materia['docente'] ?? 'N/A' }}</small>
                                                                </li>
                                                                <li class="mb-0 d-flex align-items-center">
                                                                    <i
                                                                        class="mdi mdi-star-circle-outline mdi-18px me-2 text-muted"></i>
                                                                    <small><strong>Créditos:</strong>
                                                                        {{ $materia['creditos'] ?? 'N/A' }}</small>
                                                                </li>
                                                            </ul>

                                                            @if ($materia['comentarios'])
                                                                <p class="card-text text-muted border-top pt-2 mb-2">
                                                                    <small><em>{{ $materia['comentarios'] }}</em></small>
                                                                </p>
                                                            @endif

                                                            {{-- Botón para más detalles si fuera necesario --}}
                                                            {{-- <a href="#" class="btn btn-sm btn-outline-primary w-100">Ver Detalles</a> --}}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="alert alert-warning text-center" role="alert">
                                            No hay materias registradas para esta escuela.
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info text-center" role="alert">
                            No hay historial educativo disponible para mostrar.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- NOTA: Asegúrate de tener los iconos MDI (Material Design Icons) --}}
    {{-- Si tu template (Sneat/Materio) los incluye, perfecto. Sino, necesitarías añadirlos. --}}
    {{-- Ejemplo de CDN para MDI (si no está en tu proyecto):
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@latest/css/materialdesignicons.min.css">
--}}

    {{-- Para el formateo de fechas como 'isoFormat('LL')' necesitas Carbon en el backend
    y asegurarte de que el locale de Carbon esté configurado a español si quieres
    nombres de meses en español (ej. en AppServiceProvider -> Carbon::setLocale(config('app.locale')); )
--}}
@endsection
