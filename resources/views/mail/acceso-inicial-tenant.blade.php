<p>Tu cuenta inicial de {{ $iglesia }} está preparada.</p>
<p>Abre <a href="{{ rtrim(config('admin_global.central_url'), '/') }}/activar-cuenta">el formulario de activación REDIL</a> y copia este código:</p>
<p><strong>{{ $codigo }}</strong></p>
<p>Vence en {{ config('admin_global.access_hours') }} horas y solo sirve una vez. No es tu contraseña: deberás elegir una propia. No compartas el código.</p>
