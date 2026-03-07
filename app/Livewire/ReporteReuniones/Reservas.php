<?php

namespace App\Livewire\ReporteReuniones;

use App\Helpers\Helpers;
use App\Models\Configuracion;
use App\Models\Reunion;
use App\Models\User;
use App\Models\ReporteReunion;
use App\Models\ReservaReunion;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Livewire\Attributes\On;
use stdClass;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail; // <-- Importa la clase Mail
use App\Mail\DefaultMail;           // <-- Importa tu Mailable


class Reservas extends Component
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

  public $buscar = '';
  public $noMasPersonas = false;
  public $isLoading = false;
  public $aforo;
  public $aforoOcupado;
  public $aforoDisponible;

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

  public string $guestName = '';
  public string $guestEmail = '';

  public $rolActivo = null;

  public function mount()
  {
    $this->rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $this->total = 0;
    $reunion = Reunion::withTrashed()->find($this->reporteReunion->reunion_id);
    $this->configuracion = Configuracion::first();
    $this->usuario = auth()->user();
    $this->reunion_id = $this->reporteReunion->reunion_id;
    $this->reunion = Reunion::withTrashed()->find($this->reunion_id);
    $this->arrayTiposUsuarios = $this->reunion->tipoUsuarios()->select('tipo_usuarios.id')->pluck('tipo_usuarios.id')->toArray();
    $this->tiposOfrendas = $reunion->tiposOfrendas()->orderBy("nombre")->get();
    $this->arrayGenero = json_decode($this->reunion->genero);
    $this->arrayRangosEdades = $this->reunion->rangosEdades()->get();
    $this->arraySedes = $this->reunion->sedes()->select('sedes.id')->pluck('sedes.id')->toArray();
    $this->arraySedes[] = $this->reunion->sede_id;
    $this->actualizarAforo();
    $this->cargarPersonas();

  }

  public function actualizarAforo()
  {
      // Refresca la instancia del modelo para obtener los últimos datos de la BD
      $this->reporteReunion->refresh();

      // Actualiza las propiedades públicas
      $this->aforo = $this->reporteReunion->aforo;
      $this->aforoOcupado = $this->reporteReunion->aforo_ocupado;
      $this->aforoDisponible = $this->aforo - $this->aforoOcupado;
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
                DB::raw("true as es_invitado"),
                DB::raw("CONCAT('guest_', reservas_reuniones.id) as unique_key"),
                'reservas_reuniones.id as reserva_id',
                DB::raw('true as tiene_reserva'),
                'reservas_reuniones.registrada as tiene_asistencia'
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
                DB::raw("false as es_invitado"),
                DB::raw("CONCAT('user_', users.id) as unique_key"),
                'reservas_reuniones.id as reserva_id',
                DB::raw('reservas_reuniones.id IS NOT NULL as tiene_reserva'),
                DB::raw('asistencia_reuniones.id IS NOT NULL as tiene_asistencia')
            )->whereNotNull('users.email_verified_at')
            ->leftJoin('reservas_reuniones', function ($join) use ($reporteReunionId) {
                $join->on('users.id', '=', 'reservas_reuniones.user_id')
                     ->where('reservas_reuniones.reporte_reunion_id', $reporteReunionId);
            })
            ->leftJoin('asistencia_reuniones', function ($join) use ($reporteReunionId) {
                $join->on('users.id', '=', 'asistencia_reuniones.user_id')
                     ->where('asistencia_reuniones.reporte_reunion_id', $reporteReunionId);
            });

        // Si el rol solo puede ver a su ministerio, restringimos la consulta de usuarios.
        if ($this->rolActivo->hasPermissionTo('personas.lista_asistentes_solo_ministerio')) {
          $discipuloIds = $this->usuario->discipulos()->pluck('id')->toArray();
          if (empty($discipuloIds)) {
              $discipuloIds = [0];
          }
          $usersQuery->whereIn('users.id', $discipuloIds);
        }

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

      }

      // --- Paginación y Carga (simplificada) ---
      $finalQuery->orderBy('primer_nombre', 'asc');

      if ($this->paginaActual == 1) {
          $this->personas = $finalQuery->take($this->cantidadPorCarga)->get();
      } else {
          $personasNuevas = $finalQuery->skip(($this->paginaActual - 1) * $this->cantidadPorCarga)
              ->take($this->cantidadPorCarga)->get();
          $this->personas = $this->personas->concat($personasNuevas);
      }

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


  #[On('loadMore')]
  public function loadMore()
  {
    $this->paginaActual++;
    $this->cargarPersonas();
  }


  public function añadirInvitado()
  {
      // Reseteamos los campos por si tenía datos anteriores
      $this->reset(['guestName', 'guestEmail']);

      $this->dispatch('abrirModal', nombreModal: 'modalNuevoInvitado');
  }


  public function crearInvitado()
  {
      // a. Validar los datos del formulario del modal
       $rules = [
        'guestName' => 'required|string|max:255',
        'guestEmail' => 'required|email|max:255',
      ];

      $messages = [
          'guestName.required' => 'El nombre es obligatorio.',
          'guestEmail.email'   => 'El formato del correo electrónico no es válido.',
      ];

      $attributes = [
          'guestName' => 'nombre',
          'guestEmail' => 'Email',
      ];

      $this->validate($rules, $messages, $attributes);

      // Comprobar el aforo general
      if (!$this->reporteReunion->hayAforoDisponible()) {
          $this->dispatch('msn', msnIcono: 'info', msnTitulo: '¡Ups! se agotaron los cupos', timer: 3000, msnTexto: 'No hay cupos disponibles.');
          return;
      }

      // c. Crear la reserva del invitado sin responsable
      $reserva = ReservaReunion::create([
          'reporte_reunion_id' => $this->reporteReunion->id,
          'invitado' => true,
          'nombre_invitado' => $this->guestName,
          'email_invitado' => $this->guestEmail,
          'responsable_id' => null, // Sin responsable
          'autor_creacion_reserva_id' => auth()->id(), // El admin que lo crea
      ]);

      // Actualizar el aforo
      $this->reporteReunion->increment('aforo_ocupado');
      $this->actualizarAforo();

      // Refrescar la lista de personas
      $this->paginaActual = 1;
      $this->personas = collect();
      $this->cargarPersonas();

      // Enviar correo
      if (filter_var($this->guestEmail, FILTER_VALIDATE_EMAIL)) {
        // Preparamos los datos para el correo
        $mailData = new stdClass();
        $mailData->subject = 'Tu reserva para: ' . $this->reporteReunion->reunion->nombre;
        $mailData->saludo = 'si';
        $mailData->nombre = $this->guestName;
        $mailData->mensaje = "Te confirmamos que tu reserva ha sido realizada con éxito. Adjunto encontrarás tu ticket con el código QR para el ingreso.";

        // Generamos el PDF
        $pdfData = $reserva->generarPdf();
        $pdfFilename = 'Reserva-' . $reserva->id . '.pdf';

        // Enviamos el correo con el PDF adjunto
        Mail::to($this->guestEmail)->send(new DefaultMail($mailData, $pdfData, $pdfFilename));
      }


      // Cerrar el modal y mostrar mensaje de éxito
      $this->dispatch('cerrarModal', nombreModal: 'modalNuevoInvitado');
      $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Muy bien!', timer: 2000, msnTexto: 'Invitado agregado correctamente.');
  }


  public function render()
  {
      return view('livewire.reporte-reuniones.reservas');
  }
}
