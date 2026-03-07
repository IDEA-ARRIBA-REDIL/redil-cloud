<!DOCTYPE html>
<html>
<head>
    <title>Confirmación de Compra</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">

    <h2>¡Gracias por tu compra, {{ $compra->nombre_completo_comprador }}!</h2>

    <p>Hemos recibido exitosamente tu pago para la actividad:</p>
    <p><strong>{{ $actividad->nombre }}</strong></p>

    <p>Adjunto a este correo encontrarás tu ticket de compra en formato PDF con todos los detalles y tu código QR de acceso personal.</p>

    <p>¡Te esperamos!</p>

    <br>
    <p>Atentamente,<br>
        El equipo organizador.</p>

</body>
</html>
