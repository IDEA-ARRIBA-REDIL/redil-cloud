{{-- Este es el nuevo contenido completo para tu vista de correo --}}
<div style="height:100%;margin:0;padding:0;width:100%;background-color:#f8f7fa">
  <center>
    <table align="center" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" style="border-collapse:collapse;height:100%;margin:0;padding:0;width:100%;background-color:#f8f7fa">
      <tbody>
        <tr>
          <td align="center" valign="top" style="height:100%;margin:0;padding:12px;width:100%;border-top:0">
            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;border:0;max-width:600px!important">
              <tbody>
                <tr>
                  <td valign="top" id="logo" style="background:#f8f7fa;border-top:0;border-bottom:0;padding-top:15px;padding-bottom:12px">
                    {{-- Aquí se asume que tienes un parcial para el logo. Si no, puedes poner una etiqueta <img> directamente --}}
                    {{-- <img src="{{ asset('path/to/your/logo.png') }}" alt="Logo" style="width: 40px; height: 40px;"> --}}
                  </td>
                </tr>
                <tr>
                  <td valign="top" id="banner" style="background:#f8f7fa;border-top:0;border-bottom:0;padding-top:9px;padding-bottom:0">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">
                      <tbody>
                        <tr>
                          <td valign="top" style="padding:0px">
                            <table align="left" width="100%" border="0" cellpadding="0" cellspacing="0" style="min-width:100%;border-collapse:collapse">
                              <tbody>
                                <tr>
                                  {{-- El banner de la actividad se mostrará aquí si existe --}}
                                  @if($mailData->banner)
                                  <td valign="top" style="padding-right:0px;padding-left:0px;padding-top:0;padding-bottom:0;text-align:center">
                                    <a href="#" title="banner" target="_blank">
                                      <img align="center" alt="Banner de la Actividad" src="{{ url('') }}{{ $mailData->banner }}" width="600" style="max-width:1200px;padding-bottom:0;display:inline!important;vertical-align:bottom;border:0;height:auto;outline:none;text-decoration:none">
                                    </a>
                                  </td>
                                  @endif
                                </tr>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td valign="top" id="mensaje" style="background:#ffffff;border-top:0;border-bottom:2px solid #eaeaea;padding: 15px">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">
                      <tbody>
                        <tr>
                          <td valign="top" style="padding-top:9px">
                            <table align="left" border="0" cellpadding="0" cellspacing="0" style="max-width:100%;min-width:100%;border-collapse:collapse" width="100%">
                              <tbody>
                                <tr>
                                  <td valign="top" style="padding-top:10px;padding-right:18px;padding-bottom:20px;padding-left:18px;word-break:break-word;color:#202020;font-family:Helvetica;font-size:14px;line-height:150%;text-align:left">
                                    @if(!isset($mailData->saludo) || $mailData->saludo != "no")
                                      <p style="font:15px/1.25em 'Century Gothic',Arial,Helvetica;color:#292929fa">¡Hola, <b style="color:#292929fa">{{ $mailData->nombre }}</b>!</p>
                                    @endif
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">
                      <tbody>
                        <tr>
                          <td valign="top" style="padding-top:0px">
                            <table align="left" border="0" cellpadding="0" cellspacing="0" style="max-width:100%;min-width:100%;border-collapse:collapse" width="100%">
                              <tbody>
                                <tr>
                                  <td valign="top" style="padding-top:0;padding-right:18px;padding-bottom:9px;padding-left:18px;word-break:break-word;color:#202020;font-family:'Public Sans',Helvetica;font-size:14px;line-height:150%;text-align:left">
                                    {!! $mailData->mensaje !!}
                                  </td>
                                </tr>
                                 {{-- NUEVO BLOQUE: Enlace al formulario --}}
                                @if($actividad->elementos->count() > 0)
                                <tr>
                                  <td valign="top" style="padding-top:15px;padding-right:18px;padding-bottom:9px;padding-left:18px;word-break:break-word;color:#202020;font-family:'Public Sans',Helvetica;font-size:14px;line-height:150%;text-align:left">
                                    <p style="margin-bottom: 10px;">
                                      <strong>¿Olvidaste completar algo?</strong> Puedes actualizar o completar el formulario de tu inscripción en cualquier momento ingresando al siguiente enlace:
                                    </p>
                                    <a href="{{ route('carrito.formulario', ['compra' => $inscripcion->compra_id, 'actividad' => $actividad->id]) }}" style="background-color: #3b71fe; color: #ffffff; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                                      Completar Formulario
                                    </a>
                                  </td>
                                </tr>
                                @endif
                              </tbody>
                            </table>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                    </td>
                </tr>
                <tr>
                  <td valign="top" style="background:#f8f7fa;border-top:0;border-bottom:0;padding-top:12px;padding-bottom:15px; text-align: center;">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">
                      <tbody>
                        <tr>
                          <td valign="top" style="padding-top:9px">
                            <a href="https://{{ $iglesia->url_subdominio }}" style="text-decoration:none;" valign="top" align="center">
                              @if($version == 1)
                                <p style="font:15px/2.25em 'Century Gothic',Arial,Helvetica;color:#939393; line-height: normal; text-transform: uppercase;"><b>{{ $iglesia->url_subdominio }}</b> <br></p>
                              @elseif($version == 2)
                                <p style="font:15px/2.25em 'Century Gothic',Arial,Helvetica;color:#939393; line-height: normal; text-transform: uppercase;"> SOFTWARE CRECER - SOFTWARE DE GRUPOS FAMILIARES <br> <b>{{ $iglesia->url_subdominio }}</b></p>
                              @endif
                            </a>
                          </td>
                        </tr>
                      </tbody>
                    </table>
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