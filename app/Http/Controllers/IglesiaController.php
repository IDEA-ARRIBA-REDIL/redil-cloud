<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Iglesia;
use App\Models\Continente;
use App\Models\Municipio;
use App\Models\Pais;
use App\Models\Region;
use App\Models\Configuracion;
use Illuminate\Http\Request;

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
      'configuracion' => $configuracion
    ]);
  }

  public function update(Request $request, Iglesia $iglesia)
  {
    $configuracion = Configuracion::first();

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

    $iglesia->save();
    if ($iglesia->save()) {
      if ($request->foto) {
        if ($configuracion->version == 1) {
          $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/iglesia/');
          !is_dir($path) && mkdir($path, 0777, true);

          $imagenPartes = explode(';base64,', $request->foto);
          $imagenBase64 = base64_decode($imagenPartes[1]);
          $nombreFoto = 'iglesia' . $iglesia->id . '.png';
          $imagenPath = $path . $nombreFoto;
          file_put_contents($imagenPath, $imagenBase64);
          $iglesia->logo = $nombreFoto;
          $iglesia->save();
        }
      }
    }
    return back()->with('success', "Iglesia actualizada correctamente");
  }
}
