<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Validation\Rules\File;
use App\Mail\InscripcionConfirmacionMail;
use App\Mail\CompraConfirmacionMail;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Validator; // Importar la clase Validator
use App\Services\ValidadorEscuelas;
use Illuminate\Validation\Rule;
use App\Helpers\Helpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use \stdClass;
use Exception;

use App\Models\Actividad;
use App\Models\ActividadCarritoCompra;
use App\Models\ActividadCategoria;
use App\Models\Moneda;
use App\Models\Inscripcion;
use App\Models\Iglesia;
use App\Models\Configuracion;
use App\Models\User;
use App\Models\Compra;
use App\Models\Pago;
use App\Models\RespuestaElementoFormulario;
use App\Models\ElementoFormularioActividad;
use App\Models\SedeDestinatario;


class CarritoController extends Controller
{
  //
  public function __construct()
  {
    // Aplicar middleware auth a todos los métodos excepto listado y perfil
    $this->middleware('auth')->except(['carrito', 'formulario', 'checkout', 'guardarFormulario', 'inscripcionFinalizada', 'compraFinalizada', 'descargarTicketPdf']);
  }


  public function carrito(Actividad $actividad)
  {
    // --- INICIO DE LA CORRECCIÓN CLAVE ---
    $compra = null; // Por defecto, no hay compra. Se usa null en lugar de [].

    // Si el usuario está autenticado, se busca si ya tiene una compra iniciada.
    if (Auth::check()) {
      $compra = Compra::where('user_id', auth()->id())
        ->where('actividad_id', $actividad->id)
        ->first(); // first() devuelve el objeto o null, que es el tipo correcto.
    }
    // --- FIN DE LA CORRECCIÓN CLAVE ---

    return view('contenido.paginas.carrito.carrito', [
      'actividad' => $actividad,
      'configuracion' => Configuracion::first(),
      'compraId' => $compra?->id, // Ahora esto funciona correctamente con null.
      'contador' => 1,
      'totalSecciones' => $actividad->elementos->count() > 0 ? 4 : 3,
    ]);
  }


  public function destinatario(Actividad $actividad)
  {
    $sedes = SedeDestinatario::all();
    $centro = [
      'lat' => 4.60971, // Latitud de Bogotá
      'lng' => -74.08175 // Longitud de Bogotá
    ];

    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $usuario = User::find($rolActivo->pivot->model_id);

    if (count($actividad->destinatarios) > 0) {
      $contador = 1;
      $totalSecciones = 4;
    } else {
      $contador = 1;
      $totalSecciones = 3;
    }

    //dd($sedes->first())
    return view('contenido.paginas.carrito.destinatario',  [
      'sedes' => $sedes,
      'centro' => $centro,
      'contador' => $contador,
      'totalSecciones' => $totalSecciones,
      'rolActivo' => $rolActivo,
      'usuario' => $usuario,
      'actividad'=>$actividad

    ]);
  }

  public function iniciarProcesoAbono(Actividad $actividad)
  {
    // Primero, asegurarnos de que el usuario está autenticado
    if (!Auth::check()) {
      return redirect()->route('login')->with('error', 'Debes iniciar sesión para continuar.');
    }

    $usuario = auth()->user();

    // Buscamos una compra existente para este usuario y esta actividad
    $compraExistente = Compra::where('user_id', $usuario->id)
      ->where('actividad_id', $actividad->id)
      ->first();

    if ($compraExistente) {
      // Si ya existe una compra, redirigimos con el ID de la compra y primeraVez = 0
      return redirect()->route('carrito.abonoCarrito', [
        'compra' => $compraExistente->id,
        'actividad' => $actividad->id,
        'primeraVez' => 0
      ]);
    } else {
      // Si no existe, es la primera vez. Redirigimos con un ID de compra "0" como placeholder y primeraVez = 1
      return redirect()->route('carrito.abonoCarrito', [
        'compra' => 0, // Usamos 0 como indicador de que no hay compra
        'actividad' => $actividad->id,
        'primeraVez' => 1
      ]);
    }
  }

  public function abonoCarrito($compra_id, Actividad $actividad, $primeraVez)
  {
    $usuario = Auth::user(); // Obtenemos el usuario autenticado
    $compra = null;

    if ($primeraVez == '0') {
      // Si no es la primera vez, buscamos la compra por su ID.
      // Nos aseguramos de que pertenezca al usuario actual para mayor seguridad.
      $compra = Compra::where('id', $compra_id)
        ->where('user_id', $usuario->id)
        ->first();

      // Si por alguna razón no se encuentra la compra, es un error.
      if (!$compra) {
        abort(404, 'Compra no encontrada.');
      }
    }

    $configuracion = Configuracion::find(1);
    $rolActivo = $usuario->roles()->wherePivot('activo', true)->first();

    if (count($actividad->elementos) > 0) {
      $contador = 1;
      $totalSecciones = 4;
    } else {
      $contador = 1;
      $totalSecciones = 3;
    }

    return view(
      'contenido.paginas.carrito.abono-carrito',
      [
        'actividad' => $actividad,
        'configuracion' =>  $configuracion,
        'rolActivo' => $rolActivo,
        'usuario' => $usuario,
        'compra' => $compra, // Será el objeto Compra o null, correctamente
        'totalSecciones' => $totalSecciones,
        'contador' => $contador,
        'primeraVez' => $primeraVez
      ]
    );
  }


  public function formulario(Compra $compra, Actividad $actividad)
  {
    $configuracion = Configuracion::find(1);
    $usuario = '';
    $html = '';
    $rolActivo = '';
    $usuario = '';
    $usuarioCompra = '';

    // Obtener las respuestas del formulario para la compra actual
    $respuestas = RespuestaElementoFormulario::where('compra_id', $compra->id)->get();

    if (Auth::check()) {

      if ($compra->pariente_usuario_id != null) {

        $usuarioCompra = User::find($compra->pariente_usuario_id);
      } else {
        $usuarioCompra = User::find($compra->user_id);
      }
    }

    // --- INICIO DE LA NUEVA LÓGICA ---
    // Verificamos si la actividad permite invitados y si esta compra específica tiene invitados registrados.
    $tieneInvitadosEnCompra = false;
    if ($actividad->tiene_invitados) {
      // Contamos las inscripciones de esta compra donde el user_id es nulo (es decir, son invitados)
      $tieneInvitadosEnCompra = $compra->inscripciones()->whereNull('user_id')->exists();
    }
    // --- FIN DE LA NUEVA LÓGICA ---

    // --- INICIO DE LA CORRECCIÓN ---

    // 1. Consultamos explícitamente los ítems del carrito para ESTA compra.
    $itemsDelCarrito = ActividadCarritoCompra::where('compra_id', $compra->id)->get();

    // 2. Calculamos el valor total basándonos solo en estos ítems.
    $valorTotal = $itemsDelCarrito->sum(function ($item) {
      return $item->precio * $item->cantidad;
    });

    // --- FIN DE LA CORRECCIÓN ---

    if (count($actividad->elementos) > 0) {
      $contador = 2;
      $totalSecciones = 4;
    } else {
      $contador = 2;
      $totalSecciones = 3;
    }

    if (Auth::check()) {
      $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
      $usuario = User::find($rolActivo->pivot->model_id);
    }
    $moneda = Moneda::find($compra->moneda_id);
    return view(
      'contenido.paginas.carrito.formulario',
      [
        'actividad' => $actividad,
        'compra' => $compra,
        'respuestas' => $respuestas,
        'usuarioCompra' => $usuarioCompra,
        'totalSecciones' => $totalSecciones,
        'contador' => $contador,
        'configuracion' => $configuracion,
        'itemsDelCarrito' => $itemsDelCarrito,
        'valorTotal' => $valorTotal,
        'moneda' => $moneda,
        'tieneInvitadosEnCompra' => $tieneInvitadosEnCompra,
        'puedeEditar' => $respuestas->isEmpty() || $actividad->editar_formulario,
      ]
    );
  }

  public function guardarFormulario(Request $request, Compra $compra)
  {
    // --- 1. CONFIGURACIÓN INICIAL ---
    $configuracion = Configuracion::find(1);
    $usuario = Auth::user();

    // --- 2. VALIDACIÓN DINÁMICA DE CAMPOS REQUERIDOS ---
    $rules = [];
    $customAttributes = [];
    // Obtener respuestas existentes para validar si ya se subieron archivos previamente
    $respuestasExistentes = RespuestaElementoFormulario::where('compra_id', $compra->id)
      ->get()
      ->keyBy('elemento_formulario_actividad_id');

    foreach ($compra->actividad->elementos as $elemento) {
      $fieldName = 'elemento-' . $elemento->id;
      if ($elemento->required) {

        // Validación Específica para Archivos
        if ($elemento->tipoElemento->clase == 'archivo') {
          $yaRespondido = isset($respuestasExistentes[$elemento->id]) && !empty($respuestasExistentes[$elemento->id]->url_archivo);

          // Si no hay respuesta previa y no se está subiendo un archivo ahora -> Error
          if (!$yaRespondido && !$request->hasFile($fieldName)) {
            return back()->withErrors([$fieldName => "El campo '{$elemento->titulo}' es obligatorio."])->withInput();
          }
        }
        // Validación Específica para Imágenes
        elseif ($elemento->tipoElemento->clase == 'imagen') {
          $yaRespondido = isset($respuestasExistentes[$elemento->id]) && !empty($respuestasExistentes[$elemento->id]->url_foto);

          // Verificar si viene como input (base64) o archivo
          $vieneEnRequest = $request->filled($fieldName) || $request->hasFile($fieldName);

          if (!$yaRespondido && !$vieneEnRequest) {
            return back()->withErrors([$fieldName => "El campo de imagen '{$elemento->titulo}' es obligatorio."])->withInput();
          }
        }
        // Validación Estándar para otros campos
        else {
          $rules[$fieldName] = 'required';
          $customAttributes[$fieldName] = "'{$elemento->titulo}'";
        }
      }
    }

    $inscripcion = Inscripcion::where('compra_id', $compra->id)->first();


    // Ejecutamos la validación para todos los campos excepto archivos/imágenes.
    $request->validate($rules, [], $customAttributes);

    // --- 3. PROCESO DE GUARDADO DE RESPUESTAS ---
    // Iteramos sobre todos los datos enviados en el formulario.
    foreach ($request->all() as $key => $value) {
      if (strpos($key, 'elemento-') === 0) {
        $elementoId = substr($key, strlen('elemento-'));
        $elemento = ElementoFormularioActividad::find($elementoId);

        // Usamos firstOrNew para actualizar una respuesta existente o crear una nueva.
        // Esto evita duplicados si el usuario retrocede y reenvía el formulario.
        $respuesta = RespuestaElementoFormulario::firstOrNew([
          'compra_id' => $compra->id,
          'elemento_formulario_actividad_id' => $elementoId,
        ]);
        $respuesta->inscripcion_id = $inscripcion->id;

        // Asignamos el ID del usuario que se está inscribiendo.
        if (isset($usuario)) {

          $respuesta->user_id = $compra->pariente_usuario_id ?  $compra->pariente_usuario_id : $compra->user_id;
        }
        $respuesta->inscripcion_id = $inscripcion->id;



        // --- SWITCH COMPLETO PARA GUARDAR CADA TIPO DE RESPUESTA ---
        switch ($elemento->tipoElemento->clase) {
          case 'corta':
            $respuesta->respuesta_texto_corto = $value;
            break;
          case 'larga':
            $respuesta->respuesta_texto_largo = $value;
            break;
          case 'si_no':
            // Asumiendo que el valor ya es numérico desde el select.
            $respuesta->respuesta_si_no = $value;
            break;
          case 'unica_respuesta':
            $respuesta->respuesta_unica = $value;
            break;
          case 'multiple_respuesta':
            // Convertimos el array de selecciones en una cadena separada por comas.
            $respuesta->respuesta_multiple = is_array($value) ? implode(",", $value) : $value;
            break;
          case 'fecha':
            $respuesta->respuesta_fecha = $value;
            break;
          case 'numero':
            $respuesta->respuesta_numero = $value;
            break;
          case 'moneda':
            $respuesta->respuesta_moneda = $value;
            break;

          case 'archivo':
            if ($request->hasFile($key) && $request->file($key)->isValid()) {
              $file = $request->file($key);
              // Validación manual de peso máximo (en MB)
              if (isset($elemento->peso_maximo) && $elemento->peso_maximo > 0) {
                $fileSizeInBytes = $file->getSize();
                $maxSizeInBytes = $elemento->peso_maximo * 1024 * 1024;
                if ($fileSizeInBytes > $maxSizeInBytes) {
                  return back()->with('error', "El archivo para '{$elemento->titulo}' es muy grande. El tamaño máximo es {$elemento->peso_maximo} MB.")->withInput();
                }
              }
              // Guardado del archivo
              $directorio = $configuracion->ruta_almacenamiento . '/archivos/actividades/';
              $nombreOriginalLimpio = preg_replace('/[^A-Za-z0-9.\-\_]/', '', $file->getClientOriginalName());
              $nombreArchivo = time() . '_' . $nombreOriginalLimpio;
              $rutaGuardada = $file->storeAs($directorio, $nombreArchivo, 'public');
              $respuesta->url_archivo = $nombreArchivo; // Guardamos la ruta completa que devuelve storeAs
            } elseif ($elemento->required && !$respuesta->exists) {
              // Si es requerido y no se subió un nuevo archivo (y no había uno antes).
              return back()->with('error', "El campo '{$elemento->titulo}' es obligatorio.")->withInput();
            }
            break;

          case 'imagen':
            if ($request->filled($key) && str_contains($request->$key, 'base64')) {
              // Validación manual de peso máximo (en MB)
              if (isset($elemento->peso_maximo) && $elemento->peso_maximo > 0) {
                $imagenPartes = explode(';base64,', $request->$key);
                $fileSizeInBytes = isset($imagenPartes[1]) ? strlen(base64_decode($imagenPartes[1])) : 0;
                $maxSizeInBytes = $elemento->peso_maximo * 1024 * 1024;
                if ($fileSizeInBytes > $maxSizeInBytes) {
                  return back()->with('error', "La imagen para '{$elemento->titulo}' es muy grande. El tamaño máximo es {$elemento->peso_maximo} MB.")->withInput();
                }
              }
              // Validación manual de dimensiones máximas
              $anchoMaximo = $elemento->ancho;
              $altoMaximo = $elemento->largo;
              if (isset($anchoMaximo) && $anchoMaximo > 0 && isset($altoMaximo) && $altoMaximo > 0) {
                $imagenBinaria = base64_decode(explode(';base64,', $request->$key)[1]);
                $img = @imagecreatefromstring($imagenBinaria);
                if ($img) {
                  if (imagesx($img) > $anchoMaximo || imagesy($img) > $altoMaximo) {
                    imagedestroy($img);
                    return back()->with('error', "Las dimensiones para '{$elemento->titulo}' superan el máximo de {$anchoMaximo}x{$altoMaximo}px.")->withInput();
                  }
                  imagedestroy($img);
                }
              }
              // Guardado de la imagen
              $numero = random_int(1, 10000);
              $directorioRelativo = $configuracion->ruta_almacenamiento . '/img/respuestas-formulario/';
              $directorioCompleto = public_path('storage/' . $directorioRelativo);
              if (!is_dir($directorioCompleto)) {
                mkdir($directorioCompleto, 0775, true);
              }
              $nombreFoto = 'imagen-' . $elementoId . '-user-' . ($usuario->id ?? '0') . '-' . $numero . '.png';
              file_put_contents($directorioCompleto . $nombreFoto, base64_decode(explode(';base64,', $request->$key)[1]));
              $respuesta->url_foto = $nombreFoto;
            } elseif ($elemento->required && !$respuesta->exists) {
              // Si es requerido y no se envió una nueva imagen (y no había una antes).
              return back()->with('error', "El campo de imagen '{$elemento->titulo}' es obligatorio.")->withInput();
            }
            break;
        } // Fin del switch

        $respuesta->save();
      } // Fin del if strpos
    } // Fin del foreach

    // --- 4. REDIRECCIÓN CONDICIONAL FINAL ---
    if ($compra->valor > 0) {
      // Si el valor es mayor a cero, es una compra de pago. Redirigimos al checkout.
      return redirect()->route('carrito.checkout', ['compra' => $compra, 'actividad' => $compra->actividad]);
    } else {
      // Si el valor es cero, es una inscripción gratuita.
      $inscripcion = $compra->inscripciones->first();
      if (!$inscripcion) {
        Log::error("No se encontró inscripción para la compra gratuita #{$compra->id}");
        return redirect()->route('dashboard')->with('error', 'Hubo un problema al finalizar tu inscripción.');
      }
      // Redirigimos a la página de finalización para inscripciones gratuitas.
      return redirect()->route('carrito.inscripcionFinalizada', ['inscripcion' => $inscripcion, 'actividad' => $compra->actividad]);
    }
  }

  public function eliminarRespuesta(Compra $compra, RespuestaElementoFormulario $respuesta)
  {
    // Medida de seguridad: Asegurarnos de que la respuesta que se intenta borrar
    // realmente pertenece a la compra que se está editando.
    if ($respuesta->compra_id !== $compra->id) {
      abort(403, 'Acción no autorizada.');
    }

    // Verificamos si hay un archivo o una imagen para borrar del disco.
    if ($respuesta->url_archivo) {
      // Borramos el archivo físico del disco 'public'.
      Storage::disk('public')->delete($respuesta->url_archivo);
    }
    if ($respuesta->url_foto) {
      // Borramos la imagen física del disco 'public'.
      Storage::disk('public')->delete($respuesta->url_foto);
    }

    // Eliminamos el registro de la respuesta de la base de datos.
    $respuesta->delete();

    // Redirigimos al usuario de vuelta al formulario con un mensaje de éxito.
    return back()->with('success', 'El archivo/imagen ha sido eliminado correctamente.');
  }


  public function escuelasCarrito($compra_id = null, Actividad $actividad, $primeraVez)
  {
    // 1. Asegurarse de que el usuario está autenticado
    if (!Auth::check()) {
      return redirect()->guest(route('login'))->with('url.intended', route('actividades.perfil', $actividad));
    }
    $usuario = Auth::user();

    // 2. Lógica de Validación (el "guardia de seguridad")
    // Se instancia y se usa el ValidadorEscuelas para determinar a qué puede matricularse el usuario.
    $validador = new ValidadorEscuelas();
    // Llamamos al método con la nueva lógica secuencial
    $resultadoValidacion = $validador->filtrarCategoriasDisponibles($actividad, auth()->user());

    if (!$resultadoValidacion['success']) {
      return redirect()->route('actividades.perfil', $actividad)
        ->with('error', $resultadoValidacion['message']);
    }



    // 4. Si la validación es exitosa, se preparan los datos para la vista
    // $categoriasHabilitadas ahora contiene solo las materias permitidas.
    $categoriasHabilitadas = $resultadoValidacion['categorias'];

    // Se busca la compra usando el $compra_id opcional de la URL.
    $compra = $compra_id ? Compra::find($compra_id) : null;

    $configuracion = Configuracion::find(1);
    $totalSecciones = $actividad->elementos->count() > 0 ? 4 : 3;

    // 5. Renderizar la vista del carrito con los datos correctos y ya filtrados.
    return view(
      'contenido.paginas.carrito.escuelas-carrito',
      [
        'actividad'             => $actividad,
        'configuracion'         => $configuracion,
        'compraActual'          => $compra, // Se pasa la compra encontrada o null
        'primeraVez'            => $primeraVez,
        'categoriasHabilitadas' => $categoriasHabilitadas, // Lista ya validada
        'totalSecciones'        => $totalSecciones,
        'contador'              => 1,
        'compra'                => $compra
      ]
    );
  }

  public function checkout(Compra $compra, Actividad $actividad)
  {
    $configuracion = Configuracion::find(1);
    $html = '';
    $rolActivo = '';
    $usuario = '';
    $usuarioCompra = '';
    $moneda = Moneda::find($compra->moneda_id);


    //return $categoriaSeleccionada;


    if (count($actividad->elementos) > 0) {
      $contador = 3;
      $totalSecciones = 4;
    } else {
      $contador = 3;
      $totalSecciones = 3;
    }
    // Obtener las respuestas del formulario para la compra actual
    if (Auth::check()) {
      $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
      $usuario = User::find($rolActivo->pivot->model_id);
    }



    return view(
      'contenido.paginas.carrito.checkout',
      [
        'actividad' => $actividad,
        'compra' => $compra,
        'usuario' => $usuario,
        'contador' => $contador,
        'totalSecciones' => $totalSecciones,
        'configuracion' => $configuracion,
        'moneda' => $moneda,




      ]
    );
  }

  public function compraFinalizada(Pago $pago)
  {
    // Carga anticipada de relaciones (sin cambios)
    $iglesia = Iglesia::find(1);
    $configuracion = Configuracion::find(1);
    // Carga anticipada de relaciones
    $pago->load([
      'estadoPago',
      'compra.user',
      'compra.actividad.tipo',
      'compra.carritos.categoria.monedas',
      'moneda'
    ]);
    $inscripcion = $pago->compra->inscripciones->first();

    if ($inscripcion) {
      // 2. Determinamos la dirección de correo del destinatario.
      $emailDestinatario = $inscripcion->user?->email ?? $pago->compra->email_comprador;

      // 3. Verificamos que tengamos un email antes de intentar enviar.
      if ($emailDestinatario) {
        try {
          $compra = $pago->compra;
          $actividad = $pago->compra->actividad;
          $inscripcion = Inscripcion::where('compra_id', $compra->id)->first();
          // 4. Enviamos el correo usando nuestra nueva clase Mailable.
          Mail::to($emailDestinatario)->send(new CompraConfirmacionMail($compra, $pago, $inscripcion, $actividad));
        } catch (\Exception $e) {
          // Si el envío falla, lo registramos pero no detenemos el flujo.
          Log::error("Fallo al enviar correo de confirmación para inscripción #{$inscripcion->id}: " . $e->getMessage());
        }
      }
    }
    // --- FIN DE LA LÓGICA DE ENVÍO DE CORREO ---

    // --- Lógica para el QR (sin cambios) ---

    if (Auth::check()) {
      $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
      $usuario = User::find($rolActivo->pivot->model_id);
    }

    if (isset($usuario)) {
      $datosQrArray = [
        'id' => $usuario->id,
        'nombre' => $usuario->nombre(3),
        'tipo' => 'verificar_asistencia_inscripcion_usuario' // Nombre más genérico

      ];
    } else {
      $datosQrArray = [
        'id' => $inscripcion->id,
        'nombre' => 'invitado',
        'tipo' => 'verificar_asistencia_inscripcion_invitado'
      ]; // Nombre más genérico
    }

    $datosParaQr = json_encode($datosQrArray);

    // --- INICIO DE LA LÓGICA MEJORADA ---

    // 1. Definir valores por defecto
    $titulo = 'Estado de la Compra';
    $mensaje = 'Aquí están los detalles de tu transacción.';
    $colorEncabezado = '#6c757d'; // Un color gris neutro por defecto
    $icono = 'generales/img/otros/dibujo_formulario_usuario_respuesta.png';
    $datosAbono = [];

    // 2. Determinar los valores dinámicos basados en el estado del pago
    if ($pago->estadoPago) {
      // Obtenemos el color directamente del registro en la base de datos
      $colorEncabezado = $pago->estadoPago->color ?? $colorEncabezado;

      if ($pago->estadoPago->estado_final_inscripcion) {
        $titulo = '¡Pago exitoso!';
        $mensaje = 'Gracias, hemos confirmado tu pago.';
      } elseif ($pago->estadoPago->estado_pendiente) {
        $titulo = 'Pago pendiente';
        $mensaje = 'Estamos a la espera de la confirmación del pago.';
        $icono = 'generales/img/otros/dibujo_reloj_arena.png';
      } elseif ($pago->estadoPago->estado_anulado_inscripcion) {
        $titulo = 'Pago rechazado o cancelado';
        $mensaje = 'Hubo un problema con tu pago o la transacción fue cancelada.';
        $icono = 'generales/img/otros/dibujo_error.png';
      }
    }

    if ($pago->estadoPago) {
      if ($pago->estadoPago->estado_final_inscripcion) {
        $viewData['titulo'] = '¡Pago exitoso!';
        $viewData['mensaje'] = 'Gracias, hemos confirmado tu pago.';
        $viewData['colorEncabezado'] = 'text-success';
      } elseif ($pago->estadoPago->estado_pendiente) {
        $viewData['titulo'] = 'Pago pendiente';
        $viewData['mensaje'] = 'Estamos a la espera de la confirmación del pago.';
        $viewData['icono'] = 'generales/img/otros/dibujo_reloj_arena.png';
        $viewData['colorEncabezado'] = 'text-warning';
      } elseif ($pago->estadoPago->estado_anulado_inscripcion) {
        $viewData['titulo'] = 'Pago rechazado o cancelado';
        $viewData['mensaje'] = 'Hubo un problema con tu pago o la transacción fue cancelada.';
        $viewData['icono'] = 'generales/img/otros/dibujo_error.png';
        $viewData['colorEncabezado'] = 'text-danger';
      }
    }

    // Lógica para el resumen financiero de abonos
    if ($pago->compra->actividad->tipo->permite_abonos) {
      // Obtenemos el item principal de la compra para saber la categoría
      $itemPrincipal = $pago->compra->carritos->first();
      $categoria = $itemPrincipal->categoria;

      // 1. Calculamos el valor total de la categoría/evento
      $viewData['valorTotalCategoria'] = $categoria->monedas->firstWhere('id', $pago->moneda_id)->pivot->valor ?? 0;

      // 2. Buscamos todos los pagos APROBADOS de esta compra (excluyendo el actual)
      $pagosAnterioresAprobados = Pago::where('compra_id', $pago->compra_id)
        ->where('id', '!=', $pago->id)
        ->whereHas('estadoPago', function ($query) {
          $query->where('estado_final_inscripcion', true);
        })
        ->get();

      $viewData['totalPagadoAnteriormente'] = $pagosAnterioresAprobados->sum('valor');
    }


    // Pasar todos los datos a la vista
    return view('contenido.paginas.carrito.compra-finalizada', [
      'pago' => $pago,
      'configuracion' => $configuracion,
      'titulo' => $titulo,
      'mensaje' => $mensaje,
      'colorEncabezado' => $colorEncabezado,
      'icono' => $icono, // Asegúrate de pasar el icono también
      'datosParaQr' => $datosParaQr,
      'datosAbono' => $datosAbono,
      'iglesia' => $iglesia,
      'viewData' => $viewData
    ]);
  }

  public function inscripcionFinalizada(Inscripcion $inscripcion, Actividad $actividad)
  {
    // Cargas iniciales (sin cambios)
    $iglesia = Iglesia::find(1);
    $configuracion = Configuracion::find(1);

    if (Auth::check()) {
      $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
      $usuario = User::find($rolActivo->pivot->model_id);
      $emailDestinatario = $inscripcion->user->email;
    } else {
      $emailDestinatario = $inscripcion->compra->email_comprado;
    }

    if ($inscripcion) {


      if ($emailDestinatario) {
        try {

          // --- LÍNEA MODIFICADA ---
          // Pasamos ambos objetos al constructor del Mailable.

          Mail::to($emailDestinatario)->send(new InscripcionConfirmacionMail($inscripcion, $actividad));
          // --- FIN DE LA LÍNEA MODIFICADA ---
        } catch (\Exception $e) {
          // 1. Lo registramos en el log (como ya lo hacías)
          Log::error("Fallo al enviar correo para inscripción #{$inscripcion->id}: " . $e->getMessage());

          // 2. Preparamos un mensaje de error para mostrar al usuario.
          $errorMessage = 'Tu pago se completó, pero no pudimos enviar el correo de confirmación.';

          // 3. Si estamos en modo debug, añadimos el error técnico para ayudarte a depurar.
          if (config('app.debug')) {
            $errorMessage .= '<br><br><strong class="text-danger">Error técnico:</strong><br><small>' . htmlspecialchars($e->getMessage()) . '</small>';
          }

          // 4. Guardamos el mensaje en la sesión para que se muestre en la vista.
          session()->flash('error', $errorMessage);
        }
      }
    }

    // --- INICIO DE LA LÓGICA DE MENSAJES DINÁMICOS ---

    // 1. Definimos valores por defecto para el título, mensaje e icono.
    $titulo = 'Inscripción Registrada';
    $mensaje = 'Hemos recibido correctamente tu solicitud de inscripción.';
    $icono = 'generales/img/otros/dibujo_formulario_usuario_respuesta.png'; // Un icono genérico

    // 2. Usamos un switch para determinar el contenido según el estado de la inscripción.
    switch ($inscripcion->estado) {
      case 1: // Estado: Iniciada
        $titulo = 'Inscripción Iniciada';
        $mensaje = 'Hemos guardado tu progreso correctamente. Revisa tu correo electrónico para futuras notificaciones sobre tu inscripción.';
        // Podrías usar un ícono de "checklist" si tienes uno.
        break;

      case 2: // Estado: Pendiente
        $titulo = 'Inscripción Pendiente de Aprobación';
        $mensaje = '¡Gracias! Hemos recibido tu inscripción. Nuestro equipo la revisará y te notificaremos por correo electrónico una vez sea aprobada.';
        $icono = 'generales/img/otros/dibujo_reloj_arena.png'; // Reutilizamos el ícono de 'pendiente'
        break;

      case 3: // Estado: Finalizada
        $titulo = '¡Tu inscripción fue un éxito!';
        $mensaje = '¡Te esperamos en ';
        // Aquí podrías usar un ícono de "éxito" o "celebración".
        break;
    }
    // --- FIN DE LA LÓGICA DE MENSAJES DINÁMICOS ---

    // La lógica para el código QR no cambia.
    if (isset($usuario)) {
      $datosQrArray = [
        'id' => $usuario->id,
        'nombre' => $usuario->nombre(3),
        'tipo' => 'verificar_asistencia_inscripcion_usuario' // Nombre más genérico

      ];
    } else {
      $datosQrArray = [
        'id' => $inscripcion->id,
        'nombre' => 'invitado',
        'tipo' => 'verificar_asistencia_inscripcion_invitado'
      ]; // Nombre más genérico
    }




    $datosParaQr = json_encode($datosQrArray);

    // 3. Pasamos las nuevas variables dinámicas a la vista.
    return view('contenido.paginas.carrito.inscripcion-finalizada', [
      'titulo' => $titulo,
      'mensaje' => $mensaje,
      'icono' => $icono, // Pasamos la nueva variable del icono
      'datosParaQr' => $datosParaQr,
      'iglesia' => $iglesia,
      'actividad' => $actividad,
      'configuracion' => $configuracion,
      'inscripcion' => $inscripcion
    ]);
  }

  /**
   * NUEVO MÉTODO:
   * Genera y descarga el ticket en PDF para una inscripción específica.
   */
  public function descargarTicketPdf(Inscripcion $inscripcion)
  {
    try {
      // 1. Cargar las relaciones necesarias (buena práctica)
      $inscripcion->load('user', 'compra', 'categoriaActividad.actividad');
      $actividad = $inscripcion->categoriaActividad->actividad;

      // 2. Recrear la lógica de generación de datos QR
      // (Copiada de tu método inscripcionFinalizada)
      $datosParaQr = '';
      if ($inscripcion->user_id) {
        // Es un usuario registrado
        $usuario = $inscripcion->user;
        $datosQrArray = [
          'id' => $usuario->id,
          'nombre' => $usuario->nombre(3),
          'tipo' => 'verificar_asistencia_inscripcion_usuario'
        ];
        $datosParaQr = json_encode($datosQrArray);
      } else {
        // Es un invitado
        $datosQrArray = [
          'id' => $inscripcion->id,
          'nombre' => $inscripcion->nombre_inscrito ?? 'invitado',
          'tipo' => 'verificar_asistencia_inscripcion_invitado'
        ];
        $datosParaQr = json_encode($datosQrArray);
      }

      // 3. Generar el PDF
      // (Esta lógica se basa en la que ya tienes en otros componentes)
      // Asegúrate de que esta vista exista:
      $pdf = PDF::loadView('contenido.paginas.actividades.inscripcion-ticket', [
        'inscripcion' => $inscripcion,
        'datosParaQr' => $datosParaQr,
        'actividad' => $actividad
      ]);

      // 4. Definir un nombre de archivo amigable
      $fileName = 'Ticket-Inscripcion-' . $inscripcion->id . '-' . Str::slug($actividad->nombre) . '.pdf';

      // 5. Retornar el PDF como una descarga
      return $pdf->download($fileName);
    } catch (\Exception $e) {
      Log::error("Error al generar PDF para inscripcion #{$inscripcion->id}: " . $e->getMessage());
      return redirect()->back()->with('error', 'No se pudo generar el ticket en PDF.');
    }
  }

  public function descargarComprobantePago(Pago $pago)
  {
      try {
          // Cargar relaciones del pago (excepto matrícula, que cargaremos manualmente para evitar error de tipos SQL)
          $pago->load([
              'estadoPago',
              'compra.user',
              'compra.actividad.tipo',
              'compra.carritos.categoria.monedas',
              'moneda',
              // 'matricula.escuela', // Se carga manualmente abajo
          ]);

          // Carga manual de matrícula para evitar error: operator does not exist: character varying = integer
          // Esto ocurre porque 'referencia_pago' es string y pago->id es int.
          $matricula = \App\Models\Matricula::where('referencia_pago', (string)$pago->id)
              ->with([
                  'escuela',
                  'sede',
                  'materialSede',
                  'horarioMateriaPeriodo.materiaPeriodo.materia',
                  'horarioMateriaPeriodo.horarioBase.aula.sede'
              ])
              ->first();

          // Asignar la relación manualmente al objeto pago
          if ($matricula) {
              $pago->setRelation('matricula', $matricula);
          }

          $configuracion = Configuracion::find(1);
          $iglesia = Iglesia::find(1);

          // Lógica QR (reutilizada)
          $user = $pago->compra->user;
          if ($user) {
              $datosQrArray = [
                  'id' => $user->id,
                  'nombre' => $user->nombre(3),
                  'tipo' => 'verificar_asistencia_inscripcion_usuario'
              ];
          } else {
              $inscripcion = $pago->compra->inscripciones->first();
              $datosQrArray = [
                  'id' => $inscripcion->id ?? 0,
                  'nombre' => 'invitado',
                  'tipo' => 'verificar_asistencia_inscripcion_invitado'
              ];
          }
          $datosParaQr = json_encode($datosQrArray);

          // Datos de Abono (reutilizada)
          $datosAbono = [];
          // (Si necesitas lógica compleja de abonos, cópiala de compraFinalizada,
          // por ahora simplificamos o pasamos lo básico)

          // Usamos la misma vista que la web, o una optimizada para PDF.
          // DomPDF suele requerir estilos inline o simplificados.
          // Intentaremos cargar la misma vista pero con un flag $esPdf = true si es necesario ajustar estilos.
          $titulo = 'Comprobante de Pago';
          $mensaje = 'Detalle de la transacción.';
          $colorEncabezado = '#6c757d';

          if ($pago->estadoPago) {
              $colorEncabezado = $pago->estadoPago->color ?? $colorEncabezado;
              if ($pago->estadoPago->estado_final_inscripcion) {
                  $titulo = '¡Pago Exitoso!';
              } elseif ($pago->estadoPago->estado_pendiente) {
                  $titulo = 'Pago Pendiente';
              } elseif ($pago->estadoPago->estado_anulado_inscripcion) {
                  $titulo = 'Pago Rechazado';
              }
          }

          $pdf = PDF::loadView('contenido.paginas.carrito.compra-finalizada', [
              'pago' => $pago,
              'configuracion' => $configuracion,
              'titulo' => $titulo,
              'mensaje' => $mensaje,
              'colorEncabezado' => $colorEncabezado,
              'icono' => null, // Quizás omitir icono en PDF si da problemas
              'datosParaQr' => $datosParaQr,
              'datosAbono' => $datosAbono,
              'iglesia' => $iglesia,
              'esPdf' => true // Flag útil para la vista
          ]);

          // Configurar opciones para evitar problemas con rutas de fuentes (especialmente en local vs prod)
          $pdf->setOptions([
              'isRemoteEnabled' => true,
              'defaultFont' => 'sans-serif',
              'fontDir' => sys_get_temp_dir(), // Usar directorio temporal del sistema
              'fontCache' => sys_get_temp_dir(), // Usar directorio temporal del sistema
              'chroot' => realpath(base_path()), // Permitir acceso a archivos del proyecto
          ]);

          // Ajustes de papel si es necesario
          // $pdf->setPaper('a4', 'portrait');

          return $pdf->download('Comprobante-Pago-' . $pago->id . '.pdf');

      } catch (\Exception $e) {
          Log::error("Error PDF Pago #{$pago->id}: " . $e->getMessage());
          return back()->with('error', 'No se pudo generar el comprobante PDF.'.$e->getMessage());
      }
  }
}
