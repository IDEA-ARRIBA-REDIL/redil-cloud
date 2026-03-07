<?php


namespace App\Livewire\QrAsistencias;

use Carbon\Carbon;
use App\Models\User;
use Livewire\Component;
use App\Models\Configuracion;
use Illuminate\Support\Facades\DB;

class QrAsistencias extends Component
{
  // Variable para almacenar el resultado del escaneo
  public $scanResult = '';

  // Variable para controlar mensajes de éxito
  public $success = false;
  public $reporteReunion;

  // Método que se llamará cuando se lea un código QR exitosamente
  public function handleSuccessfulScan($decodedText)
  {
    if (!is_numeric($decodedText) || $decodedText <= 0) {
      $this->dispatch('msn', msnIcono: 'error', msnTitulo: 'QR Inválido', msnTexto: 'El código QR no contiene un ID de usuario válido.');
      return;
    }

    $userIdDesdeQr = (int) $decodedText;
    $this->scanResult = $userIdDesdeQr; // Almacenamos el ID del usuario escaneado

    $usuarioId = (int) $decodedText;

    $horaActual = Carbon::now();
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

    $fechaMaxima = Carbon::parse($this->reporteReunion->fecha)
      ->addDays($this->reporteReunion->reunion->dias_plazo_reporte)
      ->format('Y-m-d');

    $fechaHoy = Carbon::now()->format("Y-m-d");
    $horaMaxima = Carbon::createFromFormat('h:i:s', $this->reporteReunion->reunion->hora_maxima_reportar_asistencia);

    $fechaReporte = Carbon::parse($this->reporteReunion->fecha)->format('Y-m-d');

    $usuario = User::find($usuarioId);
    $asistio_reunion = DB::table('asistencia_reuniones')
      ->where('user_id', '=', $usuarioId)
      ->where('reporte_reunion_id', '=', $this->reporteReunion->id)
      ->where('asistio', '=', TRUE)
      ->count();

    if ($fechaHoy >= $this->reporteReunion->fecha && $fechaHoy <= $fechaMaxima) {
      if (
        $fechaHoy < $fechaMaxima || ($fechaHoy == $fechaMaxima && $horaActual->format('h:i A') <= $horaMaxima->format('h:i A')) ||
        $rolActivo->hasPermissionTo('reporte_reuniones.privilegio_anadir_asistente_reporte_reunion_cualquier_fecha')
      ) {
        if ($asistio_reunion > 0) {
          // Resto cantidad de asistentes
          $this->dispatch(
            'msn',
            msnIcono: 'info',
            msnTitulo: '¡Ups!',
            timer: 1500,
            msnTexto: 'Hola, ya reportaste asistencia en esta reunión con anterioridad.'
          );
        } else {
          $registroActual = DB::table('asistencia_reuniones')
            ->where('user_id', '=', $usuarioId)
            ->where('reporte_reunion_id', '=', $this->reporteReunion->id)
            ->first();

          if ($registroActual) {
            // Si existe la edito
            DB::table('asistencia_reuniones')
              ->where('user_id', '=', $usuarioId)
              ->where('reporte_reunion_id', '=', $this->reporteReunion->id)->update(['asistio' => TRUE, "autor_creacion_asistencia_id" => auth()->user()->id]);
          } else {

            // NO EXISTE REGISTRO lo creo
            $this->reporteReunion->usuarios()->attach($usuarioId, array(
              "asistio" => TRUE,
              "autor_creacion_asistencia_id" => auth()->user()->id,
              "reservacion" => FALSE,
              "autor_creacion_reserva_id" => auth()->user()->id
            ));
          }

          if ($this->reporteReunion->clasificacionesAsistentes) {
            foreach ($this->reporteReunion->clasificacionesAsistentes  as $clasificacionAsistente) {
              $tiposUsuarioDeClasificacion = $clasificacionAsistente->tipoUsuarios;
              $sumatoriaAsistentes = 0;

              foreach ($tiposUsuarioDeClasificacion as $tipoUsuarioDeClasificacion) {
                $usuarioClasificacion = User::withTrashed()
                  ->where('users.id', '=', $usuarioId)
                  ->where('users.tipo_usuario_id', '=', $tipoUsuarioDeClasificacion->id);

                // MAYORES
                if ($tipoUsuarioDeClasificacion->pivot->edad_minima) {
                  $edadMinima = $tipoUsuarioDeClasificacion->pivot->edad_minima;
                  $fechaEdadMinima = Carbon::parse($fechaReporte)->subYears($edadMinima)->format('Y-m-d');
                  $usuarioClasificacion = $usuarioClasificacion->where('users.fecha_nacimiento', '<=', $fechaEdadMinima);
                }

                //MENORES
                if ($tipoUsuarioDeClasificacion->pivot->edad_maxima) {
                  $edadMinima = $tipoUsuarioDeClasificacion->pivot->edad_minima;
                  $edadMaxima = $tipoUsuarioDeClasificacion->pivot->edad_maxima;

                  $fechaEdadMinima = Carbon::parse($fechaReporte)->subYears($edadMinima)->format('Y-m-d');
                  $fechaEdadMaxima = Carbon::parse($fechaReporte)->subYears($edadMaxima)->format('Y-m-d');

                  $usuarioClasificacion = $usuarioClasificacion
                    ->where('users.fecha_nacimiento', '<=', $fechaEdadMinima)
                    ->where('users.fecha_nacimiento', '>=', $fechaEdadMaxima);
                }

                //FECHA INGRESO == FECHA REPORTE
                if ($tipoUsuarioDeClasificacion->pivot->fecha_ingreso_igual_fecha_reporte == TRUE) {
                  $usuarioClasificacion = $usuarioClasificacion->where('users.fecha_ingreso', '=', $fechaReporte);
                }

                // GENERO
                if ($clasificacionAsistente->genero) {
                  $genero = $clasificacionAsistente->genero;
                  $usuarioClasificacion = $usuarioClasificacion->where('users.genero', '=', $genero);
                }

                // PASO DE CRECIMIENTO
                if ($tipoUsuarioDeClasificacion->pivot->paso_id) {
                  $pasoId = $tipoUsuarioDeClasificacion->pivot->paso_id;

                  if ($tipoUsuarioDeClasificacion->pivot->estado_paso) {
                    $estadoPaso = $tipoUsuarioDeClasificacion->pivot->estado_paso;

                    if ($tipoUsuarioDeClasificacion->pivot->fecha_paso_igual_fecha_reporte == TRUE) {
                      $usuarioClasificacion = $usuarioClasificacion->whereHas('pasosCrecimiento', function ($q) use ($pasoId, $fechaReporte, $estadoPaso) {
                        $q->where('pasos_crecimiento.id', '=', $pasoId)
                          ->where('crecimiento_usuario.estado_id', '=', $estadoPaso)
                          ->whereDate('crecimiento_usuario.fecha', '=', $fechaReporte);
                      })->get();
                    } else {
                      $usuarioClasificacion = $usuarioClasificacion->whereHas('pasosCrecimiento', function ($q) use ($pasoId, $estadoPaso) {
                        $q->where('pasos_crecimiento.id', '=', $pasoId)
                          ->where('crecimiento_usuario.estado_id', '=', $estadoPaso);
                      })->get();
                    }
                  } else {
                    $usuarioClasificacion = $usuarioClasificacion->whereHas('pasosCrecimiento', function ($q) use ($pasoId) {
                      $q->where('pasos_crecimiento.id', '=', $pasoId);
                    })->get();
                  }
                }

                $sumatoriaAsistentes = $sumatoriaAsistentes + $usuarioClasificacion->count();
              }
              $clasificacionAsistente->pivot->cantidad = $sumatoriaAsistentes + $clasificacionAsistente->pivot->cantidad;
              $clasificacionAsistente->pivot->save();
              $sumatoriaAsistentes = 0;
            }
          }

          $cantidad_asistencias = $this->reporteReunion->cantidad_asistencias;
          $cantidad_asistencias = ((int)$cantidad_asistencias) + 1;
          $this->reporteReunion->cantidad_asistencias = $cantidad_asistencias;
          $this->reporteReunion->save();

          // // Recalcula las fechas de ultimo reporte
          $ultima_fecha_reporte_reunion = $usuario->ultimo_reporte_reunion;
          $ultima_fecha_reporte_reunion = date('Y-m-d', strtotime($ultima_fecha_reporte_reunion));

          if ($this->reporteReunion->fecha > $ultima_fecha_reporte_reunion) {
            $usuario->ultimo_reporte_reunion = $ultima_fecha_reporte_reunion;
            $usuario->ultimo_reporte_reunion = $this->reporteReunion->fecha;
            $usuario->save();
          }

          if ($this->reporteReunion->fecha < $ultima_fecha_reporte_reunion && $this->reporteReunion->fecha > $usuario->ultimo_reporte_reunion_auxiliar) {
            $usuario->ultimo_reporte_reunion_auxiliar = $this->reporteReunion->fecha;
            $usuario->save();
          }

          if ($this->reporteReunion->fecha == $usuario->ultimo_reporte_reunion_auxiliar && $this->reporteReunion->fecha < $usuario->ultimo_reporte_reunion) {
            $usuario->save();
          }

          if ($this->reporteReunion->fecha == $ultima_fecha_reporte_reunion) {
            $usuario->save();
          }

          $this->dispatch(
            'msn',
            msnIcono: 'success',
            msnTitulo: 'Exitoso!',
            timer: 1500,
            msnTexto: 'Se registro tu asistencia a esta reunión con éxito.'
          );
        }
      } else {
        $this->dispatch(
          'msn',
          msnIcono: 'warning',
          msnTitulo: 'Ups!',
          timer: 1500,
          msnTexto: 'Hola, el tiempo para registrar la asistencia a reunión a expirado.'
        );
      }
    } else {
      $this->dispatch(
        'msn',
        msnIcono: 'warning',
        msnTitulo: 'Ups!',
        timer: 1500,
        msnTexto: 'Hola, el tiempo para registrar la asistencia a reunión a expirado.'
      );
    }

    // La variable $success solo se usa en el componente Livewire para indicar éxito
    $this->success = true;


    // Primero disparamos el evento para registrar la asistencia
    // Aquí pasamos el ID del usuario que se acaba de registrar.
    $this->dispatch('registrarAsistencia', $userIdDesdeQr);

    // Emitir evento para mostrar SweetAlert
    $this->dispatch('showAlert', [
      'title' => '¡Éxito!',
      'text' => 'Asistencia registrada exitosamente para el usuario ID: ' . $userIdDesdeQr,
      'icon' => 'success'
    ]);
  }
  public function render()
  {
    return view('livewire.qr-asistencias.qr-asistencias');
  }
}
