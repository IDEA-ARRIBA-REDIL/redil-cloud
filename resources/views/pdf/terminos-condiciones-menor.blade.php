<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Términos y Condiciones - {{ $menor->nombre(3) }}</title>
    <style type="text/css">
        @page {
            margin: 25mm 20mm 20mm 20mm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #2b2f33;
            font-size: 11pt;
            line-height: 1.45;
            margin: 0;
            padding: 0;
        }

        /* --- Encabezado --- */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #303030ff;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .header-table td {
            vertical-align: middle;
        }

        .church-title {
            font-size: 15pt;
            font-weight: bold;
            color: #1c222b;
            text-transform: uppercase;
            margin: 0;
        }

        .church-subtitle {
            font-size: 9pt;
            color: #6e7887;
            margin-top: 3px;
        }

        .doc-badge {
            text-align: right;
        }

        .doc-badge-title {
            display: inline-block;
            background-color: #f1f0fd;
            color: #7367f0;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* --- Título Principal --- */
        .title-section {
            text-align: center;
            margin-bottom: 22px;
        }

        .title-section h1 {
            font-size: 13pt;
            font-weight: bold;
            color: #1c222b;
            margin: 0 0 4px 0;
            text-transform: uppercase;
        }

        .title-section .subtitle {
            font-size: 10pt;
            color: #5d6778;
        }

        /* --- Cajas de Información (Tablas) --- */
        .info-card {
            width: 100%;
            border: 1px solid #e1e4e8;
            border-radius: 6px;
            margin-bottom: 18px;
            border-collapse: collapse;
        }

        .info-card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e1e4e8;
            padding: 8px 12px;
            font-size: 10pt;
            font-weight: bold;
            color: #384252;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-card-body {
            padding: 10px 12px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table td {
            padding: 4px 6px;
            font-size: 9.5pt;
            vertical-align: top;
        }

        .data-label {
            font-weight: bold;
            color: #556070;
            width: 25%;
        }

        .data-value {
            color: #1a1e24;
            width: 25%;
        }

        /* --- Sección de Términos y Condiciones --- */
        .terms-box {
            border: 1px solid #e1e4e8;
            background-color: #fafbfc;
            border-radius: 6px;
            padding: 14px 16px;
            font-size: 9pt;
            color: #333d4b;
            text-align: justify;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .terms-box h3 {
            font-size: 10pt;
            margin-top: 0;
            margin-bottom: 8px;
            color: #1a1e24;
            font-weight: bold;
        }

        /* --- Declaración de Aceptación --- */
        .declaration-box {
            border-left: 4px solid #000000ff;
            background-color: #f7f6fe;
            padding: 10px 14px;
            font-size: 9pt;
            color: #2b2f33;
            text-align: justify;
            margin-bottom: 25px;
        }

        /* --- Firma / Pie de Auditoría --- */
        .signature-table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
        }

        .signature-cell {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 20px;
        }

        .signature-line {
            border-top: 1px solid #6c757d;
            padding-top: 6px;
            font-size: 9pt;
            color: #495057;
            font-weight: bold;
        }

        .signature-role {
            font-size: 8pt;
            color: #6c757d;
        }

        .footer-note {
            text-align: center;
            font-size: 8pt;
            color: #000000ff;
            margin-top: 30px;
            border-top: 1px solid #000000ff;
            padding-top: 8px;
        }
    </style>
</head>
<body>

    <!-- Encabezado Institucional -->
    <table class="header-table">
        <tr>
            <td>
                <div class="church-title">{{ $iglesia->nombre ?? $configuracion->nombre_empresa ?? 'REDIL' }}</div>
                <div class="church-subtitle">
                    @if(!empty($iglesia->nit)) NIT: {{ $iglesia->nit }} &bull; @endif
                    @if(!empty($iglesia->telefono)) Tel: {{ $iglesia->telefono }} &bull; @endif
                    @if(!empty($iglesia->email)) Correo: {{ $iglesia->email }} @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- Título del Documento -->
    <div class="title-section">
        <h1>Certificado de aceptación de términos y condiciones</h1>
        <div class="subtitle">Registro de persona representada / dependiente</div>
    </div>

    <!-- 1. Datos del Acudiente / Representante -->
    <table class="info-card">
        <tr>
            <td class="info-card-header">1. Información del representante legal / acudiente</td>
        </tr>
        <tr>
            <td class="info-card-body">
                <table class="data-table">
                    <tr>
                        <td class="data-label">Nombre completo:</td>
                        <td class="data-value">{{ $acudiente->nombre(3) ?? 'No registrado' }}</td>
                        <td class="data-label">Identificación:</td>
                        <td class="data-value">{{ $acudiente->identificacion ?? 'No registrada' }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Parentesco:</td>
                        <td class="data-value">{{ $nombreParentescoTexto ?? ($tipoParentesco->nombre ?? 'Representante / Acudiente') }}</td>
                        <td class="data-label">Teléfono:</td>
                        <td class="data-value">{{ $acudiente->telefono_movil ?? $acudiente->telefono_fijo ?? 'No registrado' }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Correo electrónico:</td>
                        <td class="data-value" colspan="3">{{ $acudiente->email ?? 'No registrado' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- 2. Datos de la Persona Representada -->
    <table class="info-card">
        <tr>
            <td class="info-card-header">2. Información de la persona representada</td>
        </tr>
        <tr>
            <td class="info-card-body">
                <table class="data-table">
                    <tr>
                        <td class="data-label">Nombre completo:</td>
                        <td class="data-value">{{ $menor->nombre(3) }}</td>
                        <td class="data-label">Identificación:</td>
                        <td class="data-value">{{ $menor->identificacion ?: 'Sin documento' }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Fecha de nacimiento:</td>
                        <td class="data-value">{{ $menor->fecha_nacimiento ? \Carbon\Carbon::parse($menor->fecha_nacimiento)->format('d/m/Y') : 'No registrada' }}</td>
                        <td class="data-label">Género:</td>
                        <td class="data-value">{{ $menor->genero === 1 ? 'Femenino' : ($menor->genero === 0 ? 'Masculino' : 'No especificado') }}</td>
                    </tr>
                    @if(!empty($menor->indicaciones_medicas))
                    <tr>
                        <td class="data-label">Indicaciones médicas:</td>
                        <td class="data-value" colspan="3">{{ $menor->indicaciones_medicas }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <!-- 3. Términos y Condiciones Aceptados -->
    <div class="terms-box">
        <h3>Términos y condiciones</h3>
        @php
            $terminosContenido = $formulario->mensaje_terminos_condiciones_detallado
                ?: ($formulario->mensaje_terminos_condiciones_resumen
                ?: ($formulario->descripcion ?: ''));
        @endphp

        @if(!empty($terminosContenido))
            @if(strip_tags($terminosContenido) !== $terminosContenido)
                {!! $terminosContenido !!}
            @else
                {!! nl2br(e($terminosContenido)) !!}
            @endif
        @else
            Se aceptan los términos y condiciones de tratamiento de datos personales y normas generales de registro de la institución.
        @endif
    </div>

    <!-- 4. Declaración Jurada de Aceptación -->
    <div class="declaration-box">
        <strong>Declaración de consentimiento expreso:</strong><br>
        Yo, <strong>{{ $acudiente->nombre(3) ?? 'el representante legal' }}</strong>, identificado(a) con documento N° <strong>{{ $acudiente->identificacion ?? 'N/A' }}</strong>, en mi calidad de <strong>{{ $nombreParentescoTexto ?? ($tipoParentesco->nombre ?? 'Representante legal / Acudiente') }}</strong>, certifico que he leído, entendido y aceptado voluntariamente la totalidad de los términos, condiciones y políticas de tratamiento de datos personales para la vinculación y registro de <strong>{{ $menor->nombre(3) }}</strong>.
    </div>

    <!-- Firmas / Constancia Digital -->
    <table class="signature-table">
        <tr>
            <td class="signature-cell">
                <div class="signature-line">
                    {{ $acudiente->nombre(3) ?? 'Representante legal / acudiente' }}
                </div>
                <div class="signature-role">
                    {{ $nombreParentescoTexto ?? 'Representante legal' }} &bull; Doc: {{ $acudiente->identificacion ?? 'N/A' }}
                </div>
            </td>
            <td class="signature-cell">
                <div class="signature-line">
                    {{ $iglesia->nombre ?? 'Administración' }}
                </div>
                <div class="signature-role">
                    Fecha y Hora: {{ $fechaAceptacion }}
                </div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Este documento es un comprobante de aceptación electrónica emitido por {{ config('app.name', 'REDIL Cloud') }} en la fecha {{ $fechaAceptacion }}.
    </div>

</body>
</html>
