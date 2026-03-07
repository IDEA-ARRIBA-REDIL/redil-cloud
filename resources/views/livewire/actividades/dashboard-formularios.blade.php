<div>
    @if ($compradores->isEmpty())
    <div class="alert alert-warning" role="alert">
        <i class="mdi mdi-information-outline me-2"></i>
        Aún no hay participantes con registros en esta actividad.
    </div>
    @else
    <div class="d-flex justify-content-end mb-3 gap-2">
        <button wire:click="notificarPendientes" class="btn rounded-pill btn-outline-primary waves-effect" wire:loading.attr="disabled">
            <i class="ti ti-mail pe-2"></i>
            Notificar actualización
        </button>
        <button wire:click="exportarExcel" class="btn rounded-pill btn-outline-secondary waves-effect">
            <i class="ti ti-file-type-xls pe-2"></i>
            Exportar a excel
        </button>
    </div>
    <div class="accordion" id="dashboardFormulariosAccordion">
        @foreach ($compradores as $comprador)
        @php
        // Obtenemos la inscripción y las respuestas del comprador desde los mapas.
        $inscripcion = $mapaInscripciones[$comprador->id] ?? null;
        $respuestasDelComprador = $mapaRespuestas[$comprador->id] ?? [];
        @endphp
        <div class="accordion-item">
            <h2 class="accordion-header " id="heading-{{ $comprador->id }}">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $comprador->id }}" aria-expanded="false" aria-controls="collapse-{{ $comprador->id }}">
                    <i class="mdi mdi-account-circle-outline me-2"></i>
                     <p class="m-0"><b>Nombre:</b> <span class="text-uppercase">{{ $comprador->nombre_completo_comprador }}</span>  | <b>Contacto:</b> {{ $comprador->telefono_comprador ?? 'No indicado' }}</p>
                </button>
            </h2>
            <div id="collapse-{{ $comprador->id }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ $comprador->id }}" data-bs-parent="#dashboardFormulariosAccordion">
                <div class="accordion-body">

                    {{-- ===================== INICIO DEL CAMBIO ===================== --}}

                    @if ($inscripcion)
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                        {{-- Estado actual y botón de ELIMINAR REGISTRO --}}
                        <div class="d-flex align-items-center gap-3">
                            <div class="fw-semibold">
                                Estado actual:
                                @switch($inscripcion->estado)
                                @case(1)
                                <span class="badge bg-label-secondary rounded-pill">Iniciada</span>
                                @break
                                @case(2)
                                <span class="badge bg-label-warning rounded-pill">Pendiente</span>
                                @break
                                @case(3)
                                <span class="badge bg-label-primary rounded-pill">Finalizada</span>
                                @break
                                @default
                                <span class="badge bg-label-secondary ">Desconocido</span>
                                @endswitch
                            </div>

                           
                        </div>

                        {{-- Lógica de botones de aprobación --}}
                        @if($actividad->tiene_invitados)
                        <div>
                            @if ($inscripcion->estado == 3)
                            {{-- Botón para REVERTIR/DESAPROBAR --}}
                            <button wire:click="desaprobarInscripcion({{ $inscripcion->id }})" class="btn btn-sm btn-outline-warning" wire:loading.attr="disabled">
                                <i class="ti ti-logout me-1"></i>
                                Revertir aprobación
                            </button>
                            @else
                            {{-- Botón para APROBAR --}}
                            <button wire:click="aprobarInscripcion({{ $inscripcion->id }})" class="btn btn-primary btn-sm rounded-pill shadow-sm" wire:loading.attr="disabled">
                                <i class="mdi mdi-check-circle-outline me-1"></i>
                                Aprobar inscripción
                            </button>
                            @endif
                             <button type="button" 
                                    onclick="confirmarEliminacion({{ $comprador->id }})"
                                    class="btn btn-sm ms-3 btn-outline-danger waves-effect" 
                                    wire:loading.attr="disabled">
                                <i class="ti ti-trash me-1"></i>
                                Eliminar registro
                            </button>
                        </div>
                        @endif
                    </div>
                    @endif




                    {{-- ====================== FIN DEL CAMBIO ====================== --}}

                    {{-- Listado de preguntas y respuestas (sin cambios en la lógica) --}}
                    <div class="row g-4">
                        {{-- INICIO DEL CÓDIGO AÑADIDO: Input para invitados --}}
                        {{-- Input para invitados (ahora siempre visible si la actividad lo permite) --}}
                        @if ($actividad->tiene_invitados)
                         {{-- ===================== INICIO DEL CÓDIGO AÑADIDO ===================== --}}
                         @php
                        // ===================== INICIO DE LA MODIFICACIÓN =====================
                        // Ahora obtenemos el dato directamente de la inscripción. ¡Mucho más simple!
                        $invitadosSolicitados = $inscripcion->limite_invitados ?? 0;
                        // ====================== FIN DE LA MODIFICACIÓN ======================
                         @endphp

                    <div class="alert alert-info d-flex align-items-center" role="alert">
                       
                          <i class="ti ti-users-group"></i>
                        <div>
                            Invitados solicitados por el usuario: <strong class="fs-5">{{ $invitadosSolicitados }}</strong>
                        </div>
                    </div>
                    {{-- ====================== FIN DEL CÓDIGO AÑADIDO ====================== --}}

                        <div class="mt-3">
                            <label for="invitados-{{$inscripcion->id}}" class="form-label">Cupos para invitados aprobados:</label>
                            <input type="number" class="form-control form-control-sm" style="width: 100px;" id="invitados-{{$inscripcion->id}}" wire:model="cantidadInvitadosAprobados.{{ $inscripcion->id }}" min="0" placeholder="0">
                            {{-- Nota: El valor se carga desde el método render del componente --}}
                        </div>
                        @endif
                        {{-- FIN DEL CÓDIGO AÑADIDO --}}

                        {{-- INICIO DEL CÓDIGO AÑADIDO --}}
                        @if($inscripcion->categoriaActividad)
                        @php
                        $aforoDisponible = $inscripcion->categoriaActividad->aforo - $inscripcion->categoriaActividad->aforo_ocupado;
                        @endphp

                        <div class="p-3 d-flex mb-3" style="color:black; font-size:14px;border: solid 2px #95CDDF;border-radius: 14px;">
                            <i class="ti ti-bulb text-secondary me-2"></i>
                            <p class="m-0 text-start">Límite sugerido para esta categoría:<strong> {{ $inscripcion->categoriaActividad->limite_invitados ?? 'N/A' }} </strong>
                                Cupos totales aún disponibles en la categoría: <strong>{{ $aforoDisponible }}</strong>. </p>
                        </div>

                        @endif
                        {{-- FIN DEL CÓDIGO AÑADIDO --}}

                        @foreach ($elementosFormulario as $elemento)
                        {{-- 2. Cada elemento ahora es una columna responsiva --}}

                        <div class="col-12 col-md-6">
                            {{-- 3. Este div con h-100 asegura que todas las "tarjetas" en una fila tengan la misma altura --}}
                            <div style="border-bottom:solid 2px #E3E5E8" class="d-flex flex-column  p-3 h-100">
                                <p class="fw-semibold mb-1 text-black">{{ $elemento->titulo }}</p>
                                @php
                                $respuesta = $respuestasDelComprador[$elemento->id] ?? null;
                                @endphp
                                {{-- La clase flex-grow-1 hace que esta sección ocupe el espacio sobrante, empujando el borde al fondo --}}
                                <div class="ps-2 flex-grow-1">

                                    {!! $this->getValorRespuesta($respuesta) !!}
                                </div>
                            </div>
                        </div>
                        @endforeach


                        {{-- ===================== INICIO DEL CÓDIGO AÑADIDO ===================== --}}

                        @if ($inscripcion)
                        @php
                        // Buscamos los invitados para esta inscripción principal en el mapa
                        $invitados = $mapaInvitados->get($inscripcion->id, collect());
                        @endphp

                        @if ($invitados->isNotEmpty())
                        <div class="mt-4 border-top pt-3">
                            <h6 class="fw-semibold">Invitados registrados ({{ $invitados->count() }})</h6>
                            <div class="row g-3">
                                @foreach ($invitados as $invitado)
                                <div class="col-12 col-md-6">
                                    <div class="d-flex align-items-center bg-light p-2 rounded">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded-circle bg-label-secondary">
                                                <i class="ti ti-user ti-sm"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $invitado->nombre_inscrito }}</div>
                                            <small class="text-muted">{{ $invitado->email }}</small>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        @endif
                        {{-- ====================== FIN DEL CÓDIGO AÑADIDO ====================== --}}

                    </div>

                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
    @push('scripts')
    <script>
        // Nos aseguramos de que el código se ejecute después de que Livewire se haya inicializado.
        document.addEventListener('livewire:initialized', () => {

            // Escuchar mensajes (success/error)
            Livewire.on('msn', (data) => {
                Swal.fire({
                    title: data[0].msnTitulo,
                    html: data[0].msnTexto,
                    icon: data[0].msnIcono,
                    customClass: { confirmButton: 'btn btn-primary rounded-pill' },
                    buttonsStyling: false
                });
            });

            // Función global para confirmación
            window.confirmarEliminacion = (id) => {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: 'Se eliminará la compra y liberarán los cupos.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    customClass: { confirmButton: 'btn btn-primary rounded-pill me-2', cancelButton: 'btn btn-outline-secondary rounded-pill' },
                    buttonsStyling: false
                }).then((r) => { if(r.isConfirmed) @this.eliminarCompra(id); });
            }
        });

    </script>
    @endpush
</div>
