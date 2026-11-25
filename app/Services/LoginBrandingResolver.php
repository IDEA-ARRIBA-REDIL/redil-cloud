<?php

namespace App\Services;

use App\Models\LoginBrandingDefault;
use App\Models\LoginPersonalizacion;
use App\Models\ThemeSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class LoginBrandingResolver
{
    public function __construct(private readonly BrandingEntitlementService $entitlementService) {}

    /**
     * @return array{left_image_url: ?string, slides: array<int, array{url: string, alt: string, link_url: ?string}>, interval: int, custom: bool}
     */
    public function resolve(): array
    {
        $defaults = LoginBrandingDefault::query()->with(['slides' => fn ($query) => $query->where('is_active', true)])->first();
        $leftImageUrl = $defaults?->left_image_path
            ? Storage::disk('global_media')->url($defaults->left_image_path)
            : null;
        $slides = $defaults?->slides->map(fn ($slide): array => [
            'url' => Storage::disk('global_media')->url($slide->image_path),
            'alt' => $slide->alt_text ?: 'Imagen institucional',
            'link_url' => $slide->link_url,
        ])->values()->all() ?? [];
        $interval = $defaults?->carousel_interval_ms ?? 6000;
        $custom = false;
        $tenantHasLeftImage = false;

        if ($this->entitlementService->canCustomize()) {
            $personalizacion = LoginPersonalizacion::query()
                ->with(['imagenes' => fn ($query) => $query->where('is_active', true)])
                ->first();

            if ($personalizacion) {
                if ($personalizacion->left_image_path) {
                    $leftImageUrl = tenant_asset($personalizacion->left_image_path);
                    $tenantHasLeftImage = true;
                }

                $customSlides = $personalizacion->imagenes->map(fn ($imagen): array => [
                    'url' => tenant_asset($imagen->image_path),
                    'alt' => $imagen->alt_text ?: 'Imagen de la iglesia',
                    'link_url' => $imagen->link_url,
                ])->values()->all();

                if ($customSlides !== []) {
                    $slides = $customSlides;
                    $interval = $personalizacion->carousel_interval_ms;
                }

                $custom = $tenantHasLeftImage || $customSlides !== [];
            }
        }

        if (! $tenantHasLeftImage && $this->hasActiveLoginBackgroundColor()) {
            $leftImageUrl = null;
        }

        if ($slides === []) {
            $slides[] = [
                'url' => Storage::disk('global_media')->url('Banner-login.png'),
                'alt' => 'Software Redil',
                'link_url' => null,
            ];
        }

        return [
            'left_image_url' => $leftImageUrl,
            'slides' => $slides,
            'interval' => max(3000, min(15000, $interval)),
            'custom' => $custom,
        ];
    }

    /**
     * Determina si el tenant tiene configurado un color activo para el fondo izquierdo del login.
     */
    protected function hasActiveLoginBackgroundColor(): bool
    {
        if (! Schema::hasTable('theme_settings')) {
            return false;
        }

        $setting = ThemeSetting::query()
            ->where('class', 'bg-login-left')
            ->where('is_active', true)
            ->first();

        return $setting !== null && filled($setting->value);
    }
}
