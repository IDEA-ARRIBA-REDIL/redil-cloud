<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\BannerGeneral;
use App\Models\Configuracion;
use Illuminate\Support\Facades\Storage;

class BannerGeneralController extends Controller
{
    public function listarBanners(Request $request) {
        $query = BannerGeneral::query();

        if ($request->has('buscar') && $request->buscar != '') {
            $query->where('nombre', 'like', '%' . $request->buscar . '%');
        }

        $banners = $query->orderBy('created_at', 'desc')->paginate(12);
        $configuracion = Configuracion::first();

        return view('contenido.paginas.banner-general.listar-banner-general', compact('banners', 'configuracion'));
    }

    public function crearBanner(Request $request)
    {
        // 1. VALIDACIÓN ROBUSTA
        $request->validate([
            'nombre' => 'nullable|string|max:255',
            'fecha_visualizacion' => 'required|string', 
            'visible' => 'required|boolean',
            'link' => 'nullable|string',
            'imagen' => 'required_without:imagen_recortada|image|max:2048', 
            'imagen_recortada' => 'required_without:imagen', 
        ], [
            'imagen.required_without' => 'Debes subir una imagen.',
            'imagen.image' => 'El archivo debe ser una imagen válida.'
        ]);

        // 2. Lógica de Fechas
        $fechaInicio = null;
        $fechaFin = null;

        if ($request->filled('fecha_visualizacion')) {
            $rango = $request->fecha_visualizacion;
            if(str_contains($rango, ' a ')) {
                $partes = explode(' a ', $rango);
                if (count($partes) == 2) {
                    $fechaInicio = trim($partes[0]);
                    $fechaFin = trim($partes[1]);
                }
            } else {
                $fechaInicio = trim($rango);
                $fechaFin = trim($rango);
            }
        }

        // 3. Lógica de Imagen
        $nombreArchivo = null;
        $configuracion = Configuracion::find(1);
        $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/banners/');

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        if ($request->filled('imagen_recortada')) {
            $image_64 = $request->imagen_recortada;
            if (strpos($image_64, 'base64') !== false) {
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
                $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                $image = str_replace($replace, '', $image_64);
                $image = str_replace(' ', '+', $image);
                $nombreArchivo = 'banner_' . time() . '.' . $extension;
                file_put_contents($path . $nombreArchivo, base64_decode($image));
            }
        } elseif ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $nombreArchivo = 'banner_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($path, $nombreArchivo);
        }

        BannerGeneral::create([
            'imagen' => $nombreArchivo,
            'nombre' => $request->nombre,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'link' => $request->link,
            'visible' => $request->visible,
        ]);

        return redirect()->route('banner-general.listarBanners')->with('success', 'Banner creado correctamente.');
    }

    public function actualizarBanner(Request $request, $id)
    {
        $banner = BannerGeneral::findOrFail($id);

        $request->validate([
            'nombre' => 'nullable|string|max:255',
            'fecha_visualizacion' => 'nullable|string', 
            'visible' => 'required|boolean',
            'link' => 'nullable|string',
        ]);

        $fechaInicio = $banner->fecha_inicio;
        $fechaFin = $banner->fecha_fin;

        if ($request->filled('fecha_visualizacion')) {
            $rango = $request->fecha_visualizacion;
            if(str_contains($rango, ' a ')) {
                $partes = explode(' a ', $rango);
                if (count($partes) == 2) {
                    $fechaInicio = trim($partes[0]);
                    $fechaFin = trim($partes[1]);
                }
            } else {
                $fechaInicio = trim($rango);
                $fechaFin = trim($rango);
            }
        }

        // Lógica de Imagen
        $nombreArchivo = $banner->imagen;
        $configuracion = Configuracion::find(1);
        $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/banners/');

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        if ($request->filled('imagen_recortada')) {
            // Borrar imagen anterior si existe en la NvA ubicación
            if ($banner->imagen && file_exists($path . $banner->imagen)) {
                unlink($path . $banner->imagen);
            }

            $image_64 = $request->imagen_recortada;
            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
            $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
            $image = str_replace($replace, '', $image_64);
            $image = str_replace(' ', '+', $image);
            $nombreArchivo = 'banner_' . time() . '.' . $extension;

            file_put_contents($path . $nombreArchivo, base64_decode($image));

        } elseif ($request->hasFile('imagen')) {
             if ($banner->imagen && file_exists($path . $banner->imagen)) {
                unlink($path . $banner->imagen);
            }
            $file = $request->file('imagen');
            $nombreArchivo = 'banner_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($path, $nombreArchivo);
        }

        $banner->update([
            'imagen' => $nombreArchivo,
            'nombre' => $request->nombre,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'link' => $request->link,
            'visible' => $request->visible,
        ]);

        return redirect()->route('banner-general.listarBanners')->with('success', 'Banner actualizado correctamente.');
    }

    public function eliminarBanner($id)
    {
        $banner = BannerGeneral::findOrFail($id);
        $configuracion = Configuracion::find(1);
        $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/banners/');

        if ($banner->imagen && file_exists($path . $banner->imagen)) {
            unlink($path . $banner->imagen);
        }
        $banner->delete();

        return redirect()->route('banner-general.listarBanners')->with('success', 'Banner eliminado correctamente.');
    }
}
