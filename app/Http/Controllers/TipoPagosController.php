<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Moneda;
use App\Models\TipoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            'monedas' => $monedas,
        ]);
    }

    public function crearTipoPagos(Request $request)
    {
        $configuracion = Configuracion::find(1);

        // 1. Validaciones
        $request->validate([
            'nombre' => 'required|max:30',
            'enlace' => 'nullable|max:100',
            'cuenta_sap' => 'nullable|max:30',
            'observaciones' => 'required',
            'imagen' => 'nullable|image|max:2048',
            'fondo' => 'nullable|image|max:2048',
        ], [
            'imagen.required' => 'Debes seleccionar una imagen para el Logo.',
            'imagen.image' => 'El archivo del logo debe ser una imagen.',
            'nombre.required' => 'El nombre es obligatorio.',
            'cuenta_sap.required' => 'La cuenta SAP es obligatoria.',
            'observaciones' => 'Las observaciones son obligatorias',
        ]);

        // 2. Preparar datos (sin imágenes ni tokens)
        $data = $request->except(['imagen', 'fondo', '_token', '_method']);

        // 3. Normalizar Booleanos
        $this->normalizarBooleanos($data);

        // 4. Crear el registro base (sin imágenes)
        $tipoPago = new TipoPago($data);
        $tipoPago->save();

        // 5. Procesar Logo (Obligatorio)
        if ($request->hasFile('imagen')) {
            $nombreLogo = $this->guardarImagen(
                $request->file('imagen'),
                $configuracion->ruta_almacenamiento,
                'logos',
                $tipoPago->id
            );
            $tipoPago->imagen = $nombreLogo;
        }

        // 6. Procesar Fondo (Opcional)
        if ($request->hasFile('fondo')) {
            $nombreFondo = $this->guardarImagen(
                $request->file('fondo'),
                $configuracion->ruta_almacenamiento,
                'fondos',
                $tipoPago->id
            );
            $tipoPago->fondo = $nombreFondo;
        }

        // 7. Guardar de nuevo si hubo imágenes
        if ($request->hasFile('imagen') || $request->hasFile('fondo')) {
            $tipoPago->save();
        }

        Log::info('TipoPago creado ID: '.$tipoPago->id.' | imagen: '.($tipoPago->imagen ?? 'NULL'));

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
            'monedas' => $monedas,
        ]);
    }

    public function actualizarTipoPagos(Request $request, $id)
    {
        $configuracion = Configuracion::find(1);

        // 1. Buscar el registro
        $tipoPago = TipoPago::findOrFail($id);

        // 2. Validación
        $request->validate(
            [
                'nombre' => 'required|string|max:30',
                'enlace' => 'required|string|max:100',
                'cuenta_sap' => 'required|string|max:30',
                'client_id' => 'nullable|string|max:500',
                'key_id' => 'nullable|string|max:500',
                'bussines_id' => 'nullable|string|max:500',
                'url_retorno' => 'nullable|string|max:500',
                'identity_token' => 'nullable|string|max:500',
                'key_reservada' => 'nullable|string|max:50',
                'account_id' => 'nullable|string|max:50',
                'color' => 'nullable|string',
                'unica_moneda_id' => 'required|numeric',
                'porcentaje_tax1' => 'nullable|numeric',
                'porcentaje_tax2' => 'nullable|numeric',
                'transaccion_minima' => 'nullable|numeric',
                'transaccion_maxima' => 'nullable|numeric',
                'incremento_pdp' => 'nullable|numeric',
                'activo' => 'required|in:0,1',
                'habilitado_punto_pago' => 'required|in:0,1',
                'subir_archivo_pagos' => 'required|in:0,1',
                'botones_valores_moneda' => 'required|in:0,1',
                'habilitado_donacion' => 'required|in:0,1',
                'tiene_limite_dinero_acumulado' => 'required|in:0,1',
                'punto_de_pago' => 'required|in:0,1',
                'permite_personas_externas' => 'required|in:0,1',
                'codigo_datafono' => 'required|in:0,1',
                'label_destinatario' => 'nullable|string',
                'observaciones' => 'required|string',
                'imagen' => 'nullable|image|max:2048',
                'fondo' => 'nullable|image|max:2048',
            ],
            [
                'nombre.required' => 'El nombre del tipo de pago es obligatorio.',
                'nombre.max' => 'El nombre no puede tener más de 30 caracteres.',
                'enlace.required' => 'El enlace es obligatorio.',
                'enlace.max' => 'El enlace no puede exceder los 100 caracteres.',
                'cuenta_sap.required' => 'La cuenta SAP es obligatoria.',
                'unica_moneda_id.required' => 'Debes seleccionar una moneda de la lista.',
                'unica_moneda_id.numeric' => 'El formato de la moneda no es válido.',
                'observaciones.required' => 'Las observaciones son obligatorias.',
                'imagen.image' => 'El archivo del logo debe ser una imagen.',
            ]
        );

        // 3. Actualizar campos de texto, numéricos y booleanos (sin imágenes)
        $data = $request->except(['imagen', 'fondo', '_token', '_method']);
        $tipoPago->fill($data);
        $tipoPago->save();

        // 4. Procesar Logo si se envió uno nuevo
        if ($request->hasFile('imagen')) {
            $this->eliminarImagenAnterior($tipoPago->imagen, $configuracion->ruta_almacenamiento, 'logos');

            $nombreLogo = $this->guardarImagen(
                $request->file('imagen'),
                $configuracion->ruta_almacenamiento,
                'logos',
                $tipoPago->id
            );
            $tipoPago->imagen = $nombreLogo;
        }

        // 5. Procesar Fondo si se envió uno nuevo
        if ($request->hasFile('fondo')) {
            $this->eliminarImagenAnterior($tipoPago->fondo, $configuracion->ruta_almacenamiento, 'fondos');

            $nombreFondo = $this->guardarImagen(
                $request->file('fondo'),
                $configuracion->ruta_almacenamiento,
                'fondos',
                $tipoPago->id
            );
            $tipoPago->fondo = $nombreFondo;
        }

        // 6. Guardar de nuevo si hubo imágenes
        if ($request->hasFile('imagen') || $request->hasFile('fondo')) {
            $tipoPago->save();
        }

        Log::info('TipoPago actualizado ID: '.$tipoPago->id.' | imagen: '.($tipoPago->imagen ?? 'NULL'));

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
        $tipoPago->activo = ! $tipoPago->activo;
        $tipoPago->save();

        return response()->json([
            'success' => true,
            'nuevo_estado' => $tipoPago->activo,
            'mensaje' => $tipoPago->activo ? 'El tipo de pago ha sido activado.' : 'El tipo de pago ha sido desactivado.',
        ]);
    }

    /**
     * Guarda un archivo de imagen subido en la carpeta pública del tenant.
     * Mismo patrón que GestionarTipoDeGruposController.
     *
     * @param  \Illuminate\Http\UploadedFile  $file  El archivo subido
     * @param  string  $rutaAlmacenamiento  El identificador del tenant (ej: 'iglesia1')
     * @param  string  $carpeta  Subcarpeta destino (ej: 'logos', 'fondos')
     * @param  int  $tipoPagoId  ID del tipo de pago para generar nombre único
     */
    private function guardarImagen($file, string $rutaAlmacenamiento, string $carpeta, int $tipoPagoId): string
    {
        $extension = $file->getClientOriginalExtension();
        $nombreArchivo = $carpeta.'-'.$tipoPagoId.'.'.$extension;

        $destinationDir = public_path('storage/'.$rutaAlmacenamiento.'/'.$carpeta);

        if (! is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        $file->move($destinationDir, $nombreArchivo);

        Log::info("TipoPago: Imagen guardada en {$rutaAlmacenamiento}/{$carpeta}/{$nombreArchivo}");

        return $nombreArchivo;
    }

    /**
     * Elimina una imagen anterior del filesystem si existe.
     */
    private function eliminarImagenAnterior(?string $nombreArchivo, string $rutaAlmacenamiento, string $carpeta): void
    {
        if (! $nombreArchivo) {
            return;
        }

        $rutaCompleta = public_path('storage/'.$rutaAlmacenamiento.'/'.$carpeta.'/'.$nombreArchivo);

        if (file_exists($rutaCompleta)) {
            unlink($rutaCompleta);
        }
    }

    /**
     * Normaliza los checkboxes para que los desmarcados se guarden como 0.
     */
    private function normalizarBooleanos(array &$data): void
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
            'codigo_datafono',
        ];

        foreach ($camposBooleanos as $campo) {
            $data[$campo] = isset($data[$campo]) ? 1 : 0;
        }
    }
}
