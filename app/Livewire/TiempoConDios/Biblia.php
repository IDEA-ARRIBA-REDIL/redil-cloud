<?php

namespace App\Livewire\TiempoConDios;

use App\Helpers\Helpers;
use App\Models\BibliaLibro;
use App\Models\BibliaVersiculo;
use App\Models\BibliaVersion;
use Illuminate\Support\Collection;
use Livewire\Component;

class Biblia extends Component
{
    public $class = '';

    public $name_id;

    public $libros;

    public $librosAt;

    public $librosNt;

    public $versiones;

    public $capituloSeleccionado;

    public $libroSeleccionado;

    public $versionSeleccionada;

    public $selectCapitulo;

    public $selectLibro;

    public $selectVersion;

    public $capitulos = [];

    public $subrayadosTemp = [];

    public $subrayados = [];

    public $subrayadosData = [];

    public $versiculosResaltados = [];

    public $despacharEvento = false;

    public $respuestasPrevias;

    public $versiculoViendo = null;

    public $indexVersiculoViendo = null;

    public function mount()
    {
        if ($this->respuestasPrevias) {
            $this->versiculosResaltados = json_decode($this->respuestasPrevias, true) ?? [];
        }

        $this->libros = Collection::make(Helpers::libros2());
        $this->librosAt = $this->libros->where('testament', 'Antiguo Testamento');
        $this->librosNt = $this->libros->where('testament', 'Nuevo Testamento');

        try {
            $versionesDB = BibliaVersion::where('activo', true)->orderBy('orden')->get();

            if ($versionesDB->isNotEmpty()) {
                $this->versiones = $versionesDB->map(function ($v) {
                    return [
                        'id' => $v->id,
                        'name' => $v->nombre,
                        'version' => $v->slug,
                        'shortName' => $v->abreviatura,
                    ];
                });
            } else {
                // Fallback inicial mientras se corre la importación
                $this->versiones = collect([
                    ['id' => null, 'name' => 'Reina-Valera 1960', 'version' => 'rvr1960', 'shortName' => 'RVR1960'],
                    ['id' => null, 'name' => 'Traducción en Lenguaje Actual', 'version' => 'tla', 'shortName' => 'TLA'],
                    ['id' => null, 'name' => 'Nueva Traducción Viviente', 'version' => 'ntv', 'shortName' => 'NTV'],
                    ['id' => null, 'name' => 'Nueva Versión Internacional', 'version' => 'nvi', 'shortName' => 'NVI'],
                    ['id' => null, 'name' => 'La Biblia de las Américas', 'version' => 'lbla', 'shortName' => 'LBLA'],
                ]);
            }
        } catch (\Exception $e) {
            $this->versiones = collect([
                ['id' => null, 'name' => 'Reina-Valera 1960', 'version' => 'rvr1960', 'shortName' => 'RVR1960'],
                ['id' => null, 'name' => 'Traducción en Lenguaje Actual', 'version' => 'tla', 'shortName' => 'TLA'],
                ['id' => null, 'name' => 'Nueva Traducción Viviente', 'version' => 'ntv', 'shortName' => 'NTV'],
                ['id' => null, 'name' => 'Nueva Versión Internacional', 'version' => 'nvi', 'shortName' => 'NVI'],
                ['id' => null, 'name' => 'La Biblia de las Américas', 'version' => 'lbla', 'shortName' => 'LBLA'],
            ]);
        }

        $this->libroSeleccionado = null;
        $this->capituloSeleccionado = null;
        $this->capitulos = [];
        $this->selectLibro = '';
        $this->selectCapitulo = '';
    }

    public function abrirBiblia()
    {
        $this->subrayadosTemp = [];
        $this->dispatch('abrirModal', nombreModal: 'modalBiblia_'.$this->name_id);
    }

    public function updatedSelectLibro($value)
    {
        $this->subrayados = [];
        $this->subrayadosTemp = [];
        $this->actualizarSubrayadosData();

        if ($value) {
            $this->libroSeleccionado = $this->libros->firstWhere('libro', $value);
            $this->capitulos = range(1, $this->libroSeleccionado ? $this->libroSeleccionado->chapters : 0);

            $this->selectCapitulo = 1;
            $this->versionSeleccionada = $this->versiones->first();
            $this->selectVersion = $this->versionSeleccionada['name'] ?? '';
            $this->updatedSelectCapitulo(1);
        } else {
            $this->libroSeleccionado = null;
            $this->capituloSeleccionado = null;
            $this->capitulos = [];
            $this->selectCapitulo = '';
        }
    }

    public function updatedSelectCapitulo($value)
    {
        $this->subrayados = [];
        $this->subrayadosTemp = [];
        $this->actualizarSubrayadosData();

        if ($value && $this->libroSeleccionado && $this->versionSeleccionada) {
            $this->selectCapitulo = $value;
            $bookSlug = strtolower($this->libroSeleccionado->names[0]);
            $versionSlug = $this->versionSeleccionada['version'];

            try {
                $versionModel = BibliaVersion::where('slug', $versionSlug)->first();
                $libroModel = BibliaLibro::where('slug', $bookSlug)->first();

                if ($versionModel && $libroModel) {
                    $versiculosDB = BibliaVersiculo::query()
                        ->where('version_id', $versionModel->id)
                        ->where('libro_id', $libroModel->id)
                        ->where('capitulo', $value)
                        ->orderBy('versiculo', 'asc')
                        ->get();

                    if ($versiculosDB->isNotEmpty()) {
                        $versesFormatted = [];
                        foreach ($versiculosDB as $v) {
                            $row = [
                                'number' => $v->versiculo,
                                'verse' => $v->texto,
                            ];
                            if (! empty($v->encabezado)) {
                                $row['study'] = $v->encabezado;
                            }
                            $versesFormatted[] = $row;
                        }

                        $this->capituloSeleccionado = collect([
                            'chapter' => $value,
                            'vers' => $versesFormatted,
                        ]);
                    } else {
                        $this->capituloSeleccionado = null;
                        $this->dispatch(
                            'msn',
                            msnIcono: 'info',
                            msnTitulo: 'Capítulo no disponible',
                            msnTexto: 'El capítulo solicitado no se encuentra cargado. Asegúrese de haber ejecutado: php artisan biblia:importar'
                        );
                    }
                } else {
                    $this->capituloSeleccionado = null;
                    $this->dispatch(
                        'msn',
                        msnIcono: 'info',
                        msnTitulo: 'Versión no cargada',
                        msnTexto: 'La versión seleccionada no se encuentra disponible en la base de datos local. Ejecute: php artisan biblia:importar'
                    );
                }
            } catch (\Exception $e) {
                $this->dispatch(
                    'msn',
                    msnIcono: 'error',
                    msnTitulo: '¡Ups!',
                    msnTexto: 'No se pudo leer la Biblia local: '.$e->getMessage()
                );
                $this->capituloSeleccionado = null;
            }
        } else {
            $this->capituloSeleccionado = null;
        }
    }

    public function updatedSelectVersion($value)
    {
        $this->subrayados = [];
        $this->subrayadosTemp = [];
        $this->actualizarSubrayadosData();

        $this->versionSeleccionada = $this->versiones->firstWhere('name', $value);
        $capituloActual = data_get($this->capituloSeleccionado, 'chapter', 1);
        $this->updatedSelectCapitulo($capituloActual);
    }

    // funcion para cuando doy click en el texto para subrayarlo
    public function toggleSubrayado($numeroVersiculo)
    {
        if (in_array($numeroVersiculo, $this->subrayados)) {
            // Si el versículo ya está subrayado, quítalo
            $this->subrayados = array_diff($this->subrayados, [$numeroVersiculo]);
        } else {
            // Si el versículo no está subrayado, añádelo
            $this->subrayados[] = $numeroVersiculo;

        }

        // Ordena el array de forma ascendente
        sort($this->subrayados);

        // Actualiza el array de versículos subrayados
        $this->actualizarSubrayadosData();
    }

    private function actualizarSubrayadosData()
    {
        if (count($this->subrayados) > 0 && $this->libroSeleccionado && $this->capituloSeleccionado && ! empty(data_get($this->capituloSeleccionado, 'vers'))) {
            $rangos = [];
            $start = null;
            $end = null;

            foreach ($this->subrayados as $numero) {
                if ($start === null) {
                    $start = $numero;
                    $end = $numero;
                } elseif ($numero === $end + 1) {
                    $end = $numero;
                } else {
                    $rangos[] = ($start === $end) ? (string) $start : (string) $start.'-'.(string) $end;
                    $start = $numero;
                    $end = $numero;
                }
            }

            // Agregar el último rango después del bucle
            if ($start !== null) {
                $rangos[] = ($start === $end) ? (string) $start : (string) $start.'-'.(string) $end;
            }

            $chapter = data_get($this->capituloSeleccionado, 'chapter', '');
            $versionSlug = strtoupper($this->versionSeleccionada['version'] ?? '');
            $cita = $this->libroSeleccionado->abrev.' '.$chapter.': '.implode('; ', $rangos).' ('.$versionSlug.')';
            $cita_larga = $this->libroSeleccionado->libro.' '.$chapter.': '.implode('; ', $rangos).' ('.$versionSlug.')';

            $this->subrayadosData = [
                'libro' => $this->libroSeleccionado->libro,
                'capitulo' => $chapter,
                'version' => $this->versionSeleccionada['name'] ?? '',
                'cita' => $cita,
                'cita_larga' => $cita_larga,
                'versiculos' => [],
            ];

            foreach ($this->subrayados as $numeroVersiculo) {
                // Encuentra el versículo correspondiente en $capituloSeleccionado
                foreach ($this->capituloSeleccionado['vers'] as $versiculo) {
                    if ($versiculo['number'] == $numeroVersiculo) {
                        $this->subrayadosData['versiculos'][] = [
                            'numero' => $numeroVersiculo,
                            'texto' => $versiculo['verse'],
                        ];
                        break; // Detiene el bucle interno cuando se encuentra el versículo
                    }
                }
            }
        } else {
            $this->subrayadosData = [];
        }

        if ($this->despacharEvento) {
            $this->dispatch('bibliaSeleccionada', $this->subrayadosData);
        }
    }

    public function resaltar()
    {
        $this->versiculosResaltados[] = $this->subrayadosData;
        $this->subrayados = [];
    }

    public function previsualizarVersiculo($index)
    {
        if (isset($this->versiculosResaltados[$index])) {
            $this->versiculoViendo = $this->versiculosResaltados[$index];
            $this->indexVersiculoViendo = $index;
            $this->dispatch('abrirModal', nombreModal: 'modalVersiculoResaltado_'.$this->name_id);
        }
    }

    public function verVersiculoResaltado($index)
    {
        // Verifica que el índice sea válido
        if (isset($this->versiculosResaltados[$index])) {
            $x = $this->versiculosResaltados[$index];

            $this->selectLibro = $x['libro'];
            $this->selectCapitulo = $x['capitulo'];
            $this->selectVersion = $x['version'];

            $this->updatedSelectLibro($x['libro']);
            $this->updatedSelectCapitulo($x['capitulo']);
            $this->updatedSelectVersion($x['version']);
            $this->dispatch('abrirModal', nombreModal: 'modalBiblia_'.$this->name_id);

            foreach ($x['versiculos'] as $versiculo) {
                $this->subrayadosTemp[] = $versiculo['numero'];
            }

        }
    }

    public function desmarcar($index)
    {
        // Verifica que el índice sea válido
        if (isset($this->versiculosResaltados[$index])) {
            // Elimina el elemento del array usando el índice
            unset($this->versiculosResaltados[$index]);
            // Reindexa el array para evitar índices faltantes
            $this->versiculosResaltados = array_values($this->versiculosResaltados);
        }
        $this->subrayadosTemp = [];
    }

    public function render()
    {
        return view('livewire.tiempo-con-dios.biblia');
    }
}
