@if($configuracion && $configuracion->logo_app && Storage::exists("img/branding/".$configuracion->logo_app))  
	<img style="width:{{$width}}" class="app-brand-logo" src="{{ tenant_asset('img/branding/'.$configuracion->logo_app) }}">
@else
	<img style="width:{{$width}}" class="app-brand-logo" src="{{ Storage::disk('global_media')->url('logo_principal.png') }}">
@endif