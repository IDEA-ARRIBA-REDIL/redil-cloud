<?php

namespace App\Livewire\ReporteReuniones;

use App\Helpers\Helpers;
use App\Models\Configuracion;
use App\Models\Reunion;
use App\Models\User;
use App\Models\ReporteReunion;
use App\Models\ClasificacionAsistenteReporteReunion;
use App\Models\ReservaReunion;
use App\Models\TipoUsuario;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Carbon\CarbonTimeZone;
use stdClass;
use Illuminate\Support\Str;


class Asistentes extends Component
{
  use WithPagination;

  public $personas;
  public $noMasDatos;
  public $asistencias = [];
  public $asistenciasInvitados = [];
  public ReporteReunion $reporteReunion;

  public $configuracion;
  public $usuario;
  public $x;
  public $mensajeAccion = '';
  public $porPagina = 3;
  public $page = 0;
  public $sumatoriasAdicionales = [];
  public $clasificaciones = [];
  public $buscar = '';
  public $noMasPersonas = false;
  public $isLoading = false;
  public $total;

  public $reunion_id; // Usado en render
  public ?Reunion $reunion; // Usado en render, puede ser null si no se encuentra
  public $arrayTiposUsuarios = [];
  public $arrayGenero = [];
  public $arrayRangosEdades = []; // Debe ser una colección de modelos RangoEdad
  public $arraySedes = [];
  public $tiposOfrendas = [];
  public $moneda;
  public $data;

  public $cantidadPorCarga = 3; // Cuántas personas cargar cada vez
  public $paginaActual = 1; // Para rastrear la "página" actual para la carga manual

  protected $listeners = ['qrCodeScanned' => 'registrarAsistenciaPorQr'];
  public bool $procesandoQR = false;

  public $rolActivo = null;

  public function mount()
  {
    $this->rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $this->total = 0;
    $reunion = Reunion::withTrashed()->find($this->reporteReunion->reunion_id);
    $this->configuracion = Configuracion::first();
    $this->usuario = auth()->user();
    $this->sumatoriasAdicionales = $this->reporteReunion->clasificacionesAsistentes()->where('tiene_sumatoria_adicional', true)->get();
    $this->reunion_id = $this->reporteReunion->reunion_id;
    $this->reunion = Reunion::withTrashed()->find($this->reunion_id);
    $this->arrayTiposUsuarios = $this->reunion->tipoUsuarios()->select('tipo_usuarios.id')->pluck('tipo_usuarios.id')->toArray();
    $this->tiposOfrendas = $reunion->tiposOfrendas()->orderBy("nombre")->get();
    $this->arrayGenero = json_decode($this->reunion->genero);
    $this->arrayRangosEdades = $this->reunion->rangosEdades()->get();
    $this->arraySedes = $this->reunion->sedes()->select('sedes.id')->pluck('sedes.id')->toArray();
    $this->arraySedes[] = $this->reunion->sede_id;
    $this->cargarPersonas();
  }


  public function updatedBuscar($value)
  {
    $this->paginaActual = 1; // Reinicia la paginación para la nueva búsqueda
    $this->noMasPersonas = false; // Permite cargar más si hay resultados
    $this->personas = collect(); // Limpia los resultados anteriores
    $this->cargarPersonas(); // Llama al método que aplica el filtro y carga los datos
  }

  #[On('buscar')]
  public function buscar()
  {
    $this->cargarPersonas();
  }

  public function resetPage()
  {
    $this->page = 1;
  }

  public function cargarPersonas()
  {
      $reporteReunionId = $this->reporteReunion->id;
      $finalQuery = null;

      // Escenario 1: Las reservas están habilitadas, por lo que cargamos Usuarios con reserva Y también Invitados.
      if ($this->reporteReunion->habilitar_reserva) {

        // --- Subconsulta para Invitados ---
        // Esta consulta no cambia, ya que los invitados siempre deben tener una reserva.
        $guestsQuery = DB::table('reservas_reuniones')
            ->select(
                'reservas_reuniones.id as id',
                DB::raw("null as foto"),
                'reservas_reuniones.email_invitado as email',
                'reservas_reuniones.nombre_invitado as primer_nombre',
                DB::raw("null as segundo_nombre"),
                DB::raw("null as primer_apellido"),
                DB::raw("null as segundo_apellido"),
                DB::raw("null as identificacion"),
                'reservas_reuniones.id as reserva_id',
                'reservas_reuniones.registrada as asistio', // Alias para el switch
                DB::raw("true as es_invitado"),
                DB::raw("CONCAT('guest_', reservas_reuniones.id) as unique_key")
            )
            ->where('reservas_reuniones.reporte_reunion_id', $reporteReunionId)
            ->whereNull('reservas_reuniones.user_id')
            ->whereNotNull('reservas_reuniones.nombre_invitado');

        // --- Subconsulta para Usuarios ---
        $usersQuery = User::query()
            ->select(
                'users.id',
                'users.foto',
                'users.email',
                'users.primer_nombre',
                'users.segundo_nombre',
                'users.primer_apellido',
                'users.segundo_apellido',
                'users.identificacion',
                'reservas_reuniones.id as reserva_id',
                DB::raw('asistencia_reuniones.user_id IS NOT NULL as asistio'),
                DB::raw("false as es_invitado"),
                DB::raw("CONCAT('user_', users.id) as unique_key")
            )->whereNotNull('users.email_verified_at');

        // Si el rol solo puede ver a su ministerio, restringimos la consulta de usuarios.
        if ($this->rolActivo->hasPermissionTo('personas.lista_asistentes_solo_ministerio')) {
          $discipuloIds = $this->usuario->discipulos()->pluck('id')->toArray();
          if (empty($discipuloIds)) {
              $discipuloIds = [0];
          }
          $usersQuery->whereIn('users.id', $discipuloIds);
        }


        // --- LÓGICA ACTUALIZADA ---
        if ($this->reporteReunion->solo_reservados_pueden_asistir) {
            // Sub-escenario 1.A: Mostrar SÓLO usuarios CON reserva (INNER JOIN)
            $usersQuery->join('reservas_reuniones', function ($join) use ($reporteReunionId) {
                $join->on('users.id', '=', 'reservas_reuniones.user_id')
                    ->where('reservas_reuniones.reporte_reunion_id', $reporteReunionId);
            });
        } else {
            // Sub-escenario 1.B: Mostrar TODOS los usuarios (tengan o no reserva) (LEFT JOIN)
            $usersQuery->leftJoin('reservas_reuniones', function ($join) use ($reporteReunionId) {
                $join->on('users.id', '=', 'reservas_reuniones.user_id')
                    ->where('reservas_reuniones.reporte_reunion_id', $reporteReunionId);
            });
        }

        // Este leftJoin para obtener la asistencia real del usuario siempre es necesario.
        $usersQuery->leftJoin('asistencia_reuniones', function ($join) use ($reporteReunionId) {
            $join->on('users.id', '=', 'asistencia_reuniones.user_id')
                ->where('asistencia_reuniones.reporte_reunion_id', '=', $reporteReunionId);
        });

        // Aplicar filtros de búsqueda a AMBAS consultas
        if ($this->buscar) {
            $palabras = explode(' ', Helpers::sanearStringConEspacios($this->buscar));

            $usersQuery->where(function ($q) use ($palabras) {
                foreach ($palabras as $palabra) {
                  $q->whereRaw("LOWER( translate( CONCAT_WS(' ', primer_nombre, segundo_nombre, primer_apellido, segundo_apellido ) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ["%{$palabra}%"])
                  ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', primer_nombre, primer_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ["%{$palabra}%"])
                  ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', primer_nombre, segundo_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ["%{$palabra}%"])
                  ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', segundo_apellido, segundo_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ["%{$palabra}%"])
                  ->orWhereRaw("LOWER(email) LIKE LOWER(?)", ["%{$palabra}%"])
                  ->orWhereRaw("LOWER(identificacion) LIKE LOWER(?)", ["%{$palabra}%"]);
                }
            });


            $guestsQuery->where(function ($q) use ($palabras) {
                foreach ($palabras as $palabra) {
                    $q->whereRaw("LOWER( translate( CONCAT_WS(' ', nombre_invitado) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ["%{$palabra}%"])
                    ->orWhereRaw("LOWER(email_invitado) LIKE LOWER(?)", ["%{$palabra}%"]);
                }
            });
        }

        // Aplicar filtros específicos de usuario SÓLO a la consulta de usuarios
        $this->aplicarFiltrosDeUsuario($usersQuery);

        $unionQuery = $usersQuery->union($guestsQuery);
        $finalQuery = DB::query()->fromSub($unionQuery, 'personas_unidas');

      } else {
          // Escenario 2: Las reservas no están habilitadas. Lógica anterior.
          $query = User::query()->select(
              'users.id', 'users.foto', 'users.email', 'users.primer_nombre', 'users.segundo_nombre',
              'users.primer_apellido', 'users.segundo_apellido', 'users.identificacion',
              DB::raw('asistencia_reuniones.user_id IS NOT NULL as asistio'),
              DB::raw("false as es_invitado"),
              DB::raw("CONCAT('user_', users.id) as unique_key")
          )
          ->whereNotNull('users.email_verified_at')
          ->leftJoin('asistencia_reuniones', function ($join) use ($reporteReunionId) {
              $join->on('users.id', '=', 'asistencia_reuniones.user_id')
                  ->where('asistencia_reuniones.reporte_reunion_id', '=', $reporteReunionId);
          });

          // Si el rol solo puede ver a su ministerio, restringimos la consulta de usuarios.
          if ($this->rolActivo->hasPermissionTo('personas.lista_asistentes_solo_ministerio')) {
            $discipuloIds = $this->usuario->discipulos()->pluck('id')->toArray();
            if (empty($discipuloIds)) {
                $discipuloIds = [0];
            }
            $query->whereIn('users.id', $discipuloIds);
          }

          $this->aplicarFiltrosDeUsuario($query);
          // Aquí faltaba aplicar la búsqueda para el escenario 2
          if ($this->buscar) {
              $palabras = explode(' ', Helpers::sanearStringConEspacios($this->buscar));
              $query->where(function ($q) use ($palabras) {
                  foreach ($palabras as $palabra) {
                    $q->whereRaw("LOWER( translate( CONCAT_WS(' ', primer_nombre, segundo_nombre, primer_apellido, segundo_apellido ) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ["%{$palabra}%"])
                    ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', primer_nombre, primer_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ["%{$palabra}%"])
                    ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', primer_nombre, segundo_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ["%{$palabra}%"])
                    ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', segundo_apellido, segundo_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ["%{$palabra}%"])
                    ->orWhereRaw("LOWER(email) LIKE LOWER(?)", ["%{$palabra}%"])
                    ->orWhereRaw("LOWER(identificacion) LIKE LOWER(?)", ["%{$palabra}%"]);
                  }
              });
          }

          if ($this->reporteReunion->solo_reservados_pueden_asistir) {
              $query->whereExists(function ($subQuery) use ($reporteReunionId) {
                  $subQuery->select(DB::raw(1))->from('reservas_reuniones')
                          ->whereColumn('reservas_reuniones.user_id', 'users.id')
                          ->where('reservas_reuniones.reporte_reunion_id', $reporteReunionId);
              });
          }
          $finalQuery = $query;
      }

      // --- Paginación y Carga (común para ambos escenarios) ---
      $finalQuery->orderBy('primer_nombre', 'asc');

      // --- Lógica de paginación ---
      if ($this->paginaActual == 1) {
        // Para la PRIMERA página, cargamos los datos iniciales
        $personasCargadas = $finalQuery->take($this->cantidadPorCarga)->get();
        $this->personas = $personasCargadas;

        // Y SÓLO en la primera página, reseteamos y llenamos los arrays de asistencia
        $this->asistencias = [];
        $this->asistenciasInvitados = [];

        $personasCargadas->each(function ($item) {
            if ($item->es_invitado) {
                $this->asistenciasInvitados[$item->id] = (bool) $item->asistio;
            } else {
                $this->asistencias[$item->id] = isset($item->asistio) ? (bool) $item->asistio : null;
            }
        });

    } else {
        // Para páginas SIGUIENTES (cuando se hace scroll)
        $personasNuevas = $finalQuery->skip(($this->paginaActual - 1) * $this->cantidadPorCarga)
            ->take($this->cantidadPorCarga)->get();

        // Mapeamos SÓLO las nuevas personas SIN borrar los arrays existentes
        $personasNuevas->each(function ($item) {
            if ($item->es_invitado) {
                $this->asistenciasInvitados[$item->id] = (bool) $item->asistio;
            } else {
                $this->asistencias[$item->id] = isset($item->asistio) ? (bool) $item->asistio : null;
            }
        });

        // Añadimos las nuevas personas a la colección existente
        $this->personas = $this->personas->concat($personasNuevas);
    }

    // La lógica para saber si hay más personas no cambia
    $this->noMasPersonas = $this->personas->count() < (($this->paginaActual - 1) * $this->cantidadPorCarga) + $this->cantidadPorCarga;

  }

  /**
   * Helper para no repetir los filtros de usuario.
   */
  protected function aplicarFiltrosDeUsuario($query)
  {
      if (!empty($this->arrayTiposUsuarios)) {
          $query->whereIn('tipo_usuario_id', collect($this->arrayTiposUsuarios)->flatten()->toArray());
      }
      if (!empty($this->arrayGenero)) {
          $query->whereIn('genero', $this->arrayGenero);
      }
      if (!empty($this->arraySedes)) {
          $query->whereIn('sede_id', $this->arraySedes);
      }
      if (!empty($this->arrayEdad)) {
          $rangos = RangoEdad::whereIn('id', $this->arrayRangosEdades)->get();
          $query->where(function ($q) use ($rangos) {
              foreach ($rangos as $rango) {
                  $q->orWhereBetween(DB::raw('TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE())'), [$rango->edad_minima, $rango->edad_maxima]);
              }
          });
      }
      return $query;
  }

  public function sumarInvitados()
  {
    $this->reporteReunion->invitados += 1;
    $this->reporteReunion->cantidad_asistencias += 1;
    $this->reporteReunion->save();
    $this->actualizarSumatorias();
  }

  public function restarInvitados()
  {
    if ($this->reporteReunion->invitados > 0) {
      $this->reporteReunion->invitados -= 1;
      $this->reporteReunion->cantidad_asistencias -= 1;
      $this->reporteReunion->save();
    }
    $this->actualizarSumatorias();
  }

  public function sumarClasificacion($id)
  {
    $registro = ClasificacionAsistenteReporteReunion::where('reporte_reunion_id', $this->reporteReunion->id)
      ->where('clasificacion_asistente_id', $id)
      ->first();

    if (!$registro) {
      $this->reporteReunion->clasificacionesAsistentes()->attach($id, ['cantidad' => 1]);
    }
    if ($registro) {
      $registro->cantidad += 1;
      $registro->save();

      $this->reporteReunion->cantidad_asistencias += 1;
      $this->reporteReunion->save();
    }

    $this->actualizarSumatorias();
  }

  public function restarClasificacion($id)
  {
    $registro = ClasificacionAsistenteReporteReunion::where('reporte_reunion_id', $this->reporteReunion->id)
      ->where('clasificacion_asistente_id', $id)
      ->first();

    if ($registro && $registro->cantidad > 0) {
      $registro->cantidad -= 1;
      $registro->save();

      $this->reporteReunion->cantidad_asistencias -= 1;
      $this->reporteReunion->save();
    }

    $this->actualizarSumatorias();
  }

  public function actualizarSumatorias()
  {
    if ($this->reporteReunion) { // Asegúrate que reporteReunion esté disponible
      $this->sumatoriasAdicionales = $this->reporteReunion->clasificacionesAsistentes()->where('tiene_sumatoria_adicional', true)->get();
    }
  }

  #[On('loadMore')]
  public function loadMore()
  {
    $this->paginaActual++;
    $this->cargarPersonas();
  }

  /*public function updatedPruebas($value, $key)
  {
    $usuarioId = (int) $key;
    $asistio = (bool) $value;
  }*/

  #[On('qrCodeScanned')]
  public function qrCodeScanned($qrText)
  {

    // 1. Decodificar el string JSON a un objeto PHP
    $data = json_decode($qrText);

    // 2. Validar que el QR sea un JSON válido y contenga la propiedad "tipo"
    if (!$data || !isset($data->tipo)) {
        $this->dispatch(
            'msn',
            msnIcono: 'error',
            msnTitulo: 'QR Inválido',
            timer: 3000,
            msnTexto: 'El código QR no tiene el formato esperado.'
        );
        return;
    }

    // 3. Usar un switch para ejecutar la lógica según el tipo
    switch ($data->tipo) {
        case 'perfil':

          if (!isset($data->id)) {
              $this->dispatch('msn', msnIcono: 'error', msnTitulo: 'QR Inválido',  timer: 3000, msnTexto: 'El código QR no tiene el formato esperado.');
              return;
          }

          $usuario = User::find($data->id);

          if (!$usuario) {
              $this->dispatch('msn', msnIcono: 'error', msnTitulo: 'Usuario no encontrado', timer: 3000, msnTexto: 'El usuario asociado a este QR no fue encontrado.');
              return;
          }

          $puedeAsistir = false;
          $mensajeError = '';

          if ($this->reporteReunion->habilitar_reserva && $this->reporteReunion->solo_reservados_pueden_asistir) {

              $tieneReserva = $usuario->reservasReunion()
                                      ->where('reporte_reunion_id', $this->reporteReunion->id)
                                      ->exists();

              if ($tieneReserva) {
                  $puedeAsistir = true;
              } else {
                  $mensajeError = 'Esta persona no tiene una reserva para esta reunión.';
              }

          } else {
              $query = User::query()->where('id', $usuario->id);
              $queryConFiltros = $this->aplicarFiltrosDeUsuario($query);

              if ($queryConFiltros->exists()) {
                  $puedeAsistir = true;
              } else {
                  $mensajeError = 'Esta persona no cumple con los requisitos para esta reunión.';
              }
          }

          if ($puedeAsistir) {
              $yaAsistio = DB::table('asistencia_reuniones')
              ->where('user_id', $usuario->id)
              ->where('reporte_reunion_id', $this->reporteReunion->id)
              ->exists();

              if ($yaAsistio) {
                $this->dispatch('msn', msnIcono: 'info', msnTitulo: 'Ya registrado', timer: 3000, msnTexto: 'La asistencia para esta persona ya fue registrada anteriormente.');
                return;
              }
              // FIN DE LA NUEVA VALIDACIÓN

              $this->siAsistio($usuario->id);
              $this->actualizarSumatorias();

              $this->asistencias[$usuario->id] = true;
          } else {
              $this->dispatch('msn', msnIcono: 'warning', msnTitulo: 'Acceso denegado', timer: 3000, msnTexto: $mensajeError);
          }

          break;

        case 'reserva':
            if (!isset($data->id)) {
                $this->dispatch('msn', msnIcono: 'error', msnTitulo: 'QR Inválido', timer: 3000, msnTexto: 'El código QR no tiene el formato esperado.');
                return;
            }

            $reserva = ReservaReunion::find($data->id);

            if (!$reserva) {
                $this->dispatch('msn', msnIcono: 'error', msnTitulo: 'Reserva no encontrada', timer: 3000, msnTexto: 'La reserva asociada a este QR no existe.');
                return;
            }

            if ($reserva->reporte_reunion_id != $this->reporteReunion->id) {
                $this->dispatch('msn', msnIcono: 'warning', msnTitulo: 'Reserva incorrecta', timer: 3000, msnTexto: 'Esta reserva no pertenece a la reunión actual.');
                return;
            }

            if ($reserva->registrada) {
              $this->dispatch('msn', msnIcono: 'info', msnTitulo: 'Ya registrado', timer: 3000, msnTexto: 'La asistencia para esta reserva ya fue registrada anteriormente.');
              return;
            }

            if ($reserva->user_id) {
              $this->siAsistio($reserva->user_id);
              $this->actualizarSumatorias();
              $this->asistencias[$reserva->user_id] = true;
            } else {
              $this->siAsistioInvitado($reserva->id);
              $this->actualizarSumatorias();
              $this->asistenciasInvitados[$reserva->id] = true;
            }
            break;

        default:
            // Se ejecuta si el "tipo" no es ninguno de los casos anteriores
            $this->dispatch(
                'msn',
                msnIcono: 'warning',
                msnTitulo: 'QR Desconocido',
                msnTexto: 'El tipo de QR escaneado no es reconocido.'
            );
            break;
    }

  }

  public function siAsistio($usuarioId)
  {
    $horaActual = Carbon::now();

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
      ->first();

    if ($fechaHoy >= $this->reporteReunion->fecha && $fechaHoy <= $fechaMaxima) {
      if (
        $fechaHoy < $fechaMaxima || ($fechaHoy == $fechaMaxima && $horaActual->format('h:i A') <= $horaMaxima->format('h:i A')) ||
        $this->rolActivo->hasPermissionTo('reporte_reuniones.privilegio_anadir_asistente_reporte_reunion_cualquier_fecha')
      ) {
        if ($asistio_reunion) {
          $this->dispatch(
            'msn',
            msnIcono: 'info',
            msnTitulo: '¡Ups!',
            timer: 3000,
            msnTexto: 'Hola, tu asistencia ya fue reportada anteriormente.'
          );
        } else {

          // NO EXISTE REGISTRO lo creo
          $this->reporteReunion->usuarios()->attach($usuarioId, array(
            "autor_creacion_asistencia_id" => auth()->user()->id,
          ));

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

          // si tiene reserva, la actualizo
          $usuario->reservasReunion()
          ->where('reporte_reunion_id', $this->reporteReunion->id)
          ->update(['registrada' => true]);

          $this->dispatch(
            'msn',
            msnIcono: 'success',
            msnTitulo: 'Exitoso!',
            timer: 3000,
            msnTexto: 'Se registro tu asistencia a esta reunión con éxito.'
          );
        }
      } else {
        $this->dispatch(
          'msn',
          msnIcono: 'warning',
          msnTitulo: 'Ups!',
          timer: 3000,
          msnTexto: 'Hola, el tiempo para registrar la asistencia a reunión a expirado.'
        );
      }
    } else {
      $this->dispatch(
        'msn',
        msnIcono: 'warning',
        msnTitulo: 'Ups!',
        timer: 3000,
        msnTexto: 'Hola, el tiempo para registrar la asistencia a reunión a expirado.'
      );
    }
  }

  public function noAsistio($usuarioId)
  {
    $horaActual = Carbon::now();

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
      ->first();

    if ($fechaHoy >= $this->reporteReunion->fecha && $fechaHoy <= $fechaMaxima) {
      if (
        $fechaHoy < $fechaMaxima || ($fechaHoy == $fechaMaxima && $horaActual->format('h:i A') <= $horaMaxima->format('h:i A')) ||
        $this->rolActivo->hasPermissionTo('reporte_reuniones.privilegio_anadir_asistente_reporte_reunion_cualquier_fecha')
      ) {

          if ($asistio_reunion) {

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

                $clasificacionAsistente->pivot->cantidad = $clasificacionAsistente->pivot->cantidad - $sumatoriaAsistentes;
                $clasificacionAsistente->pivot->save();
                $sumatoriaAsistentes = 0;
              }
            }

            $cantidad_asistencias = $this->reporteReunion->cantidad_asistencias;
            $cantidad_asistencias = ((int)$cantidad_asistencias);

            if ($cantidad_asistencias >= 1) {
              $cantidad_asistencias = $cantidad_asistencias - 1;
            }

            $this->reporteReunion->cantidad_asistencias = $cantidad_asistencias;
            $this->reporteReunion->save();

            // Elimina o modifica el registro de la reunion
            $this->reporteReunion->usuarios()->detach($usuarioId);

            // Recalcula las fechas de ultimo reporte
            $ultima_fecha_reporte_reunion = $usuario->ultimo_reporte_reunion;
            $ultima_fecha_reporte_reunion = date('Y-m-d', strtotime($ultima_fecha_reporte_reunion));

            if ($this->reporteReunion->fecha < $ultima_fecha_reporte_reunion) {
              // Consulto la penultima reunión
              $penultimoReporte = $usuario->reportesReunion()->where('fecha', '<', $this->reporteReunion->fecha)->orderBy('fecha', 'DESC')->first();
              if ($penultimoReporte) {
                $usuario->ultimo_reporte_reunion_auxiliar = $penultimoReporte->fecha;
              } else {
                $usuario->ultimo_reporte_reunion_auxiliar = "2016-01-01 00:00:00";
              }
              $usuario->save();
            }

            if ($this->reporteReunion->fecha == $ultima_fecha_reporte_reunion) {
              $usuario->ultimo_reporte_reunion = $usuario->ultimo_reporte_reunion_auxiliar;

              //consulto la penultima reunión
              $penultimoReporte = $usuario->reportesReunion()->where('fecha', '<', $this->reporteReunion->fecha)->orderBy('fecha', 'DESC')->first();
              if ($penultimoReporte) {
                $usuario->ultimo_reporte_reunion_auxiliar = $penultimoReporte->fecha;
              } else {
                $usuario->ultimo_reporte_reunion_auxiliar = "2016-01-01 00:00:00";
              }
              $usuario->save();
            }

            // si tiene reserva, la actualizo
            $usuario->reservasReunion()
            ->where('reporte_reunion_id', $this->reporteReunion->id)
            ->update(['registrada' => false]);

            $this->dispatch(
              'msn',
              timer: 3000,
              msnIcono: 'success',
              msnTitulo: 'Exitoso!',
              msnTexto: 'El registro de asistencia a esta reunión fue eliminado con éxito.',
            );

          } else {

            $this->dispatch(
              'msn',
              msnIcono: 'info',
              msnTitulo: '¡Ups!',
              timer: 3000,
              msnTexto: 'Hola, tu asistencia ya fue eliminada anteriormente.'
            );
          }


        } else {
          $this->dispatch(
            'msn',
            msnIcono: 'warning',
            msnTitulo: 'Ups!',
            timer: 3000,
            msnTexto: 'Hola, el tiempo para registrar la asistencia a reunión a expirado.'
          );
        }
    } else {
      $this->dispatch(
        'msn',
        msnIcono: 'warning',
        msnTitulo: 'Ups!',
        timer: 3000,
        msnTexto: 'Hola, el tiempo para registrar la asistencia a reunión a expirado.'
      );
    }




  }

  public function updatedAsistencias($value, $key)
  {
    if ($value == true) {
      $this->siAsistio($key);
    } else {
      $this->noAsistio($key);
    }

    //$this->cargarPersonas();
    $this->actualizarSumatorias();
  }

  public function updatedAsistenciasInvitados($value, $key)
  {
    if ($value == true) {
      $this->siAsistioInvitado($key);
    } else {
      $this->noAsistioInvitado($key);
    }

    //$this->cargarPersonas();
    $this->actualizarSumatorias();
  }

  public function siAsistioInvitado ($reservaId)
  {
    $horaActual = Carbon::now();
    $fechaMaxima = Carbon::parse($this->reporteReunion->fecha)
      ->addDays($this->reporteReunion->reunion->dias_plazo_reporte)
      ->format('Y-m-d');
    $fechaHoy = Carbon::now()->format("Y-m-d");
    $horaMaxima = Carbon::createFromFormat('h:i:s', $this->reporteReunion->reunion->hora_maxima_reportar_asistencia);

    $reserva = ReservaReunion::find($reservaId);

    if ($fechaHoy >= $this->reporteReunion->fecha && $fechaHoy <= $fechaMaxima) {
      if (
        $fechaHoy < $fechaMaxima || ($fechaHoy == $fechaMaxima && $horaActual->format('h:i A') <= $horaMaxima->format('h:i A')) ||
        $this->rolActivo->hasPermissionTo('reporte_reuniones.privilegio_anadir_asistente_reporte_reunion_cualquier_fecha')
      ) {
        if ($reserva->registrada) {
          $this->dispatch(
            'msn',
            msnIcono: 'info',
            msnTitulo: '¡Ups!',
            timer: 3000,
            msnTexto: 'Hola, tu asistencia ya fue reportada anteriormente.'
          );
        } else {
          $reserva->registrada = true;
          $reserva->save();

          $this->reporteReunion->invitados += 1;
          $this->reporteReunion->cantidad_asistencias += 1;
          $this->reporteReunion->save();

          $this->dispatch(
            'msn',
            msnIcono: 'success',
            msnTitulo: 'Exitoso!',
            timer: 3000,
            msnTexto: 'Se registro tu asistencia a esta reunión con éxitodd.'
          );
        }
      } else {
        $this->dispatch(
          'msn',
          msnIcono: 'warning',
          msnTitulo: 'Ups!',
          timer: 3000,
          msnTexto: 'Hola, el tiempo para registrar la asistencia a reunión a expirado.'
        );
      }
    } else {
      $this->dispatch(
        'msn',
        msnIcono: 'warning',
        msnTitulo: 'Ups!',
        timer: 3000,
        msnTexto: 'Hola, el tiempo para registrar la asistencia a reunión a expirado o la todavía no es la fecha del evento.'
      );
    }
  }

  public function noAsistioInvitado ($reservaId)
  {

    $horaActual = Carbon::now();
    $fechaMaxima = Carbon::parse($this->reporteReunion->fecha)->addDays($this->reporteReunion->reunion->dias_plazo_reporte)->format('Y-m-d');
    $fechaHoy = Carbon::now()->format("Y-m-d");
    $horaMaxima = Carbon::createFromFormat('h:i:s', $this->reporteReunion->reunion->hora_maxima_reportar_asistencia);

    $reserva = ReservaReunion::find($reservaId);

    if ($fechaHoy >= $this->reporteReunion->fecha && $fechaHoy <= $fechaMaxima) {
      if (
        $fechaHoy < $fechaMaxima || ($fechaHoy == $fechaMaxima && $horaActual->format('h:i A') <= $horaMaxima->format('h:i A')) ||
        $this->rolActivo->hasPermissionTo('reporte_reuniones.privilegio_anadir_asistente_reporte_reunion_cualquier_fecha')
      ) {
          if ($reserva->registrada) {

            $reserva->registrada = false;
            $reserva->save();

            $cantidad_asistencias = $this->reporteReunion->cantidad_asistencias;
            $cantidad_asistencias = ((int)$cantidad_asistencias);

            if ($cantidad_asistencias >= 1) {
              $cantidad_asistencias = $cantidad_asistencias - 1;
            }

            $cantidad_invitados = $this->reporteReunion->invitados;
            $cantidad_invitados = ((int)$cantidad_invitados);

            if ($cantidad_invitados >= 1) {
              $cantidad_invitados = $cantidad_invitados - 1;
            }

            $this->reporteReunion->cantidad_asistencias = $cantidad_asistencias;
            $this->reporteReunion->invitados = $cantidad_invitados;
            $this->reporteReunion->save();

            $this->dispatch(
              'msn',
              timer: 3000,
              msnIcono: 'success',
              msnTitulo: 'Exitoso!',
              msnTexto: 'El registro de asistencia a esta reunión fue eliminado con éxito.',
            );

          } else {
            $this->dispatch(
              'msn',
              msnIcono: 'info',
              msnTitulo: '¡Ups!',
              timer: 3000,
              msnTexto: 'Hola, tu asistencia ya fue removida anteriormente.'
            );
          }
      } else {
        $this->dispatch(
          'msn',
          msnIcono: 'warning',
          msnTitulo: 'Ups!',
          timer: 3000,
          msnTexto: 'Hola, el tiempo para modificar la asistencia a expirado.'
        );
      }
    } else {
      $this->dispatch(
        'msn',
        msnIcono: 'warning',
        msnTitulo: 'Ups!',
        timer: 3000,
        msnTexto: 'Hola, el tiempo para modificar la asistencia a expirado.'
      );
    }
  }



  public function render()
  {
    $this->actualizarSumatorias();
    return view('livewire.reporte-reuniones.asistentes', [
      'configuracion' => $this->configuracion
    ]);
  }
}
