<!DOCTYPE html>
<html lang="es">
<head>
    <title>Carnet de Identificación</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style type="text/css">
        /* --- Estilos Generales --- */
        body {
            font-family: "Helvetica", sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f2f5; /* Un fondo neutro para la vista previa */
            color: #333;
        }

        /* --- Contenedor Principal del Carnet --- */
        .card-container {
            width: 350px; /* Ancho estándar de un carnet */
            margin: 30px auto;
            border-radius: 15px;
            background-color: #ffffff;
            /* Sombra sutil para darle profundidad */
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden; /* Importante para que el border-radius afecte a los hijos */
        }

        /* --- Cabecera con el Rol/Tipo de Usuario --- */
        .card-header {
            background-color: #000000ff; /* Un color corporativo oscuro */
            color: #ffffff;
            padding: 15px 20px;
            text-align: center;
        }
        .card-header h2 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        /* --- Contenido Principal del Carnet --- */
        .card-content {
            padding: 25px;
            text-align: center;
        }

        /* --- Estilo de la Foto de Perfil --- */
        .profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%; /* Foto circular */
            border: 4px solid #ffffff;
            /* Sombra interior para resaltar la foto */
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            margin: -80px auto 15px auto; /* La magia para que la foto se superponga */
            position: relative;
            background-color: #eee;
        }

        /* --- Detalles del Usuario --- */
        .user-details h3 {
            margin: 0 0 5px 0;
            font-size: 22px;
            font-weight: bold;
            color: #2c3e50;
        }
        .user-details p {
            margin: 0;
            font-size: 14px;
            color: #3b3b3bff;
        }

        /* --- Separador y QR --- */
        .qr-section {
            padding: 20px;
            border-top: 1px solid #eeeeee;
        }
        .qr-code {
            width: 180px;
            height: 180px;
            margin: 0 auto;
        }
    </style>
</head>
<body>

<div class="card-container">
    <!-- Cabecera del Carnet -->
    <div class="card-header">
        <h2>{{ $usuario->tipoUsuario->nombre ?? 'Miembro' }}</h2>
    </div>
    <br><br><br><br>

    <!-- Contenido Principal -->
    <div class="card-content">
        <!-- Foto de Perfil -->
        <img src="{{ $foto }}" alt="Foto de perfil" class="profile-picture">

        <!-- Detalles del Usuario -->
        <div class="user-details">
            <h3>{{ $usuario->nombre(4) }}</h3>
            @if($usuario->identificacion)
                <p>
                    {{ $usuario->tipoIdentificacion->nombre ?? 'ID' }}: {{ $usuario->identificacion }}
                </p>
            @endif
        </div>
    </div>

    <!-- Sección del Código QR -->
    <div class="qr-section">
      <center>
        <img src="data:image/png;base64,{{ DNS2D::getBarcodePNG($dataQr, 'QRCODE', 7, 7) }}" alt="barcode" class="qr-code"/>
      </center>
    </div>
</div>

</body>
</html>
