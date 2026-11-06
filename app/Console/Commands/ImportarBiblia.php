<?php

namespace App\Console\Commands;

use App\Helpers\Helpers;
use App\Models\BibliaLibro;
use App\Models\BibliaVersiculo;
use App\Models\BibliaVersion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class ImportarBiblia extends Command
{
    protected $signature = 'biblia:importar {version? : Slug de la versión (rvr1960, tla, ntv, nvi, lbla) o vacía para todas} {--force : Sobrescribir versículos si ya existen}';

    protected $description = 'Descarga e importa las versiones de la Biblia a la base de datos central';

    protected array $versionesConfig = [
        'rvr1960' => [
            'nombre' => 'Reina-Valera 1960',
            'abreviatura' => 'RVR1960',
            'orden' => 1,
            'archivo' => 'RVR1960_vid_149.json',
            'url' => 'https://raw.githubusercontent.com/mrk214/bible-data-es-spa/main/data/es___spa___spa/RVR1960_vid_149.json',
        ],
        'tla' => [
            'nombre' => 'Traducción en Lenguaje Actual',
            'abreviatura' => 'TLA',
            'orden' => 2,
            'archivo' => 'TLA_vid_176.json',
            'url' => 'https://raw.githubusercontent.com/mrk214/bible-data-es-spa/main/data/es___spa___spa/TLA_vid_176.json',
        ],
        'ntv' => [
            'nombre' => 'Nueva Traducción Viviente',
            'abreviatura' => 'NTV',
            'orden' => 3,
            'archivo' => 'NTV_vid_127.json',
            'url' => 'https://raw.githubusercontent.com/mrk214/bible-data-es-spa/main/data/es___spa___spa/NTV_vid_127.json',
        ],
        'nvi' => [
            'nombre' => 'Nueva Versión Internacional',
            'abreviatura' => 'NVI',
            'orden' => 4,
            'archivo' => 'NVI_vid_128.json',
            'url' => 'https://raw.githubusercontent.com/mrk214/bible-data-es-spa/main/data/es___spa___spa/NVI_vid_128.json',
        ],
        'lbla' => [
            'nombre' => 'La Biblia de las Américas',
            'abreviatura' => 'LBLA',
            'orden' => 5,
            'archivo' => 'LBLA_vid_89.json',
            'url' => 'https://raw.githubusercontent.com/mrk214/bible-data-es-spa/main/data/es___spa___spa/LBLA_vid_89.json',
        ],
    ];

    protected array $usfmMap = [
        1 => 'GEN', 2 => 'EXO', 3 => 'LEV', 4 => 'NUM', 5 => 'DEU', 6 => 'JOS', 7 => 'JDG', 8 => 'RUT',
        9 => '1SA', 10 => '2SA', 11 => '1KI', 12 => '2KI', 13 => '1CH', 14 => '2CH', 15 => 'EZR', 16 => 'NEH',
        17 => 'EST', 18 => 'JOB', 19 => 'PSA', 20 => 'PRO', 21 => 'ECC', 22 => 'SNG', 23 => 'ISA', 24 => 'JER',
        25 => 'LAM', 26 => 'EZK', 27 => 'DAN', 28 => 'HOS', 29 => 'JOL', 30 => 'AMO', 31 => 'OBA', 32 => 'JON',
        33 => 'MIC', 34 => 'NAM', 35 => 'HAB', 36 => 'ZEP', 37 => 'HAG', 38 => 'ZEC', 39 => 'MAL',
        40 => 'MAT', 41 => 'MRK', 42 => 'LUK', 43 => 'JHN', 44 => 'ACT', 45 => 'ROM', 46 => '1CO', 47 => '2CO',
        48 => 'GAL', 49 => 'EPH', 50 => 'PHP', 51 => 'COL', 52 => '1TH', 53 => '2TH', 54 => '1TI', 55 => '2TI',
        56 => 'TIT', 57 => 'PHM', 58 => 'HEB', 59 => 'JAS', 60 => '1PE', 61 => '2PE', 62 => '1JN', 63 => '2JN',
        64 => '3JN', 65 => 'JUD', 66 => 'REV',
    ];

    public function handle(): int
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $this->info('=============================================');
        $this->info('  IMPORTADOR DE BIBLIA LOCAL - REDIL CLOUD   ');
        $this->info('=============================================');

        // Paso 1: Asegurar los 66 libros en la base de datos
        $this->asegurarLibros();

        // Paso 2: Determinar qué versiones importar
        $versionParam = strtolower((string) $this->argument('version'));
        $force = (bool) $this->option('force');

        if ($versionParam && ! isset($this->versionesConfig[$versionParam])) {
            $this->error("La versión [{$versionParam}] no es válida. Opciones: ".implode(', ', array_keys($this->versionesConfig)));

            return self::FAILURE;
        }

        $versionesAImportar = $versionParam ? [$versionParam => $this->versionesConfig[$versionParam]] : $this->versionesConfig;

        foreach ($versionesAImportar as $slug => $config) {
            $this->importarVersion($slug, $config, $force);
        }

        $this->newLine();
        $this->info('✓ Proceso de importación finalizado con éxito.');

        return self::SUCCESS;
    }

    private function asegurarLibros(): void
    {
        $this->line('Verificando libros de la Biblia en base de datos central...');
        $librosHelpers = Helpers::libros2();

        $orden = 1;
        foreach ($librosHelpers as $item) {
            $usfm = $this->usfmMap[$orden] ?? null;
            $slug = strtolower($item->names[0]);

            BibliaLibro::updateOrCreate(
                ['orden' => $orden],
                [
                    'slug' => $slug,
                    'nombre' => $item->libro,
                    'abreviatura' => $item->abrev,
                    'usfm' => $usfm,
                    'testamento' => $item->testament,
                    'capitulos' => $item->chapters,
                ]
            );
            $orden++;
        }

        $this->info('✓ 66 libros verificados correctamente.');
    }

    private function importarVersion(string $slug, array $config, bool $force): void
    {
        $this->newLine();
        $this->info("▶ Procesando versión: [{$config['abreviatura']}] {$config['nombre']}");

        $version = BibliaVersion::updateOrCreate(
            ['slug' => $slug],
            [
                'nombre' => $config['nombre'],
                'abreviatura' => $config['abreviatura'],
                'orden' => $config['orden'],
                'activo' => true,
            ]
        );

        // Verificar si ya tiene versículos importados
        $versiculosCount = BibliaVersiculo::where('version_id', $version->id)->count();
        if ($versiculosCount >= 31000 && ! $force) {
            $this->warn("  Esta versión ya tiene {$versiculosCount} versículos cargados. Se omite. (Usa --force para recargar).");

            return;
        }

        // Si es una ejecución forzada o una importación anterior quedó a medias, limpiar para empezar desde cero limpiamente
        if ($versiculosCount > 0) {
            $this->line("  Limpiando registros previos o incompletos de {$config['abreviatura']}...");
            BibliaVersiculo::where('version_id', $version->id)->delete();
        }

        // Obtener contenido JSON (local o remoto)
        $jsonContent = $this->obtenerJsonVersion($config);
        if (! $jsonContent) {
            $this->error("  No se pudo obtener el JSON para {$config['abreviatura']}.");

            return;
        }

        $data = json_decode($jsonContent, false);
        unset($jsonContent); // Liberar memoria

        if (! isset($data->books) || ! is_array($data->books)) {
            $this->error("  El formato del JSON de {$config['abreviatura']} no contiene la lista de libros esperada.");

            return;
        }

        $librosDB = BibliaLibro::all()->keyBy('usfm');
        $librosPorOrden = BibliaLibro::all()->keyBy('orden');

        $now = now();
        $batch = [];
        $totalInsertados = 0;

        $bar = $this->output->createProgressBar(count($data->books));
        $bar->start();

        foreach ($data->books as $indexBook => $bookData) {
            // Buscar el libro correspondiente
            $usfm = $bookData->usfm ?? ($this->usfmMap[$indexBook + 1] ?? null);
            $libroModel = $librosDB->get($usfm) ?? $librosPorOrden->get($indexBook + 1);

            if (! $libroModel) {
                $bar->advance();

                continue;
            }

            if (! empty($bookData->chapters) && is_array($bookData->chapters)) {
                foreach ($bookData->chapters as $indexChap => $chapterData) {
                    $capituloNum = $this->extraerNumeroCapitulo($chapterData->usfm ?? '', $indexChap + 1);

                    // Agrupar versículos del capítulo
                    $versiculosCapitulo = [];
                    $ultimoEncabezado = null;

                    if (! empty($chapterData->items) && is_array($chapterData->items)) {
                        foreach ($chapterData->items as $item) {
                            $type = (string) ($item->type ?? '');

                            // Si es un subtítulo / encabezado
                            if (str_starts_with($type, 'heading') || str_starts_with($type, 'section')) {
                                $textoHeading = $this->extraerTexto($item->lines ?? ($item->text ?? ''));
                                if ($textoHeading !== '') {
                                    $ultimoEncabezado = $textoHeading;
                                }

                                continue;
                            }

                            // Si es un versículo
                            if ($type === 'verse') {
                                $verseNum = $this->extraerNumeroVersiculo($item->verse_numbers ?? null);

                                if ($verseNum <= 0) {
                                    continue;
                                }

                                $verseText = $this->extraerTexto($item->lines ?? ($item->text ?? ''));

                                if ($verseText === '') {
                                    continue;
                                }

                                if (! isset($versiculosCapitulo[$verseNum])) {
                                    $versiculosCapitulo[$verseNum] = [
                                        'version_id' => $version->id,
                                        'libro_id' => $libroModel->id,
                                        'capitulo' => $capituloNum,
                                        'versiculo' => $verseNum,
                                        'texto' => $verseText,
                                        'encabezado' => $ultimoEncabezado,
                                        'created_at' => $now,
                                        'updated_at' => $now,
                                    ];
                                    $ultimoEncabezado = null; // Asignado al primer versículo de la sección
                                } else {
                                    // Continuación de versículo en varias líneas
                                    $versiculosCapitulo[$verseNum]['texto'] .= ' '.$verseText;
                                }
                            }
                        }
                    }

                    // Encolar versículos para inserción en lote con llave única para evitar duplicados en el mismo lote
                    foreach ($versiculosCapitulo as $vNum => $vRow) {
                        $key = "{$vRow['libro_id']}_{$vRow['capitulo']}_{$vNum}";

                        if (! isset($batch[$key])) {
                            $batch[$key] = $vRow;
                        } else {
                            if (! str_contains($batch[$key]['texto'], $vRow['texto'])) {
                                $batch[$key]['texto'] .= ' '.$vRow['texto'];
                            }
                        }

                        if (count($batch) >= 1000) {
                            DB::connection('central')->table('biblia_versiculos')->upsert(
                                array_values($batch),
                                ['version_id', 'libro_id', 'capitulo', 'versiculo'],
                                ['texto', 'encabezado', 'updated_at']
                            );
                            $totalInsertados += count($batch);
                            $batch = [];
                        }
                    }
                }
            }

            $bar->advance();
        }

        if (count($batch) > 0) {
            DB::connection('central')->table('biblia_versiculos')->upsert(
                array_values($batch),
                ['version_id', 'libro_id', 'capitulo', 'versiculo'],
                ['texto', 'encabezado', 'updated_at']
            );
            $totalInsertados += count($batch);
            $batch = [];
        }

        $bar->finish();
        $this->newLine();
        $this->info("  ✓ {$config['abreviatura']} completada: {$totalInsertados} versículos insertados.");
    }

    private function obtenerJsonVersion(array $config): ?string
    {
        $storageDir = storage_path('app/biblia');
        if (! File::isDirectory($storageDir)) {
            File::makeDirectory($storageDir, 0755, true, true);
        }

        $localPath = "{$storageDir}/{$config['archivo']}";

        // 1. Si ya existe localmente en storage/app/biblia/, leerlo directamente
        if (File::exists($localPath)) {
            $this->line("  Leyendo desde caché local: {$config['archivo']}");

            return File::get($localPath);
        }

        // 2. Si no existe, descargarlo automáticamente desde GitHub
        $this->line("  Descargando desde GitHub: {$config['url']}...");
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'REDIL-Cloud-Importer/1.0',
            ])->timeout(180)->get($config['url']);

            if ($response->successful()) {
                $body = $response->body();
                File::put($localPath, $body);
                $this->line("  Descarga completada y guardada en storage/app/biblia/{$config['archivo']}");

                return $body;
            }

            $this->error("  Error al descargar ({$response->status()})");

            return null;
        } catch (\Exception $e) {
            $this->error("  Excepción de red al descargar: {$e->getMessage()}");

            return null;
        }
    }

    private function extraerNumeroCapitulo(string $usfm, int $fallback): int
    {
        if (str_contains($usfm, '.')) {
            $parts = explode('.', $usfm);
            $num = (int) end($parts);
            if ($num > 0) {
                return $num;
            }
        }

        return $fallback;
    }

    private function extraerTexto(mixed $lines): string
    {
        if (is_null($lines)) {
            return '';
        }

        if (is_array($lines)) {
            $partes = [];
            foreach ($lines as $item) {
                $sub = $this->extraerTexto($item);
                if ($sub !== '') {
                    $partes[] = $sub;
                }
            }

            return implode(' ', $partes);
        }

        if (is_object($lines)) {
            if (isset($lines->text)) {
                return $this->extraerTexto($lines->text);
            }
            if (isset($lines->lines)) {
                return $this->extraerTexto($lines->lines);
            }

            return '';
        }

        $str = trim(strip_tags((string) $lines));

        return html_entity_decode($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function extraerNumeroVersiculo(mixed $verseNumbers): int
    {
        if (is_null($verseNumbers)) {
            return 0;
        }

        if (is_array($verseNumbers)) {
            if (empty($verseNumbers)) {
                return 0;
            }

            return $this->extraerNumeroVersiculo($verseNumbers[0]);
        }

        $str = (string) $verseNumbers;
        if (str_contains($str, '-')) {
            $str = explode('-', $str)[0];
        }

        $num = (int) preg_replace('/[^0-9]/', '', $str);

        return $num > 0 ? $num : 0;
    }
}
