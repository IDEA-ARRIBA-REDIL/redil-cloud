<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class RolController extends Controller
{
  public function gestionar(): View
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('configuraciones.subitem_roles');
    return view('contenido.paginas.roles-privilegios.gestionar-roles-privilegios');
  }

  public function editarPermisos(Request $request, $id): View
  {
      $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
      $rolActivo->verificacionDelPermiso('configuraciones.subitem_roles');

      $role = \Spatie\Permission\Models\Role::findOrFail($id);

      return view('contenido.paginas.roles-privilegios.editar-permisos-rol', compact('role'));
  }
}
