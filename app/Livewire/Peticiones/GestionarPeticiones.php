<?php

namespace App\Livewire\Peticiones;

use App\Helpers\Helpers;
use App\Mail\DefaultMail;
use App\Models\Configuracion;
use App\Models\Iglesia;
use App\Models\Peticion;
use App\Models\SeguimientoPeticion;
use App\Models\User;
use Exception;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

use Carbon\Carbon;
use stdClass;

class GestionarPeticiones extends Component
{
  public $titulo = '';

  //modalRespuesta
  public $peticionRespuestaId;
  public $respuesta="";

  //modalSeguimento
  public $peticionSeguimientoId;
  public $descripcionSeguimiento="";
  public $versiculosRecomendados;
  public $libros;

  //modalBiblia
  public $listadoVersiculos;

  public function mount()
  {
    $this->libros= Helpers::libros();
    $this->listadoVersiculos = '<center>
      <h5 class="m-5"><i class="ti ti-search"></i> Resultados de la busqueda</h5>
    </center>';
  }


  #[On('modalRespuesta')]
  public function modalRespuesta($peticionId, $personaId)
  {
    $usuario = User::withTrashed()->select('id','primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido')->find($personaId);
    $this->titulo = "Agregar respuesta de <b>".$usuario->nombre(3)."</b>";

    $this->peticionRespuestaId = $peticionId;
    $this->respuesta = '';
    $this->dispatch('abrirModal', nombreModal: 'modalRespuesta');
  }

  public function addRespuesta()
  {
    $peticion = Peticion::find($this->peticionRespuestaId);

    $peticion->estado=2;
		$peticion->respuesta = $this->respuesta;
		$peticion->save();
    return redirect(request()->header('Referer'))->with('success', ' ¡Muy bien! la respuesta a la petición se agrego de éxitosa.');
  }

  #[On('modalSeguimiento')]
  public function modalSeguimiento($peticionId, $personaId)
  {
    $usuario = User::withTrashed()->select('id','primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido')->find($personaId);
    $this->titulo = "Agregar seguimiento a <b>".$usuario->nombre(3)."</b>";

    $this->peticionSeguimientoId = $peticionId;
    $this->descripcionSeguimiento = "<p>¡Hola! <b>".$usuario->nombre(3)."</b>. </p>";

    $this->versiculosRecomendados = '<p>Cargando versiculos recomendados</p>
    <div class="spinner-border spinner-border-lg text-primary mt-1" role="status">
    <span class="visually-hidden">Loading...</span>
    </div>';

    $this->dispatch('textoInicialSeguimiento', textoInicial:  $this->descripcionSeguimiento );
    $this->dispatch('abrirModal', nombreModal: 'modalSeguimiento');
    $this->dispatch('cargarVersiculosRecomendados', peticionId: $peticionId);
  }

  public function addSeguimiento()
  {
    $configuracion = Configuracion::find(1);
    $peticion = Peticion::find($this->peticionSeguimientoId);
    $usuario_logueado=auth()->user();
    $usuario = User::withTrashed()->select('id','primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido','telefono_movil','email','pais_id')->find($peticion->user_id);

		$email= $usuario->email;
		$telefono= $usuario->telefono_movil;

		if(isset($usuario->pais->prefijo))
		{
			$prefijo= $usuario->pais->prefijo;
		}else
		{
			$iglesia= Iglesia::find(1);
			$prefijo= $iglesia->pais->prefijo;
		}

    // Crear el nuevo seguimiento
    $seguimiento = new SeguimientoPeticion;
    $seguimiento->peticion_id = $peticion->id;
    $seguimiento->fecha = Carbon::now()->format('Y-m-d');
    $seguimiento->descripcion = $this->descripcionSeguimiento;
    $seguimiento->usuario_id = $usuario_logueado->id;
    $seguimiento->save();

    // Enviar el correo
    if ($email!="")
    {
      $mensaje = $this->descripcionSeguimiento;
      $mailData = new stdClass();
      $mailData->subject = 'Seguimiento petición';
      $mailData->nombre = $usuario->nombre(3);
      $mailData->mensaje = $mensaje;

      if ($peticion->tipoPeticion->banner_email != '') {
        $mailData->banner =
          $configuracion->version == 1
          ? Storage::url(
            $configuracion->ruta_almacenamiento . '/img/email/' . $peticion->tipoPeticion->banner_email
          )
          : Storage::url(
            $configuracion->ruta_almacenamiento . '/img/email/' . $peticion->tipoPeticion->banner_email
          );
      }

      //Mail::to($usuario->email)->send(new DefaultMail($mailData));
      Mail::to('softjuancarlos@gmail.com')->send(new DefaultMail($mailData));
    }

    // Se actualiza la peticion
		if($peticion->estado==1)
		{
			$peticion->estado=3;
			$peticion->save();
		}

    $respuesta="El seguimiento fue realizado con éxito. ";

    if($telefono!="")
		{
			$validarTelefono = strpos($telefono, '+');

			if($validarTelefono==FALSE)
			{
				$pefijoTelefono = $prefijo.$telefono;
			}

			$mensajeWhatsapp= 'Hola '.$usuario->nombre(3).' DIOS te bendiga ';

			$respuesta.=' Si deseas puedes continuar con el seguimiento de <b>'.$usuario->nombre(3).'</b>
			a través de WhatsApp, da clic aquí <a target="_blank" href="https://api.whatsapp.com/send?phone='.$pefijoTelefono.'&text='.$mensajeWhatsapp.'" ><i class="ti ti-brand-whatsapp"></i> '.$telefono.'</a>';

		}

    return redirect(request()->header('Referer'))->with('success', $respuesta);
  }

  #[On('cargarVersiculosRecomendados')]
  public function versiculosSegunTipoPeticion($peticionId)
	{
    $peticion = Peticion::find($peticionId);
    $tipoPeticion = $peticion->tipoPeticion;
    $versiculos = json_decode($tipoPeticion->json_versiculos);

    if($versiculos)
    {
      $key = config('variables.biblia_key');
      $arrContextOptions = [
        'ssl' => [
          'verify_peer' => false,
          'verify_peer_name' => false,
        ],
      ];

      $this->versiculosRecomendados = '<p>Añade el versículo dando clic sobre él</p>';
      foreach ($versiculos as $versiculo)
			{
        try {
          $respuestaText = file_get_contents(
            'https://api.biblia.com/v1/bible/content/RVR60.txt?passage=' .
            $versiculo->cita .
            '&key=' .
            $key .
            '&style=neVersePerLineFullReference&culture=es',
            false,
            stream_context_create($arrContextOptions)
          );

          $this->versiculosRecomendados = $this->versiculosRecomendados.' <button type="button" class="btn rounded-pill btn-outline-primary waves-effect btn-sm mt-1 add-versiculo" data-toggle="tooltip" data-placement="top" title="'.$respuestaText.'" data-verso="'.$respuestaText.'" data-cita="'.$versiculo->titulo.'" >'.$versiculo->titulo.'</button>';
        } catch (Exception $e) {
					$this->versiculosRecomendados = $this->versiculosRecomendados.'<button type="button" class="btn rounded-pill btn-outline-primary waves-effect mt-1 ">'.$versiculo->titulo.' (No encontrado)</button>';
				}
			}
    }
	}

  #[On('buscarBibliaCita')]
  public function buscarBibliaCita($libro,$capitulo,$versiculo)
  {
    $key = config('variables.biblia_key');
    $arrContextOptions = [
      'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
      ],
    ];

		$libroFormateado= Helpers::eliminarTildes($libro);
		$libroFormateado= str_replace(' ', '', $libroFormateado);
		$citaFormateada =$libroFormateado.$capitulo.".".$versiculo;
		$cita=$libro." ".$capitulo.":".$versiculo;

		// cita biblica
		try {
      $respuestaText = file_get_contents("https://api.biblia.com/v1/bible/content/RVR60.txt?passage=".$citaFormateada."&key=".$key."&style=neVersePerLineFullReference&culture=es", false, stream_context_create($arrContextOptions));
      $this->listadoVersiculos = '
      <div class="d-flex bd-highlight">
        <div class="p-2 w-100 bd-highlight"><i>"'.$respuestaText.'"</i> <b>('.$cita.', RVR60)</b></div>
        <div class="p-2 flex-shrink-1 bd-highlight align-self-center"><button type="button" class="btn btn-sm rounded-pill btn-success waves-effect waves-light add-versiculo my-auto" data-verso="'.$respuestaText.'" data-cita="'.$cita.'" >Añadir</button></div>
      </div>';
    } catch (Exception $e) {
      $this->listadoVersiculos = '<center>
        <h5 class="m-5"><i class="ti ti-search"></i>  La búsqueda no arrojo ningún resultado </h5>
      </center>';
    }
  }

  #[On('buscarBibliaPalabraClave')]
  public function buscarBibliaPalabraClave($palabrasClaves)
  {

    $key = config('variables.biblia_key');
    $arrContextOptions = [
      'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
      ],
    ];

    $respuesta = file_get_contents("https://api.biblia.com/v1/bible/search/RVR60.js?query=".$palabrasClaves."&mode=verse&start=0&limit=30&key=".$key."&culture=es", false, stream_context_create($arrContextOptions));
    $respuesta = json_decode($respuesta);

    if($respuesta->resultCount==0)
    {
      $this->listadoVersiculos = '<center>
        <h5 class="m-5"><i class="ti ti-search"></i>  La búsqueda no arrojo ningún resultado </h5>
      </center>';
    }else{
      $this->listadoVersiculos = '';
      foreach($respuesta->results as $versiculo)
      {
        $this->listadoVersiculos = $this->listadoVersiculos.'
        <div class="d-flex bd-highlight">
          <div class="p-2 w-100 bd-highlight"><i>"'.$versiculo->preview.'"</i> <b>('.$versiculo->title.', RVR60)</b></div>
          <div class="p-2 flex-shrink-1 bd-highlight align-self-center"><button type="button" class="btn btn-sm rounded-pill btn-success waves-effect waves-light add-versiculo my-auto" data-verso="'.$versiculo->preview.'" data-cita="'.$versiculo->title.'" >Añadir</button></div>
        </div>';
      }
    }

  }

  public function render()
  {
      return view('livewire.peticiones.gestionar-peticiones');
  }
}
