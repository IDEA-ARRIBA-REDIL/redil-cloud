<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets de Retiro — Iglesia Infantil</title>
    <style>
        /* =====================================================
           RESET & ESTILOS GLOBALES DE PANTALLA
        ===================================================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #eef2f5;
            color: #1a1a1a;
            padding-top: 70px; /* Espacio para barra flotante */
            padding-bottom: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        /* =====================================================
           BARRA DE HERRAMIENTAS FLOTANTE (NO IMPRIMIBLE)
        ===================================================== */
        .toolbar-tickets {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #1e293b;
            color: #ffffff;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            z-index: 9999;
            box-shadow: 0 4px 15px rgba(0,0,0,0.25);
            flex-wrap: wrap;
        }

        .toolbar-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toolbar-info .badge-formato {
            background: #3b82f6;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 3px 8px;
            border-radius: 4px;
            letter-spacing: 0.5px;
        }

        .toolbar-formatos {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #0f172a;
            padding: 4px;
            border-radius: 8px;
        }

        .btn-formato {
            background: transparent;
            color: #94a3b8;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }

        .btn-formato:hover {
            color: #ffffff;
            background: rgba(255,255,255,0.08);
        }

        .btn-formato.active {
            background: #2563eb;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(37,99,235,0.4);
        }

        .toolbar-acciones {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-accion {
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn-imprimir-todo {
            background: #10b981;
            color: white;
        }
        .btn-imprimir-todo:hover {
            background: #059669;
        }

        .btn-imprimir-filtro {
            background: #334155;
            color: #e2e8f0;
        }
        .btn-imprimir-filtro:hover {
            background: #475569;
            color: white;
        }

        .btn-cerrar {
            background: #ef4444;
            color: white;
        }
        .btn-cerrar:hover {
            background: #dc2626;
        }

        /* =====================================================
           CONTENEDOR PRINCIPAL DEL DOCUMENTO
        ===================================================== */
        .documento-impresion {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 25px;
            margin: 20px auto;
        }

        .separador-pantalla {
            width: 100%;
            text-align: center;
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            position: relative;
            margin: 10px 0;
        }

        .separador-pantalla::before,
        .separador-pantalla::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 32%;
            height: 1px;
            background: #cbd5e1;
        }
        .separador-pantalla::before { left: 0; }
        .separador-pantalla::after { right: 0; }

        /* =====================================================
           LÍNEA DE SALTO DE PÁGINA (PRINT)
        ===================================================== */
        .salto-pagina {
            page-break-after: always;
            break-after: page;
        }

        /* =====================================================
           CLASES COMUNES PARA ELEMENTOS
        ===================================================== */
        .qr-box {
            text-align: center;
            margin: 8px 0;
        }

        .alerta-medica-box {
            background: #fff3cd;
            border: 1px dashed #856404;
            color: #856404;
            padding: 6px 8px;
            border-radius: 4px;
            font-size: 11px;
            margin: 8px 0;
            font-weight: 600;
            text-align: left;
        }

        /* ====================================================================
           1. FORMATO ACTUAL (58 MM - ESTÁNDAR MINI POS)
        ==================================================================== */
        .formato-actual .ticket-page {
            background: #ffffff;
            width: 300px;
            padding: 16px 14px;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000000;
        }

        .formato-actual .header {
            text-align: center;
            border-bottom: 1px dashed #777;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }

        .formato-actual .header h1 {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .formato-actual .header .subtitulo {
            font-size: 11px;
            color: #333;
            margin-top: 2px;
            font-weight: 600;
        }

        .formato-actual .linea {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            padding: 2px 0;
        }

        .formato-actual .linea .etiqueta {
            color: #555;
            min-width: 75px;
        }

        .formato-actual .linea .valor {
            font-weight: bold;
            text-align: right;
            word-break: break-word;
        }

        .formato-actual .separador {
            border: none;
            border-top: 1px dashed #777;
            margin: 8px 0;
        }

        .formato-actual .nombre-destacado {
            font-size: 15px;
            font-weight: 900;
            text-align: center;
            text-transform: uppercase;
            margin: 6px 0;
            line-height: 1.2;
        }

        .formato-actual .codigo-box {
            text-align: center;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 8px;
            margin: 8px 0;
        }

        .formato-actual .codigo-box .lbl {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #475569;
        }

        .formato-actual .codigo-box .cod {
            font-size: 24px;
            font-weight: 900;
            letter-spacing: 3px;
        }

        .formato-actual .footer {
            text-align: center;
            font-size: 10px;
            color: #555;
            border-top: 1px dashed #777;
            padding-top: 6px;
            margin-top: 8px;
        }

        /* ====================================================================
           2. FORMATO TÉRMICA 80 MM (POS ESTÁNDAR)
        ==================================================================== */
        .formato-termica_80mm .ticket-page {
            background: #ffffff;
            width: 350px; /* ~80mm */
            padding: 18px 16px;
            border-radius: 6px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            font-size: 13px;
            color: #000000;
        }

        .formato-termica_80mm .header-pos {
            text-align: center;
            border-bottom: 2px solid #000000;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .formato-termica_80mm .header-pos .iglesia {
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .formato-termica_80mm .header-pos .tipo-ticket {
            font-size: 15px;
            font-weight: 900;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .formato-termica_80mm .nombre-nino-80 {
            font-size: 18px;
            font-weight: 900;
            text-align: center;
            text-transform: uppercase;
            margin: 6px 0 2px;
            padding: 4px;
            border: 1px solid #000;
            background: #f8fafc;
        }

        .formato-termica_80mm .badges-row {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 8px;
            font-size: 11px;
            font-weight: 700;
        }

        .formato-termica_80mm .badge-info {
            background: #000;
            color: #fff;
            padding: 2px 6px;
            border-radius: 3px;
        }

        .formato-termica_80mm .tabla-datos {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin: 6px 0;
        }

        .formato-termica_80mm .tabla-datos td {
            padding: 3px 0;
            vertical-align: top;
        }

        .formato-termica_80mm .tabla-datos .lbl {
            color: #4b5563;
            width: 32%;
            font-weight: 600;
        }

        .formato-termica_80mm .tabla-datos .val {
            font-weight: 800;
            text-align: right;
        }

        .formato-termica_80mm .codigo-80 {
            text-align: center;
            border: 2px dashed #000;
            padding: 10px;
            margin: 10px 0;
            background: #f8fafc;
        }

        .formato-termica_80mm .codigo-80 .title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .formato-termica_80mm .codigo-80 .numero {
            font-size: 30px;
            font-weight: 900;
            letter-spacing: 4px;
            line-height: 1.1;
            margin-top: 2px;
        }

        .formato-termica_80mm .linea-corte-pos {
            text-align: center;
            font-size: 11px;
            font-weight: 700;
            border-top: 1px dashed #000;
            padding-top: 6px;
            margin-top: 12px;
            letter-spacing: 1px;
        }

        /* ====================================================================
           3. FORMATO DYMO LABELWRITER 450 (PAPEL MANILLA / ETIQUETA ADHESIVA)
           Medida oficial: 102mm ancho x 59mm alto (2-5/16" x 4")
        ==================================================================== */
        .formato-dymo_450 .ticket-page {
            background: #ffffff;
            width: 385px; /* ~102mm */
            min-height: 220px; /* ~59mm */
            padding: 10px 14px;
            border: 2px solid #000;
            border-radius: 6px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            color: #000000;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .formato-dymo_450 .dymo-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1.5px solid #000;
            padding-bottom: 4px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }

        /* Hoja 1 Niño: Nombre GIGANTE para visibilidad en ropa */
        .formato-dymo_450 .dymo-nombre-gigante {
            font-size: 22px;
            font-weight: 900;
            text-transform: uppercase;
            text-align: center;
            margin: 6px 0;
            line-height: 1.1;
            word-break: break-word;
        }

        .formato-dymo_450 .dymo-salon-grid {
            display: flex;
            justify-content: space-around;
            background: #000;
            color: #fff;
            padding: 3px 6px;
            font-size: 11px;
            font-weight: 800;
            border-radius: 3px;
            text-transform: uppercase;
        }

        .formato-dymo_450 .dymo-footer-mini {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            font-size: 9px;
            border-top: 1px solid #000;
            padding-top: 4px;
            margin-top: 4px;
        }

        /* Hoja 2 Adulto: 2 columnas compactas (Datos + QR) */
        .formato-dymo_450 .dymo-adulto-layout {
            display: flex;
            gap: 10px;
            align-items: center;
            height: 100%;
            padding: 4px 0;
        }

        .formato-dymo_450 .dymo-adulto-izq {
            flex: 1;
            font-size: 11px;
            line-height: 1.3;
        }

        .formato-dymo_450 .dymo-adulto-der {
            width: 95px;
            text-align: center;
            border-left: 1px dashed #000;
            padding-left: 8px;
        }

        .formato-dymo_450 .dymo-codigo-retiro {
            font-size: 18px;
            font-weight: 900;
            letter-spacing: 2px;
            margin-top: 2px;
        }

        /* ====================================================================
           4. FORMATO ALARGADO (LARGO 20 CM X ANCHO 9 CM)
           Medida: 90mm ancho x 200mm largo
        ==================================================================== */
        .formato-largo_20x9 .ticket-page {
            background: #ffffff;
            width: 340px; /* ~90mm */
            min-height: 755px; /* ~200mm */
            padding: 24px 20px;
            border: 2px solid #1e293b;
            border-radius: 8px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            color: #000000;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .formato-largo_20x9 .largo-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }

        .formato-largo_20x9 .largo-header .iglesia {
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #334155;
        }

        .formato-largo_20x9 .largo-header .titulo {
            font-size: 18px;
            font-weight: 900;
            text-transform: uppercase;
            margin-top: 4px;
            letter-spacing: 0.5px;
        }

        .formato-largo_20x9 .largo-nombre-menor {
            font-size: 20px;
            font-weight: 900;
            text-transform: uppercase;
            text-align: center;
            border: 2px solid #000;
            padding: 8px;
            background: #f8fafc;
            margin: 10px 0;
            line-height: 1.2;
        }

        .formato-largo_20x9 .largo-card {
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            border-radius: 6px;
            padding: 10px 12px;
            margin: 10px 0;
            font-size: 12px;
        }

        .formato-largo_20x9 .largo-card-title {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            color: #475569;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 3px;
        }

        .formato-largo_20x9 .largo-codigo-box {
            text-align: center;
            border: 3px double #000;
            padding: 12px;
            margin: 15px 0;
            background: #fff;
        }

        .formato-largo_20x9 .largo-codigo-box .lbl {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .formato-largo_20x9 .largo-codigo-box .cod {
            font-size: 34px;
            font-weight: 900;
            letter-spacing: 6px;
            line-height: 1.1;
            margin-top: 4px;
        }

        .formato-largo_20x9 .largo-reglas {
            font-size: 10px;
            color: #334155;
            line-height: 1.4;
            padding: 8px;
            background: #f1f5f9;
            border-radius: 4px;
            margin-top: 10px;
        }

        /* ====================================================================
           FILTROS DE PANTALLA / IMPRESIÓN (Solo Niño / Solo Adulto)
        ==================================================================== */
        body.imprimir-solo-nino .hoja-adulto,
        body.imprimir-solo-nino .separador-pantalla,
        body.imprimir-solo-nino .salto-pagina {
            display: none !important;
        }

        body.imprimir-solo-adulto .hoja-nino,
        body.imprimir-solo-adulto .separador-pantalla,
        body.imprimir-solo-adulto .salto-pagina {
            display: none !important;
        }

        /* ====================================================================
           REGLAS DE IMPRESIÓN (@MEDIA PRINT)
        ==================================================================== */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                color: #000000 !important;
            }

            .toolbar-tickets,
            .separador-pantalla,
            .no-print {
                display: none !important;
            }

            .documento-impresion {
                margin: 0 !important;
                gap: 0 !important;
            }

            .ticket-page {
                box-shadow: none !important;
                border-radius: 0 !important;
                margin: 0 auto !important;
            }

            /* Configuración @page según formato */
            body.formato-actual {
                font-size: 12px;
            }

            /* Térmica 80mm */
            body.formato-termica_80mm {
                width: 80mm;
            }

            /* Dymo 450 (102mm x 59mm) */
            body.formato-dymo_450 .ticket-page {
                border: 1px solid #000 !important;
                width: 100mm !important;
                height: 56mm !important;
                min-height: 56mm !important;
                page-break-inside: avoid;
            }

            /* Largo 20x9 cm */
            body.formato-largo_20x9 .ticket-page {
                width: 88mm !important;
                height: 195mm !important;
                min-height: 195mm !important;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body class="formato-{{ $formato }}">

    {{-- ====================================================================
         BARRA FLOTANTE DE CONTROL Y CAMBIO DE MODELO (NO IMPRIMIBLE)
    ==================================================================== --}}
    <div class="toolbar-tickets no-print">
        <div class="toolbar-info">
            <strong>🖨️ Formato de Ticket:</strong>
            <span class="badge-formato" id="etiquetaFormatoActivo">
                @switch($formato)
                    @case('termica_80mm')
                        Térmica 80 mm (POS)
                        @break
                    @case('dymo_450')
                        Dymo LabelWriter 450
                        @break
                    @case('largo_20x9')
                        Largo 20 cm × 9 cm
                        @break
                    @default
                        Estándar 58 mm
                @endswitch
            </span>
            <small style="color: #94a3b8; font-size: 11px;">(Imprime 2 hojas: 1 Niño + 1 Adulto)</small>
        </div>

        {{-- Selector de formatos --}}
        <div class="toolbar-formatos">
            <a href="?formato=actual" class="btn-formato {{ $formato === 'actual' ? 'active' : '' }}" title="Impresora de recibos 58mm">
                <span>🖨️ 58 mm (Actual)</span>
            </a>
            <a href="?formato=termica_80mm" class="btn-formato {{ $formato === 'termica_80mm' ? 'active' : '' }}" title="Impresora POS 80mm estándar">
                <span>🧾 80 mm (POS)</span>
            </a>
            <a href="?formato=dymo_450" class="btn-formato {{ $formato === 'dymo_450' ? 'active' : '' }}" title="Dymo 450 manilla / etiqueta adhesiva">
                <span>🏷️ Dymo 450</span>
            </a>
            <a href="?formato=largo_20x9" class="btn-formato {{ $formato === 'largo_20x9' ? 'active' : '' }}" title="Formato alargado de 20cm x 9cm">
                <span>📄 20 × 9 cm</span>
            </a>
        </div>

        {{-- Acciones de impresión --}}
        <div class="toolbar-acciones">
            <button type="button" class="btn-accion btn-imprimir-todo" onclick="imprimir('todo')" title="Imprimir ambas hojas">
                🖨️ Imprimir Todo
            </button>
            <button type="button" class="btn-accion btn-imprimir-filtro" onclick="imprimir('nino')" title="Imprimir solo gafete del niño">
                🧒 Solo Niño
            </button>
            <button type="button" class="btn-accion btn-imprimir-filtro" onclick="imprimir('adulto')" title="Imprimir solo ticket del adulto">
                👨 Solo Adulto
            </button>
            <button type="button" class="btn-accion btn-cerrar" onclick="window.close()" title="Cerrar pestaña">
                ✕ Cerrar
            </button>
        </div>
    </div>

    {{-- ====================================================================
         CONTENEDOR DEL DOCUMENTO (RENDERIZA EL FORMATO ACTIVO)
    ==================================================================== --}}
    <div class="documento-impresion">

        @php
            $nombreMenor = $registro->menor?->nombre(3) ?? 'Menor no registrado';
            $edadMenor = $registro->menor?->fecha_nacimiento ? $registro->menor->edad() : null;
            $nombreAdulto = $registro->adultoIngreso?->nombre(3) ?? 'Adulto no registrado';
            $telAdulto = $registro->adultoIngreso?->telefono_movil ?? $registro->adultoIngreso?->telefono_otro;
            $nombreSalon = $registro->salon?->nombre ?? 'General';
            $nombreEstacion = $registro->estacion?->nombre ?? 'Principal';
            $nombreReunion = $registro->reporteReunion?->reunion?->nombre ?? 'Servicio';
            $nombreServidor = $registro->servidorIngreso?->nombre(2) ?? auth()->user()?->nombre(2);
            $fechaFormateada = \Carbon\Carbon::parse($registro->fecha)->format('d/m/Y');
            $horaFormateada = \Carbon\Carbon::parse($registro->hora_entrada)->format('h:i A');
            $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($registro->codigo_retiro) . "&format=png";
        @endphp

        {{-- ================================================================
             1. MODELO ACTUAL (58 MM)
        ================================================================ --}}
        @if ($formato === 'actual')
            {{-- HOJA 1: NIÑO (GAFETE DE SALÓN) --}}
            <div class="ticket-page hoja-nino">
                <div class="header">
                    <h1>Iglesia Infantil</h1>
                    <div class="subtitulo">Gafete de Custodia (Salón)</div>
                </div>

                <div class="nombre-destacado">{{ $nombreMenor }}</div>

                <div class="linea">
                    <span class="etiqueta">Edad:</span>
                    <span class="valor">{{ $edadMenor ? $edadMenor . ' años' : 'N/A' }}</span>
                </div>
                <div class="linea">
                    <span class="etiqueta">Salón:</span>
                    <span class="valor">{{ $nombreSalon }}</span>
                </div>
                <div class="linea">
                    <span class="etiqueta">Estación:</span>
                    <span class="valor">{{ $nombreEstacion }}</span>
                </div>

                @if ($registro->indicaciones_medicas)
                    <div class="alerta-medica-box">
                        ⚠️ CUIDADO: {{ $registro->indicaciones_medicas }}
                    </div>
                @endif

                <hr class="separador">

                <div class="linea">
                    <span class="etiqueta">Adulto:</span>
                    <span class="valor">{{ $nombreAdulto }}</span>
                </div>
                @if ($telAdulto)
                    <div class="linea">
                        <span class="etiqueta">Teléfono:</span>
                        <span class="valor">{{ $telAdulto }}</span>
                    </div>
                @endif
                <div class="linea">
                    <span class="etiqueta">Entrada:</span>
                    <span class="valor">{{ $fechaFormateada }} {{ $horaFormateada }}</span>
                </div>
                <div class="linea">
                    <span class="etiqueta">Recibió:</span>
                    <span class="valor">{{ $nombreServidor }}</span>
                </div>

                <div class="codigo-box">
                    <div class="lbl">Código de Retiro</div>
                    <div class="cod">{{ $registro->codigo_retiro }}</div>
                </div>

                <div class="footer">
                    <p>Portar este gafete en el menor durante el servicio.</p>
                    <p>Registro #{{ $registro->id }}</p>
                </div>
            </div>

            <div class="separador-pantalla no-print">✂ - - - - Salto de Página (Corte) - - - - ✂</div>
            <div class="salto-pagina"></div>

            {{-- HOJA 2: ADULTO (TICKET DE RETIRO) --}}
            <div class="ticket-page hoja-adulto">
                <div class="header">
                    <h1>Iglesia Infantil</h1>
                    <div class="subtitulo">Ticket de Retiro (Adulto)</div>
                </div>

                <div class="linea">
                    <span class="etiqueta">Fecha:</span>
                    <span class="valor">{{ $fechaFormateada }}</span>
                </div>
                <div class="linea">
                    <span class="etiqueta">Entrada:</span>
                    <span class="valor">{{ $horaFormateada }}</span>
                </div>
                <div class="linea">
                    <span class="etiqueta">Reunión:</span>
                    <span class="valor">{{ $nombreReunion }}</span>
                </div>

                <hr class="separador">

                <div class="linea">
                    <span class="etiqueta">Menor:</span>
                    <span class="valor">{{ $nombreMenor }}</span>
                </div>
                <div class="linea">
                    <span class="etiqueta">Salón:</span>
                    <span class="valor">{{ $nombreSalon }} › {{ $nombreEstacion }}</span>
                </div>
                <div class="linea">
                    <span class="etiqueta">Adulto:</span>
                    <span class="valor">{{ $nombreAdulto }}</span>
                </div>

                <hr class="separador">

                <div class="codigo-box">
                    <div class="lbl">Código de Retiro</div>
                    <div class="cod">{{ $registro->codigo_retiro }}</div>
                </div>

                <div class="qr-box">
                    <img src="{{ $qrUrl }}" alt="QR {{ $registro->codigo_retiro }}" style="width:115px; height:115px;">
                </div>

                <div class="footer">
                    <p><strong>Conserve este ticket para retirar al menor.</strong></p>
                    <p>Solo el adulto autorizado con este comprobante podrá retirarlo.</p>
                    <p style="margin-top:4px;">Registro #{{ $registro->id }}</p>
                </div>
            </div>
        @endif

        {{-- ================================================================
             2. MODELO TÉRMICA 80 MM (POS ESTÁNDAR)
        ================================================================ --}}
        @if ($formato === 'termica_80mm')
            {{-- HOJA 1: NIÑO (GAFETE DE SALÓN 80MM) --}}
            <div class="ticket-page hoja-nino">
                <div class="header-pos">
                    <div class="iglesia">REDIL CLOUD • IGLESIA INFANTIL</div>
                    <div class="tipo-ticket">GAFETE DEL MENOR</div>
                </div>

                <div class="nombre-nino-80">{{ $nombreMenor }}</div>

                <div class="badges-row">
                    <span class="badge-info">EDAD: {{ $edadMenor ? $edadMenor . ' AÑOS' : 'N/A' }}</span>
                    <span class="badge-info">REGISTRO #{{ $registro->id }}</span>
                </div>

                <table class="tabla-datos">
                    <tr>
                        <td class="lbl">Salón:</td>
                        <td class="val">{{ $nombreSalon }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Estación:</td>
                        <td class="val">{{ $nombreEstacion }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Responsable:</td>
                        <td class="val">{{ $nombreAdulto }}</td>
                    </tr>
                    @if ($telAdulto)
                    <tr>
                        <td class="lbl">Teléfono:</td>
                        <td class="val">{{ $telAdulto }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="lbl">Reunión:</td>
                        <td class="val">{{ $nombreReunion }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Ingreso:</td>
                        <td class="val">{{ $fechaFormateada }} - {{ $horaFormateada }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Atendió:</td>
                        <td class="val">{{ $nombreServidor }}</td>
                    </tr>
                </table>

                @if ($registro->indicaciones_medicas)
                    <div class="alerta-medica-box">
                        ⚠️ ATENCIÓN / ALERGIAS: {{ $registro->indicaciones_medicas }}
                    </div>
                @endif

                <div class="codigo-80">
                    <div class="title">Código de Identificación</div>
                    <div class="numero">{{ $registro->codigo_retiro }}</div>
                </div>

                <div class="linea-corte-pos">
                    ✂ - - - - - - - CORTAR AQUÍ - - - - - - - ✂
                </div>
            </div>

            <div class="separador-pantalla no-print">✂ - - - - Salto de Página (Corte) - - - - ✂</div>
            <div class="salto-pagina"></div>

            {{-- HOJA 2: ADULTO (TICKET DE RETIRO 80MM) --}}
            <div class="ticket-page hoja-adulto">
                <div class="header-pos">
                    <div class="iglesia">REDIL CLOUD • IGLESIA INFANTIL</div>
                    <div class="tipo-ticket">COMPROBANTE DE CUSTODIA</div>
                </div>

                <table class="tabla-datos">
                    <tr>
                        <td class="lbl">Menor:</td>
                        <td class="val">{{ $nombreMenor }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Salón entrega:</td>
                        <td class="val">{{ $nombreSalon }} ({{ $nombreEstacion }})</td>
                    </tr>
                    <tr>
                        <td class="lbl">Adulto autoriz.:</td>
                        <td class="val">{{ $nombreAdulto }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Hora entrada:</td>
                        <td class="val">{{ $fechaFormateada }} {{ $horaFormateada }}</td>
                    </tr>
                </table>

                <div class="codigo-80">
                    <div class="title">Código Único de Retiro</div>
                    <div class="numero">{{ $registro->codigo_retiro }}</div>
                </div>

                <div class="qr-box">
                    <img src="{{ $qrUrl }}" alt="QR {{ $registro->codigo_retiro }}" style="width:130px; height:130px;">
                    <div style="font-size:10px; color:#4b5563; margin-top:4px;">Apunta este código QR al escáner de salida</div>
                </div>

                <div style="font-size:10px; color:#374151; text-align:center; border-top:1px dashed #777; padding-top:6px; margin-top:8px;">
                    <p><strong>IMPORTANTE:</strong> Por la seguridad del menor, este ticket es indispensable para la entrega. Ningún menor será entregado sin validación del código.</p>
                    <p style="margin-top:2px;">Turno #{{ $registro->id }}</p>
                </div>
            </div>
        @endif

        {{-- ================================================================
             3. MODELO DYMO LABELWRITER 450 (MANILLA / ETIQUETA ADHESIVA)
        ================================================================ --}}
        @if ($formato === 'dymo_450')
            {{-- HOJA 1: NIÑO (NAME BADGE ADHESIVO DYMO) --}}
            <div class="ticket-page hoja-nino">
                <div class="dymo-header">
                    <span>IGLESIA INFANTIL</span>
                    <span>EDAD: {{ $edadMenor ? $edadMenor . ' AÑOS' : 'N/A' }}</span>
                </div>

                <div class="dymo-nombre-gigante">
                    {{ $nombreMenor }}
                </div>

                <div class="dymo-salon-grid">
                    <span>SALÓN: {{ $nombreSalon }}</span>
                    <span>EST: {{ $nombreEstacion }}</span>
                </div>

                @if ($registro->indicaciones_medicas)
                    <div style="font-size: 10px; font-weight: bold; border: 1px dashed #000; padding: 2px 4px; margin: 3px 0; text-align:center;">
                        ⚠️ ALERTA: {{ $registro->indicaciones_medicas }}
                    </div>
                @endif

                <div class="dymo-footer-mini">
                    <div>
                        <strong>Resp:</strong> {{ Str::limit($nombreAdulto, 20) }}
                        @if ($telAdulto) | {{ $telAdulto }} @endif
                    </div>
                    <div style="font-weight: 900; font-size: 11px;">
                        #{{ $registro->codigo_retiro }}
                    </div>
                </div>
            </div>

            <div class="separador-pantalla no-print">🏷️ Siguiente Etiqueta Dymo (Salto) 🏷️</div>
            <div class="salto-pagina"></div>

            {{-- HOJA 2: ADULTO (TICKET RETIRO DYMO) --}}
            <div class="ticket-page hoja-adulto">
                <div class="dymo-header">
                    <span>COMPROBANTE DE RETIRO</span>
                    <span>{{ $fechaFormateada }}</span>
                </div>

                <div class="dymo-adulto-layout">
                    <div class="dymo-adulto-izq">
                        <div><strong>Menor:</strong> {{ Str::limit($nombreMenor, 22) }}</div>
                        <div><strong>Salón:</strong> {{ $nombreSalon }} ({{ $nombreEstacion }})</div>
                        <div><strong>Adulto:</strong> {{ Str::limit($nombreAdulto, 22) }}</div>
                        <div style="font-size:9px; color:#333; margin-top:2px;">Entrada: {{ $horaFormateada }}</div>
                        <div style="font-size:8px; color:#555; margin-top:2px;">Presentar para retiro</div>
                    </div>
                    <div class="dymo-adulto-der">
                        <img src="{{ $qrUrl }}" alt="QR" style="width:72px; height:72px;">
                        <div class="dymo-codigo-retiro">{{ $registro->codigo_retiro }}</div>
                    </div>
                </div>

                <div class="dymo-footer-mini">
                    <span>Solo el adulto registrado retira</span>
                    <span>ID #{{ $registro->id }}</span>
                </div>
            </div>
        @endif

        {{-- ================================================================
             4. MODELO LARGO 20 CM X ANCHO 9 CM (200MM X 90MM)
        ================================================================ --}}
        @if ($formato === 'largo_20x9')
            {{-- HOJA 1: NIÑO (FICHA DE CUSTODIA 20x9 CM) --}}
            <div class="ticket-page hoja-nino">
                <div>
                    <div class="largo-header">
                        <div class="iglesia">REDIL CLOUD • GESTIÓN ECLESIÁSTICA</div>
                        <div class="titulo">IGLESIA INFANTIL</div>
                        <div style="font-size:11px; font-weight:700; color:#64748b; margin-top:2px;">FICHA DE CUSTODIA DEL MENOR</div>
                    </div>

                    <div class="largo-nombre-menor">
                        {{ $nombreMenor }}
                    </div>

                    <div class="largo-card">
                        <div class="largo-card-title">1. UBICACIÓN Y SALÓN</div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                            <span><strong>Salón Asignado:</strong></span>
                            <span><strong>{{ $nombreSalon }}</strong></span>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                            <span><strong>Estación:</strong></span>
                            <span>{{ $nombreEstacion }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between;">
                            <span><strong>Edad del Menor:</strong></span>
                            <span>{{ $edadMenor ? $edadMenor . ' años' : 'Sin registrar' }}</span>
                        </div>
                    </div>

                    <div class="largo-card">
                        <div class="largo-card-title">2. CONDICIONES Y CUIDADOS DEL DÍA</div>
                        @if ($registro->indicaciones_medicas)
                            <div style="background:#fef3c7; border:1px solid #f59e0b; color:#92400e; padding:6px; border-radius:4px; font-weight:700;">
                                ⚠️ {{ $registro->indicaciones_medicas }}
                            </div>
                        @else
                            <div style="color:#64748b; font-style:italic;">Sin observaciones médicas reportadas.</div>
                        @endif
                    </div>

                    <div class="largo-card">
                        <div class="largo-card-title">3. DATOS DEL RESPONSABLE</div>
                        <div style="margin-bottom:3px;"><strong>Adulto:</strong> {{ $nombreAdulto }}</div>
                        @if ($telAdulto)
                            <div style="margin-bottom:3px;"><strong>Teléfono:</strong> {{ $telAdulto }}</div>
                        @endif
                        <div><strong>Reunión:</strong> {{ $nombreReunion }}</div>
                    </div>

                    <div class="largo-card">
                        <div class="largo-card-title">4. REGISTRO DE ENTRADA</div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:3px;">
                            <span>Fecha: <strong>{{ $fechaFormateada }}</strong></span>
                            <span>Hora: <strong>{{ $horaFormateada }}</strong></span>
                        </div>
                        <div>Servidor receptor: <strong>{{ $nombreServidor }}</strong></div>
                    </div>
                </div>

                <div>
                    <div class="largo-codigo-box">
                        <div class="lbl">Código de Verificación</div>
                        <div class="cod">{{ $registro->codigo_retiro }}</div>
                    </div>

                    <div style="text-align:center; font-size:10px; color:#64748b; border-top:1px solid #cbd5e1; padding-top:6px;">
                        REDIL Cloud • Control Infantil • Registro #{{ $registro->id }}
                    </div>
                </div>
            </div>

            <div class="separador-pantalla no-print">📄 Salto de Página (Hoja 2 de 20x9 cm) 📄</div>
            <div class="salto-pagina"></div>

            {{-- HOJA 2: ADULTO (BOLETO DE RETIRO 20x9 CM) --}}
            <div class="ticket-page hoja-adulto">
                <div>
                    <div class="largo-header">
                        <div class="iglesia">REDIL CLOUD • SEGURIDAD INFANTIL</div>
                        <div class="titulo">PASE DE RETIRO</div>
                        <div style="font-size:11px; font-weight:700; color:#10b981; margin-top:2px;">COMPROBANTE OFICIAL DE ENTREGA</div>
                    </div>

                    <div class="largo-codigo-box">
                        <div class="lbl">CÓDIGO DE RETIRO AUTORIZADO</div>
                        <div class="cod">{{ $registro->codigo_retiro }}</div>
                    </div>

                    <div class="qr-box">
                        <img src="{{ $qrUrl }}" alt="QR {{ $registro->codigo_retiro }}" style="width:145px; height:145px;">
                        <div style="font-size:11px; font-weight:700; color:#334155; margin-top:6px;">Escanee este código en la salida</div>
                    </div>

                    <div class="largo-card">
                        <div class="largo-card-title">DATOS DE CUSTODIA</div>
                        <div style="margin-bottom:4px;"><strong>Menor a reclamar:</strong> {{ $nombreMenor }}</div>
                        <div style="margin-bottom:4px;"><strong>Salón de recogida:</strong> {{ $nombreSalon }} › {{ $nombreEstacion }}</div>
                        <div style="margin-bottom:4px;"><strong>Adulto responsable:</strong> {{ $nombreAdulto }}</div>
                        <div><strong>Hora de ingreso:</strong> {{ $fechaFormateada }} - {{ $horaFormateada }}</div>
                    </div>
                </div>

                <div>
                    <div class="largo-reglas">
                        <strong>NORMAS ESTRICTAS DE SEGURIDAD:</strong><br>
                        1. Este pase es el único comprobante válido para retirar al menor.<br>
                        2. El personal verificará el código alfanumérico o escaneará el código QR.<br>
                        3. Por protocolo eclesiástico, ningún menor saldrá con terceros sin autorización expresa registrada.
                    </div>

                    <div style="text-align:center; font-size:10px; color:#64748b; border-top:1px solid #cbd5e1; padding-top:6px; margin-top:8px;">
                        Conserve este documento hasta finalizar el servicio • Folio #{{ $registro->id }}
                    </div>
                </div>
            </div>
        @endif

    </div>

    {{-- ====================================================================
         JAVASCRIPT PARA INTERACCIÓN E IMPRESIÓN
    ==================================================================== --}}
    <script>
        /**
         * Función para disparar la impresión según el filtro:
         * 'todo': imprime ambas hojas (Niño + Adulto)
         * 'nino': imprime exclusivamente la hoja del menor
         * 'adulto': imprime exclusivamente el ticket del adulto
         */
        function imprimir(tipo) {
            document.body.classList.remove('imprimir-solo-nino', 'imprimir-solo-adulto');

            if (tipo === 'nino') {
                document.body.classList.add('imprimir-solo-nino');
            } else if (tipo === 'adulto') {
                document.body.classList.add('imprimir-solo-adulto');
            }

            // Esperar que el navegador aplique clases antes de imprimir
            setTimeout(function() {
                window.print();
            }, 150);
        }

        // Auto-impresión inicial (da 750ms para asegurar la carga del QR)
        window.addEventListener('load', function () {
            // Solo auto-imprime si no se especificó noauto
            const urlParams = new URLSearchParams(window.location.search);
            const noAuto = urlParams.get('noauto');
            if (!noAuto) {
                setTimeout(function () {
                    window.print();
                }, 750);
            }
        });
    </script>
</body>
</html>
