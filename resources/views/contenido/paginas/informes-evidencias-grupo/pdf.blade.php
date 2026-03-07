<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe de Evidencia - {{ $informe->nombre }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .info-section {
            margin-bottom: 20px;
            background-color: #f8f7fa;
            padding: 15px;
            border-radius: 5px;
        }
        .info-grid {
            width: 100%;
        }
        .info-label {
            font-weight: bold;
            color: #5d596c;
            width: 150px;
        }
        .content-section {
            margin-bottom: 25px;
        }
        .content-title {
            font-size: 18px;
            font-weight: bold;
            color: #3b3b3bff;
            border-left: 4px solid #3b3b3bff;
            padding-left: 10px;
            margin-bottom: 10px;
        }
        .content-body {
            padding: 10px 0;
            font-size: 14px;
        }
        /* Estilos básicos para el contenido de Quill */
        .content-body p { margin-bottom: 10px; }
        .content-body ul, .content-body ol { margin-left: 20px; }
        .content-body img { max-width: 100%; height: auto; }
    </style>
</head>
<body>

    <div class="info-section">
        <table class="info-grid">
            <tr>
                <td class="info-label">Nombre Informe:</td>
                <td colspan="3">{{ $informe->nombre }}</td>
            </tr>
            <tr>
                <td class="info-label">Grupo:</td>
                <td>{{ $grupo->nombre }}</td>
            </tr>
            <tr>
                <td class="info-label">Fecha:</td>
                <td>{{ $informe->fecha }}</td>
            </tr>
           
        </table>
    </div>

    @for($i = 1; $i <= 3; $i++)
        @php
            $habilitar = "habilitar_campo_{$i}_informe_evidencias_grupo";
            $labelConf = "label_campo_{$i}_informe_evidencias_grupo";
            $campoName = "campo{$i}";
            $content = $informe->$campoName;
        @endphp

        @if($configuracion->$habilitar && !empty($content))
            <div class="content-section">
                <div class="content-title">{{ $configuracion->$labelConf ?? "Campo {$i}" }}</div>
                <div class="content-body">
                    {!! $content !!}
                </div>
            </div>
        @endif
    @endfor
</body>
</html>
