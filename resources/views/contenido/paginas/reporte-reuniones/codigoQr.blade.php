<!DOCTYPE html>
<html>
<head>
    <title>Reserva QR - {{ $reserva->id }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
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
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            overflow: hidden; /* Para que el border-radius afecte a los hijos */
        }
        .header {
            background-color: #000000ff; /* Un color de cabecera profesional */
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
</head>
<body>

<div class="ticket-container">
    <div class="header">
        <h1>Ticket de Reserva</h1>
    </div>

    <div class="content">
        {{-- SECCIÓN DE LA REUNIÓN --}}
        <div class="section">
            <div class="section-title">Reunión</div>
            <div class="section-content">{{ $reunion->nombre }}</div>
        </div>

        <div class="section">
            <div class="section-title">Lugar</div>
            <div class="section-content">{{ $reunion->sede->nombre }}</div>
        </div>

        {{-- SECCIÓN DE FECHA Y HORA --}}
        <div class="section">
            <table>
                <tr>
                    <td style="width: 50%;">
                        <div class="section-title">Fecha</div>
                        <div class="section-content">{{ Carbon\Carbon::parse($reporte->fecha)->translatedFormat('d F, Y') }}</div>
                    </td>
                    <td style="width: 50%;">
                        <div class="section-title">Hora</div>
                        <div class="section-content">{{ Carbon\Carbon::parse($reunion->hora)->format('g:i A') }}</div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- SECCIÓN DEL ASISTENTE --}}
        <div class="section">
            <div class="section-title">Asistente</div>
            <div class="section-content">{{ $nombreAsistente }}</div>
        </div>

        {{-- SECCIÓN DEL CÓDIGO QR --}}
        <div class="qr-section">
            <img src="data:image/png;base64,{{ DNS2D::getBarcodePNG($dataQr, 'QRCODE', 6, 6) }}" alt="barcode"/>
            <p style="font-size: 12px; color: #444; margin-top: 10px;">Presenta este código al ingresar</p>
        </div>
    </div>

    <div class="footer">
        ID de Reserva: {{ $reserva->id }}
    </div>
</div>

</body>
</html>
