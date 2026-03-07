<?php

namespace App\Livewire\Cursos;

use App\Models\Curso;
use Livewire\Component;
use App\Models\CursoModulo;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\On;

use Livewire\WithFileUploads;


class GestionarContenidoDelCurso extends Component
{
     use WithFileUploads;
    public $curso;
    public $nombre;
    public $descripcion;
    public $archivo; // Propiedad para subida de archivos
    public $modoEdicion = false;
    public $moduloEditando;

    // Propiedades para Ítems
    public $itemEditandoId = null;
    public $nuevoTituloItem = '';

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
    ];

    public function mount(Curso $curso)
    {
        $this->curso = $curso;
    }

    public function crearModulo()
    {
        $this->reset(['nombre', 'descripcion', 'modoEdicion', 'moduloEditando']);
        $this->dispatch('abrirModal', nombreModal: 'offcanvasModulo');
    }

    public function editarModulo($moduloId)
    {
        $this->moduloEditando = CursoModulo::find($moduloId);
        $this->nombre = $this->moduloEditando->nombre;
        $this->descripcion = $this->moduloEditando->descripcion;
        $this->modoEdicion = true;
        $this->dispatch('abrirModal', nombreModal: 'offcanvasModulo');
    }

    public function guardarModulo()
    {
        $this->validate();

        if ($this->modoEdicion) {
            $this->moduloEditando->update([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
            ]);
            $msn = 'Módulo actualizado correctamente.';
        } else {
            $nuevoModulo = $this->curso->modulos()->create([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'orden' => $this->curso->modulos()->count() + 1,
            ]);
            $msn = 'Módulo creado correctamente.';
            $this->dispatch('moduloCreado', moduloId: $nuevoModulo->id);
        }

        $this->dispatch('cerrarModal', nombreModal: 'offcanvasModulo');
        if($this->modoEdicion) {
            $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Hecho!', msnTexto: $msn);
        }
        $this->dispatch('refreshSortable');
        $this->reset(['nombre', 'descripcion', 'modoEdicion', 'moduloEditando']);
    }

    public function eliminarModulo($moduloId)
    {
        $modulo = CursoModulo::find($moduloId);
        if ($modulo) {
            foreach ($modulo->items as $item) {
                $this->eliminarArchivosAsociadosAItem($item);
            }
            $modulo->delete();
            $this->reordenarModulos();
            $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Eliminado!', msnTexto: 'El módulo y sus contenidos han sido eliminados.');
        }
    }

    public function agregarItem($moduloId, $codigo)
    {
        $modulo = CursoModulo::find($moduloId);
        $tipo = \App\Models\CursoItemTipo::where('codigo', $codigo)->first();

        if (!$modulo || !$tipo) return;

        $itemable = null;
        if ($tipo->categoria === 'leccion') {
            $itemable = \App\Models\CursoLeccion::create();
        } elseif ($tipo->categoria === 'evaluacion') {
            // Verificar unicidad de Evaluación Final a nivel de curso
            if ($codigo === 'evaluacion_final') {
                $existeFinal = \App\Models\CursoItem::whereHas('modulo', function($q) {
                    $q->where('curso_id', $this->curso->id);
                })->where('curso_item_tipo_id', $tipo->id)->exists();

                if ($existeFinal) {
                    $this->dispatch('msn', msnIcono: 'warning', msnTitulo: 'Acción no permitida', msnTexto: 'El curso ya contiene una Evaluación Final.');
                    return;
                }
            }

            $itemable = \App\Models\CursoEvaluacion::create();
        }

        if ($itemable) {
            $prefijo = in_array($codigo, ['video', 'recurso', 'iframe']) ? 'Nuevo ' : 'Nueva ';
            $item = $modulo->items()->create([
                'curso_item_tipo_id' => $tipo->id,
                'titulo' => $prefijo . strtolower($tipo->nombre),
                'orden' => $modulo->items()->count() + 1,
                'itemable_id' => $itemable->id,
                'itemable_type' => get_class($itemable),
            ]);

            $this->dispatch('refreshSortableItems', moduloId: $moduloId);
            $this->dispatch('itemAgregado', moduloId: $moduloId, itemId: $item->id);
        }
    }


    public function eliminarItem($itemId)
    {
        $item = \App\Models\CursoItem::find($itemId);
        if ($item) {
            $this->eliminarArchivosAsociadosAItem($item);
            $itemable = $item->itemable;
            if ($itemable) $itemable->delete();
            $moduloId = $item->curso_modulo_id;
            $item->delete();
            $this->reordenarItems($moduloId);
            $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Eliminado!', msnTexto: 'El ítem ha sido eliminado.');
        }
    }

    private function eliminarArchivosAsociadosAItem($item)
    {
        if ($item->itemable_type === \App\Models\CursoLeccion::class) {
            $leccion = $item->itemable;
            if ($leccion && $leccion->archivo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($leccion->archivo_path);
            }
        }
    }

    public function editarNombreItem($itemId)
    {
        $item = \App\Models\CursoItem::find($itemId);
        if ($item) {
            $this->itemEditandoId = $itemId;
            $this->nuevoTituloItem = $item->titulo;
        }
    }

    public function guardarNombreItem()
    {
        if ($this->itemEditandoId) {
            $item = \App\Models\CursoItem::find($this->itemEditandoId);
            if ($item) {
                $item->update(['titulo' => $this->nuevoTituloItem]);
            }
            $this->cancelarEdicionItem();
        }
    }

    public function cancelarEdicionItem()
    {
        $this->itemEditandoId = null;
        $this->nuevoTituloItem = '';
    }


    public function guardarVideoLeccion($leccionId, $url)
    {
        // This is kept for compatibility if needed elsewhere, but we add the unified one below
        $leccion = \App\Models\CursoLeccion::find($leccionId);
        if ($leccion) {
            $plataforma = null;
            $videoId = null;

            // Validación de YouTube
            if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $match)) {
                $plataforma = 'youtube';
                $videoId = $match[1];
            } 
            // Validación de Vimeo
            elseif (preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/i', $url, $match)) {
                $plataforma = 'vimeo';
                $videoId = $match[1];
            }

            if (!$plataforma) {
                $this->dispatch('msn', msnIcono: 'error', msnTitulo: 'URL no válida', msnTexto: 'El link es incorrecto, por favor ingresa un link válido de YouTube o Vimeo.');
                return false;
            }

            $leccion->update([
                'video_url' => $url,
                'video_plataforma' => $plataforma,
                'video_id' => $videoId
            ]);
            $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Guardado!', msnTexto: 'Video actualizado correctamente.');
            return true;
        }
        return false;
    }

    public function guardarVideoYTextoLeccion($leccionId, $url, $html)
    {
        $leccion = \App\Models\CursoLeccion::find($leccionId);
        if ($leccion) {
            $plataforma = null;
            $videoId = null;

            if (empty($url)) {
                $this->dispatch('msn', msnIcono: 'error', msnTitulo: 'URL requerida', msnTexto: 'Por favor ingresa la URL del video.');
                return false;
            }

            // Validación de YouTube
            if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $match)) {
                $plataforma = 'youtube';
                $videoId = $match[1];
            } 
            // Validación de Vimeo
            elseif (preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/i', $url, $match)) {
                $plataforma = 'vimeo';
                $videoId = $match[1];
            }

            if (!$plataforma) {
                $this->dispatch('msn', msnIcono: 'error', msnTitulo: 'URL no válida', msnTexto: 'El link es incorrecto, por favor ingresa un link válido de YouTube o Vimeo.');
                return false;
            }

            $leccion->update([
                'video_url' => $url,
                'video_plataforma' => $plataforma,
                'video_id' => $videoId,
                'contenido_html' => $html
            ]);
            $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Guardado!', msnTexto: 'Contenido actualizado correctamente.');
            return true;
        }
        return false;
    }

    public function guardarIframeYTextoLeccion($leccionId, $iframeCode, $html)
    {
        $leccion = \App\Models\CursoLeccion::find($leccionId);
        if ($leccion) {
            $leccion->update([
                'iframe_code' => $iframeCode,
                'contenido_html' => $html
            ]);
            $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Guardado!', msnTexto: 'Contenido actualizado correctamente.');
            return true;
        }
        return false;
    }

    public function guardarArchivoYTextoLeccion($leccionId, $html)
    {
        $leccion = \App\Models\CursoLeccion::find($leccionId);
        if (!$leccion) return false;

        // Si hay un archivo nuevo cargado, procesarlo primero
        if ($this->archivo) {
            try {
                $this->validate([
                    'archivo' => 'required|mimes:pdf,pptx,ppt,jpg,jpeg,png,gif,webp|max:10240', // 10MB max
                ], [
                    'archivo.mimes' => 'El archivo debe ser un PDF, imagen (jpg, png, webp) o PowerPoint (pptx, ppt).',
                    'archivo.max' => 'El archivo no debe pesar más de 10MB.'
                ]);

                $configuracion = \App\Models\Configuracion::find(1);
                if ($configuracion) {
                    $path = null;
                    if ($configuracion->version == 1) {
                        $originalName = $this->archivo->getClientOriginalName();
                        $extension = $this->archivo->getClientOriginalExtension();
                        $filename = pathinfo($originalName, PATHINFO_FILENAME);
                        $nombreArchivo = $filename . '-' . $leccion->id . '.' . $extension;
                        $rutaDestino = $configuracion->ruta_almacenamiento . '/archivos/cursos/' . $this->curso->id;
                        $path = $this->archivo->storeAs($rutaDestino, $nombreArchivo, 'public');
                    }
                    
                    if ($path) {
                        if ($leccion->archivo_path) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($leccion->archivo_path);
                        }
                        $leccion->update(['archivo_path' => $path]);
                        $this->reset('archivo');
                    }
                }
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->dispatch('msn', msnIcono: 'error', msnTitulo: 'Archivo no permitido', msnTexto: $e->getMessage());
                return false;
            }
        }

        // Guardar siempre el HTML
        $leccion->update([
            'contenido_html' => $html
        ]);

        $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Guardado!', msnTexto: 'Contenido actualizado correctamente.');
        return true;
    }

    public function guardarTextoLeccion($leccionId, $html)
    {
        $leccion = \App\Models\CursoLeccion::find($leccionId);
        if ($leccion) {
            $leccion->update(['contenido_html' => $html]);
            $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Guardado!', msnTexto: 'Contenido de texto guardado.');
        }
    }

    public function guardarIframeLeccion($leccionId, $codigo)
    {
        $leccion = \App\Models\CursoLeccion::find($leccionId);
        if ($leccion) {
            // Guardar el código del iframe. Laravel se encargará de sanitizarlo según la configuración.
            $leccion->update(['iframe_code' => $codigo]);
            $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Guardado!', msnTexto: 'Contenido embebido guardado.');
        }
    }

    public function updatedArchivo()
    {
        try {
            $this->validate([
                'archivo' => 'required|mimes:pdf,pptx,ppt,jpg,jpeg,png,gif,webp|max:10240',
            ], [
                'archivo.mimes' => 'El archivo debe ser un PDF, imagen (jpg, png, webp) o PowerPoint (pptx, ppt).',
                'archivo.max' => 'El archivo no debe pesar más de 10MB.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->reset('archivo');
            $this->dispatch('msn', msnIcono: 'error', msnTitulo: 'Archivo no permitido', msnTexto: $e->getMessage());
        }
    }

    public function guardarArchivoLeccion($leccionId)
    {
        try {
            $this->validate([
                'archivo' => 'required|mimes:pdf,pptx,ppt,jpg,jpeg,png,gif,webp|max:10240', // 10MB max
            ], [
                'archivo.mimes' => 'El archivo debe ser un PDF, imagen (jpg, png, webp) o PowerPoint (pptx, ppt).',
                'archivo.max' => 'El archivo no debe pesar más de 10MB.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('msn', msnIcono: 'error', msnTitulo: 'Archivo no permitido', msnTexto: $e->getMessage());
            return;
        }

        $leccion = \App\Models\CursoLeccion::find($leccionId);
        $configuracion = \App\Models\Configuracion::find(1);

        if ($leccion && $this->archivo && $configuracion) {
            $path = null;

            if ($configuracion->version == 1) {
                // Versión Local - Asegurar nombre único usando el ID de la lección
                $originalName = $this->archivo->getClientOriginalName();
                $extension = $this->archivo->getClientOriginalExtension();
                $filename = pathinfo($originalName, PATHINFO_FILENAME);
                
                // Formato: nombre-original-ID.extension
                $nombreArchivo = $filename . '-' . $leccion->id . '.' . $extension;
                
                $rutaDestino = $configuracion->ruta_almacenamiento . '/archivos/cursos/' . $this->curso->id;
                
                $path = $this->archivo->storeAs($rutaDestino, $nombreArchivo, 'public');

            } elseif ($configuracion->version == 2) {
                // Versión S3 (Estructura preparada)
                /*
                $nombreArchivo = $this->archivo->getClientOriginalName();
                $rutaDestino = $_ENV['aws_carpeta'] . "/archivos/cursos/" . $this->curso->id . "/" . $nombreArchivo;
                
                $s3 = AWS::get('s3');
                $s3->putObject(array(
                    'Bucket'     => $_ENV['aws_bucket'],
                    'Key'        => $rutaDestino,
                    'SourceFile' => $this->archivo->getRealPath(),
                ));
                
                $path = $rutaDestino; // O la URL completa si es necesario
                */
            }

            if ($path) {
                // Eliminar archivo anterior si existe
                if ($leccion->archivo_path) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($leccion->archivo_path);
                }

                $leccion->update(['archivo_path' => $path]);
                $this->reset('archivo');
                $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Subido!', msnTexto: 'El recurso ha sido guardado.');
            }
        }
    }



    // --- Lógica de Evaluaciones ---

    public function guardarConfiguracionEvaluacion($evaluacionId, $data)
    {
        $evaluacion = \App\Models\CursoEvaluacion::with('item')->find($evaluacionId);
        if ($evaluacion) {
            $evaluacion->update([
                'minimo_aprobacion' => isset($data['minimo_aprobacion']) ? (int)$data['minimo_aprobacion'] : $evaluacion->minimo_aprobacion,
                'limite_tiempo' => isset($data['limite_tiempo']) ? (int)$data['limite_tiempo'] : $evaluacion->limite_tiempo,
                'cantidad_repeticiones' => isset($data['cantidad_repeticiones']) ? (int)$data['cantidad_repeticiones'] : $evaluacion->cantidad_repeticiones,
                'tiempo_dilatacion' => isset($data['tiempo_dilatacion']) ? (int)$data['tiempo_dilatacion'] : $evaluacion->tiempo_dilatacion,
            ]);
            $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Hecho!', msnTexto: 'Configuración guardada exitosamente.');
        }
    }

    public function agregarPregunta($evaluacionId)
    {
        $evaluacion = \App\Models\CursoEvaluacion::with('item')->find($evaluacionId);
        if ($evaluacion) {
            $pregunta = $evaluacion->preguntas()->create([
                'pregunta' => 'Nueva pregunta',
                'tipo_respuesta' => 'unica',
                'orden' => $evaluacion->preguntas()->count() + 1
            ]);

            // Agregar una opción por defecto
            $pregunta->opciones()->create([
                'opcion' => 'Opción 1',
                'es_correcta' => true
            ]);
        }
    }

    public function eliminarPregunta($preguntaId)
    {
        $pregunta = \App\Models\CursoPregunta::with('evaluacion.item')->find($preguntaId);
        if ($pregunta) {
            $evaluacionId = $pregunta->curso_evaluacion_id;
            $pregunta->delete();
            
            // Reordenar
            $preguntas = \App\Models\CursoPregunta::where('curso_evaluacion_id', $evaluacionId)->orderBy('orden')->get();
            foreach ($preguntas as $index => $p) {
                $p->update(['orden' => $index + 1]);
            }
            
            $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Eliminada!', msnTexto: 'La pregunta ha sido eliminada.');
        }
    }

    public function guardarPregunta($preguntaId, $texto, $tipo)
    {
        $pregunta = \App\Models\CursoPregunta::with('evaluacion.item')->find($preguntaId);
        if ($pregunta) {
            $pregunta->update([
                'pregunta' => $texto,
                'tipo_respuesta' => $tipo
            ]);

            // Si es Verdadero/Falso, asegurar que solo haya 2 opciones
            if ($tipo === 'verdadero_falso') {
                $pregunta->opciones()->delete();
                $pregunta->opciones()->create(['opcion' => 'Verdadero', 'es_correcta' => true]);
                $pregunta->opciones()->create(['opcion' => 'Falso', 'es_correcta' => false]);
            }
        }
    }

    public function agregarOpcion($preguntaId)
    {
        $pregunta = \App\Models\CursoPregunta::with('evaluacion.item')->find($preguntaId);
        if ($pregunta && $pregunta->tipo_respuesta !== 'verdadero_falso') {
            $pregunta->opciones()->create([
                'opcion' => 'Nueva opción',
                'es_correcta' => false
            ]);
        }
    }

    public function eliminarOpcion($opcionId)
    {
        $opcion = \App\Models\CursoPreguntaOpcion::with('pregunta.evaluacion.item')->find($opcionId);
        if ($opcion) {
            $pregunta = $opcion->pregunta;
            if ($pregunta->opciones()->count() > 1) {
                $opcion->delete();
            } else {
                $this->dispatch('msn', msnIcono: 'warning', msnTitulo: 'Atención', msnTexto: 'Una pregunta debe tener al menos una opción.');
            }
        }
    }

    public function guardarOpcion($opcionId, $texto)
    {
        $opcion = \App\Models\CursoPreguntaOpcion::with('pregunta.evaluacion.item')->find($opcionId);
        if ($opcion) {
            $opcion->update(['opcion' => $texto]);
        }
    }

    public function marcarCorrecta($opcionId)
    {
        $opcion = \App\Models\CursoPreguntaOpcion::with('pregunta.evaluacion.item')->find($opcionId);
        if ($opcion && $opcion->pregunta) {
            $pregunta = $opcion->pregunta;
            
            if ($pregunta->tipo_respuesta === 'unica' || $pregunta->tipo_respuesta === 'verdadero_falso') {
                // Desmarcar todas y marcar solo esta
                $pregunta->opciones()->update(['es_correcta' => false]);
                $opcion->update(['es_correcta' => true]);
            } else {
                // Toggle para múltiple
                $opcion->update(['es_correcta' => !$opcion->es_correcta]);
            }
        }
    }

    #[On('actualizarOrdenPreguntas')]
    public function actualizarOrdenPreguntas($evaluacionId, $ordenJson)
    {
        $evaluacion = \App\Models\CursoEvaluacion::with('item')->find($evaluacionId);
        if ($evaluacion && $evaluacion->item) {
            $nuevoOrden = json_decode($ordenJson, true);
            foreach ($nuevoOrden as $item) {
                \App\Models\CursoPregunta::where('id', $item['id'])->update(['orden' => $item['orden']]);
            }
        }
    }


    private function reordenarItems($moduloId)
    {
        $items = \App\Models\CursoItem::where('curso_modulo_id', $moduloId)->orderBy('orden')->get();
        foreach ($items as $index => $item) {
            $item->update(['orden' => $index + 1]);
        }
    }

    #[On('actualizarOrdenItems')]
    public function actualizarOrdenItems($moduloId, $ordenJson)
    {
        $nuevoOrden = json_decode($ordenJson, true);
        foreach ($nuevoOrden as $item) {
            \App\Models\CursoItem::where('id', $item['id'])->update(['orden' => $item['orden']]);
        }
    }

    #[On('actualizarOrdenModulos')]
    public function actualizarOrdenModulos($ordenJson)
    {
        $nuevoOrden = json_decode($ordenJson, true);
        foreach ($nuevoOrden as $item) {
            CursoModulo::where('id', $item['id'])->update(['orden' => $item['orden']]);
        }
    }

    private function reordenarModulos()
    {
        $modulos = $this->curso->modulos()->orderBy('orden')->get();
        foreach ($modulos as $index => $modulo) {
            $modulo->update(['orden' => $index + 1]);
        }
    }

    public function render()
    {
        $modulos = $this->curso->modulos()
            ->with([
                'items.tipo',
                'items.itemable' => function ($morphTo) {
                    $morphTo->morphWith([
                        \App\Models\CursoEvaluacion::class => ['preguntas.opciones'],
                    ]);
                },
            ])
            ->orderBy('orden')
            ->get();
            
        return view('livewire.cursos.gestionar-contenido-del-curso',[
            'modulos' => $modulos
        ]);
    }
}
