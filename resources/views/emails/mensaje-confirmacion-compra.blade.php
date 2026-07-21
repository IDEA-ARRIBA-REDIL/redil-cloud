<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="x-apple-disable-message-reformatting" />
  <meta name="format-detection" content="telephone=no,date=no,address=no,email=no" />
  <title>Confirmación de Compra</title>
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

<!-- PREHEADER: texto invisible que aparece en preview del inbox -->
<div style="display:none;font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;font-family:sans-serif;">
  Tu pago para la actividad {{ $actividad->nombre }} ha sido confirmado. Adjuntamos tu ticket en PDF.&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F0EFE9;">
<tr><td align="center" style="padding:24px 16px 40px;">

  <table role="presentation" class="email-wrapper" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;">

    <!-- ─── HEADER ─── -->
    <tr>
      <td style="background-color:#FFFFFF;border-radius:16px 16px 0 0;padding:20px 32px;border-bottom:1px solid #EBEBEB;text-align:center;">
        @php
          $iglesiaObj = $iglesia ?? \App\Models\Iglesia::first();
        @endphp
        @if($iglesiaObj && $iglesiaObj->logo_negro && Storage::exists("img/iglesia/".$iglesiaObj->logo_negro))
          <img
            src="{{ tenant_asset('img/iglesia/'.$iglesiaObj->logo_negro) }}"
            alt="{{ $iglesiaObj->nombre ?? config('app.name') }}"
            width="160"
            style="display:inline-block;max-width:160px;height:auto;"
          />
        @elseif($iglesiaObj && $iglesiaObj->logo && Storage::exists("img/iglesia/".$iglesiaObj->logo))
          <img
            src="{{ tenant_asset('img/iglesia/'.$iglesiaObj->logo) }}"
            alt="{{ $iglesiaObj->nombre ?? config('app.name') }}"
            width="160"
            style="display:inline-block;max-width:160px;height:auto;background-color:#040407;padding:4px;border-radius:4px;"
          />
        @else
          <img
            src="{{ Storage::disk('global_media')->url('logo_principal.png') }}"
            alt="{{ $iglesiaObj->nombre ?? config('app.name') }}"
            width="160"
            style="display:inline-block;max-width:160px;height:auto;"
          />
        @endif
      </td>
    </tr>

    <!-- ─── CUERPO ─── -->
    <tr>
      <td style="background-color:#FFFFFF;padding:40px 32px;" class="card-pad">

        <!-- Eyebrow -->
        <p style="font-family:Arial,sans-serif;font-size:10px;font-weight:700;color:#0099d9;letter-spacing:2.5px;text-transform:uppercase;margin:0 0 14px 0;">
          CONFIRMACIÓN DE COMPRA · TICKETS
        </p>

        <!-- Titular -->
        <h1 style="font-family:Georgia,'Times New Roman',serif;font-size:26px;font-weight:700;color:#040407;line-height:1.25;margin:0 0 20px 0;">
          ¡Gracias por tu compra, {{ $compra->nombre_completo_comprador }}!
        </h1>

        <!-- Mensaje de Contexto -->
        <p style="font-family:Arial,sans-serif;font-size:15px;color:#374151;line-height:1.75;margin:0 0 24px 0;">
          Hemos recibido exitosamente tu pago para la actividad:
        </p>

        <!-- Destacado de Actividad -->
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%;background-color:#F8F8F6;border:1px solid #EBEBEB;border-radius:8px;margin-bottom:24px;">
          <tr>
            <td style="padding:20px;text-align:center;font-family:Georgia,'Times New Roman',serif;font-size:18px;font-weight:700;color:#0099d9;line-height:1.45;">
              {{ $actividad->nombre }}
            </td>
          </tr>
        </table>

        <!-- Mensaje Ticket PDF -->
        <p style="font-family:Arial,sans-serif;font-size:15px;color:#374151;line-height:1.75;margin:0 0 24px 0;">
          Adjunto a este correo encontrarás tu ticket de compra en formato PDF con todos los detalles y tu código QR de acceso personal para ingresar al evento.
        </p>


      </td>
    </tr>

    <!-- ─── FOOTER ─── -->
    <tr>
      <td style="background-color:#0A0A10;border-radius:0 0 16px 16px;padding:28px 32px;">

        <!-- Logo -->
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:20px;">
          <tr>
            <td align="center">
              @if($iglesiaObj && $iglesiaObj->logo && Storage::exists("img/iglesia/".$iglesiaObj->logo))
                <img
                  src="{{ tenant_asset('img/iglesia/'.$iglesiaObj->logo) }}"
                  alt="{{ $iglesiaObj->nombre ?? config('app.name') }}"
                  width="140"
                  style="display:inline-block;max-width:140px;height:auto;"
                />
              @elseif($iglesiaObj && $iglesiaObj->logo_negro && Storage::exists("img/iglesia/".$iglesiaObj->logo_negro))
                <img
                  src="{{ tenant_asset('img/iglesia/'.$iglesiaObj->logo_negro) }}"
                  alt="{{ $iglesiaObj->nombre ?? config('app.name') }}"
                  width="140"
                  style="display:inline-block;max-width:140px;height:auto;background-color:#FFFFFF;padding:4px;border-radius:4px;"
                />
              @else
                <img
                  src="{{ Storage::disk('global_media')->url('logo_principal.png') }}"
                  alt="{{ $iglesiaObj->nombre ?? config('app.name') }}"
                  width="140"
                  style="display:inline-block;max-width:140px;height:auto;"
                />
              @endif
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
          {{ $iglesiaObj->direccion ?? 'Calle 000 #00-00, Bogotá D.C., Colombia' }} &nbsp;·&nbsp; {{ $iglesiaObj->email_soporte ?? '' }}
        </p>

        <!-- Legal -->
        <p style="font-family:Arial,sans-serif;font-size:10px;color:#374151;text-align:center;margin:0 0 15px 0;line-height:1.9;">
          Recibiste este mensaje porque haces parte de nuestra comunidad.<br/>
          @if($iglesiaObj && $iglesiaObj->url_subdominio)
            <a href="https://{{ $iglesiaObj->url_subdominio }}" style="color:#4B5563;text-decoration:underline;">{{ $iglesiaObj->url_subdominio }}</a>
            &nbsp;·&nbsp;
          @endif
          <span style="color:#4B5563;">{{ $iglesiaObj->nombre }}</span>
        </p>

        <!-- Aviso Confirmación de Pago -->
        <p style="font-family:Arial,sans-serif;font-size:10px;color:#4B5563;text-align:center;margin:0;line-height:1.9;border-top:1px solid #1A1A24;padding-top:15px;opacity:0.85;">
          Este mensaje es una confirmación de pago de tu compra.<br/>
          Si tienes alguna duda o inquietud, por favor ponte en contacto con nosotros.
        </p>

      </td>
    </tr>

    <tr><td style="height:24px;"></td></tr>

  </table>
</td></tr>
</table>
</body>
</html>
