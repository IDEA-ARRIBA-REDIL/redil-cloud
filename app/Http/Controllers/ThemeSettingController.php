<?php

namespace App\Http\Controllers;

use App\Models\ThemeSetting;
use App\Services\BrandingEntitlementService;
use App\Services\ThemeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThemeSettingController extends Controller
{
    public function __construct(
        protected ThemeService $themeService,
        protected BrandingEntitlementService $entitlementService
    ) {}

    public function index(): View
    {
        $this->entitlementService->authorizeUser(auth()->user(), 'configuraciones.subitem_plantilla');
        $settings = ThemeSetting::all();

        return view('contenido.paginas.theme.index', ['settings' => $settings]);
    }

    public function update(Request $request, ThemeSetting $setting): RedirectResponse
    {
        $this->entitlementService->authorizeUser(auth()->user(), 'configuraciones.subitem_plantilla');
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
