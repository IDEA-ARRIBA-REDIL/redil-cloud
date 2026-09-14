<?php

namespace App\Livewire\Central;

use App\Models\LoginBrandingDefault;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class LoginPredeterminado extends Component
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
        $this->authorizeAdmin();
        $this->intervalo = $this->branding()->carousel_interval_ms;
        $this->loadSlideUrls();
    }

    public function guardar(): void
    {
        $this->authorizeAdmin();
        $this->validate([
            'imagenIzquierda' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'],
            'nuevasImagenes' => ['array', 'max:20'],
            'nuevasImagenes.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'],
            'intervalo' => ['required', 'integer', 'min:3000', 'max:15000'],
            'slideUrls.*' => ['nullable', 'url:http,https', 'max:2048'],
        ]);

        $branding = $this->branding();
        if ($branding->slides()->count() + count($this->nuevasImagenes) > 20) {
            $this->addError('nuevasImagenes', 'El carrusel admite máximo 20 imágenes.');

            return;
        }
        $oldLeft = null;
        $newPaths = [];

        try {
            $branding->getConnection()->transaction(function () use ($branding, &$newPaths, &$oldLeft): void {
                if ($this->imagenIzquierda) {
                    $oldLeft = $branding->left_image_path;
                    $branding->left_image_path = $this->storeFile($this->imagenIzquierda, 'login-branding/default/left');
                    $newPaths[] = $branding->left_image_path;
                }

                $branding->carousel_interval_ms = $this->intervalo;
                $branding->save();

                foreach ($branding->slides()->get() as $slide) {
                    $slide->update([
                        'link_url' => filled($this->slideUrls[$slide->id] ?? null)
                            ? trim($this->slideUrls[$slide->id])
                            : null,
                    ]);
                }

                $nextOrder = ((int) $branding->slides()->max('sort_order')) + 1;

                foreach ($this->nuevasImagenes as $image) {
                    $path = $this->storeFile($image, 'login-branding/default/slides');
                    $newPaths[] = $path;
                    $branding->slides()->create([
                        'image_path' => $path,
                        'alt_text' => 'Imagen institucional',
                        'sort_order' => $nextOrder++,
                        'is_active' => true,
                    ]);
                }
            });
        } catch (Throwable $throwable) {
            Storage::disk('global_media')->delete($newPaths);

            throw $throwable;
        }

        if ($oldLeft) {
            Storage::disk('global_media')->delete($oldLeft);
        }

        $this->reset('imagenIzquierda', 'nuevasImagenes');
        $this->loadSlideUrls();
        session()->flash('success', 'La presentación predeterminada se actualizó correctamente.');
    }

    public function eliminarImagenIzquierda(): void
    {
        $this->authorizeAdmin();
        $branding = $this->branding();

        if ($branding->left_image_path) {
            $oldPath = $branding->left_image_path;
            $branding->update(['left_image_path' => null]);
            Storage::disk('global_media')->delete($oldPath);
        }
    }

    public function eliminarSlide(int $slideId): void
    {
        $this->authorizeAdmin();
        $slide = $this->branding()->slides()->findOrFail($slideId);
        $oldPath = $slide->image_path;
        $slide->delete();
        unset($this->slideUrls[$slideId]);

        if ($oldPath !== 'Banner-login.png') {
            Storage::disk('global_media')->delete($oldPath);
        }
        $this->normalizeOrder();
    }

    public function alternarSlide(int $slideId): void
    {
        $this->authorizeAdmin();
        $slide = $this->branding()->slides()->findOrFail($slideId);
        $slide->update(['is_active' => ! $slide->is_active]);
    }

    public function moverSlide(int $slideId, string $direction): void
    {
        $this->authorizeAdmin();
        $slides = $this->branding()->slides()->get()->values();
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
        $branding = $this->branding()->load('slides');

        return view('livewire.central.login-predeterminado', compact('branding'))
            ->layout('layouts.centralApp');
    }

    private function branding(): LoginBrandingDefault
    {
        return LoginBrandingDefault::query()->firstOrCreate(
            ['singleton_key' => true],
            ['carousel_interval_ms' => 6000]
        );
    }

    private function authorizeAdmin(): void
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin && ! $admin->is_suspended, 403);
    }

    private function storeFile(mixed $file, string $directory): string
    {
        $name = Str::uuid().'.'.strtolower($file->getClientOriginalExtension());

        $path = $file->storeAs($directory, $name, 'global_media');
        abort_unless(is_string($path), 500, 'No fue posible guardar la imagen.');

        return $path;
    }

    private function normalizeOrder(): void
    {
        foreach ($this->branding()->slides()->get() as $order => $slide) {
            $slide->update(['sort_order' => $order]);
        }
    }

    private function loadSlideUrls(): void
    {
        $this->slideUrls = $this->branding()->slides()->pluck('link_url', 'id')->all();
    }
}
