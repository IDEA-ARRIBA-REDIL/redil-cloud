@php
  $iglesiaObj = $iglesia ?? \App\Models\Iglesia::first();
  $usarNegro = $logo_negro ?? false;
  $logoCampo = $usarNegro ? 'logo_negro' : 'logo';
  $styleAttr = '';
  if (isset($width)) {
      $styleAttr .= "width:{$width};";
  }
  if (isset($height)) {
      $styleAttr .= "height:{$height};";
  }
@endphp

@if($iglesiaObj && $iglesiaObj->$logoCampo && Storage::exists("img/iglesia/".$iglesiaObj->$logoCampo))  
	<img style="{{ $styleAttr }}" class="church-logo" src="{{ tenant_asset('img/iglesia/'.$iglesiaObj->$logoCampo) }}">
@endif
