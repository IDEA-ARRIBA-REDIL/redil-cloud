<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\TipoPago;
use App\Models\Configuracion;
use App\Models\Moneda;
use Illuminate\Support\Str;

class TipoPagosController extends Controller
{
  public function listarTipoPagos()
  {
    $tipoPagos = TipoPago::paginate(10);

    return view('contenido.paginas.tipo-pagos.listar-tipo-pagos', [
      'tipoPagos' => $tipoPagos,
    ]);
  }

  public function creacionTipoPagos()
  {
    $configuracion = Configuracion::find(1);
    $monedas = Moneda::all();

    return view('contenido.paginas.tipo-pagos.crear-tipo-pagos', [
      'configuracion' => $configuracion,
      'monedas' => $monedas
    ]);
  }

  public function crearTipoPagos(Request $request)
  {
    // 1. Validaciones
    $validated = $request->validate([
      // Obligatorios
      'nombre'           => 'required|max:30',
      'enlace'           => 'required|max:100',
      'cuenta_sap'       => 'required|max:30',
      'observaciones'    => 'required',
      // Validamos que venga la cadena Base64 del logo
      'imagen_recortada' => 'required',
    ], [
      'imagen_recortada.required' => 'Debes seleccionar y recortar una imagen para el Logo.',
      'nombre.required' => 'El nombre es obligatorio.',
      'cuenta_sap.required' => 'La cuenta SAP es obligatoria.',
      'observaciones' => 'Las observaciones son obligatorias'
    ]);

    // Clonamos el request para manipular datos
    $data = $request->except(['imagen_recortada', 'fondo_recortado']);

    // 2. Procesar Logo (Obligatorio)
    if ($request->filled('imagen_recortada')) {
      $filename = $this->procesarImagenBase64($request->imagen_recortada, 'logos');
      $data['imagen'] = $filename;
    }

    // 3. Procesar Fondo (Opcional)
    if ($request->filled('fondo_recortado')) {
      $filename = $this->procesarImagenBase64($request->fondo_recortado, 'fondos');
      $data['fondo'] = $filename;
    } else {
      $data['fondo'] = null; // Asegurar null si no hay imagen
    }

    // 4. Normalizar Booleanos
    $this->normalizarBooleanos($data);

    // 5. Crear
    TipoPago::create($data);

    return redirect()->route('tipo-pagos.listarTipoPagos')
      ->with('success', 'Tipo de pago creado correctamente.');
  }

  public function actualizacionTipoPagos($id)
  {
    $tipoPago = TipoPago::findOrFail($id);
    $configuracion = Configuracion::find(1);
    $monedas = Moneda::all();

    return view('contenido.paginas.tipo-pagos.editar-tipo-pagos', [
      'tipoPago' => $tipoPago,
      'configuracion' => $configuracion,
      'monedas' => $monedas
    ]);
  }

  public function actualizarTipoPagos(Request $request, $id)
  {
    // 1. Buscar el registro. Si no existe, lanza error 404 automáticamente.
    $tipoPago = TipoPago::findOrFail($id);

    // 2. Validación COMPLETA de todos los campos de tu vista HTML
    $request->validate(
      [
        // Campos de texto obligatorios
        'nombre' => 'required|string|max:30',
        'enlace' => 'required|string|max:100',
        'cuenta_sap' => 'required|string|max:30',

        // Campos de texto opcionales (pero con límite según tu HTML)
        'client_id' => 'nullable|string|max:500',
        'key_id' => 'nullable|string|max:500',
        'bussines_id' => 'nullable|string|max:500',
        'url_retorno' => 'nullable|string|max:500',
        'identity_token' => 'nullable|string|max:500',
        'key_reservada' => 'nullable|string|max:50',
        'account_id' => 'nullable|string|max:50',

        // Color
        'color' => 'nullable|string',

        // Campos Numéricos (pueden ser nulos o números)
        'unica_moneda_id' => 'required|numeric',
        'porcentaje_tax1' => 'nullable|numeric',
        'porcentaje_tax2' => 'nullable|numeric',
        'transaccion_minima' => 'nullable|numeric',
        'transaccion_maxima' => 'nullable|numeric',
        'incremento_pdp' => 'nullable|numeric',

        // Campos Booleanos (check switches)
        // Se validan como integer o boolean porque llegan como "0" o "1"
        'activo' => 'required|in:0,1',
        'habilitado_punto_pago' => 'required|in:0,1',
        'subir_archivo_pagos' => 'required|in:0,1',
        'botones_valores_moneda' => 'required|in:0,1',
        'habilitado_donacion' => 'required|in:0,1',
        'tiene_limite_dinero_acumulado' => 'required|in:0,1',
        'punto_de_pago' => 'required|in:0,1',
        'permite_personas_externas' => 'required|in:0,1',
        'codigo_datafono' => 'required|in:0,1',

        // Textareas
        'label_destinatario' => 'nullable|string',
        'observaciones' => 'required|string',

        // Inputs hidden de las imágenes (Base64)
        'imagen_recortada' => 'nullable|string',
        'fondo_recortado' => 'nullable|string',
      ],
      [
        // Textos Generales
        'nombre.required' => 'El nombre del tipo de pago es obligatorio.',
        'nombre.max' => 'El nombre no puede tener más de 30 caracteres.',

        'enlace.required' => 'El enlace es obligatorio.',
        'enlace.max' => 'El enlace no puede exceder los 100 caracteres.',

        'cuenta_sap.required' => 'La cuenta SAP es obligatoria.',

        // Numéricos / Selects
        'unica_moneda_id.required' => 'Debes seleccionar una moneda de la lista.',
        'unica_moneda_id.numeric' => 'El formato de la moneda no es válido.',

        // Textareas
        'observaciones.required' => 'Las observaciones son obligatorias.',

        // Imágenes
        'imagen_recortada.required' => 'Debes cargar una imagen para el logo.',
      ]
    );

    // 3. Preparar los datos base
    // Excluimos 'imagen_recortada' y 'fondo_recortado' porque no son columnas reales en la BD
    // Excluimos '_token' y '_method' por seguridad, aunque el update suele ignorarlos
    $data = $request->except(['imagen_recortada', 'fondo_recortado', '_token', '_method']);

    // --- LOGICA DE IMAGEN (LOGO) ---
    // Verificamos si el input hidden tiene contenido (viene la cadena Base64)
    if ($request->filled('imagen_recortada')) {
      try {
        // Obtener el string base64
        $base64_string = $request->input('imagen_recortada');

        // Separar la metadata del contenido real (data:image/jpeg;base64,CONTENIDO)
        // Esto evita errores si la cadena viene sucia
        if (strpos($base64_string, ';base64,') !== false) {
          $image_parts = explode(";base64,", $base64_string);

          // Determinar extensión (jpeg, png, etc)
          $image_type_aux = explode("image/", $image_parts[0]);
          $image_type = $image_type_aux[1] ?? 'png'; // fallback a png si falla

          // Decodificar
          $image_base64 = base64_decode($image_parts[1]);

          // Crear nombre único
          $nombreImagen = 'logo_' . uniqid() . '.' . $image_type;

          // Guardar en disco 'public' dentro de la carpeta 'logos'
          Storage::disk('public')->put('logos/' . $nombreImagen, $image_base64);

          // Borrar imagen anterior si existía
          if ($tipoPago->imagen) {
            if (Storage::disk('public')->exists('logos/' . $tipoPago->imagen)) {
              Storage::disk('public')->delete('logos/' . $tipoPago->imagen);
            }
          }

          // Asignar el nuevo nombre al array de datos
          $data['imagen'] = $nombreImagen;
        }
      } catch (Exception $e) {
        // Si falla la imagen, no detenemos todo el proceso, pero podrías loguearlo
        // Log::error("Error subiendo logo: " . $e->getMessage());
      }
    }

    // --- LOGICA DE FONDO (IMAGEN) ---
    // Exactamente la misma lógica que arriba pero para el fondo
    if ($request->filled('fondo_recortado')) {
      try {
        $base64_string_fondo = $request->input('fondo_recortado');

        if (strpos($base64_string_fondo, ';base64,') !== false) {
          $image_parts = explode(";base64,", $base64_string_fondo);
          $image_type_aux = explode("image/", $image_parts[0]);
          $image_type = $image_type_aux[1] ?? 'jpg';
          $image_base64 = base64_decode($image_parts[1]);

          $nombreFondo = 'fondo_' . uniqid() . '.' . $image_type;

          // Guardar en carpeta 'fondos'
          Storage::disk('public')->put('fondos/' . $nombreFondo, $image_base64);

          // Borrar fondo anterior
          if ($tipoPago->fondo) {
            if (Storage::disk('public')->exists('fondos/' . $tipoPago->fondo)) {
              Storage::disk('public')->delete('fondos/' . $tipoPago->fondo);
            }
          }

          $data['fondo'] = $nombreFondo;
        }
      } catch (Exception $e) {
        // Log::error("Error subiendo fondo: " . $e->getMessage());
      }
    }

    // 4. Actualizar el registro en la base de datos
    // Aquí se guardan tanto los campos de texto, números, booleanos (0/1) y nombres de archivos
    $tipoPago->update($data);

    // 5. Redireccionar
    return redirect()->route('tipo-pagos.listarTipoPagos')
      ->with('success', 'Tipo de pago actualizado correctamente.');
  }

  public function eliminarTipoPagos($id)
  {
    $tipoPago = TipoPago::findOrFail($id);
    $tipoPago->delete();

    return redirect()->route('tipo-pagos.listarTipoPagos')
      ->with('success', 'Tipo de pago eliminado correctamente.');
  }

  public function toggleEstado($id)
  {
    $tipoPago = TipoPago::findOrFail($id);

    // Invertimos el estado (si es 1 pasa a 0, si es 0 pasa a 1)
    $tipoPago->activo = !$tipoPago->activo;
    $tipoPago->save();

    return response()->json([
      'success' => true,
      'nuevo_estado' => $tipoPago->activo,
      'mensaje' => $tipoPago->activo ? 'El tipo de pago ha sido activado.' : 'El tipo de pago ha sido desactivado.'
    ]);
  }

  /**
   * Función auxiliar para decodificar Base64 y guardar archivo
   */
  private function procesarImagenBase64($base64String, $folder)
  {
    // Obtener extensión (png, jpg, etc)
    // formato: data:image/jpeg;base64,....
    $extension = explode('/', explode(':', substr($base64String, 0, strpos($base64String, ';')))[1])[1];

    // Limpiar encabezado para obtener solo la data
    $replace = substr($base64String, 0, strpos($base64String, ',') + 1);
    $image = str_replace($replace, '', $base64String);
    $image = str_replace(' ', '+', $image);

    // Generar nombre único corto para cumplir con el límite de varchar(100) o (30)
    // Usamos uniqid que son 13 caracteres + extensión. Seguro para varchar(30).
    $imageName = uniqid() . '.' . $extension;

    // Guardar en Storage
    Storage::disk('public')->put($folder . '/' . $imageName, base64_decode($image));

    return $imageName;
  }

  // Método auxiliar para asegurar que los checkbox desmarcados se guarden como 0 o false
  private function normalizarBooleanos(&$data)
  {
    $camposBooleanos = [
      'activo',
      'habilitado_punto_pago',
      'subir_archivo_pagos',
      'botones_valores_moneda',
      'habilitado_donacion',
      'tiene_limite_dinero_acumulado',
      'punto_de_pago',
      'permite_personas_externas',
      'codigo_datafono'
    ];

    foreach ($camposBooleanos as $campo) {
      $data[$campo] = isset($data[$campo]) ? 1 : 0;
    }
  }
}
