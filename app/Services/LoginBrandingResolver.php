<?php

namespace App\Services;

use App\Models\LoginBrandingDefault;
use App\Models\LoginPersonalizacion;
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

        if ($this->entitlementService->canCustomize()) {
            $personalizacion = LoginPersonalizacion::query()
                ->with(['imagenes' => fn ($query) => $query->where('is_active', true)])
                ->first();

            if ($personalizacion) {
                $leftImageUrl = $personalizacion->left_image_path
                    ? tenant_asset($personalizacion->left_image_path)
                    : $leftImageUrl;
                $customSlides = $personalizacion->imagenes->map(fn ($imagen): array => [
                    'url' => tenant_asset($imagen->image_path),
                    'alt' => $imagen->alt_text ?: 'Imagen de la iglesia',
                    'link_url' => $imagen->link_url,
                ])->values()->all();

                if ($customSlides !== []) {
                    $slides = $customSlides;
                    $interval = $personalizacion->carousel_interval_ms;
                }

                $custom = (bool) $personalizacion->left_image_path || $customSlides !== [];
            }
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
}
