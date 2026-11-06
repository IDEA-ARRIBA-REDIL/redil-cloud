<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Services\BrandingEntitlementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class PwaController extends Controller
{
    /**
     * Genera el archivo manifest.json dinámicamente según el tenant actual y su plan.
     */
    public function manifest(BrandingEntitlementService $entitlementService): JsonResponse
    {
        $configuracion = Configuracion::first();
        $permitePersonalizar = $this->puedePersonalizarPwa($entitlementService, $configuracion);

        // Valores globales por defecto
        $name = 'REDIL CLOUD';
        $shortName = 'REDIL';
        $description = config('variables.templateDescription') ?: 'Plataforma REDIL';
        $themeColor = '#7367f0';

        if ($permitePersonalizar) {
            $nombrePersonalizado = $this->resolverNombrePwa($configuracion);

            if ($nombrePersonalizado !== '') {
                $name = $nombrePersonalizado;
                $shortName = $nombrePersonalizado;
            } elseif (config('variables.templateName')) {
                $name = (string) config('variables.templateName');
                $shortName = (string) config('variables.templateName');
            }

            if (! empty($configuracion?->color_nombre_app)) {
                $themeColor = $configuracion->color_nombre_app;
            } elseif (config('variables.templateNameColor')) {
                $themeColor = (string) config('variables.templateNameColor');
            }
        }

        $version = substr(md5(implode('|', [
            $name,
            $permitePersonalizar ? ($configuracion?->favicon_app ?? '') : '',
            $permitePersonalizar ? ($configuracion?->logo_app ?? '') : 'default',
        ])), 0, 8);
        $iconUrl = url('/pwa-icon.png').'?v='.$version;

        $manifest = [
            'name' => $name,
            'short_name' => mb_substr($shortName, 0, 12),
            'description' => $description,
            'start_url' => '/login',
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => $themeColor,
            'icons' => [
                [
                    'src' => $iconUrl,
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable',
                ],
                [
                    'src' => $iconUrl,
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any maskable',
                ],
            ],
        ];

        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json');
    }

    /**
     * Genera un ícono cuadrado opaco en tiempo real para iOS y PWA.
     * iOS no soporta transparencias ni imágenes rectangulares adecuadamente.
     */
    public function icon(BrandingEntitlementService $entitlementService)
    {
        $configuracion = Configuracion::first();
        $logoPath = null;

        // 1. Intentar usar el logo de la app personalizado (img/branding/) si el plan lo permite
        if ($this->puedePersonalizarPwa($entitlementService, $configuracion)) {
            $logoPath = $this->resolverRutaLogoPwa($configuracion);
        }

        // 2. Si no se encontró el logo personalizado o el plan no lo incluye, buscar fallback en global_media
        if (! $logoPath || ! is_file($logoPath)) {
            $fallbacks = [
                'ICONO_APP.png',
                'logo_crecer.png',
                'logo_principal.png',
            ];

            foreach ($fallbacks as $fallback) {
                try {
                    $path = Storage::disk('global_media')->path($fallback);
                    if (is_file($path)) {
                        $logoPath = $path;
                        break;
                    }
                } catch (\Throwable $e) {
                    // Continuar al siguiente fallback
                }
            }
        }

        if (! $logoPath || ! is_file($logoPath)) {
            abort(404);
        }

        $mime = mime_content_type($logoPath) ?: 'image/png';
        $source = null;

        if (function_exists('imagecreatetruecolor') && function_exists('imagecreatefrompng')) {
            if (str_contains($mime, 'png')) {
                $source = @imagecreatefrompng($logoPath);
            } elseif (str_contains($mime, 'jpeg') || str_contains($mime, 'jpg')) {
                $source = @imagecreatefromjpeg($logoPath);
            } elseif (str_contains($mime, 'webp') && function_exists('imagecreatefromwebp')) {
                $source = @imagecreatefromwebp($logoPath);
            }
        }

        if (! $source) {
            return response()->file($logoPath, ['Content-Type' => $mime]);
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        $size = 512;
        $canvas = imagecreatetruecolor($size, $size);

        // Si el logo es claro/blanco sobre fondo transparente (ej. CRECER para login oscuro), usar fondo oscuro (#171821)
        if ($this->requiereFondoOscuro($source, $sourceWidth, $sourceHeight)) {
            $bgColor = imagecolorallocate($canvas, 23, 24, 33);
        } else {
            $bgColor = imagecolorallocate($canvas, 255, 255, 255);
        }
        imagefill($canvas, 0, 0, $bgColor);

        // Padding para que el logo respire (50px por lado en 512)
        $padding = 50;
        $maxWidth = $size - ($padding * 2);
        $maxHeight = $size - ($padding * 2);

        $scale = min($maxWidth / $sourceWidth, $maxHeight / $sourceHeight);
        $newWidth = (int) ($sourceWidth * $scale);
        $newHeight = (int) ($sourceHeight * $scale);

        $x = (int) (($size - $newWidth) / 2);
        $y = (int) (($size - $newHeight) / 2);

        // Preservar la mezcla alfa del PNG original sobre el fondo blanco
        imagealphablending($canvas, true);
        imagecopyresampled($canvas, $source, $x, $y, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);

        ob_start();
        imagepng($canvas);
        $image_data = ob_get_clean();

        imagedestroy($source);
        imagedestroy($canvas);

        return response($image_data, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Verifica si el tenant actual puede usar nombre e ícono personalizados en la PWA según su plan.
     */
    private function puedePersonalizarPwa(BrandingEntitlementService $entitlementService, ?Configuracion $configuracion): bool
    {
        if (! $configuracion) {
            return false;
        }

        $tenant = tenant();
        if ($tenant instanceof \App\Models\Tenant) {
            $tenant->loadMissing('plan');
            if ($tenant->plan !== null) {
                return $entitlementService->canCustomize($tenant);
            }
        }

        return (bool) $configuracion->logo_personalizado || (bool) $configuracion->marca_blanca;
    }

    /**
     * Resuelve el nombre personalizado para la PWA desde Configuración General / Marca Blanca.
     */
    private function resolverNombrePwa(?Configuracion $configuracion): string
    {
        $nombreConfig = trim((string) ($configuracion?->nombre_app_personalizado ?? ''));

        if ($nombreConfig !== '') {
            return $nombreConfig;
        }

        if (tenant()) {
            $nombreTenant = trim((string) (tenant('church_name') ?? ''));
            if ($nombreTenant !== '') {
                return $nombreTenant;
            }
        }

        return '';
    }

    /**
     * Busca la ruta física del logo de la app para la PWA en img/branding/.
     */
    private function resolverRutaLogoPwa(?Configuracion $configuracion): ?string
    {
        if (! $configuracion) {
            return null;
        }

        $rutaAlmacenamiento = trim((string) ($configuracion->ruta_almacenamiento ?? ''), '/');

        foreach ([$configuracion->favicon_app, $configuracion->logo_app, $configuracion->logo_app_negro] as $archivoBranding) {
            $ruta = $this->buscarArchivoEnStorage($archivoBranding, 'img/branding', $rutaAlmacenamiento);
            if ($ruta !== null) {
                return $ruta;
            }
        }

        return null;
    }

    /**
     * Busca un archivo físico en los discos del tenant.
     */
    private function buscarArchivoEnStorage(?string $archivo, string $directorio, string $rutaAlmacenamiento): ?string
    {
        $archivoLimpio = ltrim(trim((string) $archivo), '/');
        if ($archivoLimpio === '' || $archivoLimpio === 'default.png') {
            return null;
        }

        $rutasRelativas = [
            $directorio.'/'.basename($archivoLimpio),
            $archivoLimpio,
        ];

        if ($rutaAlmacenamiento !== '') {
            $rutasRelativas[] = $rutaAlmacenamiento.'/'.$directorio.'/'.basename($archivoLimpio);
        }

        foreach (array_unique($rutasRelativas) as $rutaRelativa) {
            foreach (['public', config('filesystems.default', 'local')] as $disco) {
                try {
                    $rutaFisica = Storage::disk($disco)->path($rutaRelativa);
                    if (is_file($rutaFisica)) {
                        return $rutaFisica;
                    }
                } catch (\Throwable $e) {
                    // Continuar buscando en el siguiente disco/ruta
                }
            }
        }

        return null;
    }

    /**
     * Detecta si un logo PNG tiene transparencia y píxeles predominantemente blancos,
     * por lo que necesita un fondo oscuro para no perder las letras blancas sobre blanco.
     */
    private function requiereFondoOscuro(\GdImage $source, int $width, int $height): bool
    {
        $whitePixels = 0;
        $darkPixels = 0;
        $transparentPixels = 0;

        $stepX = max(1, (int) floor($width / 64));
        $stepY = max(1, (int) floor($height / 64));

        for ($x = 0; $x < $width; $x += $stepX) {
            for ($y = 0; $y < $height; $y += $stepY) {
                $rgba = imagecolorat($source, $x, $y);
                $alpha = ($rgba >> 24) & 0x7F;

                if ($alpha >= 100) {
                    $transparentPixels++;

                    continue;
                }

                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;

                if ($r > 230 && $g > 230 && $b > 230) {
                    $whitePixels++;
                } elseif ($r < 60 && $g < 60 && $b < 60) {
                    $darkPixels++;
                }
            }
        }

        return $transparentPixels > 0 && $whitePixels > 0 && $whitePixels > ($darkPixels * 3);
    }
}
