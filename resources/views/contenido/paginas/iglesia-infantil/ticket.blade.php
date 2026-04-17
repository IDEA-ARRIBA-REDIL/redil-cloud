<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Retiro — Iglesia Infantil</title>
    <style>
        /* =====================================================
           Estilos para ticket térmico (58mm/80mm)
           Max-width: 300px para impresora de 58mm
        ===================================================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 20px;
            min-height: 100vh;
        }

        .ticket {
            background: white;
            width: 300px;
            padding: 16px 14px;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        }

        .header {
            text-align: center;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .header .subtitulo {
            font-size: 11px;
            color: #555;
            margin-top: 2px;
        }

        .seccion {
            margin-bottom: 8px;
        }

        .linea {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            padding: 2px 0;
        }

        .linea .etiqueta {
            color: #666;
            min-width: 80px;
        }

        .linea .valor {
            font-weight: bold;
            text-align: right;
            word-break: break-word;
        }

        .separador {
            border: none;
            border-top: 1px dashed #ccc;
            margin: 10px 0;
        }

        .codigo-retiro {
            text-align: center;
            background: #f0f0f0;
            border-radius: 4px;
            padding: 10px;
            margin: 10px 0;
        }

        .codigo-retiro .label {
            font-size: 10px;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .codigo-retiro .codigo {
            font-size: 26px;
            font-weight: bold;
            letter-spacing: 4px;
        }

        .qr-container {
            text-align: center;
            margin: 10px 0;
        }

        .qr-container img {
            width: 120px;
            height: 120px;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            color: #888;
            border-top: 1px dashed #ccc;
            padding-top: 8px;
            margin-top: 8px;
        }

        .estado-badge {
            display: inline-block;
            background: #ffc107;
            color: #000;
            font-size: 10px;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 4px auto;
        }

        .acciones-ticket {
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 300px;
            margin: 16px auto 0;
        }

        .acciones-ticket button {
            width: 100%;
            padding: 10px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            font-weight: bold;
        }

        /* Al imprimir: ocultar botones y ajustar */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .acciones-ticket {
                display: none !important;
            }
            .ticket {
                box-shadow: none;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>

    <div>
        {{-- TICKET --}}
        <div class="ticket">

            {{-- Encabezado --}}
            <div class="header">
                <h1>Iglesia Infantil</h1>
                <div class="subtitulo">Ticket de Retiro</div>
            </div>

            {{-- Fecha y reunión --}}
            <div class="seccion">
                <div class="linea">
                    <span class="etiqueta">Fecha:</span>
                    <span class="valor">{{ \Carbon\Carbon::parse($registro->fecha)->format('d/m/Y') }}</span>
                </div>
                <div class="linea">
                    <span class="etiqueta">Entrada:</span>
                    <span class="valor">{{ \Carbon\Carbon::parse($registro->hora_entrada)->format('h:i A') }}</span>
                </div>
                <div class="linea">
                    <span class="etiqueta">Reunión:</span>
                    <span class="valor">{{ $registro->reporteReunion?->reunion?->nombre ?? 'N/A' }}</span>
                </div>
            </div>

            <hr class="separador">

            {{-- Datos del menor --}}
            <div class="seccion">
                <div class="linea">
                    <span class="etiqueta">Menor:</span>
                    <span class="valor">{{ $registro->menor?->nombre(3) }}</span>
                </div>
                <div class="linea">
                    <span class="etiqueta">Adulto:</span>
                    <span class="valor">{{ $registro->adultoIngreso?->nombre(3) }}</span>
                </div>
            </div>

            <hr class="separador">

            {{-- Salón y estación --}}
            <div class="seccion">
                <div class="linea">
                    <span class="etiqueta">Salón:</span>
                    <span class="valor">{{ $registro->salon?->nombre }}</span>
                </div>
                <div class="linea">
                    <span class="etiqueta">Estación:</span>
                    <span class="valor">{{ $registro->estacion?->nombre }}</span>
                </div>
            </div>

            <hr class="separador">

            {{-- Estado --}}
            <div style="text-align:center; margin-bottom:4px;">
                <span class="estado-badge">
                    {{ $registro->estaEnCustodia() ? '🟡 En custodia' : '✅ Entregado' }}
                </span>
            </div>

            {{-- Código de retiro --}}
            <div class="codigo-retiro">
                <div class="label">Código de retiro</div>
                <div class="codigo">{{ $registro->codigo_retiro }}</div>
            </div>

            {{-- QR (usando Google Charts API como fallback sin dependencias) --}}
            <div class="qr-container">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($registro->codigo_retiro) }}&format=png"
                    alt="QR {{ $registro->codigo_retiro }}"
                    onerror="this.style.display='none'">
            </div>

            {{-- Pie --}}
            <div class="footer">
                <p>Conserve este ticket para retirar al menor.</p>
                <p>Solo el adulto que registró el ingreso puede retirar al menor.</p>
                <p style="margin-top:4px;">Registro #{{ $registro->id }}</p>
            </div>
        </div>

        {{-- Botones de acción (ocultos al imprimir) --}}
        <div class="acciones-ticket">
            <button onclick="window.print()">
                🖨️ Imprimir / Guardar PDF
            </button>
            <button onclick="window.close()" style="background:#6c757d; margin-top:8px;">
                ✕ Cerrar
            </button>
        </div>
    </div>

</body>
<script>
    // Auto-imprimir al cargar: da 800ms para que cargue el QR
    window.addEventListener('load', function () {
        setTimeout(function () {
            window.print();
        }, 800);
    });
</script>
</html>
