<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ThemeSetting;
use App\Services\ThemeService;
use \stdClass;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;


class ThemeSettingController extends Controller
{
  protected $themeService;

  public function __construct(ThemeService $themeService) {
    $this->themeService = $themeService;
  }

  public function index()
  {
      $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
      $rolActivo->verificacionDelPermiso('configuraciones.subitem_plantilla');
      $settings = ThemeSetting::all();
      return view('contenido.paginas.theme.index', ['settings'=>$settings]);
  }

  public function update(Request $request, ThemeSetting $setting)
  {
      $validated = $request->validate([
          'value' => 'required|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
      ]);

      $setting->update($validated);

      // Regenera el archivo SCSS
      $this->themeService->updateScssFile();

      // Limpia la caché


      return back()->with('success', 'Color actualizado correctamente');
  }
}
