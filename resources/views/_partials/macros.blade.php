@php
  $usarNegro = $logo_negro ?? false;
  $logoCampo = $usarNegro ? 'logo_app_negro' : 'logo_app';
  $logoDefecto = $usarNegro ? 'logo_principal_negro.png' : 'logo_principal.png';
  $campoPorUbicacion = match ($contexto_logo ?? null) {
      'login' => 'logo_login',
      'menu_general' => 'logo_menu_general',
      'menu_escuelas' => 'logo_menu_escuelas',
      default => null,
  };
  $logoSeleccionado = null;
  $candidatos = [$campoPorUbicacion ? $configuracion?->{$campoPorUbicacion} : null, $configuracion?->{$logoCampo}];
  foreach (array_filter($candidatos) as $candidato) {
      if (Storage::disk('public')->exists('img/branding/'.$candidato) || Storage::exists('img/branding/'.$candidato)) {
          $logoSeleccionado = $candidato;
          break;
      }
  }
  $styleAttr = '';
  if (isset($width)) {
      $styleAttr .= "width:{$width};";
  }
  if (isset($height)) {
      $styleAttr .= "height:{$height};";
  }
@endphp

@if($logoSeleccionado)
	<img style="{{ $styleAttr }}" class="app-brand-logo" src="{{ tenant_asset('img/branding/'.$logoSeleccionado) }}" alt="Logo">
@else
	<img style="{{ $styleAttr }}" class="app-brand-logo" src="{{ Storage::disk('global_media')->url($logoDefecto) }}">
@endif
