@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Gráfico del ministerio')

@section('vendor-style')
    <style>
        .canvas-container {
            position: relative;
            width: 100%;
            height: 650px;
            border: 1px solid #e1e4e8;
            border-radius: 8px;
            overflow: hidden;
            background-color: #fafbfc;
            user-select: none;
        }

        #svg-canvas {
            width: 100%;
            height: 100%;
            cursor: grab;
        }

        #svg-canvas:active {
            cursor: grabbing;
        }

        .nodo-group {
            cursor: pointer;
        }

        .nodo-circle {
            stroke: #ffffff;
            stroke-width: 3px;
            transition: r 0.2s, stroke-width 0.2s;
        }

        .nodo-group:hover .nodo-circle {
            r: 26px;
            stroke-width: 4px;
        }

        .nodo-texto-nombre {
            font-size: 11px;
            font-weight: 600;
            fill: #2c3e50;
            text-anchor: middle;
            pointer-events: none;
        }

        .nodo-texto-secundario {
            font-size: 9px;
            fill: #7f8c8d;
            text-anchor: middle;
            pointer-events: none;
        }

        .nodo-iniciales {
            font-size: 14px;
            font-weight: bold;
            fill: #ffffff;
            text-anchor: middle;
            dominant-baseline: central;
            pointer-events: none;
        }

        .conector-line {
            fill: none;
            stroke-dasharray: 4, 4;
            transition: stroke-width 0.2s;
        }

        .nodo-group:hover ~ .conector-line,
        .conector-line:hover {
            stroke-width: 4px;
        }

        /* Controles de Zoom/Pan */
        .zoom-controls {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            z-index: 10;
        }

        .zoom-btn {
            width: 36px;
            height: 36px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            background-color: #ffffff;
            border: 1px solid #dcdfe6;
            box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
            color: #606266;
            transition: all 0.3s;
        }

        .zoom-btn:hover {
            background-color: #f5f7fa;
            color: #409eff;
            border-color: #c6e2ff;
        }

        .info-floating-panel {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: rgba(255, 255, 255, 0.95);
            border: 1px solid #e1e4e8;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 12px;
            color: #586069;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            z-index: 10;
            pointer-events: none;
        }

        .leyenda-container {
            position: absolute;
            bottom: 20px;
            left: 20px;
            background-color: rgba(255, 255, 255, 0.95);
            border: 1px solid #e1e4e8;
            border-radius: 8px;
            padding: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            z-index: 10;
            max-width: 320px;
        }

        .leyenda-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        /* Estilos del Offcanvas */
        .offcanvas-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: bold;
            color: #ffffff;
            margin: 0 auto 15px auto;
            border: 4px solid #ffffff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
    </style>
    @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
@endsection

@section('page-script')
    <script>
        // Datos enviados por el controlador
        const nodosData = @json($nodos);
        const aristasData = @json($aristas);

        document.addEventListener("DOMContentLoaded", function() {
            const svg = document.getElementById("svg-canvas");
            const viewport = document.getElementById("viewport");
            const linesGroup = document.getElementById("lines-group");
            const nodesGroup = document.getElementById("nodes-group");

            // Configuración del canvas interactivo
            let zoomFactor = 0.8;
            let panX = 100;
            let panY = 50;
            let isPanning = false;
            let startX = 0;
            let startY = 0;

            // Drag and drop de nodos
            let isDragging = false;
            let draggedNodeId = null;
            let dragStartX = 0;
            let dragStartY = 0;
            let dragStartClientX = 0;
            let dragStartClientY = 0;
            let nodeWasDragged = false;

            // Dimensiones iniciales estimadas
            const width = svg.clientWidth || 1000;
            const height = svg.clientHeight || 650;

            // 1. Algoritmo de distribución inicial por niveles jerárquicos (Bottom-Up recursivo)
            function distribuirNodos(nodos, aristas) {
                if (nodos.length === 0) return;

                // Mapear adyacencia padres -> hijos
                const hijosDe = {};
                aristas.forEach(edge => {
                    if (!hijosDe[edge.from]) {
                        hijosDe[edge.from] = [];
                    }
                    hijosDe[edge.from].push(edge.to);
                });

                const posicionados = new Set();
                let nextX = 80;
                const spacingX = 140; // Espacio horizontal de seguridad
                const spacingY = 175; // Espacio vertical por nivel
                const startY = 80;

                // Función para resolver colisiones en el mismo nivel Y
                function evitarColision(node, level) {
                    const minDistance = 140; // Espaciado horizontal de seguridad
                    let conflicto = true;
                    let intentos = 0;
                    while (conflicto && intentos < 100) {
                        conflicto = false;
                        for (let otherId of posicionados) {
                            if (otherId === node.id) continue;
                            const other = nodos.find(n => n.id === otherId);
                            if (other) {
                                // Mismo nivel vertical Y
                                if (Math.abs(other.y - node.y) < 10) {
                                    // Conflicto de distancia X
                                    if (Math.abs(other.x - node.x) < minDistance) {
                                        node.x = other.x + minDistance;
                                        conflicto = true;
                                        intentos++;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }

                // Función recursiva Bottom-Up
                function posicionar(nodeId, level) {
                    if (posicionados.has(nodeId)) return;
                    const node = nodos.find(n => n.id === nodeId);
                    if (!node) return;

                    const hijosIds = hijosDe[nodeId] || [];
                    const hijos = nodos.filter(n => hijosIds.includes(n.id));

                    if (hijos.length === 0) {
                        // Nodo hoja
                        node.x = nextX;
                        node.y = startY + level * spacingY;
                        evitarColision(node, level);
                        nextX = Math.max(nextX, node.x + spacingX);
                        posicionados.add(nodeId);
                    } else {
                        // Determinar si todos los hijos de este nodo son hojas
                        const todosHijosHojas = hijos.every(h => {
                            const subHijos = hijosDe[h.id] || [];
                            return nodos.filter(n => subHijos.includes(n.id)).length === 0;
                        });

                        // Si todos los hijos son hojas (ej: asistentes de un grupo) y son más de 5,
                        // los agrupamos en columnas de 2 para reducir a la mitad el ancho horizontal.
                        if (todosHijosHojas && hijos.length > 5) {
                            let colX = nextX;
                            for (let k = 0; k < hijos.length; k += 2) {
                                const hijo1 = hijos[k];
                                hijo1.x = colX;
                                hijo1.y = startY + (level + 1) * spacingY;
                                posicionados.add(hijo1.id);

                                if (k + 1 < hijos.length) {
                                    const hijo2 = hijos[k + 1];
                                    hijo2.x = colX;
                                    hijo2.y = startY + (level + 1) * spacingY + 60; // 60px más abajo en columna
                                    posicionados.add(hijo2.id);
                                }
                                colX += spacingX;
                            }
                            
                            // Centrar el padre sobre sus columnas
                            const primerHijoX = hijos[0].x;
                            const ultimoHijoX = hijos[hijos.length - 1].x;
                            node.x = (primerHijoX + ultimoHijoX) / 2;
                            node.y = startY + level * spacingY;
                            evitarColision(node, level);
                            posicionados.add(nodeId);

                            nextX = colX; // Actualizar nextX global al final de este grupo
                        } else {
                            // Distribución recursiva estándar
                            hijos.forEach(hijo => {
                                posicionar(hijo.id, level + 1);
                            });

                            // Centrar el padre sobre sus hijos
                            const hijosPosicionados = hijos.filter(h => posicionados.has(h.id));
                            if (hijosPosicionados.length > 0) {
                                const totalX = hijosPosicionados.reduce((sum, h) => sum + h.x, 0);
                                node.x = totalX / hijosPosicionados.length;
                            } else {
                                node.x = nextX;
                                nextX += spacingX;
                            }
                            node.y = startY + level * spacingY;
                            evitarColision(node, level);
                            posicionados.add(nodeId);
                        }
                    }
                }

                // Encontrar niveles mínimos (raíces) para arrancar la recursión
                const nivelesKeys = nodos.map(n => n.level);
                const nivelMin = Math.min(...nivelesKeys);
                const nodosRaiz = nodos.filter(n => n.level === nivelMin);

                nodosRaiz.forEach(raiz => {
                    posicionar(raiz.id, 0);
                });

                // Posicionar nodos huérfanos que puedan haber quedado sueltos por si acaso
                nodos.forEach(node => {
                    if (!posicionados.has(node.id)) {
                        node.x = nextX;
                        node.y = startY + node.level * spacingY;
                        nextX += spacingX;
                        posicionados.add(node.id);
                    }
                });

                // Centrar horizontalmente todo el gráfico en el lienzo SVG
                if (nodos.length > 0) {
                    const xs = nodos.map(n => n.x);
                    const minX = Math.min(...xs);
                    const maxX = Math.max(...xs);
                    const centroGrafico = (minX + maxX) / 2;
                    const centroLienzo = width / 2;
                    const offsetX = centroLienzo - centroGrafico;

                    nodos.forEach(node => {
                        node.x += offsetX;
                    });
                }
            }

            // 2. Renderizado del Gráfico (Nodos y Aristas)
            function render() {
                // Limpiar contenido previo
                linesGroup.innerHTML = "";
                nodesGroup.innerHTML = "";

                // Renderizar Aristas (Líneas)
                aristasData.forEach(edge => {
                    const padre = nodosData.find(n => n.id === edge.from);
                    const hijo = nodosData.find(n => n.id === edge.to);

                    if (padre && hijo) {
                        const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
                        path.setAttribute("id", `line-${padre.id}-${hijo.id}`);
                        path.setAttribute("class", "conector-line");
                        path.setAttribute("stroke", edge.color || "#cccccc");
                        path.setAttribute("stroke-width", "2.2");
                        
                        // Dibujar curva Bezier vertical suave
                        updatePathD(path, padre.x, padre.y, hijo.x, hijo.y);
                        linesGroup.appendChild(path);
                    }
                });

                // Renderizar Nodos
                nodosData.forEach(node => {
                    const g = document.createElementNS("http://www.w3.org/2000/svg", "g");
                    g.setAttribute("class", "nodo-group");
                    g.setAttribute("transform", `translate(${node.x},${node.y})`);
                    g.setAttribute("data-id", node.id);

                    // Círculo base del nodo
                    const circle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
                    circle.setAttribute("class", "nodo-circle");
                    circle.setAttribute("cx", "0");
                    circle.setAttribute("cy", "0");
                    circle.setAttribute("r", "22");
                    circle.setAttribute("fill", node.color || "#cccccc");
                    g.appendChild(circle);

                    if (node.image) {
                        // Imagen de perfil / Icono
                        const clipPathId = `clip-${node.id}`;
                        const defs = document.createElementNS("http://www.w3.org/2000/svg", "defs");
                        const clipPath = document.createElementNS("http://www.w3.org/2000/svg", "clipPath");
                        clipPath.setAttribute("id", clipPathId);
                        
                        const clipCircle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
                        clipCircle.setAttribute("cx", "0");
                        clipCircle.setAttribute("cy", "0");
                        clipCircle.setAttribute("r", "22");
                        clipPath.appendChild(clipCircle);
                        defs.appendChild(clipPath);
                        g.appendChild(defs);

                        const image = document.createElementNS("http://www.w3.org/2000/svg", "image");
                        image.setAttribute("href", node.image);
                        image.setAttribute("crossorigin", "anonymous");
                        image.setAttribute("x", "-22");
                        image.setAttribute("y", "-22");
                        image.setAttribute("width", "44");
                        image.setAttribute("height", "44");
                        image.setAttribute("clip-path", `url(#${clipPathId})`);
                        g.appendChild(image);
                    } else if (node.iniciales) {
                        // Iniciales del nombre
                        const text = document.createElementNS("http://www.w3.org/2000/svg", "text");
                        text.setAttribute("class", "nodo-iniciales");
                        text.textContent = node.iniciales;
                        g.appendChild(text);
                    }

                    // Texto - Nombre (encima del nodo)
                    const textNombre = document.createElementNS("http://www.w3.org/2000/svg", "text");
                    textNombre.setAttribute("class", "nodo-texto-nombre");

                    if (node.tipo === 'G') {
                        textNombre.setAttribute("y", "-30");
                        const maxLen = 22;
                        if (node.nombre && node.nombre.length > maxLen) {
                            textNombre.textContent = node.nombre.substring(0, maxLen).trim() + '...';
                        } else {
                            textNombre.textContent = node.nombre;
                        }
                        g.appendChild(textNombre);
                    } else if (node.tipo === 'A') {
                        textNombre.setAttribute("y", "-38");
                        // Es persona. Nombre en un renglón y apellido debajo.
                        const partes = node.nombre.trim().split(/\s+/);
                        let primerRenglon = '';
                        let segundoRenglon = '';
                        if (partes.length >= 3) {
                            primerRenglon = partes.slice(0, partes.length - 1).join(" ");
                            segundoRenglon = partes[partes.length - 1];
                        } else if (partes.length === 2) {
                            primerRenglon = partes[0];
                            segundoRenglon = partes[1];
                        } else {
                            primerRenglon = node.nombre;
                            segundoRenglon = '';
                        }

                        const tspan1 = document.createElementNS("http://www.w3.org/2000/svg", "tspan");
                        tspan1.setAttribute("x", "0");
                        tspan1.setAttribute("dy", "0");
                        tspan1.textContent = primerRenglon;
                        textNombre.appendChild(tspan1);

                        if (segundoRenglon) {
                            const tspan2 = document.createElementNS("http://www.w3.org/2000/svg", "tspan");
                            tspan2.setAttribute("x", "0");
                            tspan2.setAttribute("dy", "13");
                            tspan2.textContent = segundoRenglon;
                            textNombre.appendChild(tspan2);
                        }
                        g.appendChild(textNombre);
                    } else {
                        textNombre.setAttribute("y", "-30");
                        textNombre.textContent = node.nombre;
                        g.appendChild(textNombre);
                    }

                    // Eventos de ratón y táctiles para el nodo (Drag and Drop y Clic)
                    function startDrag(clientX, clientY) {
                        isDragging = true;
                        draggedNodeId = node.id;
                        nodeWasDragged = false;
                        dragStartClientX = clientX;
                        dragStartClientY = clientY;

                        const rect = svg.getBoundingClientRect();
                        const x = (clientX - rect.left - panX) / zoomFactor;
                        const y = (clientY - rect.top - panY) / zoomFactor;

                        dragStartX = x - node.x;
                        dragStartY = y - node.y;
                        
                        g.parentNode.appendChild(g); // Mover el nodo al frente visualmente
                    }

                    g.addEventListener("mousedown", function(e) {
                        e.stopPropagation();
                        startDrag(e.clientX, e.clientY);
                    });

                    g.addEventListener("touchstart", function(e) {
                        e.stopPropagation();
                        if (e.cancelable) {
                            e.preventDefault();
                        }
                        if (e.touches.length > 0) {
                            startDrag(e.touches[0].clientX, e.touches[0].clientY);
                        }
                    }, { passive: false });

                    nodesGroup.appendChild(g);
                });
            }

            // Calcula la curva Bezier cúbica vertical entre dos coordenadas
            function updatePathD(pathElement, x1, y1, x2, y2) {
                const midY = (y1 + y2) / 2;
                pathElement.setAttribute("d", `M ${x1} ${y1} C ${x1} ${midY}, ${x2} ${midY}, ${x2} ${y2}`);
            }

            // Obtiene las coordenadas correctas del ratón mapeadas al espacio del SVG con zoom/pan
            function getSVGCoords(event) {
                const rect = svg.getBoundingClientRect();
                return {
                    x: (event.clientX - rect.left - panX) / zoomFactor,
                    y: (event.clientY - rect.top - panY) / zoomFactor
                };
            }

            // Actualiza la matriz de transformación del viewport
            function updateTransform() {
                viewport.setAttribute("transform", `translate(${panX}, ${panY}) scale(${zoomFactor})`);
                
                // Mover el fondo con patrón para dar sensación infinita
                const pattern = document.getElementById("dot-pattern");
                if (pattern) {
                    pattern.setAttribute("patternTransform", `translate(${panX}, ${panY}) scale(${zoomFactor})`);
                }
                
                document.getElementById("zoom-indicator").innerText = `${Math.round(zoomFactor * 100)}%`;
            }

            // 3. Manejo de Eventos en el Lienzo (Pan e Interactividad)
            svg.addEventListener("mousedown", function(e) {
                isPanning = true;
                startX = e.clientX - panX;
                startY = e.clientY - panY;
            });

            svg.addEventListener("touchstart", function(e) {
                if (e.touches.length === 1) {
                    isPanning = true;
                    startX = e.touches[0].clientX - panX;
                    startY = e.touches[0].clientY - panY;
                }
            }, { passive: true });

            function onMove(clientX, clientY, isTouch = false, e = null) {
                if (isPanning) {
                    if (isTouch && e && e.cancelable) {
                        e.preventDefault();
                    }
                    panX = clientX - startX;
                    panY = clientY - startY;
                    updateTransform();
                } else if (isDragging && draggedNodeId) {
                    if (isTouch && e && e.cancelable) {
                        e.preventDefault();
                    }
                    
                    // Calcular la distancia desde el origen del toque/clic
                    const deltaX = clientX - dragStartClientX;
                    const deltaY = clientY - dragStartClientY;
                    const distancia = Math.sqrt(deltaX * deltaX + deltaY * deltaY);
                    
                    if (distancia > 6) {
                        nodeWasDragged = true;
                        
                        const rect = svg.getBoundingClientRect();
                        const x = (clientX - rect.left - panX) / zoomFactor;
                        const y = (clientY - rect.top - panY) / zoomFactor;
                        
                        const node = nodosData.find(n => n.id === draggedNodeId);
                        
                        if (node) {
                            node.x = x - dragStartX;
                            node.y = y - dragStartY;

                            // Actualizar posición visual del nodo
                            const gElement = nodesGroup.querySelector(`[data-id="${draggedNodeId}"]`);
                            if (gElement) {
                                gElement.setAttribute("transform", `translate(${node.x},${node.y})`);
                            }

                            // Redibujar las aristas conectadas
                            aristasData.forEach(edge => {
                                if (edge.from === draggedNodeId || edge.to === draggedNodeId) {
                                    const pNode = nodosData.find(n => n.id === edge.from);
                                    const hNode = nodosData.find(n => n.id === edge.to);
                                    const path = document.getElementById(`line-${edge.from}-${edge.to}`);
                                    if (path && pNode && hNode) {
                                        updatePathD(path, pNode.x, pNode.y, hNode.x, hNode.y);
                                    }
                                }
                            });
                        }
                    }
                }
            }

            window.addEventListener("mousemove", function(e) {
                onMove(e.clientX, e.clientY);
            });

            window.addEventListener("touchmove", function(e) {
                if (e.touches.length > 0) {
                    onMove(e.touches[0].clientX, e.touches[0].clientY, true, e);
                }
            }, { passive: false });

            function onEnd() {
                if (isDragging && draggedNodeId) {
                    // Si no se arrastró, fue un clic simple -> Abrir detalle
                    if (!nodeWasDragged) {
                        abrirDetalleNodo(draggedNodeId);
                    }
                    isDragging = false;
                    draggedNodeId = null;
                }
                isPanning = false;
            }

            window.addEventListener("mouseup", function(e) {
                onEnd();
            });

            window.addEventListener("touchend", function(e) {
                onEnd();
            });

            // Zoom por rueda del ratón
            svg.addEventListener("wheel", function(e) {
                e.preventDefault();
                const zoomIntensity = 0.05;
                const delta = e.deltaY < 0 ? 1 : -1;
                
                const rect = svg.getBoundingClientRect();
                const mouseX = e.clientX - rect.left;
                const mouseY = e.clientY - rect.top;

                // Zoom centrado en la posición del ratón
                const svgMouseX = (mouseX - panX) / zoomFactor;
                const svgMouseY = (mouseY - panY) / zoomFactor;

                zoomFactor += delta * zoomIntensity;
                zoomFactor = Math.max(0.15, Math.min(zoomFactor, 3.0));

                panX = mouseX - svgMouseX * zoomFactor;
                panY = mouseY - svgMouseY * zoomFactor;

                updateTransform();
            });

            // 4. Botones Flotantes de Zoom
            document.getElementById("btn-zoom-in").addEventListener("click", function() {
                zoomFactor += 0.1;
                zoomFactor = Math.min(zoomFactor, 3.0);
                updateTransform();
            });

            document.getElementById("btn-zoom-out").addEventListener("click", function() {
                zoomFactor -= 0.1;
                zoomFactor = Math.max(zoomFactor, 0.15);
                updateTransform();
            });

            document.getElementById("btn-zoom-reset").addEventListener("click", function() {
                zoomFactor = 0.8;
                panX = (width - width * zoomFactor) / 2 || 100;
                panY = 50;
                updateTransform();
            });

            // 5. Apertura de la Ficha Lateral (Offcanvas)
            function abrirDetalleNodo(id) {
                const node = nodosData.find(n => n.id === id);
                if (!node) return;

                const offcanvasTitle = document.getElementById("offcanvasDetalleNodoLabel");
                const offcanvasContent = document.getElementById("offcanvas-contenido");
                const actionBtn = document.getElementById("btn-ver-grafico-desde-aqui");

                // Configurar Botón de Acción
                const currentNiveles = "{{ $maximos_levels ?? '' }}" || "20";
                actionBtn.setAttribute("href", `/grupo/grafico-del-ministerio/${node.id}/${currentNiveles}`);

                // Generar contenido del offcanvas según el tipo de nodo
                if (node.tipo === 'A') {
                    // Es Persona
                    offcanvasTitle.innerText = "";
                    
                    // Generar HTML para el menú dropdown de 3 puntos
                    let dropdownHtml = '';
                    if (node.opciones_menu && node.opciones_menu.length > 0) {
                        dropdownHtml = `
                            <div class="dropdown">
                                <button type="button" class="btn btn-sm rounded-pill btn-icon btn-outline-secondary waves-effect" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ti ti-dots-vertical fs-5"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    ${node.opciones_menu.map(opt => `
                                        <li>
                                            <a class="dropdown-item ${opt.class || ''}" 
                                               href="${opt.url}" 
                                               ${opt.onclick ? `onclick="${opt.onclick}"` : ''}>
                                               ${opt.label}
                                            </a>
                                        </li>
                                    `).join('')}
                                </ul>
                            </div>
                        `;
                    }

                    offcanvasContent.innerHTML = `
                        <!-- Banner de portada -->
                        <div class="position-relative" style="height: 100px; margin: -1.5rem -1.5rem 1.5rem -1.5rem; overflow: hidden; background-color: #f5f5f5;">
                            <img src="${node.banner_url || 'https://crecer.ubicalo.com/storage/personas/profile-banner.png'}" 
                                 style="width: 100%; height: 100px; object-fit: cover;" 
                                 alt="Banner" />
                        </div>

                        <!-- Avatar superpuesto -->
                        <div class="user-profile-header d-flex flex-row text-start mb-2 px-1">
                            <div class="flex-grow-1 text-start">
                                <div class="avatar avatar-xl" style="width: 80px; height: 80px; margin-top: -40px; position: relative; z-index: 5;">
                                    ${node.image 
                                        ? `<img src="${node.image}" alt="${node.nombre}" class="rounded-circle border border-3 border-white bg-info" style="width: 80px; height: 80px; object-fit: cover;" />`
                                        : `<span class="avatar-initial rounded-circle border border-3 border-white bg-info d-flex align-items-center justify-content-center text-white fw-bold fs-4" style="width: 80px; height: 80px;">${node.iniciales}</span>`
                                    }
                                </div>
                            </div>
                        </div>

                        <!-- Nombre, Edad y Dropdown -->
                        <div class="d-flex justify-content-between mb-3 mt-2 px-1">
                            <div class="d-flex align-items-start">
                                <div class="me-2">
                                    <h5 class="mb-1 fw-semibold text-black lh-sm fs-4">${node.nombre}</h5>
                                    <div class="client-info text-black fs-6">
                                        <b>Edad:</b> ${node.edad}
                                    </div>
                                </div>
                            </div>
                            <div class="ms-auto">
                                ${dropdownHtml}
                            </div>
                        </div>

                        <!-- Badge de Tipo de Usuario -->
                        <div class="mb-4 px-1">
                            <span class="badge d-inline-flex align-items-center px-3 py-2 rounded-pill text-white" style="background-color: ${node.color || '#8c57ff'}">
                                <i class="ti ${node.tipo_icono || 'ti-user'} me-1 fs-6"></i>${node.tipo_nombre}
                            </span>
                        </div>

                        <!-- Información Adicional -->
                        <hr class="my-4">
                        <div class="row g-3 px-1 mb-4">
                            <div class="col-12">
                                <small class="text-muted d-block">Identificador único</small>
                                <span class="fw-semibold text-heading">${node.id}</span>
                            </div>
                        </div>
                    `;
                } else {
                     // Es Grupo
                     offcanvasTitle.innerText = "";

                     // Generar HTML para el menú dropdown de 3 puntos
                     let dropdownHtml = '';
                     if (node.opciones_menu && node.opciones_menu.length > 0) {
                         dropdownHtml = `
                             <div class="dropdown">
                                 <button type="button" class="btn btn-sm rounded-pill btn-icon btn-outline-secondary waves-effect" data-bs-toggle="dropdown" aria-expanded="false">
                                     <i class="ti ti-dots-vertical fs-5"></i>
                                 </button>
                                 <ul class="dropdown-menu dropdown-menu-end">
                                     ${node.opciones_menu.map(opt => `
                                         <li>
                                             <a class="dropdown-item ${opt.class || ''}" 
                                                href="${opt.url}" 
                                                ${opt.onclick ? `onclick="${opt.onclick}"` : ''}>
                                                ${opt.label}
                                             </a>
                                         </li>
                                     `).join('')}
                                 </ul>
                             </div>
                         `;
                     }

                     offcanvasContent.innerHTML = `
                         <!-- Banner de portada -->
                         <div class="position-relative" style="height: 130px; margin: -1.5rem -1.5rem 1.5rem -1.5rem; overflow: hidden; background-color: #f5f5f5;">
                             <img src="${node.banner_url || 'https://crecer.ubicalo.com/storage/personas/profile-banner.png'}" 
                                  style="width: 100%; height: 130px; object-fit: cover;" 
                                  alt="Banner" />
                         </div>

                         <!-- Nombre, Tipo y Dropdown -->
                         <div class="d-flex justify-content-between mb-2 mt-2 px-1">
                             <div class="d-flex align-items-start">
                                 <div class="me-2">
                                     <h5 class="mb-1 fw-semibold text-black lh-sm fs-4">${node.nombre}</h5>
                                     <div class="client-info text-black fs-6">${node.tipo_nombre}</div>
                                 </div>
                             </div>
                             <div class="ms-auto">
                                 ${dropdownHtml}
                             </div>
                         </div>

                         <!-- Badges de Reporte -->
                         <div class="d-flex my-3 px-1 gap-2 flex-wrap">
                             ${node.tiene_ultimo_reporte 
                                 ? `<span class="badge rounded-pill bg-label-primary px-3 py-2 fs-tiny"><b>Último reporte:</b> ${node.fecha_ultimo_reporte}</span>`
                                 : `<span class="badge rounded-pill bg-label-danger px-3 py-2 fs-tiny">Nunca reportado</span>`
                             }
                             ${node.al_dia 
                                 ? `<span class="badge rounded-pill bg-label-success px-3 py-2 fs-tiny">Al día</span>`
                                 : ''
                             }
                             ${node.es_sede 
                                 ? `<span class="badge rounded-pill bg-label-info px-3 py-2 fs-tiny">Sede Principal</span>`
                                 : ''
                             }
                         </div>

                         <!-- Grilla de Detalles -->
                         <div class="row g-3 px-1 mb-4 mt-3">
                             <div class="col-6 d-flex align-items-start">
                                 <i class="ti ti-clock me-2 text-black fs-4 mt-0.5"></i>
                                 <div>
                                     <small class="text-black d-block" style="font-size: 11px;">Día de reunión:</small>
                                     <span class="fw-semibold text-black" style="font-size: 13px;">${node.dia_reunion}</span>
                                 </div>
                             </div>
                             <div class="col-6 d-flex align-items-start">
                                 <i class="ti ti-users me-2 text-black fs-4 mt-0.5"></i>
                                 <div>
                                     <small class="text-black d-block" style="font-size: 11px;">Integrantes</small>
                                     <span class="fw-semibold text-black" style="font-size: 13px;">${node.personas_count} ${node.personas_count === 1 ? 'persona' : 'personas'}</span>
                                 </div>
                             </div>
                             <div class="col-12 d-flex align-items-start mt-3">
                                 <i class="ti ti-confetti me-2 text-black fs-4 mt-0.5"></i>
                                 <div>
                                     <small class="text-black d-block" style="font-size: 11px;">Fecha de apertura:</small>
                                     <span class="fw-semibold text-black" style="font-size: 13px;">${node.fecha_apertura}</span>
                                 </div>
                             </div>
                         </div>

                         <!-- Información Adicional -->
                         <hr class="my-4">
                         <div class="row g-3 px-1 mb-4">
                             <div class="col-12">
                                 <small class="text-black d-block">Identificador único</small>
                                 <span class="fw-semibold text-black">${node.id}</span>
                             </div>
                         </div>
                     `;
                }

                // Desplegar Offcanvas
                const myOffcanvas = new bootstrap.Offcanvas(document.getElementById('offcanvasDetalleNodo'));
                myOffcanvas.show();
            }

            // Inicialización general diferida y protegida para resiliencia ante fallos externos de JS
            setTimeout(function() {
                try {
                    distribuirNodos(nodosData, aristasData);
                    render();
                    updateTransform();
                } catch (err) {
                    console.error("Error en renderizado de gráfico:", err);
                }
            }, 80);
        });

        // Función para descargar el gráfico como PNG
        function downloadImage() {
            const container = document.getElementById("graph-container");
            html2canvas(container, {
                useCORS: true,
                allowTaint: true,
                backgroundColor: "#fafbfc"
            }).then(function(canvas) {
                var link = document.createElement('a');
                link.href = canvas.toDataURL('image/png');
                link.download = 'grafico_ministerio.png'; 
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        }
    </script>
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            const btns = document.querySelectorAll('.cargando');
            btns.forEach(btn => {
                btn.addEventListener('click', function() {
                    Swal.fire({
                        title: "Espera un momento",
                        text: "Esto puede tardar un momento...",
                        icon: "info",
                        showCancelButton: false,
                        showConfirmButton: false,
                        showDenyButton: false
                    });
                });
            });
        });
    </script>
@endsection

@section('content')
    <!-- Cabecera: Título y Subtítulo -->
    <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap">
        <div>
            <h4 class="mb-1 fw-bold text-primary">Gráfico del ministerio</h4>
        </div>
    </div>

    <!-- Fila de Acciones: alineadas a la derecha -->
    <div class="d-flex justify-content-end align-items-center gap-2 mb-4 flex-wrap">
        <a href="{{ route('grupo.graficoDelMinisterio') }}"
            class="cargando btn btn-sm btn-outline-secondary waves-effect shadow-sm bg-white">
            <i class="ti ti-rotate-clockwise me-1.5 ti-xs"></i> Restablecer
        </a>

        @if ($maximos_niveles != 20)
            <a href="{{ route('grupo.graficoDelMinisterio', ['U-logueado', 20]) }}"
                class="cargando btn btn-sm btn-primary waves-effect d-flex align-items-center shadow-sm">
                <i class="ti ti-sitemap me-1.5 ti-xs"></i> Ver completo
            </a>
        @endif

        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle waves-effect shadow-sm bg-white" type="button" id="dropdownMasAcciones" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="ti ti-dots me-1 ti-xs"></i> Más acciones
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMasAcciones">
                @if ($tipoDeNodo != 'U-principal')
                    <li>
                        <button type="button" class="dropdown-item d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalCambiarIndice">
                            <i class="ti ti-transform me-2 ti-xs"></i> Cambiar índice
                        </button>
                    </li>
                @endif
                <li>
                    <button type="button" class="dropdown-item d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalPersonasNoGraficadas">
                        <i class="ti ti-pencil-off me-2 ti-xs"></i> Personas no graficadas
                    </button>
                </li>
                <li>
                    <button class="dropdown-item d-flex align-items-center" onclick="downloadImage()">
                        <i class="ti ti-download me-2 ti-xs"></i> Descargar Imagen
                    </button>
                </li>
            </ul>
        </div>
    </div>

    @include('layouts.status-msn')

    <!-- Tarjetas de KPI -->
    <div class="row mb-4">
        <!-- Card Total Personas -->
        <div class="col-md-6 col-lg-3 col-12 mb-3 mb-md-0">
            <div class="card border rounded-3 shadow-sm bg-white">
                <div class="card-body d-flex flex-row p-3 align-items-center">
                    <div class="card-icon me-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 50px; height: 50px; background-color: #51a903; flex-shrink: 0;">
                            <i class="ti ti-users fs-3"></i>
                        </div>
                    </div>
                    <div class="card-title mb-0">
                        <p class="text-black mb-0" style="font-size: .8125rem">Total Personas</p>
                        <h5 class="mb-0 me-2 fw-bold" style="font-size: 1.25rem;">{{ $totalPersonas }}</h5>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Card Total Grupos -->
        <div class="col-md-6 col-lg-3 mb-3 col-12">
            <div class="card border rounded-3 shadow-sm bg-white">
                <div class="card-body d-flex flex-row p-3 align-items-center">
                    <div class="card-icon me-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 50px; height: 50px; background-color: #51a903; flex-shrink: 0;">
                            <i class="ti ti-users-group fs-3"></i>
                        </div>
                    </div>
                    <div class="card-title mb-0">
                        <p class="text-black mb-0" style="font-size: .8125rem">Total Grupos</p>
                        <h5 class="mb-0 me-2 fw-bold" style="font-size: 1.25rem;">{{ $totalGrupos }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Buscador Ministerial --> 
        <div class="col-md-12 col-lg-6">
            <div class="card px-3 py-2 shadow-sm border rounded-3 bg-white">
                <small class="text-black mb-1" style="font-size: 12px; letter-spacing: 0.5px; display: block;">Busqueda a partir de la persona</small>
                @livewire('usuarios.usuarios-para-busqueda', [
                    'id' => 'buscador-ministerial',
                    'class' => 'w-100',
                    'placeholder' => 'Escribe el nombre de la persona...',
                    'queUsuariosCargar' => ($rolActivo->hasPermissionTo('grupos.grafico_ministerio_todos') || isset(auth()->user()->iglesiaEncargada()->first()->id)) ? 'todos' : 'discipulos',
                    'tipoBuscador' => 'lista',
                    'redirect' => 'grupo.graficoDelMinisterio',
                    'conDadosDeBaja' => 'no',
                    'soloVerificados' => false,
                ]) 
            </div>
        </div>        
    </div>


    <div class="row m-1">
        <div class="card p-0 overflow-hidden bg-white shadow-sm" style="border: 1px solid #e1e4e8; border-radius: 8px;">
            <div class="card-body p-0">
                <!-- Contenedor del Lienzo Gráfico -->
                <div id="graph-container" class="canvas-container">
                    <!-- Panel Flotante de Información -->
                    <div class="info-floating-panel">
                        Rueda del mouse para zoom: 
                        <span id="zoom-indicator" class="fw-bold text-primary">80%</span>
                    </div>

                    <!-- Leyenda Dinámica -->
                    <div class="leyenda-container">
                        <h6 class="mb-2 fw-semibold text-heading fs-6 border-bottom pb-1">Leyenda</h6>
                        <div class="d-flex flex-column gap-1.5" style="max-height: 120px; overflow-y: auto;">
                            @foreach ($leyenda ?? [] as $item)
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="leyenda-dot" style="background-color: {{ $item['color'] }}"></span>
                                        <span class="text-black fs-tiny" style="font-size: 11px;">{{ $item['nombre'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Botones Flotantes de Zoom -->
                    <div class="zoom-controls">
                        <button id="btn-zoom-in" class="zoom-btn" title="Acercar">
                            <i class="ti ti-plus"></i>
                        </button>
                        <button id="btn-zoom-out" class="zoom-btn" title="Alejar">
                            <i class="ti ti-minus"></i>
                        </button>
                        <button id="btn-zoom-reset" class="zoom-btn" title="Restablecer vista">
                            <i class="ti ti-rotate"></i>
                        </button>
                    </div>

                    <!-- SVG Interactiva -->
                    <svg id="svg-canvas">
                        <defs>
                            <pattern id="dot-pattern" width="24" height="24" patternUnits="userSpaceOnUse">
                                <circle cx="2" cy="2" r="1.2" fill="#d2d6dc" />
                            </pattern>
                        </defs>
                        <!-- Cuadrícula de Fondo -->
                        <rect width="100%" height="100%" fill="url(#dot-pattern)" />
                        <!-- Viewport para Zoom y Pan -->
                        <g id="viewport">
                            <g id="lines-group"></g>
                            <g id="nodes-group"></g>
                        </g>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- modal Personas No Graficadas -->
    <div class="modal fade" id="modalPersonasNoGraficadas" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content p-3 p-md-5">
                <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h3 class="role-title mb-2"><i class="ti ti-pencil-off ti-lg"></i> Personas no graficadas</h3>
                        <p class="text-muted">Líderes o asistentes que ya aparecen dibujados en otra rama del organigrama jerárquico.</p>
                    </div>

                    <div class="table-responsive text-nowrap">
                        <table class="table border-dashed">
                            <thead>
                                <tr>
                                    <th>Persona</th>
                                    <th>Grupos a los que pertenece</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($usuarios_no_dibujados as $usuariNoDibujado)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar me-2 my-auto">
                                                    <img src="{{ tenant_asset('img/usuarios/foto-usuario/' . $usuariNoDibujado->foto) }}"
                                                         alt="Avatar" class="rounded-circle" />
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fs-6">{{ $usuariNoDibujado->nombre(3) }}</h6>
                                                    <small class="text-muted"><i
                                                            class="ti {{ $usuariNoDibujado->tipoUsuario->icono }} text-heading fs-6 me-1"></i>{{ $usuariNoDibujado->tipoUsuario->nombre }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <ul class="list-unstyled mb-0">
                                                @foreach ($usuariNoDibujado->gruposDondeAsiste as $grupoDondeAsiste)
                                                    <li class="p-1">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <div class="d-flex align-items-center">
                                                                <i class="ti ti-users-group me-2 fs-4 text-secondary"></i>
                                                                <div>
                                                                    <span class="fw-semibold fs-tiny d-block text-heading">{{ $grupoDondeAsiste->nombre }}</span>
                                                                    <small class="text-muted fs-tiny">{{ $grupoDondeAsiste->tipoGrupo->nombre }}</small>
                                                                </div>
                                                            </div>
                                                            @if ($rolActivo->hasPermissionTo('grupos.lista_grupos_todos'))
                                                                <a href="{{ route('grupo.perfil', $grupoDondeAsiste) }}"
                                                                   target="_blank" class="btn btn-sm btn-icon btn-label-secondary rounded-pill">
                                                                    <i class="ti ti-arrow-up-right ti-xs"></i>
                                                                </a>
                                                            @endif
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

    <!-- modal cambiar indice -->
    @if ($tipoDeNodo != 'U-principal')
        <div class="modal fade" id="modalCambiarIndice" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content p-3 p-md-5">
                    <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            @if ($usuario_seleccionado)
                                <h3 class="role-title mb-2"><i class="ti ti-transform ti-lg"></i> Cambiar índice de {{ $usuario_seleccionado->nombre(3) }}</h3>
                            @elseif($grupo_seleccionado)
                                <h3 class="role-title mb-2"><i class="ti ti-transform ti-lg"></i> Cambiar índice de {{ $grupo_seleccionado->nombre }}</h3>
                            @endif
                            <p class="text-muted">Ajusta el orden en el que se ubicarán en el organigrama.</p>
                        </div>
                        <form method="POST"
                              action="{{ $usuario_seleccionado ? route('grupo.cambiarIndice', ['usuario', $usuario_seleccionado->id]) : ($grupo_seleccionado ? route('grupo.cambiarIndice', ['grupo', $grupo_seleccionado->id]) : '') }}"
                              class="row g-3">
                            @csrf
                            @method('PATCH')
                            <div class="col-12">
                                <label class="form-label" for="cambioIndice">Índice en el gráfico</label>
                                <input type="number" id="cambioIndice" name="cambioIndice" class="form-control"
                                       value="{{ $usuario_seleccionado ? $usuario_seleccionado->indice_grafico_ministerial : ($grupo_seleccionado ? $grupo_seleccionado->indice_grafico_ministerial : '') }}"
                                       placeholder="Ej: 1" />
                            </div>

                            <div class="col-12 text-center mt-4">
                                <button type="submit" class="btn btn-primary me-sm-3 me-1">Guardar cambios</button>
                                <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Offcanvas Detalle Nodo -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasDetalleNodo" aria-labelledby="offcanvasDetalleNodoLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasDetalleNodoLabel" class="offcanvas-title fw-semibold">Detalle</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div id="offcanvas-contenido">
                <!-- Contenido Dinámico de JS -->
            </div>
            
            <div class="mt-4 pt-3 border-top">
                <a id="btn-ver-grafico-desde-aqui" href="#" class="btn btn-primary rounded-pill w-100 d-flex align-items-center justify-content-center">
                    <i class="ti ti-sitemap me-2"></i> Ver gráfico a partir de aquí
                </a>
            </div>
        </div>
    </div>
@endsection
