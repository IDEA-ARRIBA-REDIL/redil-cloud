<!DOCTYPE html>
<html>
<head>
    <title>Bienvenido a REDIL Cloud</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <h2 style="color: #696cff;">¡Hola! Tu cuenta para {{ $tenant->church_name ?? 'tu iglesia' }} está lista.</h2>
    <p>Hemos terminado de configurar tu entorno en la nube.</p>
    <p>Puedes acceder a tu panel de administración a través del siguiente enlace:</p>
    <p>
        <a href="http://{{ $domain }}:8000" style="display: inline-block; padding: 10px 20px; background-color: #696cff; color: #fff; text-decoration: none; border-radius: 5px;">Ir a mi plataforma</a>
    </p>
    <p>Utiliza las credenciales que proporcionaste durante el registro para iniciar sesión como Administrador Principal.</p>
    <br>
    <p>Gracias,</p>
    <p>El equipo de <strong>REDIL Cloud</strong></p>
</body>
</html>
