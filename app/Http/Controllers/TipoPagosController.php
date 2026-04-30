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
            'nombre' => 'required|max:100',
            'enlace' => 'nullable|max:100',
            'cuenta_sap' => 'nullable|max:100',
            'observaciones' => 'nullable',
            'imagen' => 'nullable|image|max:2048',
            'fondo' => 'nullable|image|max:2048',
        ], [
            'imagen.image' => 'El archivo del logo debe ser una imagen.',
            'nombre.required' => 'El nombre es obligatorio.',
        ]);

        // 2. Crear el registro base explícitamente
        $tipoPago = new TipoPago();
        $tipoPago->nombre = $request->nombre;
        $tipoPago->enlace = $request->enlace;
        $tipoPago->cuenta_sap = $request->cuenta_sap;
        $tipoPago->client_id = $request->client_id;
        $tipoPago->key_id = $request->key_id;
        $tipoPago->bussines_id = $request->bussines_id;
        $tipoPago->url_retorno = $request->url_retorno;
        $tipoPago->identity_token = $request->identity_token;
        $tipoPago->key_reservada = $request->key_reservada;
        $tipoPago->account_id = $request->account_id;
        $tipoPago->color = $request->color;
        $tipoPago->unica_moneda_id = $request->unica_moneda_id;
        $tipoPago->porcentaje_tax1 = $request->porcentaje_tax1;
        $tipoPago->porcentaje_tax2 = $request->porcentaje_tax2;
        $tipoPago->transaccion_minima = $request->transaccion_minima;
        $tipoPago->transaccion_maxima = $request->transaccion_maxima;
        $tipoPago->incremento_pdp = $request->incremento_pdp;
        $tipoPago->label_destinatario = $request->label_destinatario;
        $tipoPago->observaciones = $request->observaciones;

        // Booleanos
        $tipoPago->activo = $request->activo ?? 0;
        $tipoPago->habilitado_punto_pago = $request->habilitado_punto_pago ?? 0;
        $tipoPago->subir_archivo_pagos = $request->subir_archivo_pagos ?? 0;
        $tipoPago->botones_valores_moneda = $request->botones_valores_moneda ?? 0;
        $tipoPago->habilitado_donacion = $request->habilitado_donacion ?? 0;
        $tipoPago->tiene_limite_dinero_acumulado = $request->tiene_limite_dinero_acumulado ?? 0;
        $tipoPago->punto_de_pago = $request->punto_de_pago ?? 0;
        $tipoPago->permite_personas_externas = $request->permite_personas_externas ?? 0;
        $tipoPago->codigo_datafono = $request->codigo_datafono ?? 0;
        // 4. Procesar Logo (Obligatorio) ANTES de guardar
        $identificadorUnico = time() . '_' . uniqid();

        if ($request->hasFile('imagen')) {
            $nombreLogo = $this->guardarImagen(
                $request->file('imagen'),
                $configuracion->ruta_almacenamiento,
                'logos',
                $identificadorUnico
            );
            $tipoPago->imagen = $nombreLogo;
        }

        // 5. Procesar Fondo (Opcional) ANTES de guardar
        if ($request->hasFile('fondo')) {
            $nombreFondo = $this->guardarImagen(
                $request->file('fondo'),
                $configuracion->ruta_almacenamiento,
                'fondos',
                $identificadorUnico
            );
            $tipoPago->fondo = $nombreFondo;
        }

        // 6. Guardar en base de datos
        try {
            $tipoPago->save();
        } catch (\Exception $e) {
            dd('ERROR DE BASE DE DATOS AL CREAR:', $e->getMessage());
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
                'nombre' => 'required|string|max:100',
                'enlace' => 'nullable|string|max:100',
                'cuenta_sap' => 'nullable|string|max:100',
                'client_id' => 'nullable|string|max:500',
                'key_id' => 'nullable|string|max:500',
                'bussines_id' => 'nullable|string|max:500',
                'url_retorno' => 'nullable|string|max:500',
                'identity_token' => 'nullable|string|max:500',
                'key_reservada' => 'nullable|string|max:100',
                'account_id' => 'nullable|string|max:100',
                'color' => 'nullable|string|max:100',
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
                'observaciones' => 'nullable|string',
                'imagen' => 'nullable|image|max:2048',
                'fondo' => 'nullable|image|max:2048',
            ],
            [
                'nombre.required' => 'El nombre del tipo de pago es obligatorio.',
                'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
                'unica_moneda_id.required' => 'Debes seleccionar una moneda de la lista.',
                'unica_moneda_id.numeric' => 'El formato de la moneda no es válido.',
                'imagen.image' => 'El archivo del logo debe ser una imagen.',
            ]
        );

        // 3. Actualizar campos explícitamente
        $tipoPago->nombre = $request->nombre;
        $tipoPago->enlace = $request->enlace;
        $tipoPago->cuenta_sap = $request->cuenta_sap;
        $tipoPago->client_id = $request->client_id;
        $tipoPago->key_id = $request->key_id;
        $tipoPago->bussines_id = $request->bussines_id;
        $tipoPago->url_retorno = $request->url_retorno;
        $tipoPago->identity_token = $request->identity_token;
        $tipoPago->key_reservada = $request->key_reservada;
        $tipoPago->account_id = $request->account_id;
        $tipoPago->color = $request->color;
        $tipoPago->unica_moneda_id = $request->unica_moneda_id;
        $tipoPago->porcentaje_tax1 = $request->porcentaje_tax1;
        $tipoPago->porcentaje_tax2 = $request->porcentaje_tax2;
        $tipoPago->transaccion_minima = $request->transaccion_minima;
        $tipoPago->transaccion_maxima = $request->transaccion_maxima;
        $tipoPago->incremento_pdp = $request->incremento_pdp;
        $tipoPago->label_destinatario = $request->label_destinatario;
        $tipoPago->observaciones = $request->observaciones;

        // Booleanos
        $tipoPago->activo = $request->activo ?? 0;
        $tipoPago->habilitado_punto_pago = $request->habilitado_punto_pago ?? 0;
        $tipoPago->subir_archivo_pagos = $request->subir_archivo_pagos ?? 0;
        $tipoPago->botones_valores_moneda = $request->botones_valores_moneda ?? 0;
        $tipoPago->habilitado_donacion = $request->habilitado_donacion ?? 0;
        $tipoPago->tiene_limite_dinero_acumulado = $request->tiene_limite_dinero_acumulado ?? 0;
        $tipoPago->punto_de_pago = $request->punto_de_pago ?? 0;
        $tipoPago->permite_personas_externas = $request->permite_personas_externas ?? 0;
        $tipoPago->codigo_datafono = $request->codigo_datafono ?? 0;
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
     * @param  string  $identifier  Identificador único para el archivo
     */
    private function guardarImagen($file, string $rutaAlmacenamiento, string $carpeta, string $identifier): string
    {
        $extension = $file->getClientOriginalExtension();
        $nombreArchivo = $carpeta.'-'.$identifier.'.'.$extension;

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
