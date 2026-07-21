@php
  $usarNegro = $logo_negro ?? false;
  $logoCampo = $usarNegro ? 'logo_app_negro' : 'logo_app';
  $logoDefecto = $usarNegro ? 'logo_principal_negro.png' : 'logo_principal.png';
  $styleAttr = '';
  if (isset($width)) {
      $styleAttr .= "width:{$width};";
  }
  if (isset($height)) {
      $styleAttr .= "height:{$height};";
  }
@endphp

@if($configuracion && $configuracion->$logoCampo && Storage::exists("img/branding/".$configuracion->$logoCampo))  
	<img style="{{ $styleAttr }}" class="app-brand-logo" src="{{ tenant_asset('img/branding/'.$configuracion->$logoCampo) }}">
@else
	<img style="{{ $styleAttr }}" class="app-brand-logo" src="{{ Storage::disk('global_media')->url($logoDefecto) }}">
@endif