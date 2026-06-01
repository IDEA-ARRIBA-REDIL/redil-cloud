<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class PwaController extends Controller
{
    /**
     * Genera el archivo manifest.json dinámicamente según el tenant actual.
     */
    public function manifest(): JsonResponse
    {
        $configuracion = Configuracion::first();

        // Valores por defecto
        $name = config('variables.templateName') ?: 'REDIL CLOUD';
        $shortName = config('variables.templateName') ?: 'REDIL';
        $description = config('variables.templateDescription') ?: 'Plataforma REDIL';
        $themeColor = config('variables.templateNameColor') ?: '#7367f0';
        $iconUrl = url('/pwa-icon.png');

        if ($configuracion) {
            $name = $configuracion->nombre_app_personalizado ?: $name;
            $shortName = $configuracion->nombre_app_personalizado ?: $shortName;
            $themeColor = $configuracion->color_nombre_app ?: $themeColor;
        }

        $manifest = [
            'name' => $name,
            'short_name' => substr($shortName, 0, 12),
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
    public function icon()
    {
        $configuracion = Configuracion::first();
        $logoPath = null;

        // 1. Intentar usar el logo personalizado si está configurado y habilitado
        if ($configuracion && $configuracion->logo_personalizado && $configuracion->logo_app) {
            try {
                $tenantPath = Storage::disk('public')->path($configuracion->logo_app);
                if (file_exists($tenantPath)) {
                    $logoPath = $tenantPath;
                }
            } catch (\Exception $e) {
                // Fallback al logo global
            }
        }

        // 2. Si no se encontró el logo personalizado o no existe, buscar fallbacks en global_media
        if (! $logoPath || ! file_exists($logoPath)) {
            $fallbacks = [
                'ICONO_APP.png',

            ];

            foreach ($fallbacks as $fallback) {
                try {
                    $path = Storage::disk('global_media')->path($fallback);
                    if (file_exists($path)) {
                        $logoPath = $path;
                        break;
                    }
                } catch (\Exception $e) {
                    // Continuar al siguiente fallback
                }
            }
        }

        if (! $logoPath || ! file_exists($logoPath)) {
            abort(404);
        }

        $mime = mime_content_type($logoPath);
        $source = null;

        if (function_exists('imagecreatetruecolor') && function_exists('imagecreatefrompng')) {
            if (str_contains($mime, 'png')) {
                $source = @imagecreatefrompng($logoPath);
            } elseif (str_contains($mime, 'jpeg') || str_contains($mime, 'jpg')) {
                $source = @imagecreatefromjpeg($logoPath);
            } elseif (str_contains($mime, 'webp')) {
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
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);

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
}
