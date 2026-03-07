<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Carbon\Carbon;

class ReporteReunion extends Model
{
  use HasFactory;
  protected $table = 'reporte_reuniones';
  protected $guarded = [];
  protected $fillable = [
    'reunion_id',
    'fecha',
    'predicador',
    'predicador_diezmos',
    'predicador_invitado',
    'predicador_diezmos_invitado',
    'observaciones',
    'invitados',
    'sumatoria_adicional_clasificacion',
    'clasificacion_asistentes',
    'cantidad_asistencias',
    'total_ofrendas',
    'autor_creacion',
    'conteo_preliminar',
    'habilitar_reserva',
    'dias_plazo_reserva',
    'aforo',
    'habilitar_reserva_invitados',
    'cantidad_maxima_reserva_invitados',
    'aforo_ocupado',
    'solo_reservados_pueden_asistir',
    'url',
    'iframe',
    'visualizaciones',
    'habilitar_preregistro_iglesia_infantil',
  ];


  public function usuarios(): BelongsToMany
  {
    return $this->belongsToMany(User::class, "asistencia_reuniones")
      ->withPivot('asistio', 'reservacion', 'invitados', 'created_at', 'updated_at', 'observacion', 'autor_creacion_reserva_id', 'autor_creacion_asistencia_id');
  }

  public function reunion(): BelongsTo
  {
    return $this->belongsTo(Reunion::class);
  }

  public function reservas(): HasMany
  {
    return $this->hasMany(ReservaReunion::class, 'reporte_reunion_id');
  }

  public function ofrendas(): BelongsToMany
  {
    return $this->belongsToMany(
      Ofrenda::class,
      "ofrenda_reuniones",
      "reporte_reunion_id",
      "ofrenda_id"
    )->withTimestamps();
  }

  public function tipoOfrenda(): BelongsTo
  {
    return $this->belongsTo(TipoOfrenda::class, 'tipo_ofrenda_id');
  }

  public function clasificacionesAsistentes(): BelongsToMany
  {
    return $this->belongsToMany(
      ClasificacionAsistente::class,
      'clasificacion_asistente_reporte_reunion',
      'reporte_reunion_id',
      'clasificacion_asistente_id',
    )->withPivot('cantidad')->withTimestamps();
  }

  public function totalOfrendasPorUsuario($userId)
  {
    // Hacer la consulta y totalizarla
    $sumatoria = $this->ofrendas()->where('user_id', $userId)->sum('valor') ?? 0;
    return $sumatoria;
  }

  public function puedeAñadirAsistentes()
  {
    $puede = false;
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

    if($rolActivo->hasPermissionTo('reporte_reuniones.privilegio_anadir_asistente_reporte_reunion_cualquier_fecha'))
    {
      $puede = true;
    }else{
      // 1. Definir la fecha y hora de inicio del plazo.
      // Usamos startOfDay() para asegurarnos de que la comparación comience a las 00:00:00 del día del reporte.
      $fechaInicio = Carbon::parse($this->fecha)->startOfDay();

      // 2. Calcular la fecha y hora máxima para reportar en un solo paso.
      // Se combina la lógica de sumar días y establecer la hora máxima en un objeto Carbon.
      $fechaHoraMaxima = Carbon::parse($this->fecha)
                              ->addDays($this->reunion->dias_plazo_reporte)
                              ->setTimeFromTimeString($this->reunion->hora_maxima_reportar_asistencia);

      // 3. Realizar la comprobación completa en una sola línea.
      // El método isBetween() verifica si la fecha y hora actual está entre el inicio y el fin (ambos inclusive).
      // Esto reemplaza toda la estructura if/else anidada.
      $puede = Carbon::now()->isBetween($fechaInicio, $fechaHoraMaxima);
    }

    return $puede;
  }

  public function puedeAñadirReservas()
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $puede = false;

    if ($rolActivo->hasPermissionTo('reporte_reuniones.opcion_anadir_asistentes_reservas_reunion') && $this->habilitar_reserva === true)
    {
        // 1. Calcular la fecha de inicio del periodo de reserva.
        $fechaInicioReserva = Carbon::parse($this->fecha)->subDays($this->dias_plazo_reserva)->startOfDay();

        // 2. La fecha de fin es la fecha del reporte, hasta el final del día.
        $fechaFinReserva = Carbon::parse($this->fecha)->endOfDay();

        // 3. Comprobar si la fecha actual está dentro del rango de reserva.
        $puede = Carbon::now()->isBetween($fechaInicioReserva, $fechaFinReserva);
    }

    return $puede;
  }

  public function elUsuarioPuedeReservar(User $user = null): bool
  {
    /*// VALIDACIÓN DE PLAZO DE RESERVA (Filtro de tiempo)
    if (isset($this->dias_plazo_reserva)) {
        $fechaInicioReserva = Carbon::parse($this->fecha)->subDays($this->dias_plazo_reserva)->startOfDay();
        $fechaFinReserva = Carbon::parse($this->fecha)->endOfDay();

        // Si la fecha actual NO está dentro del rango permitido, no se puede reservar.
        if (!Carbon::now()->isBetween($fechaInicioReserva, $fechaFinReserva)) {
            return false;
        }
    }

    // Si el aforo ocupado es igual o mayor que el aforo total, no hay cupos.
    if ($this->aforo_ocupado >= $this->aforo) {
        return false; // No hay espacio, nadie más puede reservar.
    }*/

    // Obtener el usuario y sus datos necesarios.
    $user = $user ?? auth()->user();
    if (!$user || !$user->tipo_usuario_id || !$user->sede_id || is_null($user->genero) || !$user->fecha_nacimiento) {
        return false;
    }

    // 2. Obtener la reunión asociada al reporte.
    $reunion = $this->reunion()->withTrashed()->first();
    if (!$reunion) {
        return false;
    }

    // 3. VALIDACIÓN POR TIPO DE USUARIO
    $tiposPermitidos = $reunion->tipoUsuarios;
    if ($tiposPermitidos->isNotEmpty()) {
        $tiposPermitidosIds = $tiposPermitidos->pluck('id')->toArray();
        if (!in_array($user->tipo_usuario_id, $tiposPermitidosIds)) {
            return false;
        }
    }

    // 4. VALIDACIÓN POR EDAD
    $rangosDeEdad = $reunion->rangosEdades;
    if ($rangosDeEdad->isNotEmpty()) {
        $edadUsuario = $user->edad();
        $edadValida = false;
        foreach ($rangosDeEdad as $rango) {
            if ($edadUsuario >= $rango->edad_minima && $edadUsuario <= $rango->edad_maxima) {
                $edadValida = true;
                break;
            }
        }
        if (!$edadValida) {
            return false;
        }
    }

    // 5. VALIDACIÓN POR GÉNERO
    $generosPermitidos = json_decode($reunion->genero, true);
    if (is_array($generosPermitidos) && !empty($generosPermitidos)) {
        if (!in_array($user->genero, $generosPermitidos)) {
            return false;
        }
    }

    // 6. VALIDACIÓN POR SEDE (LÓGICA CORREGIDA)
    // Luego, cargamos las sedes adicionales permitidas.
    $sedesAdicionales = $reunion->sedes;

    // Si la colección de sedes adicionales está vacía, significa que no hay restricciones.
    // El usuario pasa el filtro.
    if ($sedesAdicionales->isEmpty()) {
        return true;
    }

    // Si SÍ hay sedes adicionales definidas, entonces comprobamos si la del usuario está en la lista.
    $sedesAdicionalesIds = $sedesAdicionales->pluck('id')->toArray();
    if (in_array($user->sede_id, $sedesAdicionalesIds)) {
        return true;
    }

    // 7. Si no se cumplió ninguna de las condiciones de sede, no es elegible.
    return false;
  }


  // esta calcula y analiza si la fecha de hoy esta dentro del rango para reportar
  public function sePuedeReservar(): bool
  {

     if ($this->habilitar_reserva && isset($this->dias_plazo_reserva) && $this->dias_plazo_reserva > 0)
     {
        $fechaInicioReserva = Carbon::parse($this->fecha)->subDays($this->dias_plazo_reserva)->startOfDay();
        $fechaFinReserva = Carbon::parse($this->fecha)->endOfDay();

        // Si la fecha actual NO está dentro del rango permitido, no se puede reservar.
        if (!Carbon::now()->isBetween($fechaInicioReserva, $fechaFinReserva)) {
            return false;
        }

        return true;
     }


    return false;
  }

  public function hayAforoDisponible(): bool
  {

      // El casting (int) convierte un posible null en aforo_ocupado a 0.
      $disponible = (int)$this->aforo - (int)$this->aforo_ocupado;

      // Hay aforo si el resultado es mayor que cero.
      return $disponible > 0;
  }

  public function obtenerCantidadDisponible(): ?int
  {
      // Calculamos la diferencia.
      // El casting (int) asegura que si 'aforo_ocupado' es null, se trate como 0.
      $disponible = (int)$this->aforo - (int)$this->aforo_ocupado;

      // Devolvemos el resultado, asegurándonos de que nunca sea un número negativo.
      return max(0, $disponible);
  }

  public function cantidadDisponibleInvitados(User $user): int
  {
      // 1. Si no hay un límite definido, no se pueden reservar invitados.
      $limiteMaximo = (int)$this->cantidad_maxima_reserva_invitados;
      if ($limiteMaximo <= 0) {
          return 0;
      }

      // 2. Contamos cuántos invitados ha registrado ya este usuario para este reporte.
      $invitadosYaRegistrados = ReservaReunion::where('reporte_reunion_id', $this->id)
          ->where('responsable_id', $user->id)
          ->where('invitado', true)
          ->count();

      // 3. Calculamos la diferencia.
      $disponibles = $limiteMaximo - $invitadosYaRegistrados;

      // 4. Devolvemos el resultado, asegurando que no sea un número negativo.
      return max(0, $disponibles);
  }

  public function tengoReservasEnEsteReporte (User $user = null): bool
  {
    // 1. Obtener el usuario. Si no se provee uno, usamos el que está logueado.
    $user = $user ?? auth()->user();

    // Si no hay un usuario válido, no puede tener reservas.
    if (!$user) {
        return false;
    }

    // 2. Usamos la función exists() para una consulta de existencia rápida y eficiente.
    // Comprueba si existe al menos un registro en 'reservas_reuniones' que cumpla ambas condiciones.
    return ReservaReunion::where('reporte_reunion_id', $this->id)
                         ->where('responsable_id', $user->id)
                         ->exists();

  }

  // public function sumatoriasClasificacionesAsistentes()
  // {
  //   $clasificacionesReunion = $this->reunion
  //     ->clasificacionesAsistentes()
  //     ->select('clasificaciones_asistentes.id', 'nombre')
  //     ->where('tiene_sumatoria_adicional', true)
  //     ->get();

  //   $clasificacionesReunion->map(function ($clasificacion) {
  //     $existencia = $this->clasificacionesAsistentes()->where(
  //       'clasificacion_asistente_id',
  //       $clasificacion->id
  //     )->first();

  //     if ($existencia) {
  //       $clasificacion->cantidad = $existencia->pivot->cantidad;
  //     } else {
  //       $clasificacion->cantidad = 0;
  //     }
  //   });
  //   return $clasificacionesReunion;
  // }

  // public function todasLasClasificaciones()
  // {
  //   $clasificacionesReunion = $this->reunion
  //     ->clasificacionesAsistentes()
  //     ->select('clasificaciones_asistentes.id', 'nombre')
  //     ->get();

  //   $clasificacionesReunion->map(function ($clasificacion) {
  //     $existencia = $this->clasificacionesAsistentes()->where(
  //       'clasificacion_asistente_id',
  //       $clasificacion->id
  //     )->first();

  //     if ($existencia) {
  //       $clasificacion->cantidad = $existencia->pivot->cantidad;
  //     } else {
  //       $clasificacion->cantidad = 0;
  //     }
  //   });
  //   return $clasificacionesReunion;
  // }
}
