<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="x-apple-disable-message-reformatting" />
  <meta name="format-detection" content="telephone=no,date=no,address=no,email=no" />
  <title>Recordatorio: Reporte de grupo pendiente</title>
  <!--[if mso]><noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript><![endif]-->
  <style type="text/css">
    * { box-sizing: border-box; }
    body, table, td, a { -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; }
    table, td { mso-table-lspace:0pt; mso-table-rspace:0pt; }
    img { -ms-interpolation-mode:bicubic; border:0; outline:none; text-decoration:none; display:block; }
    body { margin:0 !important; padding:0 !important; background-color:#F0EFE9; }
    a { color:inherit; text-decoration:none; }

    @media only screen and (max-width:620px) {
      .email-wrapper { width:100% !important; }
      .card-pad      { padding:28px 20px !important; }
      .btn-cta       { display:block !important; width:100% !important; text-align:center !important; }
    }
  </style>
</head>
<body style="margin:0;padding:0;background-color:#F0EFE9;">
@php
  $iglesiaObj = $iglesia ?? \App\Models\Iglesia::first();
@endphp

<!-- PREHEADER: texto invisible que aparece en preview del inbox -->
<div style="display:none;font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;font-family:sans-serif;">
  Tienes reportes de grupo pendientes por realizar esta semana.&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F0EFE9;">
<tr><td align="center" style="padding:24px 16px 40px;">

  <table role="presentation" class="email-wrapper" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;">

    <!-- ─── HEADER ─── -->
    <tr>
      <td align="center" style="background-color:#FFFFFF;border-radius:16px 16px 0 0;padding:20px 32px;border-bottom:1px solid #EBEBEB;">
        @include('_partials.logo_iglesia', ['logo_negro' => true, 'width' => '160px'])
      </td>
    </tr>

    <!-- ─── CUERPO ─── -->
    <tr>
      <td style="background-color:#FFFFFF;padding:40px 32px;" class="card-pad">

        <!-- Eyebrow -->
        <p style="font-family:Arial,sans-serif;font-size:10px;font-weight:700;color:#0099d9;letter-spacing:2.5px;text-transform:uppercase;margin:0 0 14px 0;">
          RECORDATORIO · REPORTES PENDIENTES
        </p>

        <!-- Titular -->
        <h1 style="font-family:Georgia,'Times New Roman',serif;font-size:26px;font-weight:700;color:#040407;line-height:1.25;margin:0 0 20px 0;">
          ¡Hola, {{ $encargado->nombre(3) }}!
        </h1>

        <!-- Párrafo de Contexto -->
        <p style="font-family:Arial,sans-serif;font-size:15px;color:#374151;line-height:1.75;margin:0 0 24px 0;">
          Esperamos que estés teniendo una semana bendecida. Te escribimos para recordarte que tienes reportes pendientes por realizar o finalizar para la semana en curso.
        </p>

        <!-- Subtítulo de Sección -->
        <h3 style="font-family:Georgia,'Times New Roman',serif;font-size:18px;font-weight:700;color:#040407;margin:0 0 12px 0;">
          Tus grupos pendientes:
        </h3>

        <!-- Tabla Estilizada de Grupos -->
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="width: 100%; border-collapse: collapse; margin-bottom: 28px; border: 1px solid #EBEBEB;">
          <thead>
            <tr style="background-color: #F8F8F6;">
              <th style="font-family: Arial, sans-serif; font-size: 11px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 16px; text-align: left; border-bottom: 1px solid #EBEBEB;">Nombre del grupo</th>
              <th style="font-family: Arial, sans-serif; font-size: 11px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 16px; text-align: right; border-bottom: 1px solid #EBEBEB;">Estado actual</th>
            </tr>
          </thead>
          <tbody>
            @foreach($gruposPendientes as $grupo)
            <tr>
              <td style="font-family: Arial, sans-serif; font-size: 14px; color: #374151; padding: 12px 16px; border-bottom: 1px solid #EBEBEB; text-align: left;">
                {{ $grupo['nombre'] }}
              </td>
              <td style="font-family: Arial, sans-serif; font-size: 14px; padding: 12px 16px; border-bottom: 1px solid #EBEBEB; text-align: right;">
                @if($grupo['estado'] == 'Borrador')
                  <span style="display: inline-block; font-family: Arial, sans-serif; font-size: 10px; font-weight: 700; color: #854D0E; background-color: #FEF9C3; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.3px;">No finalizado</span>
                @else
                  <span style="display: inline-block; font-family: Arial, sans-serif; font-size: 10px; font-weight: 700; color: #991B1B; background-color: #FEE2E2; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.3px;">No reportado</span>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>

        <!-- CTA primario -->
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center">
          <tr>
            <td style="border-radius:8px;background-color:#0099d9;">
              <a href="{{ url('/') }}" class="btn-cta" style="display:inline-block;font-family:Arial,sans-serif;font-size:14px;font-weight:700;color:#FFFFFF;padding:14px 32px;border-radius:8px;letter-spacing:0.2px;">
                Ir a reportar →
              </a>
            </td>
          </tr>
        </table>

      </td>
    </tr>

    <!-- ─── FOOTER ─── -->
    <tr>
      <td style="background-color:#0A0A10;border-radius:0 0 16px 16px;padding:28px 32px;">

        <!-- Logo -->
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:20px;">
          <tr>
            <td align="center">
              @include('_partials.logo_iglesia', ['logo_negro' => false, 'width' => '140px'])
            </td>
          </tr>
        </table>

        <!-- Redes sociales -->
        @if($iglesiaObj && ($iglesiaObj->instagram || $iglesiaObj->facebook || $iglesiaObj->youtube || $iglesiaObj->tiktok))
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin-bottom:22px;">
          <tr>
            @if($iglesiaObj->instagram)
            <td style="padding:0 5px;">
              <a href="{{ $iglesiaObj->instagram }}" target="_blank" style="display:inline-block;width:32px;height:32px;border-radius:50%;background-color:#1A1A24;text-align:center;line-height:32px;text-decoration:none;vertical-align:middle;">
                <img src="https://img.icons8.com/ios-glyphs/30/ffffff/instagram-new.png" width="18" height="18" style="display:inline-block;vertical-align:middle;margin-top:-3px;" alt="Instagram">
              </a>
            </td>
            @endif
            @if($iglesiaObj->facebook)
            <td style="padding:0 5px;">
              <a href="{{ $iglesiaObj->facebook }}" target="_blank" style="display:inline-block;width:32px;height:32px;border-radius:50%;background-color:#1A1A24;text-align:center;line-height:32px;text-decoration:none;vertical-align:middle;">
                <img src="https://img.icons8.com/ios-glyphs/30/ffffff/facebook-new.png" width="18" height="18" style="display:inline-block;vertical-align:middle;margin-top:-3px;" alt="Facebook">
              </a>
            </td>
            @endif
            @if($iglesiaObj->youtube)
            <td style="padding:0 5px;">
              <a href="{{ $iglesiaObj->youtube }}" target="_blank" style="display:inline-block;width:32px;height:32px;border-radius:50%;background-color:#1A1A24;text-align:center;line-height:32px;text-decoration:none;vertical-align:middle;">
                <img src="https://img.icons8.com/ios-glyphs/30/ffffff/youtube-play.png" width="18" height="18" style="display:inline-block;vertical-align:middle;margin-top:-3px;" alt="YouTube">
              </a>
            </td>
            @endif
            @if($iglesiaObj->tiktok)
            <td style="padding:0 5px;">
              <a href="{{ $iglesiaObj->tiktok }}" target="_blank" style="display:inline-block;width:32px;height:32px;border-radius:50%;background-color:#1A1A24;text-align:center;line-height:32px;text-decoration:none;vertical-align:middle;">
                <img src="https://img.icons8.com/ios-glyphs/30/ffffff/tiktok.png" width="18" height="18" style="display:inline-block;vertical-align:middle;margin-top:-3px;" alt="TikTok">
              </a>
            </td>
            @endif
          </tr>
        </table>
        @endif

        <!-- Dirección -->
        <p style="font-family:Arial,sans-serif;font-size:11px;color:#374151;text-align:center;line-height:1.9;margin:0 0 10px 0;">
          {{ $iglesia->direccion ?? 'Calle 000 #00-00, Bogotá D.C., Colombia' }} &nbsp;·&nbsp; {{ $iglesia->email_soporte ?? '' }}
        </p>

        <!-- Legal -->
        <p style="font-family:Arial,sans-serif;font-size:10px;color:#374151;text-align:center;margin:0 0 15px 0;line-height:1.9;">
          Recibiste este mensaje porque haces parte de nuestra comunidad.<br/>
          @if($iglesia && $iglesia->url_subdominio)
            <a href="https://{{ $iglesia->url_subdominio }}" style="color:#4B5563;text-decoration:underline;">{{ $iglesia->url_subdominio }}</a>
            &nbsp;·&nbsp;
          @endif
          <span style="color:#4B5563;">{{ $iglesiaObj->nombre }}</span>
        </p>

        <!-- Advertencia de Correo Automático -->
        <p style="font-family:Arial,sans-serif;font-size:10px;color:#4B5563;text-align:center;margin:0;line-height:1.9;border-top:1px solid #1A1A24;padding-top:15px;opacity:0.85;">
          Este mensaje es un recordatorio automático, por favor no responder.<br/>
          Si ya reportaste tus grupos, por favor ignora este correo.
        </p>

      </td>
    </tr>

    <tr><td style="height:24px;"></td></tr>

  </table>
</td></tr>
</table>
</body>
</html>
