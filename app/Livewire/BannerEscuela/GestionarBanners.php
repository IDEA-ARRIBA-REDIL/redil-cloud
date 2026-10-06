<?php

namespace App\Livewire\BannerEscuela;

use App\Models\BannerEscuela;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class GestionarBanners extends Component
{
    // Propiedades del formulario
    public $descripcion = '';

    public $activo = true;

    // Propiedades de gestión
    public $banners;

    public $bannerId = null;

    public $modalVisible = false;

    // Propiedades para Alpine Upload
    public $nombreArchivoSubido = null;

    public $rutaArchivoSubida = null;

    /**
     * Carga inicial.
     */
    public function mount()
    {
        $this->cargarBanners();
    }

    /**
     * Carga o recarga la lista de banners.
     */
    public function cargarBanners()
    {
        $this->banners = BannerEscuela::latest()->get();
    }

    /**
     * Muestra el modal para crear un nuevo banner.
     */
    public function crear()
    {
        $this->resetearFormulario();
        $this->modalVisible = true;
    }

    /**
     * Guarda un banner nuevo o actualiza uno existente.
     */
    public function guardar()
    {
        $reglas = [
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ];
        if (! $this->bannerId) {
            $reglas['rutaArchivoSubida'] = 'required|string';
        }
        $this->validate($reglas, [
            'rutaArchivoSubida.required' => 'La imagen del banner es obligatoria.',
        ]);

        $datos = [
            'descripcion' => $this->descripcion,
            'activo' => $this->activo,
        ];

        if ($this->rutaArchivoSubida) {
            $datos['imagen'] = $this->rutaArchivoSubida;
        }

        if ($this->bannerId) {
            $banner = BannerEscuela::find($this->bannerId);
            if ($this->rutaArchivoSubida && $banner->imagen) {
                Storage::disk('public')->delete($banner->imagen);
            }
            $banner->update($datos);
        } else {
            BannerEscuela::create($datos);
        }

        // Anulamos la subida temporal una vez guardado con éxito para que no la borre el resetearFormulario
        $this->rutaArchivoSubida = null;
        $this->nombreArchivoSubido = null;

        $this->cargarBanners();
        $this->modalVisible = false;
        $this->resetearFormulario();

        // Despachamos un evento para SweetAlert en lugar de usar session()
        $this->dispatch('notificacion', ['titulo' => '¡Éxito!', 'mensaje' => 'Banner guardado correctamente.', 'icono' => 'success']);
    }

    /**
     * Carga los datos de un banner en el formulario para editarlo.
     */
    public function editar($id)
    {
        $banner = BannerEscuela::findOrFail($id);
        $this->bannerId = $banner->id;
        $this->descripcion = $banner->descripcion;
        $this->activo = $banner->activo;
        $this->nombreArchivoSubido = null;
        $this->rutaArchivoSubida = null;
        $this->modalVisible = true;
    }

    /**
     * Elimina un banner y su archivo asociado en el storage.
     */
    public function eliminarBanner($id)
    {
        $banner = BannerEscuela::find($id);

        if ($banner) {
            if ($banner->imagen) {
                Storage::disk('public')->delete($banner->imagen);
            }
            $banner->delete();
            $this->cargarBanners();
            $this->dispatch('notificacion', [
                'titulo' => '¡Eliminado!',
                'mensaje' => 'El banner ha sido eliminado.',
                'icono' => 'success'
            ]);
        }
    }

    /**
     * Elimina el archivo subido localmente en caso de cancelar o resetear.
     */
    public function eliminarArchivoLocal()
    {
        if ($this->rutaArchivoSubida) {
            Storage::disk('public')->delete($this->rutaArchivoSubida);
            $this->rutaArchivoSubida = null;
            $this->nombreArchivoSubido = null;
        }
    }

    /**
     * Resetea las propiedades del formulario y cierra el modal.
     */
    public function resetearFormulario()
    {
        $this->eliminarArchivoLocal();
        $this->reset(['descripcion', 'activo', 'bannerId', 'nombreArchivoSubido', 'rutaArchivoSubida', 'modalVisible']);
    }

    /**
     * Cierra el modal y resetea el formulario.
     */
    public function cerrarModal()
    {
        $this->resetearFormulario();
    }

    public function render()
    {
        return view('livewire.banner-escuela.gestionar-banners');
    }
}
