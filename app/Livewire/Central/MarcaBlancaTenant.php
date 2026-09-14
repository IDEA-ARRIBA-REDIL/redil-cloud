<?php

namespace App\Livewire\Central;

use App\Models\Configuracion;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Throwable;

class MarcaBlancaTenant extends Component
{
    use WithFileUploads;

    public Tenant $tenant;

    public bool $planIncluyeLogo = false;

    public bool $planIncluyeMarcaBlanca = false;

    public string $nombrePlan = 'Sin plan';

    public bool $logoPersonalizado = false;

    public bool $marcaBlanca = false;

    public ?string $nombreCreador = null;

    public ?string $urlCreador = null;

    public ?string $colorNombreApp = null;

    public ?string $descripcionLogin = null;

    public ?string $sufijoApp = null;

    public ?string $versionApp = null;

    public ?string $logoAppActual = null;

    public ?string $logoAppNegroActual = null;

    public ?string $faviconAppActual = null;

    public ?string $logoAppUrl = null;

    public ?string $logoAppNegroUrl = null;

    public ?string $faviconAppUrl = null;

    public ?TemporaryUploadedFile $logoAppFile = null;

    public ?TemporaryUploadedFile $logoAppNegroFile = null;

    public ?TemporaryUploadedFile $faviconAppFile = null;

    public function mount(Tenant $tenant): void
    {
        $this->autorizarAdministradorRedil();
        $this->tenant = $tenant;
        $this->cargarPlan();
        $this->cargarConfiguracion();
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'logoPersonalizado' => ['boolean'],
            'marcaBlanca' => ['boolean'],
            'nombreCreador' => ['nullable', 'string', 'max:100'],
            'urlCreador' => ['nullable', 'url', 'max:255'],
            'colorNombreApp' => ['nullable', 'string', 'max:50'],
            'descripcionLogin' => ['nullable', 'string', 'max:255'],
            'sufijoApp' => ['nullable', 'string', 'max:255'],
            'versionApp' => ['nullable', 'string', 'max:20'],
            'logoAppFile' => [$this->planIncluyeLogo ? 'nullable' : 'prohibited', 'image', 'mimes:png,jpg,jpeg', 'max:2048', 'dimensions:max_width=4096,max_height=4096'],
            'logoAppNegroFile' => [$this->planIncluyeLogo ? 'nullable' : 'prohibited', 'image', 'mimes:png,jpg,jpeg', 'max:2048', 'dimensions:max_width=4096,max_height=4096'],
            'faviconAppFile' => [$this->planIncluyeLogo ? 'nullable' : 'prohibited', 'file', 'mimes:ico,png', 'max:512'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'logoAppFile.dimensions' => 'El logo no puede superar los 4096 px de ancho o alto.',
            'logoAppNegroFile.dimensions' => 'El logo para fondo claro no puede superar los 4096 px de ancho o alto.',
            'logoAppFile.prohibited' => 'El plan actual no permite cargar un logo personalizado.',
            'logoAppNegroFile.prohibited' => 'El plan actual no permite cargar un logo personalizado.',
            'faviconAppFile.prohibited' => 'El plan actual no permite cargar un favicon personalizado.',
        ];
    }

    public function guardar(): void
    {
        $this->autorizarAdministradorRedil();
        $this->cargarPlan();
        $validated = $this->validate();

        $archivos = [
            'logo_app' => $this->prepararLogo($this->logoAppFile, 'logo'),
            'logo_app_negro' => $this->prepararLogo($this->logoAppNegroFile, 'logo_negro'),
            'favicon_app' => $this->prepararArchivo($this->faviconAppFile, 'favicon'),
        ];

        $incluyeLogo = $this->planIncluyeLogo;
        $incluyeMarcaBlanca = $this->planIncluyeMarcaBlanca;

        $this->tenant->run(function () use ($archivos, $incluyeLogo, $incluyeMarcaBlanca, $validated): void {
            $configuracion = Configuracion::firstOrFail();
            $directorio = 'img/branding';
            $archivosAnteriores = [];
            $rutasNuevas = [];

            $datos = [
                'logo_personalizado' => $incluyeLogo && $validated['logoPersonalizado'],
                'marca_blanca' => $incluyeMarcaBlanca && $validated['marcaBlanca'],
                'nombre_creador' => $validated['nombreCreador'],
                'url_creador' => $validated['urlCreador'],
                'color_nombre_app' => $validated['colorNombreApp'],
                'descripcion_login' => $validated['descripcionLogin'],
                'sufijo_app' => $validated['sufijoApp'],
                'version_app' => $validated['versionApp'],
            ];

            try {
                if ($incluyeLogo) {
                    foreach (array_filter($archivos) as $campo => $archivo) {
                        $ruta = $directorio.'/'.$archivo['nombre'];
                        abort_unless(Storage::put($ruta, $archivo['contenido']), 500, 'No fue posible guardar el archivo de marca.');
                        $rutasNuevas[] = $ruta;
                        $archivosAnteriores[] = $configuracion->{$campo};
                        $datos[$campo] = $archivo['nombre'];
                    }
                }

                $configuracion->update($datos);
            } catch (Throwable $throwable) {
                Storage::delete($rutasNuevas);

                throw $throwable;
            }

            Cache::forget('configuracion_global');

            foreach (array_filter($archivosAnteriores) as $archivoAnterior) {
                $rutaAnterior = $directorio.'/'.$archivoAnterior;

                if (Storage::exists($rutaAnterior)) {
                    Storage::delete($rutaAnterior);
                }
            }
        });

        $this->reset('logoAppFile', 'logoAppNegroFile', 'faviconAppFile');
        $this->cargarConfiguracion();
        session()->flash('success', 'La configuración de marca se actualizó correctamente.');
    }

    public function render(): View
    {
        return view('livewire.central.marca-blanca-tenant')
            ->layout('layouts.centralApp');
    }

    private function autorizarAdministradorRedil(): void
    {
        $administrador = Auth::guard('admin')->user();

        abort_unless($administrador && ! $administrador->is_suspended, 403);
    }

    private function cargarPlan(): void
    {
        $tenant = Tenant::query()->with('plan')->findOrFail($this->tenant->getTenantKey());
        $this->tenant = $tenant;
        $this->planIncluyeLogo = (bool) $tenant->plan?->incluye_logo;
        $this->planIncluyeMarcaBlanca = (bool) $tenant->plan?->incluye_marca_blanca;
        $this->nombrePlan = $tenant->plan?->nombre ?? 'Sin plan';
    }

    private function cargarConfiguracion(): void
    {
        $datos = $this->tenant->run(fn (): array => Configuracion::firstOrFail()->only([
            'logo_personalizado',
            'marca_blanca',
            'nombre_creador',
            'url_creador',
            'color_nombre_app',
            'descripcion_login',
            'sufijo_app',
            'version_app',
            'logo_app',
            'logo_app_negro',
            'favicon_app',
        ]));

        $this->logoPersonalizado = (bool) $datos['logo_personalizado'];
        $this->marcaBlanca = (bool) $datos['marca_blanca'];
        $this->nombreCreador = $datos['nombre_creador'];
        $this->urlCreador = $datos['url_creador'];
        $this->colorNombreApp = $datos['color_nombre_app'];
        $this->descripcionLogin = $datos['descripcion_login'];
        $this->sufijoApp = $datos['sufijo_app'];
        $this->versionApp = $datos['version_app'];
        $this->logoAppActual = $datos['logo_app'];
        $this->logoAppNegroActual = $datos['logo_app_negro'];
        $this->faviconAppActual = $datos['favicon_app'];

        $domain = $this->tenant->domains()->value('domain');
        $this->logoAppUrl = $this->urlAssetTenant($domain, $this->logoAppActual);
        $this->logoAppNegroUrl = $this->urlAssetTenant($domain, $this->logoAppNegroActual);
        $this->faviconAppUrl = $this->urlAssetTenant($domain, $this->faviconAppActual);
    }

    /**
     * @return array{nombre: string, contenido: string}|null
     */
    private function prepararArchivo(?TemporaryUploadedFile $archivo, string $prefijo): ?array
    {
        if ($archivo === null) {
            return null;
        }

        $contenido = file_get_contents($archivo->getRealPath());
        abort_unless(is_string($contenido), 422, 'No fue posible leer el archivo cargado.');

        return [
            'nombre' => $prefijo.'_'.Str::uuid().'.'.strtolower($archivo->getClientOriginalExtension()),
            'contenido' => $contenido,
        ];
    }

    /**
     * @return array{nombre: string, contenido: string}|null
     */
    private function prepararLogo(?TemporaryUploadedFile $archivo, string $prefijo): ?array
    {
        if ($archivo === null) {
            return null;
        }

        $contenido = file_get_contents($archivo->getRealPath());
        abort_unless(is_string($contenido), 422, 'No fue posible leer el logo cargado.');

        return [
            'nombre' => $prefijo.'_'.Str::uuid().'.png',
            'contenido' => $this->normalizarLogoDosAUno($contenido),
        ];
    }

    private function normalizarLogoDosAUno(string $contenido): string
    {
        abort_unless(function_exists('imagecreatefromstring'), 500, 'El servidor no tiene disponible la extensión GD para procesar imágenes.');

        $imagenOrigen = @imagecreatefromstring($contenido);
        abort_unless($imagenOrigen !== false, 422, 'El archivo cargado no contiene una imagen válida.');

        $anchoDestino = 300;
        $altoDestino = 150;
        $anchoOrigen = imagesx($imagenOrigen);
        $altoOrigen = imagesy($imagenOrigen);
        $escala = min($anchoDestino / $anchoOrigen, $altoDestino / $altoOrigen);
        $anchoEscalado = max(1, (int) round($anchoOrigen * $escala));
        $altoEscalado = max(1, (int) round($altoOrigen * $escala));
        $posicionX = (int) floor(($anchoDestino - $anchoEscalado) / 2);
        $posicionY = (int) floor(($altoDestino - $altoEscalado) / 2);

        $imagenDestino = imagecreatetruecolor($anchoDestino, $altoDestino);
        abort_unless($imagenDestino !== false, 500, 'No fue posible preparar el lienzo para el logo.');

        imagealphablending($imagenDestino, false);
        imagesavealpha($imagenDestino, true);
        $transparente = imagecolorallocatealpha($imagenDestino, 0, 0, 0, 127);
        imagefill($imagenDestino, 0, 0, $transparente);

        $copiado = imagecopyresampled(
            $imagenDestino,
            $imagenOrigen,
            $posicionX,
            $posicionY,
            0,
            0,
            $anchoEscalado,
            $altoEscalado,
            $anchoOrigen,
            $altoOrigen,
        );

        imagedestroy($imagenOrigen);

        if (! $copiado) {
            imagedestroy($imagenDestino);
            abort(500, 'No fue posible ajustar el logo a la proporción requerida.');
        }

        ob_start();
        $generado = imagepng($imagenDestino, null, 9);
        $logoNormalizado = ob_get_clean();
        imagedestroy($imagenDestino);

        abort_unless($generado && is_string($logoNormalizado), 500, 'No fue posible generar el logo normalizado.');

        return $logoNormalizado;
    }

    private function urlAssetTenant(?string $domain, ?string $archivo): ?string
    {
        if (! $domain || ! $archivo) {
            return null;
        }

        return tenant_route($domain, 'stancl.tenancy.asset', [
            'path' => 'img/branding/'.$archivo,
        ]);
    }
}
