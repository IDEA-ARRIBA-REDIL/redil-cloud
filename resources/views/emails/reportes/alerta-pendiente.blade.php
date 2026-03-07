<!DOCTYPE html>
<html>
<head>
    <style>          
        .group-list { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .group-list th, .group-list td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .group-list th { background-color: #f2f2f2; }
        .btn:hover {
            background:linear-gradient(to bottom, #dfdfdf 5%, #ededed 100%);
            background-color:#dfdfdf;
        }
        .btn:active {
            position:relative; 
            top:1px;
        }
    </style>
</head>
<body>
    <div style="height:100%;margin:0;padding:0;width:100%;background-color:#f8f7fa">
      <center>
        <table align="center" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="m_8975725914595533633bodyTable" style="border-collapse:collapse;height:100%;margin:0;padding:0;width:100%;background-color:#f8f7fa">
          <tbody>
            <tr>
              <td align="center" valign="top" id="m_8975725914595533633bodyCell" style="height:100%;margin:0;padding:12px;width:100%;border-top:0">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="m_8975725914595533633templateContainer" style="border-collapse:collapse;border:0;max-width:600px!important">
                  <tbody>
                    <tr>
                      <td valign="top" id="logo" style="background:#f8f7fa none no-repeat center/cover;background-color:#f8f7fa;background-image:none;background-repeat:no-repeat;background-position:center;background-size:cover;border-top:0;border-bottom:0;padding-top:15px;padding-bottom:12px">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">
                          <tbody>
                            <tr>
                              <td valign="top" style="padding:9px">
                                <table align="left" width="100%" border="0" cellpadding="0" cellspacing="0" style="min-width:100%;border-collapse:collapse">
                                  <tbody>
                                    <tr>
                                      <!-- logo -->
                                      <td valign="top" style="padding-right:9px;padding-left:9px;padding-top:0;padding-bottom:0;text-align:center">
                                        <a href="https://redil.com" title="" target="_blank">
                                          @include('_partials.macros',["height"=>"40px", "width"=>"40px", "fill"=> "#3772e4" ])
                                        </a>
                                      </td>
                                      <!-- /logo -->
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
                      <td valign="top" id="banner" style="background:#f8f7fa none no-repeat center/cover;background-color:#f8f7fa;background-image:none;background-repeat:no-repeat;background-position:center;background-size:cover;border-top:0;border-bottom:0;padding-top:9px;padding-bottom:0">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">
                          <tbody>
                            <tr>
                              <td valign="top" style="padding:0px">
                                <table align="left" width="100%" border="0" cellpadding="0" cellspacing="0" style="min-width:100%;border-collapse:collapse">
                                  <tbody>
                                    <tr>
                                      <td valign="top" style="padding-right:0px;padding-left:0px;padding-top:0;padding-bottom:0;text-align:center">

                                     

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
                    <tr>
                      <td valign="top" id="mensaje" style="background:#ffffff none no-repeat center/cover;background-color:#ffffff;background-image:none;background-repeat:no-repeat;background-position:center;background-size:cover;border-top:0;border-bottom:2px solid #eaeaea;padding: 15px">
                        <!-- Saludo -->
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">
                          <tbody>
                            <tr>
                              <td valign="top" style="padding-top:9px">

                                <table align="left" border="0" cellpadding="0" cellspacing="0" style="max-width:100%;min-width:100%;border-collapse:collapse" width="100%" class="m_8975725914595533633mcnTextContentContainer">
                                  <tbody>
                                    <tr>
                                      <td valign="top" style="padding-top:10px;padding-right:18px;padding-bottom:20px;padding-left:18px;word-break:break-word;color:#202020;font-family:Helvetica;font-size:14px;line-height:150%;text-align:left">
                                        <p style="font:15px/1.25em 'Century Gothic',Arial,Helvetica;color:#292929fa">¡Hola! <b style="color:#292929fa">{{$encargado->nombre(3)}}</b></p>
                                      </td>
                                    </tr>
                                  </tbody>
                                </table>

                              </td>
                            </tr>
                          </tbody>
                        </table>
                        <!-- /Saludo -->

                        <!-- Mensaje -->
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">
                          <tbody>
                            <tr>
                              <td valign="top" style="padding-top:0px">
                                <table align="left" border="0" cellpadding="0" cellspacing="0" style="max-width:100%;min-width:100%;border-collapse:collapse" width="100%" class="m_8975725914595533633mcnTextContentContainer">
                                  <tbody>
                                    <tr>

                                      <td valign="top"  style="padding-top:0;padding-right:18px;padding-bottom:9px;padding-left:18px;word-break:break-word;color:#202020;font-family:'Public Sans',Helvetica;font-size:14px;line-height:150%;text-align:left">
                                        

                                          <p>Esperamos que estés teniendo una semana bendecida. Te escribimos para recordarte que tienes reportes pendientes por realizar o finalizar para la semana en curso.</p>

                                          <h3>Tus grupos pendientes:</h3>
                                          
                                          <table class="group-list">
                                              <thead>
                                                  <tr>
                                                      <th>Nombre del grupo</th>
                                                      <th>Estado actual</th>
                                                  </tr>
                                              </thead>
                                              <tbody>
                                                  @foreach($gruposPendientes as $grupo)
                                                  <tr>
                                                      <td>{{ $grupo['nombre'] }}</td>
                                                      <td>
                                                          @if($grupo['estado'] == 'Borrador')
                                                              <span style="color: #ffc107; font-weight: bold;">No finalizado</span>
                                                          @else
                                                              <span style="color: #dc3545; font-weight: bold;">No reportado</span>
                                                          @endif
                                                      </td>
                                                  </tr>
                                                  @endforeach
                                              </tbody>
                                          </table>

                                          <p style="text-align: center;">
                                              <a href="{{ url('/') }}" class="btn">Ir a reportar</a>
                                          </p>
                                      </td>


                                    </tr>
                                  </tbody>
                                </table>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                        <!-- /Mensaje -->
                      </td>
                    </tr>
                    <tr>
                      <td valign="top" id="m_8975725914595533633templateFooter" style="background:#f8f7fa none no-repeat center/cover;background-color:#f8f7fa;background-image:none;background-repeat:no-repeat;background-position:center;background-size:cover;border-top:0;border-bottom:0;padding-top:12px;padding-bottom:15px">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">
                          <tbody>
                            <tr>
                              <td valign="top" style="padding-top:9px">
                                Este mensaje es un recordatorio automático, por favor no responder. Si ya reportaste los grupos, por favor ignora este correo.
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
</body>
</html>
