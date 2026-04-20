<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\JsonResponse;

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
        $logoUrl = asset('storage/global/img/logo_crecer.png');

        if ($configuracion) {
            $name = $configuracion->nombre_app_personalizado ?: $name;
            $shortName = $configuracion->nombre_app_personalizado ?: $shortName;
            $description = $configuracion->templateDescription ?: $description;
            $themeColor = $configuracion->color_nombre_app ?: $themeColor;

            if ($configuracion->marca_blanca) {
                $rutaBase = $configuracion->ruta_almacenamiento ? $configuracion->ruta_almacenamiento.'/' : '';
                if ($configuracion->logo_app) {
                    $logoUrl = tenant_asset($rutaBase.'img/branding/'.$configuracion->logo_app);
                }
            }
        }

        // Usamos '/login' como start_url para entrar directo sin rebotes
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
                    'src' => $logoUrl,
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any maskable',
                ],
                [
                    'src' => $logoUrl,
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable',
                ],
            ],
        ];

        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json');
    }
}
