<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\VersiculoDiario;
use App\Models\Configuracion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Renderless;

class VersiculoDelDia extends Component
{
    public $versiculoId;
    public $configuracion;
    public $claseColumnas;


    public function mount($claseColumnas = 'col-12 col-md-4')
    {
        $this->claseColumnas = $claseColumnas;
        $this->configuracion = Configuracion::first();

        $versiculo = VersiculoDiario::whereDate('fecha_publicacion', Carbon::today())->first();
        $this->versiculoId = $versiculo ? $versiculo->id : null;
    }

    public function toggleLike($id)
    {
        if (!auth()->check()) {
            return;
        }

        $versiculo = VersiculoDiario::find($id);
        if ($versiculo) {
            $versiculo->usuariosQueDieronLike()->toggle(auth()->id());
        }
    }

    public function render()
    {
        $versiculo = $this->versiculoId ? VersiculoDiario::with('usuariosQueDieronLike')->find($this->versiculoId) : null;
        $plainText = "";
        $fullTextModal = "";

        if ($versiculo) {
            $dataVersiculos = $versiculo->texto_versiculo;

            if (is_string($dataVersiculos)) {
                $dataVersiculos = json_decode($dataVersiculos, true);
            }

            if(isset($dataVersiculos) && is_array($dataVersiculos)) {
                foreach($dataVersiculos as $selection) {
                    $versiculosArray = isset($selection['versiculos']) ? $selection['versiculos'] : [];
                    foreach($versiculosArray as $v) {
                        $plainText .= (isset($v['texto']) ? $v['texto'] : '') . " ";
                        $num = isset($v['numero']) ? $v['numero'] : '';
                        $texto = isset($v['texto']) ? $v['texto'] : '';
                        $fullTextModal .= "<strong>".$num."</strong> " . $texto . "<br><br>";
                    }
                }
            }

            if (empty($fullTextModal)) {
                $fullTextModal = $versiculo->cita_referencia;
            }

            $plainText = trim(strip_tags($plainText));
        }

        $imageUrl = url()->current();
        $relativeUrl = '';

        if ($versiculo && $versiculo->ruta_imagen) {
            $tenantPath = $this->configuracion->ruta_almacenamiento . '/img/versiculo-diario/' . $versiculo->ruta_imagen;

            // 1. Prioridad: Disco del Inquilino (Público)
            if (Storage::disk('public')->exists($tenantPath)) {
                $relativeUrl = Storage::url($tenantPath);
            }
            // 2. Fallback: Disco Global
            else {
                $possibleGlobalPaths = [
                    $versiculo->ruta_imagen
                ];

                // Si no se encuentra con la ruta exacta, intentar con extensiones comunes (.jpg vs .jpeg)
                if (preg_match('/\.(jpe?g|png|webp|png)$/i', $versiculo->ruta_imagen)) {
                    $baseName = pathinfo($versiculo->ruta_imagen, PATHINFO_FILENAME);
                    $exts = ['jpg', 'jpeg', 'png', 'webp'];
                    foreach ($exts as $targetExt) {
                        $possibleGlobalPaths[] = $baseName . '.' . $targetExt;
                    }
                }

                foreach ($possibleGlobalPaths as $path) {
                    // Verificación vía disco
                    if (Storage::disk('global_media')->exists($path)) {
                        $relativeUrl = Storage::disk('global_media')->url($path);
                        break;
                    }
                    
                    // Verificación física directa (bypass abstraction)
                    if (file_exists(storage_path('app/global_media/' . $path))) {
                        $relativeUrl = '/global_media/' . $path;
                        break;
                    }
                }
            }

            if ($relativeUrl) {
                // Asegurar URL absoluta
                $imageUrl = str_starts_with($relativeUrl, 'http')
                    ? $relativeUrl
                    : request()->getSchemeAndHttpHost() . $relativeUrl;
            }
        }

        return view('livewire.dashboard.versiculo-del-dia', [
            'versiculo' => $versiculo,
            'plainText' => $plainText,
            'fullTextModal' => $fullTextModal,
            'imageUrl' => $imageUrl,
            'relativeUrl' => $relativeUrl
        ]);
    }
}
