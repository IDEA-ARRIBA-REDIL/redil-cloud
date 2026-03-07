<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Recibo de Ingreso #{{ $ingreso->id }}</title>
<style>
    body {
        font-family: 'Helvetica', 'Arial', sans-serif;
        color: #333;
        font-size: 12px;
    }

    .container {
        width: 100%;
        margin: 0 auto;
    }

    .header {
        text-align: center;
        margin-bottom: 20px;
    }

    .header h1 {
        margin: 0;
        font-size: 24px;
    }

    .header p {
        margin: 5px 0;
    }

    .content {
        width: 100%;
    }

    .content table {
        width: 100%;
        border-collapse: collapse;
    }

    .content th,
    .content td {
        padding: 8px;
        text-align: left;
    }

    .info-table td:first-child {
        font-weight: bold;
        width: 150px;
    }

    .footer {
        text-align: center;
        margin-top: 40px;
        font-size: 10px;
        color: #777;
    }

    hr {
        border: 0;
        border-top: 1px solid #eee;
    }

</style>

<div class="container">
    <div class="header">
        <img src="{{ public_path('/storage/'.$configuracion->ruta_almacenamiento . '/img/iglesia/'.$iglesia->logo) }}" style="width: 100px; height: auto; margin-bottom: 10px;">
        <h1>Recibo de Ingreso</h1>
        <p>ID del Ingreso: {{ $ingreso->id }}</p>
    </div>

    <div class="content">
        <table class="info-table">
            <tr>
                <td>Fecha:</td>
                <td>{{ date('d/m/Y', strtotime($ingreso->fecha)) }}</td>
            </tr>
            <tr>
                <td>Recibido de:</td>
                <td>{{ $ingreso->nombre }}</td>
            </tr>
            <tr>
                <td>Identificación:</td>
                <td>{{ $ingreso->tipo_identificacion }} - {{ $ingreso->identificacion }}</td>
            </tr>
            <tr>
                <td colspan="2">
                    <hr>
                </td>
            </tr>
            <tr>
                <td>Valor del Ingreso:</td>
                <td><strong>$ {{ number_format($ingreso->valor, 2, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td>Tipo de Ingreso:</td>
                <td>{{ $ingreso->tipoOfrendas->nombre }}</td>
            </tr>
            <tr>
                <td>Caja Receptora:</td>
                <td>{{ $ingreso->cajasFinanzas->nombre }}</td>
            </tr>
            <tr>
                <td>Descripción:</td>
                <td>{{ $ingreso->descripcion? : 'Sin descripción.' }}</td>
            </tr>
        </table>
    </div>
    <div class="footer">
        <p>Documento generado por el sistema.</p>
        <p>Fecha de impresión: {{ date('d/m/Y H:i:s') }}</p>
    </div>
</div>
