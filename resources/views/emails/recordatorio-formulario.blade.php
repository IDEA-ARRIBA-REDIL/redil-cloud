<div style="height:100%;margin:0;padding:0;width:100%;background-color:#f8f7fa">
  <center>
    <table align="center" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" style="border-collapse:collapse;height:100%;margin:0;padding:0;width:100%;background-color:#f8f7fa">
      <tbody>
        <tr>
          <td align="center" valign="top" style="height:100%;margin:0;padding:12px;width:100%;border-top:0">
            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;border:0;max-width:600px!important">
              <tbody>
                <tr>
                  <td valign="top" id="banner" style="background:#f8f7fa;border-top:0;border-bottom:0;padding-top:9px;padding-bottom:0">
                    @if($mailData->banner)
                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">
                      <tbody>
                        <tr>
                          <td valign="top" style="padding:0px; text-align:center">
                            <img align="center" alt="Banner" src="{{ url('') }}{{ $mailData->banner }}" width="600" style="max-width:1200px;padding-bottom:0;display:inline!important;vertical-align:bottom;border:0;height:auto;outline:none;text-decoration:none">
                          </td>
                        </tr>
                      </tbody>
                    </table>
                    @endif
                  </td>
                </tr>
                <tr>
                  <td valign="top" id="mensaje" style="background:#ffffff;border-top:0;border-bottom:2px solid #eaeaea;padding: 20px">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">
                      <tbody>
                        <tr>
                          <td valign="top">
                             @if(!isset($mailData->saludo) || $mailData->saludo != "no")
                                <p style="font:18px/1.25em 'Century Gothic',Arial,Helvetica;color:#292929fa; margin-bottom: 20px;">¡Hola, <strong>{{ $mailData->nombre }}</strong>!</p>
                             @endif
                             
                             <div style="color:#202020;font-family:'Public Sans',Helvetica,Arial;font-size:15px;line-height:160%;text-align:left">
                                {!! $mailData->mensaje !!}
                             </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td valign="top" style="background:#f8f7fa;padding-top:20px;padding-bottom:20px; text-align: center;">
                    <p style="font-family:Helvetica;font-size:12px;color:#939393;line-height:150%">
                      Este es un correo automático, por favor no respondas a este mensaje. <br>
                      © {{ date('Y') }} {{ $iglesia->nombre }}. Todos los derechos reservados.
                    </p>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
    </table>
  </center>
</div>
