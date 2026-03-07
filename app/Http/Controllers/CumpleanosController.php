<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;
use App\Models\TipoUsuario;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\DefaultMail;
use stdClass;

class CumpleanosController extends Controller
{
  /**
   * Muestra la lista completa de cumpleaños de los próximos 30 días.
   */
  public function listarCumpleanos(Request $request) // <--- Inyectamos Request
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $configuracion = Configuracion::find(1);
    $personas = collect();

    if ($rolActivo) {
      // 1. MODIFICACIÓN: Quitamos los filtros "where" aquí para no limitar la consulta principal.
      // Solo lo dejamos por si necesitas la lista para algo visual, pero no para filtrar.
      $tiposUsuarios = TipoUsuario::orderBy('orden', 'asc')->get();

      if (
        $rolActivo->hasPermissionTo('personas.lista_asistentes_todos') ||
        $rolActivo->hasPermissionTo('personas.lista_asistentes_solo_ministerio')
      ) {
        if ($rolActivo->hasPermissionTo('personas.lista_asistentes_solo_ministerio')) {
          $personas = auth()->user()->discipulos('todos');
        }

        if ($rolActivo->hasPermissionTo('personas.lista_asistentes_todos')) {
          // 2. MODIFICACIÓN CRÍTICA: La consulta ahora es abierta.
          $personas = User::withTrashed() // Incluye usuarios eliminados (soft delete)
            ->whereNotNull('email_verified_at')
            ->leftJoin('integrantes_grupo', 'users.id', '=', 'integrantes_grupo.user_id')

            // --- LÍNEA ELIMINADA/COMENTADA ---
            // Al quitar esto, permitimos que el Pastor Principal (id 1) y los Ocultos pasen.
            // ->whereIn('tipo_usuario_id', $tiposUsuarios->pluck('id')->toArray())

            ->select('users.*', 'integrantes_grupo.grupo_id as grupo_id')
            ->get()
            ->unique('id'); // Evitamos duplicados por el Join
        }
      }
    }

    // --- A PARTIR DE AQUÍ ES LA LÓGICA DE FECHAS QUE YA FUNCIONABA ---

    $diasFiltro = (int) $request->input('dias', 30);
    $nombreFiltro = trim($request->input('nombre'));
    $rangoFechaFiltro = $request->input('fecha_rango');

    $hoy = now();

    $fechaInicioFiltro = $hoy->clone()->startOfDay();
    $fechaFinFiltro = $hoy->clone()->addDays($diasFiltro)->endOfDay();
    $usandoRangoFijo = false;

    if ($rangoFechaFiltro) {
      $fechas = explode(' - ', $rangoFechaFiltro);
      if (count($fechas) === 2) {
        $fechaInicioFiltro = Carbon::createFromFormat('d/m/Y', $fechas[0])->startOfDay();
        $fechaFinFiltro = Carbon::createFromFormat('d/m/Y', $fechas[1])->endOfDay();
        $usandoRangoFijo = true;
      }
    }

    // 1. Preprocesar: Calculamos fechas relativas a HOY (para visualización normal)
    $cumpleanosProximos = $personas
        ->whereNotNull('fecha_nacimiento')
        ->map(function ($usuario) use ($hoy) {
            $cumpleEsteAnio = $usuario->fecha_nacimiento->copy()->setYear($hoy->year);
            // Si ya pasó hoy, calculamos para el próximo año
            $proximoCumple = $cumpleEsteAnio->isBefore($hoy->startOfDay()) ? $cumpleEsteAnio->addYear() : $cumpleEsteAnio;

            $usuario->proximo_cumpleanos = $proximoCumple;
            return $usuario;
        });

    // 2. Filtrado final
    $cumpleanosFiltrados = $cumpleanosProximos->filter(function ($usuario) use ($nombreFiltro, $fechaInicioFiltro, $fechaFinFiltro, $usandoRangoFijo) {
        // --- A. FILTRO DE FECHAS ---
        $enRango = false;

        if ($usandoRangoFijo) {
          // Lógica "Calendario": Proyectamos el cumpleaños al año que el usuario seleccionó en el filtro

          // Proyección al año de Inicio del Filtro (ej. 2025)
          $cumpleEnAnioFiltro = $usuario->fecha_nacimiento->copy()->setYear($fechaInicioFiltro->year);

          if ($cumpleEnAnioFiltro->between($fechaInicioFiltro, $fechaFinFiltro)) {
            $enRango = true;
            $usuario->proximo_cumpleanos = $cumpleEnAnioFiltro; // Actualizamos fecha para mostrarla bien
          }

          // Manejo de cruce de años en el filtro (ej. Dic 2024 - Ene 2025)
          if (!$enRango && $fechaInicioFiltro->year !== $fechaFinFiltro->year) {
            $cumpleEnAnioFin = $usuario->fecha_nacimiento->copy()->setYear($fechaFinFiltro->year);
            if ($cumpleEnAnioFin->between($fechaInicioFiltro, $fechaFinFiltro)) {
              $enRango = true;
              $usuario->proximo_cumpleanos = $cumpleEnAnioFin;
            }
          }

        } else {
          // Lógica "Próximos Días": Usamos la fecha relativa calculada arriba
          $fechaCumpleanos = $usuario->proximo_cumpleanos;

          if ($fechaFinFiltro->greaterThanOrEqualTo($fechaInicioFiltro)) {
            $enRango = $fechaCumpleanos->between($fechaInicioFiltro, $fechaFinFiltro);
          } else {
            // Rango que cruza fin de año (ej. Hoy es 30 Dic, filtro 7 días)
            $finAnio = $fechaInicioFiltro->copy()->endOfYear();
            $inicioAnio = $fechaFinFiltro->copy()->startOfYear();
            $enRango = $fechaCumpleanos->between($fechaInicioFiltro, $finAnio) ||
                      $fechaCumpleanos->between($inicioAnio, $fechaFinFiltro);
          }
        }

        if (!$enRango) {
            return false;
        }

        // --- B. FILTRO DE NOMBRE ---
        if (!empty($nombreFiltro)) {
          $nombreCompleto = "{$usuario->primer_nombre} {$usuario->segundo_nombre} {$usuario->primer_apellido} {$usuario->segundo_apellido}";
          if (!\Illuminate\Support\Str::contains(strtolower($nombreCompleto), strtolower($nombreFiltro))) {
                return false;
          }
        }

        return true;
    })
    ->sortBy('proximo_cumpleanos');

    return view('contenido.paginas.cumpleanos.listar-cumpleanos', [
      'cumpleanosProximos30Dias' => $cumpleanosFiltrados,
      'configuracion' => $configuracion,
      'diasFiltro' => $rangoFechaFiltro ? 0 : $diasFiltro
    ]);
  }

  public function getLinkWhatsappAttribute()
  {
    // 1. Si no hay teléfono móvil, retornamos null inmediatamente
    if (empty($this->telefono_movil)) {
      return null;
    }

    // 2. Limpiamos el número (quitamos espacios, guiones, paréntesis)
    $numeroLimpio = preg_replace('/[^0-9]/', '', $this->telefono_movil);

    // 3. Validación básica: Un número debe tener al menos 10 dígitos
    if (strlen($numeroLimpio) < 10) {
      return null;
    }

    // 4. Lógica de país (Si tiene 10 dígitos, asumimos Colombia y agregamos 57)
    // Si tu base de datos ya tiene códigos de país, puedes ajustar esto.
    if (strlen($numeroLimpio) == 10) {
      $numeroLimpio = '57' . $numeroLimpio;
    }

    // 5. Retornamos la URL base de la API de WhatsApp
    return "https://wa.me/" . $numeroLimpio;
  }

  public function enviarCorreo(Request $request)
  {
    // 1. Validamos los datos del formulario del modal
    $request->validate([
      'recipient_email' => 'required|email',
      'subject' => 'required|string|max:255',
      'message' => 'required|string|min:10',
      'recipient_name' => 'required|string',
    ]);

    try {
      // 2. Preparamos el objeto para el DefaultMail
      $mailData = new stdClass();
      $mailData->subject = $request->subject;
      $mailData->nombre = $request->recipient_name;
      // nl2br respeta los saltos de línea del textarea
      // e() escapa el contenido para evitar ataques XSS
      $mailData->mensaje = $request->message;

      // 3. Enviamos el correo
      Mail::to($request->recipient_email)->send(new DefaultMail($mailData));

      // 4. Devolvemos al usuario con un mensaje de éxito
      return back()->with('success', '¡Correo enviado con éxito a ' . $request->recipient_name . '!');
    } catch (\Exception $e) {
      // En caso de error (ej. credenciales SMTP mal configuradas)
      report($e); // Reporta el error real a tu log
      return back()->with('danger', 'Error al enviar el correo. Por favor, contacta al administrador.');
    }
  }
}
