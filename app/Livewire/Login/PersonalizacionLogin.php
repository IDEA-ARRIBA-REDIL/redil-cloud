<?php

namespace App\Livewire\Login;

use App\Models\LoginPersonalizacion;
use App\Services\BrandingEntitlementService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class PersonalizacionLogin extends Component
{
    use WithFileUploads;

    public mixed $imagenIzquierda = null;

    /** @var array<int, mixed> */
    public array $nuevasImagenes = [];

    public int $intervalo = 6000;

    /** @var array<int, string|null> */
    public array $slideUrls = [];

    public function mount(): void
    {
        $this->authorizeAccess();
        $this->intervalo = $this->personalizacion()->carousel_interval_ms;
        $this->loadSlideUrls();
    }

    public function guardar(): void
    {
        $this->authorizeAccess();
        $this->validate([
            'imagenIzquierda' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'],
            'nuevasImagenes' => ['array', 'max:20'],
            'nuevasImagenes.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'],
            'intervalo' => ['required', 'integer', 'min:3000', 'max:15000'],
            'slideUrls.*' => ['nullable', 'url:http,https', 'max:2048'],
        ]);

        $personalizacion = $this->personalizacion();
        if ($personalizacion->imagenes()->count() + count($this->nuevasImagenes) > 20) {
            $this->addError('nuevasImagenes', 'El carrusel admite máximo 20 imágenes.');

            return;
        }
        $oldLeft = null;
        $newPaths = [];

        try {
            $personalizacion->getConnection()->transaction(function () use ($personalizacion, &$newPaths, &$oldLeft): void {
                if ($this->imagenIzquierda) {
                    $oldLeft = $personalizacion->left_image_path;
                    $personalizacion->left_image_path = $this->storeFile($this->imagenIzquierda, 'img/login-branding/left');
                    $newPaths[] = $personalizacion->left_image_path;
                }

                $personalizacion->carousel_interval_ms = $this->intervalo;
                $personalizacion->save();

                foreach ($personalizacion->imagenes()->get() as $slide) {
                    $slide->update([
                        'link_url' => filled($this->slideUrls[$slide->id] ?? null)
                            ? trim($this->slideUrls[$slide->id])
                            : null,
                    ]);
                }

                $nextOrder = ((int) $personalizacion->imagenes()->max('sort_order')) + 1;

                foreach ($this->nuevasImagenes as $image) {
                    $path = $this->storeFile($image, 'img/login-branding/slides');
                    $newPaths[] = $path;
                    $personalizacion->imagenes()->create([
                        'image_path' => $path,
                        'alt_text' => 'Imagen de la iglesia',
                        'sort_order' => $nextOrder++,
                        'is_active' => true,
                    ]);
                }
            });
        } catch (Throwable $throwable) {
            Storage::disk('public')->delete($newPaths);

            throw $throwable;
        }

        if ($oldLeft) {
            Storage::disk('public')->delete($oldLeft);
        }

        $this->reset('imagenIzquierda', 'nuevasImagenes');
        $this->loadSlideUrls();
        session()->flash('success', 'La pantalla de acceso se actualizó correctamente.');
    }

    public function eliminarImagenIzquierda(): void
    {
        $this->authorizeAccess();
        $personalizacion = $this->personalizacion();

        if ($personalizacion->left_image_path) {
            $oldPath = $personalizacion->left_image_path;
            $personalizacion->update(['left_image_path' => null]);
            Storage::disk('public')->delete($oldPath);
        }
    }

    public function eliminarSlide(int $slideId): void
    {
        $this->authorizeAccess();
        $slide = $this->personalizacion()->imagenes()->findOrFail($slideId);
        $oldPath = $slide->image_path;
        $slide->delete();
        unset($this->slideUrls[$slideId]);
        Storage::disk('public')->delete($oldPath);
        $this->normalizeOrder();
    }

    public function alternarSlide(int $slideId): void
    {
        $this->authorizeAccess();
        $slide = $this->personalizacion()->imagenes()->findOrFail($slideId);
        $slide->update(['is_active' => ! $slide->is_active]);
    }

    public function moverSlide(int $slideId, string $direction): void
    {
        $this->authorizeAccess();
        $slides = $this->personalizacion()->imagenes()->get()->values();
        $index = $slides->search(fn ($slide): bool => $slide->id === $slideId);
        $target = $direction === 'up' ? $index - 1 : $index + 1;

        if ($index === false || ! $slides->has($target)) {
            return;
        }

        [$slides[$index], $slides[$target]] = [$slides[$target], $slides[$index]];
        foreach ($slides as $order => $slide) {
            $slide->update(['sort_order' => $order]);
        }
    }

    public function render(): View
    {
        $personalizacion = $this->personalizacion()->load('imagenes');

        return view('livewire.login.personalizacion-login', compact('personalizacion'));
    }

    private function authorizeAccess(): void
    {
        app(BrandingEntitlementService::class)
            ->authorizeUser(auth()->user(), 'configuraciones.subitem_personalizacion_login');
    }

    private function personalizacion(): LoginPersonalizacion
    {
        return LoginPersonalizacion::query()->firstOrCreate(
            ['singleton_key' => true],
            ['carousel_interval_ms' => 6000]
        );
    }

    private function storeFile(mixed $file, string $directory): string
    {
        $name = Str::uuid().'.'.strtolower($file->getClientOriginalExtension());

        $path = $file->storeAs($directory, $name, 'public');
        abort_unless(is_string($path), 500, 'No fue posible guardar la imagen.');

        return $path;
    }

    private function normalizeOrder(): void
    {
        foreach ($this->personalizacion()->imagenes()->get() as $order => $slide) {
            $slide->update(['sort_order' => $order]);
        }
    }

    private function loadSlideUrls(): void
    {
        $this->slideUrls = $this->personalizacion()->imagenes()->pluck('link_url', 'id')->all();
    }
}
