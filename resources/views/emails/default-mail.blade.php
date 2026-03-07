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

                                    <a href="" title="banner" target="_blank" style="">
                                      <img align="center" alt="" src="{{ url('') }}{{ $mailData->banner }}" width="600" style="max-width:1200px;padding-bottom:0;display:inline!important;vertical-align:bottom;border:0;height:auto;outline:none;text-decoration:none" class="m_8975725914595533633mcnImage CToWUd" data-bit="iit">
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

                                    @if(!isset($mailData->saludo) || $mailData->saludo!="no")
                                    <p style="font:15px/1.25em 'Century Gothic',Arial,Helvetica;color:#292929fa">¡Hola! <b style="color:#292929fa">{{$mailData->nombre}}</b></p>
                                    @endif

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
                                    {!! $mailData->mensaje !!}
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
                            @if($version==1)
                              <a href="https://{{$iglesia->url_subdominio}}" style="text-decoration:none; " valign="top" align="center" >
                                <p style="font:15px/2.25em 'Century Gothic',Arial,Helvetica;color:#939393; line-height: normal; text-transform: uppercase;"><b>{{$iglesia->url_subdominio}}</b> <BR> SOFTWARE REDIL</p>
                              </a>
                            @elseif($version==2)
                              <a href="https://{{$iglesia->url_subdominio}}" style="text-decoration:none; " valign="top" align="center" >
                                <p style="font:15px/2.25em 'Century Gothic',Arial,Helvetica;color:#939393; line-height: normal; text-transform: uppercase;"> SOFTWARE VISION - SOFTWARE DE GRUPOS FAMILIARES <BR>  <b> {{$iglesia->url_subdominio}} </b></p>
                              </a>
                            @endif
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
