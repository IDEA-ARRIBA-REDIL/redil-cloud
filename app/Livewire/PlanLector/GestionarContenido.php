<?php

namespace App\Livewire\PlanLector;

use Livewire\Component;
use App\Models\PlanLector;
use App\Models\PlanLectorDia;
use App\Models\PlanLectorContenido;
use App\Models\PlanLectorTipoContenido;
use Livewire\Attributes\On;

class GestionarContenido extends Component
{
    public $plan;
    
    // Properties for Day (Día)
    public $diaTitulo;
    public $modoEdicionDia = false;
    public $diaEditando;

    protected $rules = [
        'diaTitulo' => 'required|string|max:255',
    ];

    public function mount(PlanLector $plan)
    {
        $this->plan = $plan;
    }

    // --- LÓGICA DE DÍAS ---

    public function crearDia()
    {
        $this->reset(['diaTitulo', 'modoEdicionDia', 'diaEditando']);
        $this->dispatch('abrirModal', nombreModal: 'offcanvasDia');
    }

    public function editarDia($diaId)
    {
        $this->diaEditando = PlanLectorDia::find($diaId);
        $this->diaTitulo = $this->diaEditando->titulo;
        $this->modoEdicionDia = true;
        $this->dispatch('abrirModal', nombreModal: 'offcanvasDia');
    }

    public function guardarDia()
    {
        $this->validate();

        if ($this->modoEdicionDia && $this->diaEditando) {
            $this->diaEditando->update([
                'titulo' => $this->diaTitulo,
            ]);
            $msn = 'Día actualizado correctamente.';
        } else {
            // Calcular el siguiente número de día automáticamente
            $siguienteNumero = $this->plan->dias()->max('dia') + 1;
            if (!$siguienteNumero) $siguienteNumero = 1;

            $nuevoDia = $this->plan->dias()->create([
                'dia' => $siguienteNumero,
                'titulo' => $this->diaTitulo,
            ]);
            $msn = 'Día añadido correctamente.';
        }

        $this->dispatch('cerrarModal', nombreModal: 'offcanvasDia');
        $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Hecho!', msnTexto: $msn);
        $this->dispatch('refreshSortable');
        $this->reset(['diaTitulo', 'modoEdicionDia', 'diaEditando']);
    }

    public function eliminarDia($diaId)
    {
        $dia = PlanLectorDia::find($diaId);
        if ($dia) {
            $dia->delete();
            $this->reordenarDias();
            $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Eliminado!', msnTexto: 'El día y todo su contenido han sido eliminados.');
        }
    }

    private function reordenarDias()
    {
        $dias = $this->plan->dias()->orderBy('dia')->get();
        foreach ($dias as $index => $dia) {
            $dia->update(['dia' => $index + 1]);
        }
    }

    #[On('actualizarOrdenDias')]
    public function actualizarOrdenDias($ordenJson)
    {
        $nuevoOrden = json_decode($ordenJson, true);
        foreach ($nuevoOrden as $item) {
            PlanLectorDia::where('id', $item['id'])->update(['dia' => $item['orden']]);
        }
    }

    // --- LÓGICA DE CONTENIDOS ---

    public function agregarContenido($diaId, $tipoSlug)
    {
        $dia = PlanLectorDia::find($diaId);
        $tipo = PlanLectorTipoContenido::where('slug', $tipoSlug)->first();

        if (!$dia || !$tipo) {
            $this->dispatch('msn', msnIcono: 'error', msnTitulo: 'Error', msnTexto: 'Tipo de contenido no encontrado. ¿Ejecutaste los seeders?');
            return;
        }

        $contenidoInicial = '';
        if ($tipoSlug === 'pasaje') {
            $contenidoInicial = '[]'; // array JSON vacío
        }

        $contenido = $dia->contenidos()->create([
            'plan_lector_tipo_contenido_id' => $tipo->id,
            'orden' => $dia->contenidos()->count() + 1,
            'contenido' => $contenidoInicial,
        ]);

        $this->dispatch('refreshSortableItems', diaId: $diaId);
        $this->dispatch('contenidoAgregado', diaId: $diaId, contenidoId: $contenido->id, tipoSlug: $tipoSlug);
    }

    public function eliminarContenido($contenidoId)
    {
        $contenido = PlanLectorContenido::find($contenidoId);
        if ($contenido) {
            $diaId = $contenido->plan_lector_dia_id;
            $contenido->delete();
            $this->reordenarContenidos($diaId);
            $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Eliminado!', msnTexto: 'El contenido ha sido removido.');
        }
    }

    private function reordenarContenidos($diaId)
    {
        $contenidos = PlanLectorContenido::where('plan_lector_dia_id', $diaId)->orderBy('orden')->get();
        foreach ($contenidos as $index => $contenido) {
            $contenido->update(['orden' => $index + 1]);
        }
    }

    #[On('actualizarOrdenContenidos')]
    public function actualizarOrdenContenidos($diaId, $ordenJson)
    {
        $nuevoOrden = json_decode($ordenJson, true);
        foreach ($nuevoOrden as $item) {
            PlanLectorContenido::where('id', $item['id'])->update(['orden' => $item['orden']]);
        }
    }

    // --- GUARDADO ESPECÍFICO DE CONTENIDOS ---

    public function guardarReflexion($contenidoId, $html)
    {
        $contenido = PlanLectorContenido::find($contenidoId);
        if ($contenido) {
            $contenido->update(['contenido' => $html]);
            $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Guardado!', msnTexto: 'Reflexión guardada correctamente.');
        }
    }

    public function guardarVideo($contenidoId, $url)
    {
        $contenido = PlanLectorContenido::find($contenidoId);
        if ($contenido) {
            if (empty($url)) {
                $this->dispatch('msn', msnIcono: 'error', msnTitulo: 'URL requerida', msnTexto: 'Por favor ingresa la URL del video.');
                return false;
            }

            $plataforma = 'otro';
            $videoId = '';

            // Extraer ID de YouTube
            if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $match)) {
                $plataforma = 'youtube';
                $videoId = $match[1];
            } 
            // Extraer ID de Vimeo
            elseif (preg_match('/(?:vimeo\.com\/|player\.vimeo\.com\/video\/)([0-9]+)/i', $url, $match)) {
                $plataforma = 'vimeo';
                $videoId = $match[1];
            }

            if ($plataforma === 'otro') {
                $this->dispatch('msn', msnIcono: 'warning', msnTitulo: 'Atención', msnTexto: 'La URL no parece ser de YouTube o Vimeo, asegúrate de que sea correcta.');
            }

            $data = [
                'url' => $url,
                'plataforma' => $plataforma,
                'id' => $videoId
            ];

            $contenido->update(['contenido' => json_encode($data)]);
            $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Guardado!', msnTexto: 'URL del video guardada correctamente.');
            return true;
        }
        return false;
    }

    public function guardarPasajeBiblico($contenidoId, $jsonVersiculos)
    {
        $contenido = PlanLectorContenido::find($contenidoId);
        if ($contenido) {
            $contenido->update(['contenido' => $jsonVersiculos]);
            $this->dispatch('msn', msnIcono: 'success', msnTitulo: '¡Guardado!', msnTexto: 'Pasaje bíblico guardado correctamente.');
            return true;
        }
        return false;
    }

    public function render()
    {
        $dias = $this->plan->dias()
            ->with(['contenidos.tipoContenido'])
            ->orderBy('dia')
            ->get();
            
        return view('livewire.plan-lector.gestionar-contenido', [
            'dias' => $dias
        ]);
    }
}
