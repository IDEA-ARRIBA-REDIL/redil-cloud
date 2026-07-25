<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="utf-8">
    <title>Comprobante de Pago #{{ $pago->id }}</title>
    <style>
        @page {
            margin: 20px 30px;
        }
        body {
            font-family: 'Helvetica', 'DejaVu Sans', Arial, sans-serif;
            color: #2b2b2b;
            font-size: 11px;
            line-height: 1.3;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 8px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .logo {
            max-width: 130px;
            max-height: 50px;
        }
        .org-name {
            font-size: 16px;
            font-weight: bold;
            color: #1e293b;
            margin: 0;
            text-transform: uppercase;
        }
        .doc-title {
            text-align: right;
        }
        .doc-title h2 {
            margin: 0;
            font-size: 16px;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-title p {
            margin: 2px 0 0 0;
            font-size: 10px;
            color: #64748b;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            color: #ffffff;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            margin-top: 4px;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #334155;
            text-transform: uppercase;
            background-color: #f1f5f9;
            padding: 5px 8px;
            border-left: 3px solid #3b82f6;
            margin-top: 10px;
            margin-bottom: 6px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .data-table td {
            padding: 4px 6px;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
            color: #475569;
            width: 30%;
        }
        .value {
            color: #0f172a;
            width: 70%;
        }
        .grid-2 {
            width: 100%;
            border-collapse: collapse;
        }
        .grid-2 > tbody > tr > td {
            width: 50%;
            vertical-align: top;
            padding-right: 8px;
        }
        .grid-2 > tbody > tr > td:last-child {
            padding-right: 0;
            padding-left: 8px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 10px;
        }
        .items-table th {
            background-color: #f8fafc;
            color: #334155;
            font-size: 10px;
            text-transform: uppercase;
            padding: 6px;
            border-bottom: 1px solid #cbd5e1;
            text-align: left;
        }
        .items-table td {
            padding: 6px;
            border-bottom: 1px solid #f1f5f9;
        }
        .total-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 12px;
            text-align: right;
            margin-top: 6px;
        }
        .total-box .total-label {
            font-size: 11px;
            font-weight: bold;
            color: #475569;
        }
        .total-box .total-amount {
            font-size: 16px;
            font-weight: bold;
            color: #059669;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
        .footer-table td {
            vertical-align: middle;
        }
        .qr-box {
            text-align: center;
        }
        .footer-text {
            font-size: 9px;
            color: #94a3b8;
            line-height: 1.2;
        }
    </style>
</head>
<body>

    <!-- Encabezado con Logo y Datos de Organización -->
    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                @if(isset($iglesia->logo) && !empty($iglesia->logo) && file_exists(public_path('storage/tenant' . tenant('id') . '/img/iglesia/' . $iglesia->logo)))
                    <img src="{{ public_path('storage/tenant' . tenant('id') . '/img/iglesia/' . $iglesia->logo) }}" class="logo" alt="Logo">
                @else
                    <h1 class="org-name">{{ $iglesia->nombre ?? 'REDIL CLOUD' }}</h1>
                @endif
            </td>
            <td class="doc-title" style="width: 40%;">
                <h2>COMPROBANTE DE PAGO</h2>
                <p><strong>Recibo N°:</strong> #{{ $pago->id }}</p>
                <p><strong>Fecha:</strong> {{ date('d/m/Y h:i A', strtotime($pago->fecha)) }}</p>
                <div class="status-badge" style="background-color: {{ $colorEncabezado }};">
                    {{ $pago->estadoPago->nombre ?? 'CONFIRMADO' }}
                </div>
            </td>
        </tr>
    </table>

    <table class="grid-2">
        <tr>
            <!-- Datos del Comprador -->
            <td>
                <div class="section-title">Información del Comprador</div>
                @php
                    $comprador = $pago->compra->user;
                @endphp
                <table class="data-table">
                    <tr>
                        <td class="label">Nombre:</td>
                        <td class="value"><strong>{{ $comprador?->nombre(3) ?? 'N/A' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="label">Identificación:</td>
                        <td class="value">{{ $comprador?->tipo_identificacion ?? 'CC' }} - {{ $comprador?->identificacion ?? 'N/D' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Email:</td>
                        <td class="value">{{ $comprador?->email ?? 'N/D' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Teléfono:</td>
                        <td class="value">{{ $comprador?->telefono_movil ?? 'N/D' }}</td>
                    </tr>
                </table>
            </td>

            <!-- Detalles de la Transacción -->
            <td>
                <div class="section-title">Detalles de la Transacción</div>
                <table class="data-table">
                    <tr>
                        <td class="label">ID Compra:</td>
                        <td class="value">#{{ $pago->compra_id }}</td>
                    </tr>
                    <tr>
                        <td class="label">Método Pago:</td>
                        <td class="value">{{ $pago->tipoPago->nombre ?? 'PSE / ZonaPagos' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Referencia:</td>
                        <td class="value">{{ $pago->referencia_pago ?? $pago->id }}</td>
                    </tr>
                    <tr>
                        <td class="label">Moneda:</td>
                        <td class="value">{{ $pago->moneda->nombre_corto ?? 'COP' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Detalles Específicos de Matrícula (Si aplica) -->
    @if(isset($matricula) && $matricula)
        <div class="section-title">Detalles de Matrícula (Escuela)</div>
        <table class="data-table">
            <tr>
                <td class="label" style="width: 20%;">Escuela:</td>
                <td class="value" style="width: 80%;"><strong>{{ $matricula->escuela->nombre ?? ($pago->compra->actividad->nombre ?? 'N/A') }}</strong></td>
            </tr>
            @if($matricula->horarioMateriaPeriodo && $matricula->horarioMateriaPeriodo->materiaPeriodo)
            <tr>
                <td class="label">Materia:</td>
                <td class="value">{{ $matricula->horarioMateriaPeriodo->materiaPeriodo->materia->nombre ?? 'N/D' }}</td>
            </tr>
            @endif
            @if($matricula->horarioMateriaPeriodo && $matricula->horarioMateriaPeriodo->horarioBase)
                @php
                    $hb = $matricula->horarioMateriaPeriodo->horarioBase;
                    $dias = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
                    $diaTexto = $dias[$hb->dia] ?? 'N/D';
                    $ini = \Carbon\Carbon::parse($hb->hora_inicio)->format('h:i A');
                    $fin = \Carbon\Carbon::parse($hb->hora_fin)->format('h:i A');
                    $aulaTexto = $hb->aula->nombre ?? 'N/D';
                    $sedeClase = $hb->aula->sede->nombre ?? 'N/D';
                @endphp
                <tr>
                    <td class="label">Horario:</td>
                    <td class="value">{{ $diaTexto }} de {{ $ini }} a {{ $fin }}</td>
                </tr>
                <tr>
                    <td class="label">Sede y Aula:</td>
                    <td class="value">{{ $sedeClase }} - {{ $aulaTexto }}</td>
                </tr>
            @endif
            @if($matricula->materialSede)
                <tr>
                    <td class="label">Sede Material:</td>
                    <td class="value"><strong>{{ $matricula->materialSede->nombre }}</strong> (Lugar de reclamo del material)</td>
                </tr>
            @endif
        </table>
    @else
        <div class="section-title">Actividad</div>
        <table class="data-table">
            <tr>
                <td class="label" style="width: 20%;">Nombre:</td>
                <td class="value" style="width: 80%;"><strong>{{ $pago->compra->actividad->nombre ?? 'Actividad General' }}</strong></td>
            </tr>
        </table>
    @endif

    <!-- Tabla Resumen de Ítems / Valores -->
    <div class="section-title">Resumen de Conceptos</div>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 70%;">Concepto / Descripción</th>
                <th style="width: 30%; text-align: right;">Valor</th>
            </tr>
        </thead>
        <tbody>
            @if($pago->compra->carritos && $pago->compra->carritos->isNotEmpty())
                @foreach($pago->compra->carritos as $item)
                    <tr>
                        <td>{{ $item->categoria->nombre ?? $pago->compra->actividad->nombre }} (x{{ $item->cantidad }})</td>
                        <td style="text-align: right;">$ {{ number_format($item->precio * $item->cantidad, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td>Pago de registro - {{ $pago->compra->actividad->nombre ?? 'Inscripción' }}</td>
                    <td style="text-align: right;">$ {{ number_format($pago->valor, 2, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Caja de Total -->
    <div class="total-box">
        <span class="total-label">VALOR TOTAL PAGADO: </span>
        <span class="total-amount">$ {{ number_format($pago->valor, 2, ',', '.') }} {{ $pago->moneda->nombre_corto ?? 'COP' }}</span>
    </div>

    <!-- Pie de página y Código QR -->
    <table class="footer-table">
        <tr>
            <td style="width: 75%;" class="footer-text">
                <p style="margin: 0 0 4px 0;"><strong>Comprobante Oficial de Pago</strong></p>
                <p style="margin: 0;">Este documento acredita el registro y pago correspondiente en la plataforma. Consérvelo para cualquier consulta o verificación adicional.</p>
                <p style="margin: 4px 0 0 0;">Fecha de emisión: {{ date('d/m/Y H:i:s') }}</p>
            </td>
            <td style="width: 25%;" class="qr-box">
                @if(isset($datosParaQr))
                    <img src="data:image/png;base64,{{ DNS2D::getBarcodePNG($datosParaQr, 'QRCODE', 3, 3) }}" alt="QR Code" style="margin-bottom: 2px;">
                    <br><span style="font-size: 8px; color: #64748b;">Verificación QR</span>
                @endif
            </td>
        </tr>
    </table>

</body>
</html>
