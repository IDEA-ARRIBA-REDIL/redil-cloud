<!DOCTYPE html>
<html>

    <head>
        <title>Ticket de Compra - {{ $compra->id }}</title>
        {{-- (Los estilos CSS son los mismos que ya tenías) --}}
        <style type="text/css">
            body {
                font-family: "Helvetica", sans-serif;
                margin: 0;
                padding: 0;
                background-color: #f4f4f4;
                color: #444;
            }

            .ticket-container {
                width: 450px;
                margin: 20px auto;
                border: 1px solid #ddd;
                border-radius: 15px;
                background-color: #ffffff;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                overflow: hidden;
                /* Para que el border-radius afecte a los hijos */
            }

            .header {
                background-color: #000000ff;
                /* Un color de cabecera profesional */
                color: #ffffff;
                padding: 20px;
                text-align: center;
            }

            .header h1 {
                margin: 0;
                font-size: 24px;
                text-transform: uppercase;
                letter-spacing: 2px;
            }

            .content {
                padding: 25px;
            }

            .section {
                margin-bottom: 20px;
                padding-bottom: 20px;
                border-bottom: 1px dashed #ccc;
            }

            .section:last-child {
                border-bottom: none;
                margin-bottom: 0;
            }

            .section-title {
                font-size: 12px;
                color: #444;
                text-transform: uppercase;
                margin-bottom: 5px;
            }

            .section-content {
                font-size: 18px;
                font-weight: bold;
            }

            .qr-section {
                text-align: center;
                padding-top: 10px;
            }

            .footer {
                background-color: #f9f9f9;
                color: #444;
                padding: 15px;
                text-align: center;
                font-size: 11px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            td {
                padding: 5px 0;
                vertical-align: top;
            }
        </style>
        {{-- (Estilos CSS de tu plantilla sin cambios) --}}
    </head>

    <body>
        <div class="ticket-container">
            <div class="header">
                <h1>Ticket de Compra</h1>
            </div>
            <div class="content">
                @php
                    $nombreAsistente =
                        $inscripcion?->user?->nombre(3) ?? ($compra->nombre_completo_comprador ?? 'Invitado');
                    if ($inscripcion?->user) {
                        $datosQrArray = [
                            'id' => $inscripcion->user->id,
                            'tipo' => 'verificar_asistencia_inscripcion_usuario',
                        ];
                    } elseif ($inscripcion) {
                        $datosQrArray = [
                            'id' => $inscripcion->id,
                            'tipo' => 'verificar_asistencia_inscripcion_invitado',
                        ];
                    } else {
                        $datosQrArray = [
                            'id' => $matricula?->id ?? $compra->id,
                            'tipo' => 'verificar_matricula_escuela',
                        ];
                    }
                    $datosParaQr = json_encode($datosQrArray);
                @endphp

                {{-- SECCIÓN DE LA ACTIVIDAD --}}
                <div class="section">
                    <div class="section-title">Actividad</div>
                    <div class="section-content">{{ $actividad?->nombre ?? 'Actividad' }}</div>
                </div>

                {{-- SECCIÓN DE FECHA --}}
                @if ($actividad?->fecha_inicio)
                    <div class="section">
                        <div class="section-title">Fecha</div>
                        <div class="section-content">
                            {{ Carbon\Carbon::parse($actividad->fecha_inicio)->translatedFormat('d F, Y') }}</div>
                    </div>
                @endif

                {{-- SECCIÓN DEL PARTICIPANTE --}}
                <div class="section">
                    <div class="section-title">Participante</div>
                    <div class="section-content">{{ $nombreAsistente }}</div>
                </div>

                {{-- ===================== NUEVA SECCIÓN DE MATRÍCULA (ESCUELAS) ===================== --}}
                @if (isset($matricula) && $matricula)
                    <div class="section">
                        <div class="section-title">Detalles de Matrícula</div>
                        <table>
                            <tr>
                                <td style="width: 50%;">
                                    <div class="section-title">Sede de Matrícula (Clases)</div>
                                    <div class="section-content">
                                        {{ $matricula->sede?->nombre ?? $matricula->horarioMateriaPeriodo?->horarioBase?->aula?->sede?->nombre ?? 'N/A' }}
                                    </div>
                                </td>
                                <td style="width: 50%;">
                                    <div class="section-title">Sede Entrega de Material</div>
                                    <div class="section-content">{{ $matricula->materialSede?->nombre ?? $compra->destinatario?->nombre ?? 'N/A' }}</div>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 50%;">
                                    <div class="section-title">Horario</div>
                                    <div class="section-content">
                                        @php
                                            $horarioBase = $matricula->horarioMateriaPeriodo?->horarioBase;
                                            $dias = [
                                                0 => 'Dom',
                                                1 => 'Lun',
                                                2 => 'Mar',
                                                3 => 'Mié',
                                                4 => 'Jue',
                                                5 => 'Vie',
                                                6 => 'Sáb',
                                                7 => 'Dom',
                                            ];
                                            $dia = isset($horarioBase->dia) ? ($dias[$horarioBase->dia] ?? 'N/D') : 'N/D';
                                            $ini = !empty($horarioBase->hora_inicio) ? \Carbon\Carbon::parse($horarioBase->hora_inicio)->format('h:i A') : '';
                                            $fin = !empty($horarioBase->hora_fin) ? \Carbon\Carbon::parse($horarioBase->hora_fin)->format('h:i A') : '';
                                            $aula = $horarioBase?->aula?->nombre ?? 'N/D';
                                        @endphp
                                        {{ $dia }} | {{ $ini }} - {{ $fin }}
                                    </div>
                                </td>
                                <td style="width: 50%;">
                                    <div class="section-title">Aula</div>
                                    <div class="section-content">
                                        {{ $aula }}
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                @endif
                {{-- =============================================================================== --}}

                {{-- ===================== NUEVA SECCIÓN DE PAGO ===================== --}}
                @if ($pago)
                    <div class="section">
                        <div class="section-title">Resumen de Pago</div>
                        <table>
                            <tr>
                                <td style="width: 50%;">
                                    <div class="section-title">Valor Pagado</div>
                                    <div class="section-content">{{ number_format($pago->valor, 2) }}
                                        {{ $pago->moneda?->nombre_corto }}</div>
                                </td>
                                <td style="width: 50%;">
                                    <div class="section-title">Estado</div>
                                    <div class="section-content">{{ $pago->estadoPago?->nombre ?? 'Aprobado' }}</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                @endif
                {{-- =================================================================== --}}

                {{-- SECCIÓN DEL CÓDIGO QR --}}
                <div class="qr-section">
                    <img src="data:image/png;base64,{{ DNS2D::getBarcodePNG($datosParaQr, 'QRCODE', 6, 6) }}"
                        alt="barcode" />
                    <p style="font-size: 12px; color: #444; margin-top: 10px;">Presenta este código al ingresar</p>
                </div>
            </div>
            <div class="footer">
                ID de Compra: {{ $compra->id }}
            </div>
        </div>
    </body>

</html>
