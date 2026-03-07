<?php

namespace App\Livewire\BannerEscuela;



use App\Models\BannerEscuela;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Rule;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;


class GestionarBanners extends Component
{
    use WithFileUploads;

    // Propiedades del formulario
    #[Rule('nullable|image|max:5120')] // 5MB Máximo
    public $imagen;

    #[Rule('nullable|string')]
    public $descripcion = '';

    #[Rule('boolean')]
    public $activo = true;

    // Propiedades de gestión
    public $banners;
    public $bannerId = null;
    public $modalVisible = false;

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
        if (!$this->bannerId) {
            $reglas['imagen'] = 'required|image|max:5120';
        }
        $this->validate($reglas);

        $datos = [
            'descripcion' => $this->descripcion,
            'activo' => $this->activo,
        ];

        if ($this->imagen) {
            $directorio = 'archivos/escuelas/banners';
            $rutaArchivo = $this->imagen->store($directorio, 'public');
            $datos['imagen'] = $rutaArchivo;
        }

        if ($this->bannerId) {
            $banner = BannerEscuela::find($this->bannerId);
            if ($this->imagen && $banner->imagen) {
                Storage::disk('public')->delete($banner->imagen);
            }
            $banner->update($datos);
        } else {
            BannerEscuela::create($datos);
        }

        $this->cargarBanners();
        $this->modalVisible = false;
        $this->resetearFormulario();

        // CAMBIO: Despachamos un evento para SweetAlert en lugar de usar session()
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
        $this->imagen = null;
        $this->modalVisible = true;
    }

    /**
     * CAMBIO: Esta función ahora solo despacha el evento para confirmar con SweetAlert.
     */
    public function confirmarBorrado($id)
    {
        $this->dispatch('confirmar-eliminacion', ['id' => $id]);
    }

    /**
     * CAMBIO: Este método es llamado desde JS después de la confirmación.
     * Añadimos el oyente #[On] para que pueda ser llamado desde el frontend.
     */
    #[On('eliminarBanner')]
    public function eliminarBanner($id)
    {
        $banner = BannerEscuela::find($id);

        if ($banner) {
            if ($banner->imagen) {
                Storage::disk('public')->delete($banner->imagen);
            }
            $banner->delete();
            $this->cargarBanners(); // Recargamos la lista
            // Enviamos notificación de éxito
            $this->dispatch('notificacion', ['titulo' => '¡Eliminado!', 'mensaje' => 'El banner ha sido eliminado.', 'icono' => 'success']);
        }
    }

    /**
     * Resetea las propiedades del formulario.
     */
    public function resetearFormulario()
    {
        $this->reset(['imagen', 'descripcion', 'activo', 'bannerId']);
    }



    public function render()
    {
        return view('livewire.banner-escuela.gestionar-banners');
    }
}
