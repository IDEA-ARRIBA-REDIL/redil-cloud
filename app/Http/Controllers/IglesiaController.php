<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Continente;
use App\Models\Departamento;
use App\Models\Iglesia;
use App\Models\Municipio;
use App\Models\Pais;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IglesiaController extends Controller
{
    public function perfil(Iglesia $iglesia)
    {
        $continentes = Continente::orderBy('nombre', 'asc')->get();
        $paises = Pais::orderBy('nombre', 'asc')->get();
        $regiones = Region::orderBy('nombre', 'asc')->get();
        $departamentos = Departamento::orderBy('nombre', 'asc')->get();
        $ciudades = Municipio::orderBy('nombre', 'asc')->get();
        $iglesia = Iglesia::first();
        $configuracion = Configuracion::first();

        return view('contenido.paginas.iglesia.perfil', [
            'continentes' => $continentes,
            'iglesia' => $iglesia,
            'paises' => $paises,
            'regiones' => $regiones,
            'departamentos' => $departamentos,
            'ciudades' => $ciudades,
            'configuracion' => $configuracion,
        ]);
    }

    public function update(Request $request, Iglesia $iglesia)
    {
        $configuracion = Configuracion::first();

        $request->validate([
            'logoFile' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'logo_base64' => 'nullable|string',
            'logoNegroFile' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'logo_negro_base64' => 'nullable|string',
            'instagram' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'youtube' => 'nullable|string|max:255',
            'tiktok' => 'nullable|string|max:255',
        ]);

        $iglesia->nombre = $request->nombre;
        $iglesia->fecha_apertura = $request->fechaApertura;
        $iglesia->fecha_suscripcion = $request->fechaSuscripcion;
        $iglesia->membresia_estimada = $request->cantidadMembresia;
        $iglesia->telefono1 = $request->telefonoFijo;
        $iglesia->telefono2 = $request->otroTelefono;
        $iglesia->pais_id = $request->pais;
        $iglesia->continente_id = $request->continente;
        $iglesia->region_id = $request->region;
        $iglesia->departamento_id = $request->departamento;
        $iglesia->municipio_id = $request->ciudad;
        $iglesia->direccion = $request->direccion;
        $iglesia->instagram = $request->instagram;
        $iglesia->facebook = $request->facebook;
        $iglesia->youtube = $request->youtube;
        $iglesia->tiktok = $request->tiktok;

        $pathIglesia = 'img/iglesia';

        // Procesar logo blanco (logo)
        if ($request->filled('logo_base64')) {
            $base64String = $request->input('logo_base64');
            $datosImagen = explode(',', $base64String);
            $decodificado = base64_decode(end($datosImagen));

            $extension = 'png';
            if (preg_match('/^data:image\/(\w+);base64/', $base64String, $type)) {
                $extension = strtolower($type[1]);
            }

            $fileName = 'logo_'.time().'.'.$extension;

            Storage::put($pathIglesia.'/'.$fileName, $decodificado);

            if ($iglesia->logo && Storage::exists($pathIglesia.'/'.$iglesia->logo)) {
                Storage::delete($pathIglesia.'/'.$iglesia->logo);
            }

            $iglesia->logo = $fileName;
        } elseif ($request->hasFile('logoFile')) {
            $file = $request->file('logoFile');
            $fileName = 'logo_'.time().'.'.$file->getClientOriginalExtension();
            $file->storeAs($pathIglesia, $fileName);

            if ($iglesia->logo && Storage::exists($pathIglesia.'/'.$iglesia->logo)) {
                Storage::delete($pathIglesia.'/'.$iglesia->logo);
            }

            $iglesia->logo = $fileName;
        }

        // Procesar logo negro (logo_negro)
        if ($request->filled('logo_negro_base64')) {
            $base64String = $request->input('logo_negro_base64');
            $datosImagen = explode(',', $base64String);
            $decodificado = base64_decode(end($datosImagen));

            $extension = 'png';
            if (preg_match('/^data:image\/(\w+);base64/', $base64String, $type)) {
                $extension = strtolower($type[1]);
            }

            $fileName = 'logo_negro_'.time().'.'.$extension;

            Storage::put($pathIglesia.'/'.$fileName, $decodificado);

            if ($iglesia->logo_negro && Storage::exists($pathIglesia.'/'.$iglesia->logo_negro)) {
                Storage::delete($pathIglesia.'/'.$iglesia->logo_negro);
            }

            $iglesia->logo_negro = $fileName;
        } elseif ($request->hasFile('logoNegroFile')) {
            $file = $request->file('logoNegroFile');
            $fileName = 'logo_negro_'.time().'.'.$file->getClientOriginalExtension();
            $file->storeAs($pathIglesia, $fileName);

            if ($iglesia->logo_negro && Storage::exists($pathIglesia.'/'.$iglesia->logo_negro)) {
                Storage::delete($pathIglesia.'/'.$iglesia->logo_negro);
            }

            $iglesia->logo_negro = $fileName;
        }

        $iglesia->save();

        return back()->with('success', 'Iglesia actualizada correctamente');
    }
}
