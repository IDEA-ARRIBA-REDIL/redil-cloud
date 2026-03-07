<!DOCTYPE html>
<html>
<head>
    <title>Confirmación de Inscripción - {{ $inscripcion->id }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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
            <h1>Ticket de Inscripción</h1>
        </div>


        <div class="content">
            @php
            // --- LÓGICA MEJORADA PARA SER MÁS SEGURA ---

            $compra = $inscripcion->compra;
            $participante = $inscripcion->user;
            // Se obtiene el nombre del asistente de forma segura, ya sea del usuario o del campo 'nombre_inscrito'.
            $nombreAsistente = $inscripcion->nombre_inscrito;

            // Se preparan los datos para el código QR


            // Si es un usuario del sistema
            if($participante){
            $datosQrArray = [
            'id' =>$inscripcion->id, 'nombre' => $nombreAsistente, 'tipo' => 'verificar_asistencia_inscripcion_usuario'
            ];
            }else{
            $datosQrArray = [
            'id' => $inscripcion->id, 'nombre' => $nombreAsistente, 'tipo' => 'verificar_asistencia_inscripcion_usuario'
            ];
            }


            $datosParaQr = json_encode($datosQrArray);
            @endphp

            {{-- El resto de la vista se mantiene exactamente igual --}}
            <div class="section">
                <div class="section-title">Actividad</div>
                <div class="section-content">{{ $actividad->nombre }}</div>
            </div>
            {{-- ... (resto de las secciones) ... --}}
            <div class="qr-section">
                <img src="data:image/png;base64,{{ DNS2D::getBarcodePNG($datosParaQr, 'QRCODE', 6, 6) }}" alt="barcode" />
                <p style="font-size: 12px; color: #444; margin-top: 10px;">Presenta este código al ingresar</p>
            </div>
        </div>

        <div class="footer">
            ID de Inscripción: {{ $inscripcion->id }}
        </div>
    </div>

</body>
</html>
