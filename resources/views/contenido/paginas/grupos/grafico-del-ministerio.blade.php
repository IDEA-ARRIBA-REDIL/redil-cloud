the@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Gráfico del ministerio')

<!-- Page -->

@section('vendor-style')

    <style>
        #graph-container {
            width: 100%;
            height: 500px;
            border: 1px solid lightgray;
        }

        .vis-tooltip {
            position: absolute;
            background-color: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 5px 10px;
            font-size: 14px;
            border-radius: 4px;
            z-index: 1000;
        }
    </style>
    @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vis/4.21.0/vis-network.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
@endsection

@section('page-script')

    <script>
        // Parsear los datos JSON
        const nodos = JSON.parse(<?php print json_encode(json_encode($nodos)); ?>);
        const aristas = JSON.parse(<?php print json_encode(json_encode($aristas)); ?>);

        var nodes = null;
        var edges = null;
        var network = null;
        var directionInput = document.getElementById("direction");
        var container = document.getElementById("graph-container");

        function destroy() {
            if (network !== null) {
                network.destroy();
                network = null;
            }
        }

        function draw() {
            destroy();
            nodes = JSON.parse(<?php print json_encode(json_encode($nodos)); ?>);
            edges = JSON.parse(<?php print json_encode(json_encode($aristas)); ?>);
            var connectionCount = [];


            // create a network
            var data = {
                nodes: nodes,
                edges: edges,
            };

            var options = {
                interaction: {
                    hover: true,
                    tooltipDelay: 0
                }, // Muestra el tooltip inmediatamente
                manipulation: {
                    enabled: true,
                },
                edges: {
                    smooth: {
                        type: "cubicBezier",
                        forceDirection: directionInput.value == "UD" || directionInput.value == "DU" ?
                            "vertical" : "horizontal",
                        roundness: 0.4,
                    },
                },
                layout: {
                    hierarchical: {
                        direction: directionInput.value,
                    },
                },
                nodes: {
                    size: 80,
                    title: undefined, // Esto asegura que se use el título definido en cada nodo
                    // tooltipDelay: 0,
                    widthConstraint: false,
                    // Muestra el tooltip inmediatamente
                },
                physics: false,
            };
            network = new vis.Network(container, data, options);


            // add event listeners
            network.on("select", function(params) {
                document.getElementById("selection").innerText =
                    "Selection: " + params.nodes;
            });

            //funcion click
            network.on("click", function(params) {
                var nodeId = this.getNodeAt(params.pointer.DOM);
                if (nodeId != null) {
                    window.top.location.href = '/grupo/grafico-del-ministerio/' + nodeId;
                }

                // window.top.location.href = '/grupo/grafico-del-ministerio/'+nodeId;
            });

            network.on("showPopup", function(params) {
                //document.getElementById("eventSpanHeading").innerText = "showPopup event: ";
                /*document.getElementById("eventSpanContent").innerText = JSON.stringify(
                  params,
                  null,
                  4
                );*/
            });

            network.on("hidePopup", function() {
                console.log("hidePopup Event");
            });
            network.on("blurNode", function(params) {
                console.log("blurNode Event:", params);
            });

        }

        // Función para descargar el gráfico como PNG
        function downloadImage() {
            html2canvas(container).then(function(canvas) {
                var link = document.createElement('a');
                link.href = canvas.toDataURL('image/png');
                link.download = 'grafico.png';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        }

        var directionInput = document.getElementById("direction");
        var btnUD = document.getElementById("btn-UD");
        btnUD.onclick = function() {
            directionInput.value = "UD";
            draw();
        };
        var btnDU = document.getElementById("btn-DU");
        btnDU.onclick = function() {
            directionInput.value = "DU";
            draw();
        };
        var btnLR = document.getElementById("btn-LR");
        btnLR.onclick = function() {
            directionInput.value = "LR";
            draw();
        };
        var btnRL = document.getElementById("btn-RL");
        btnRL.onclick = function() {
            directionInput.value = "RL";
            draw();
        };

        window.addEventListener("load", () => {
            draw();
        });
    </script>
    <script type="text/javascript">
        $('.cargando').click(function() {
            Swal.fire({
                title: "Espera un momento",
                text: "Esto puede tardar un momento...",
                icon: "info",
                showCancelButton: false,
                showConfirmButton: false,
                showDenyButton: false
            });
        });
    </script>

@endsection

@section('content')

    <h4 class="mb-1 fw-semibold text-primary">Gráfico de tu ministerio</h4>
    <p class="mb-4 text-black">Este gráfico te permitirá visualizar un árbol con la estructura jerárquica de tu ministerio
    </p>


    @include('layouts.status-msn')

    <div class="row m-1" style="height: 800px">
        <div class="card h-100 w-100 bg-white">
            <div class="card-header align-items-center">

                @if ($tipoDeNodo == 'U-principal')
                    <p class="card-text mb-1 mt-3">
                        <b>Ministerio general</b>
                    </p>
                @endif

                @if ($maximos_niveles != 20)
                    <p class="card-text mb-1">Actualmente, solo se están visualizando algunos niveles. Si deseas ver el árbol
                        completo, da clic en el botón.</p>
                @else
                    <p class="card-text mb-1">Gráfico del árbol completo.</p>
                @endif
                <div class="">
                    @if ($maximos_niveles != 20)
                        <a href="{{ route('grupo.graficoDelMinisterio', ['U-logueado', 20]) }}" type="button"
                            class="cargando my-1 btn btn-xs btn-outline-primary waves-effect">
                            <span class="ti-xs ti ti-sitemap me-2"></span>Ver árbol completo
                        </a>
                    @endif
                    @if ($tipoDeNodo != 'U-principal')
                        <button type="button" class="my-1 btn btn-xs btn-outline-primary waves-effect"
                            data-bs-toggle="modal" data-bs-target="#modalCambiarIndice">
                            <span class="ti-xs ti ti-transform me-2"></span>Cambiar índice
                        </button>
                    @endif

                    <button type="button" class="my-1 btn btn-xs btn-outline-primary waves-effect" data-bs-toggle="modal"
                        data-bs-target="#modalPersonasNoGraficadas">
                        <span class="ti-xs ti ti-pencil-off me-2"></span>Personas no gráficadas
                    </button>


                    <!--/ estos botones son para cambiar la direccion del grafico por defecto siempre arrancara UP TO DOWN -->
                    <button class="my-1 btn btn-xs btn-outline-primary waves-effect" type="button" id="btn-UD"
                        value="Up-Down"><span class="ti-xs ti ti-arrow-down-rhombus me-2"></span>De arriba hacia
                        abajo</button>
                    <button class="my-1 btn btn-xs btn-outline-primary waves-effect" type="button" id="btn-DU"
                        value="Down-Up"><span class="ti-xs ti ti-arrow-up-rhombus me-2"></span> De abajo hacia
                        arriba</button>
                    <button class="my-1 btn btn-xs btn-outline-primary waves-effect" type="button" id="btn-LR"
                        value="Left-Right"> <span class="ti-xs ti ti-arrow-right-rhombus me-2"></span> De izquierda a
                        derecha </button>
                    <button class="my-1 btn btn-xs btn-outline-primary waves-effect" type="button" id="btn-RL"
                        value="Right-Left"><span class="ti-xs ti ti-arrow-left-rhombus me-2"></span>De derecha a izquierda
                    </button>
                    <button class="my-1 btn btn-xs btn-outline-primary waves-effect" type="button"
                        onclick="downloadImage()"><span class="ti-xs ti ti-download me-2"></span>Descargar gráfico
                        .PNG</button>
                    <button style="display:none !important" class="my-1 btn btn-xs btn-outline-primary waves-effect"
                        type="button" id="direction" value="LR"> </button>
                    <p id="selection" class="hidden"></p>
                </div>

                @if ($tipoDeNodo != 'U-principal')
                    <p class="card-text mb-1 mt-3"> <b>Ministerio del {{ $tipoDeNodo == 'G-grupo' ? 'grupo' : '' }}: </b>
                    </p>
                @endif

                @if ($tipoDeNodo == 'U-encargado' || $tipoDeNodo == 'A-encargado')
                    <ul class="list-unstyled mb-0">
                        <li class="mb-1 mt-1 p-2 border rounded">
                            <div class="d-flex align-items-start">
                                <div class="d-flex align-items-start">
                                    <div class="avatar me-2 my-auto">
                                        <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $usuario_seleccionado->foto) : $configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $usuario_seleccionado->foto }}"
                                            alt="Avatar" class="rounded-circle" />
                                    </div>
                                    <div class="me-2 ms-1">
                                        <h6 class="mb-0">{{ $usuario_seleccionado->nombre(3) }}</h6>
                                        <small class="text-muted"><i
                                                class="ti {{ $usuario_seleccionado->tipoUsuario->icono }} text-heading fs-6"></i>
                                            {{ $usuario_seleccionado->tipoUsuario->nombre }}</small><br>
                                        <small class="text-muted"><b>Índice en el gráfico:</b>
                                            {{ $usuario_seleccionado->indice_grafico_ministerial }}</small>
                                    </div>
                                </div>

                                <div class="ms-auto pt-1 my-auto ">
                                    @if ($rolActivo->hasPermissionTo('personas.lista_asistentes_todos'))
                                        <a href="{{ route('usuario.perfil', $usuario_seleccionado) }}" target="_blank"
                                            class="text-body" data-bs-toggle="tooltip" aria-label="Ver perfil"
                                            data-bs-original-title="Ver perfil">
                                            <i class="ti ti-user-check me-2 ti-sm"></i></a>
                                        </a>
                                    @endif
                                </div>

                            </div>
                        </li>
                    </ul>
                @endif

                @if ($tipoDeNodo == 'G-grupo')
                    <ul class="list-unstyled mb-0">
                        <li class="mb-1 mt-1 p-2 border rounded">
                            <div class="d-flex align-items-start">
                                <div class="d-flex align-items-start">
                                    <div class="avatar me-2 my-auto">
                                        <i class="ti ti-users-group me-2 fs-1"></i>
                                    </div>
                                    <div class="me-2 ms-1">
                                        <h6 class="mb-0">{{ $grupo_seleccionado->nombre }}</h6>
                                        <small class="text-muted"><b>Tipo:</b>
                                            {{ $grupo_seleccionado->tipoGrupo->nombre }}</small><br>
                                        <small class="text-muted"><b>Índice en el gráfico:</b>
                                            {{ $grupo_seleccionado->indice_grafico_ministerial }}</small>
                                    </div>
                                </div>

                                <div class="ms-auto pt-1 my-auto">
                                    @if ($rolActivo->hasPermissionTo('grupos.lista_grupos_todos'))
                                        <a href="{{ route('grupo.perfil', $grupo_seleccionado) }}" target="_blank"
                                            class="text-body" data-bs-toggle="tooltip" aria-label="Ver perfil"
                                            data-bs-original-title="Ver perfil del grupo">
                                            <i class="ti ti-id me-2 ti-sm"></i></a>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </li>
                    </ul>
                @endif

            </div>

            <div id="graph-container" class="card-body bg-gray"></div>
        </div>
    </div>

    <!-- modal Personas No Graficadas -->
    <div class="modal fade" id="modalPersonasNoGraficadas" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-add-new-role">
            <div class="modal-content p-3 p-md-5">
                <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h3 class="role-title mb-2"><i class="ti ti-pencil-off ti-lg"></i> Pesonas no gráficadas</h3>
                        <p class="text-muted">A continuación presentamos las personas que se repiten, y por tanto no pueden
                            ser dibujados en esta área del gráfico</p>
                    </div>

                    <div class="table-responsive text-nowrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Personas</th>
                                    <th>Grupos a los que pertenece</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @foreach ($usuarios_no_dibujados as $usuariNoDibujado)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-start">
                                                <div class="avatar me-2 my-auto">
                                                    <img src="{{ $configuracion->version == 1 ? Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $usuariNoDibujado->foto) : $configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $usuariNoDibujado->foto }}"
                                                        alt="Avatar" class="rounded-circle" />
                                                </div>
                                                <div class="me-2 ms-1">
                                                    <h6 class="mb-0">{{ $usuariNoDibujado->nombre(3) }}</h6>
                                                    <small class="text-muted"><i
                                                            class="ti {{ $usuariNoDibujado->tipoUsuario->icono }} text-heading fs-6"></i>
                                                        {{ $usuariNoDibujado->tipoUsuario->nombre }}</small><br>

                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-0">
                                            <ul class="list-unstyled mb-0">
                                                @foreach ($usuariNoDibujado->gruposDondeAsiste as $grupoDondeAsiste)
                                                    <li class="mt-1 p-1  rounded">
                                                        <div class="d-flex align-items-start">
                                                            <div class="d-flex align-items-start">
                                                                <div class="avatar me-2 my-auto">
                                                                    <i class="ti ti-users-group me-2 fs-1"></i>
                                                                </div>
                                                                <div class="me-2 ms-1">
                                                                    <h6 class="mb-0">{{ $grupoDondeAsiste->nombre }}
                                                                    </h6>
                                                                    <small class="text-muted"><b>Tipo:</b>
                                                                        {{ $grupoDondeAsiste->tipoGrupo->nombre }}</small><br>
                                                                </div>
                                                            </div>

                                                            <div class="ms-auto pt-1 my-auto">
                                                                @if ($rolActivo->hasPermissionTo('grupos.lista_grupos_todos'))
                                                                    <a href="{{ route('grupo.perfil', $grupoDondeAsiste) }}"
                                                                        target="_blank" class="text-body"
                                                                        data-bs-toggle="tooltip" aria-label="Ver perfil"
                                                                        data-bs-original-title="Ver perfil del grupo">
                                                                        <i class="ti ti-id me-2 ti-sm"></i></a>
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--/ modal Personas No Graficadas -->

    <!-- modal cambiar indice -->
    @if ($tipoDeNodo != 'U-principal')
        <div class="modal fade" id="modalCambiarIndice" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-add-new-role">
                <div class="modal-content p-3 p-md-5">
                    <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            @if ($usuario_seleccionado)
                                <h3 class="role-title mb-2"><i class="ti ti-transform ti-lg"></i> Cambiar índice de
                                    {{ $usuario_seleccionado->nombre(3) }}</h3>
                            @elseif($grupo_seleccionado)
                                <h3 class="role-title mb-2"><i class="ti ti-transform ti-lg"></i> Cambiar índice de
                                    {{ $grupo_seleccionado->nombre }} </h3>
                            @endif
                            <p class="text-muted">A continuación presentamos las personas que se repiten, y por tanto no
                                pueden ser dibujados en esta área del gráfico</p>
                        </div>
                        <form method="POST"
                            action="{{ $usuario_seleccionado ? route('grupo.cambiarIndice', ['usuario', $usuario_seleccionado->id]) : ($grupo_seleccionado ? route('grupo.cambiarIndice', ['grupo', $grupo_seleccionado->id]) : '') }}"
                            class="row g-3">
                            @csrf
                            @method('PATCH')
                            <div class="col-12 col-md-6 offset-md-3">
                                <label class="form-label" for="cambioIndice">Cambiar indice </label>
                                <input type="number" id="cambioIndice" name="cambioIndice" class="form-control"
                                    value="{{ $usuario_seleccionado ? $usuario_seleccionado->indice_grafico_ministerial : ($grupo_seleccionado ? $grupo_seleccionado->indice_grafico_ministerial : '') }}"
                                    placeholder="Cambio de indice" />
                            </div>

                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary me-sm-3 me-1">Guardar</button>
                                <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                    aria-label="Close">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <!--/ modal cambiar indice -->


@endsection
