<?php

namespace App\Http\Controllers;

use App\Exports\InformeObrerosExport;
use App\Models\CampoExtraGrupo;
use App\Models\CampoInformeExcel;
use App\Models\ClasificacionAsistente;
use App\Models\Grupo;
use App\Models\InformePersonalizado;
use App\Models\ReporteGrupo;
use App\Models\SemanaDeshabilitada;
use App\Models\TipoGrupo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class InformesPersonalizadosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // =========================================================================
    // LISTADO DE INFORMES PERSONALIZADOS
    // =========================================================================

    public function index()
    {
        return view('contenido.paginas.informes-personalizados.index');
    }

    // =========================================================================
    // INFORME ASISTENCIA SEMANAL OBREROS
    // Equivalente a: getInformeAsistenciaSemanalObrerosPersonalizado($id)
    // =========================================================================

    /**
     * Muestra el formulario de filtros para el informe de obreros.
     * selector_id=8 en campos_informe_excel corresponde a campos de obreros.
     */
    public function showInformeObreros(int $id)
    {
        $informePersonalizado = InformePersonalizado::findOrFail($id);

        $tiposDeGrupos = TipoGrupo::select('id', 'nombre')
            ->orderBy('orden', 'asc')
            ->get();

        $clasificacionAsistentes = ClasificacionAsistente::orderBy('id', 'asc')->get();

        // selector_id=8 corresponde a campos de obreros en MANANTIAL y 1 a campos de asistentes
        $camposInformeExcel = CampoInformeExcel::whereIn('selector_id', [1, 8])
            ->orderBy('orden', 'asc')
            ->get();

        return view('contenido.paginas.informes-personalizados.informe-asistencia-obreros', [
            'anio' => date('Y'),
            'semana' => '',
            'informePersonalizado' => $informePersonalizado,
            'tiposDeGrupos' => $tiposDeGrupos,
            'clasificacionAsistentes' => $clasificacionAsistentes,
            'clasificacionAsistentesSeleccionados' => null,
            'tipoGrupoSeleccionado' => null,
            'camposInformeExcel' => $camposInformeExcel,
        ]);
    }

    // =========================================================================
    // EXPORTAR INFORME OBREROS
    // Equivalente a: postExportarInformePersonalizadoObreros($id)
    // =========================================================================

    /**
     * Procesa el formulario y genera el Excel de asistencia de obreros.
     */
    public function exportarInformeObreros(Request $request, int $id)
    {
        $informePersonalizado = InformePersonalizado::findOrFail($id);

        // ── 1. Campos de información principal seleccionados ──────────────────
        if ($request->filled('info_principal')) {
            $arrayIdsCampos = $request->input('info_principal');
            $camposInfoPrincipalSeleccionados = CampoInformeExcel::whereIn('id', $arrayIdsCampos)
                ->orderBy('orden', 'asc')
                ->get();
            $cantidadCamposInfoSeleccionados = $camposInfoPrincipalSeleccionados->count();
            $arrayNombreCampos = CampoInformeExcel::whereIn('id', $arrayIdsCampos)
                ->pluck('nombre_campo_bd')
                ->toArray();
        } else {
            $camposInfoPrincipalSeleccionados = CampoInformeExcel::whereIn('selector_id', [1, 8])
                ->orderBy('orden', 'asc')
                ->get();
            $cantidadCamposInfoSeleccionados = $camposInfoPrincipalSeleccionados->count();
            $arrayNombreCampos = CampoInformeExcel::whereIn('selector_id', [1, 8])
                ->pluck('nombre_campo_bd')
                ->toArray();
        }

        // ── 2. Campos extra de grupo ──────────────────────────────────────────
        if ($request->filled('campos_extra_grupo')) {
            $arrayCamposExtra = $request->input('campos_extra_grupo');
            $camposExtraGrupoSeleccionados = CampoExtraGrupo::whereIn('id', $arrayCamposExtra)->get();
            $cantidadCamposExtraGrupo = CampoExtraGrupo::whereIn('id', $arrayCamposExtra)
                ->where('visible', true)
                ->count();
        } else {
            $camposExtraGrupoSeleccionados = CampoExtraGrupo::where('visible', true)->get();
            $cantidadCamposExtraGrupo = $camposExtraGrupoSeleccionados->count();
        }

        // ── 3. Tipos de grupo seleccionados ───────────────────────────────────
        $arrayTiposGrupos = $request->input('selectTipoDeGrupo', []);

        // ── 4. Grupo raíz seleccionado ────────────────────────────────────────
        $grupoSeleccionado = null;
        if ($request->filled('grupo_id')) {
            $grupoSeleccionado = Grupo::find($request->input('grupo_id'));
        }

        // ── 5. Rango de fechas ────────────────────────────────────────────────
        $rango = $request->input('rango', '1t');
        $anio = $request->input('anio', date('Y'));
        $banderaFinDeAnio = false;

        [$fechaInicio, $fechaFin] = $this->calcularRangoFechas($rango, $anio, $request, $banderaFinDeAnio);

        // ── 6. Incluir encargados / asistentes ────────────────────────────────
        $incluirEncargados = $request->has('incluir-encargados');
        $incluirAsistentes = $request->has('incluir-asistentes');

        // ── 7. Cálculo de semanas ─────────────────────────────────────────────
        [$semanaIni, $semanaFin] = $this->calcularSemanas($fechaInicio, $fechaFin, $anio, $banderaFinDeAnio);

        $arraySemanasDeshabilitadas = SemanaDeshabilitada::where('anio', $anio)
            ->pluck('numero_semana')
            ->toArray();

        // ── 8. Clasificaciones de asistentes ─────────────────────────────────
        if ($request->filled('filtro_clasificacion_asistentes')) {
            $clasificacionAsistentesSeleccionados = ClasificacionAsistente::whereIn(
                'id',
                $request->input('filtro_clasificacion_asistentes')
            )->orderBy('id', 'asc')->get();
        } else {
            $clasificacionAsistentesSeleccionados = ClasificacionAsistente::all();
        }

        // ── 9. Construcción de la tabla HTML ──────────────────────────────────
        $tablaCompleta = '';

        if ($grupoSeleccionado) {
            $estiloInforme = $request->input('estilo_informe', 'bloques');

            $tablaCompleta = $this->construirTablaObreros(
                grupoSeleccionado: $grupoSeleccionado,
                arrayTiposGrupos: $arrayTiposGrupos,
                semanaIni: $semanaIni,
                semanaFin: $semanaFin,
                anio: $anio,
                arraySemanasDeshabilitadas: $arraySemanasDeshabilitadas,
                camposInfoPrincipalSeleccionados: $camposInfoPrincipalSeleccionados,
                camposExtraGrupoSeleccionados: $camposExtraGrupoSeleccionados,
                cantidadCamposExtraGrupo: $cantidadCamposExtraGrupo,
                cantidadCamposInfoSeleccionados: $cantidadCamposInfoSeleccionados,
                arrayNombreCampos: $arrayNombreCampos,
                incluirEncargados: $incluirEncargados,
                incluirAsistentes: $incluirAsistentes,
                estiloInforme: $estiloInforme,
            );
        }

        // ── 10. Exportar Excel ────────────────────────────────────────────────
        $nombreArchivo = $informePersonalizado->nombre.'.xlsx';

        return Excel::download(
            new InformeObrerosExport($tablaCompleta),
            $nombreArchivo
        );
    }

    // =========================================================================
    // MÉTODOS PRIVADOS DE SOPORTE
    // =========================================================================

    /**
     * Calcula la fecha de inicio y fin según el rango seleccionado.
     */
    private function calcularRangoFechas(string $rango, int|string $anio, Request $request, bool &$banderaFinDeAnio): array
    {
        $fechaInicio = null;
        $fechaFin = null;

        if ($rango === 'semana') {
            $semana = $request->input('semana');
            $fechaInicio = Carbon::parse($semana)->startOfWeek()->format('Y-m-d');
            $fechaFin = Carbon::parse($semana)->endOfWeek()->format('Y-m-d');

            return [$fechaInicio, $fechaFin];
        }

        $mesesConBandera = ['2m', '4t', '2t', '2s', '12m', 'anio'];

        $map = [
            '1m' => ["{$anio}-01-01", Carbon::create($anio, 1)->endOfMonth()->format('Y-m-d')],
            '2m' => ["{$anio}-02-01", Carbon::create($anio, 2)->endOfMonth()->format('Y-m-d')],
            '3m' => ["{$anio}-03-01", Carbon::create($anio, 3)->endOfMonth()->format('Y-m-d')],
            '4m' => ["{$anio}-04-01", Carbon::create($anio, 4)->endOfMonth()->format('Y-m-d')],
            '5m' => ["{$anio}-05-01", Carbon::create($anio, 5)->endOfMonth()->format('Y-m-d')],
            '6m' => ["{$anio}-06-01", Carbon::create($anio, 6)->endOfMonth()->format('Y-m-d')],
            '7m' => ["{$anio}-07-01", Carbon::create($anio, 7)->endOfMonth()->format('Y-m-d')],
            '8m' => ["{$anio}-08-01", Carbon::create($anio, 8)->endOfMonth()->format('Y-m-d')],
            '9m' => ["{$anio}-09-01", Carbon::create($anio, 9)->endOfMonth()->format('Y-m-d')],
            '10m' => ["{$anio}-10-01", Carbon::create($anio, 10)->endOfMonth()->format('Y-m-d')],
            '11m' => ["{$anio}-11-01", Carbon::create($anio, 11)->endOfMonth()->format('Y-m-d')],
            '12m' => ["{$anio}-12-01", Carbon::create($anio, 12)->endOfMonth()->format('Y-m-d')],
            '1t' => ["{$anio}-01-01", Carbon::create($anio, 3)->endOfMonth()->format('Y-m-d')],
            '2t' => ["{$anio}-04-01", Carbon::create($anio, 6)->endOfMonth()->format('Y-m-d')],
            '3t' => ["{$anio}-07-01", Carbon::create($anio, 9)->endOfMonth()->format('Y-m-d')],
            '4t' => ["{$anio}-10-01", Carbon::create($anio, 12)->endOfMonth()->format('Y-m-d')],
            '1s' => ["{$anio}-01-01", Carbon::create($anio, 6)->endOfMonth()->format('Y-m-d')],
            '2s' => ["{$anio}-07-01", Carbon::create($anio, 12)->endOfMonth()->format('Y-m-d')],
            'anio' => ["{$anio}-01-01", Carbon::create($anio, 12)->endOfMonth()->format('Y-m-d')],
        ];

        if (isset($map[$rango])) {
            [$fechaInicio, $fechaFin] = $map[$rango];

            if (in_array($rango, $mesesConBandera)) {
                $banderaFinDeAnio = true;
            }
        }

        return [$fechaInicio, $fechaFin];
    }

    /**
     * Calcula semana inicial y final ajustando límites del año y semana actual.
     */
    private function calcularSemanas(string $fechaInicio, string $fechaFin, int|string $anio, bool $banderaFinDeAnio): array
    {
        $semanaIni = (int) date('W', strtotime($fechaInicio));
        $semanaFin = $banderaFinDeAnio ? 52 : (int) date('W', strtotime($fechaFin));

        if ($semanaIni >= 52) {
            $semanaIni = 1;
        }

        // Limitar a la semana actual si el año es el corriente
        $semanaActual = (int) date('W');
        if (date('Y', strtotime($fechaInicio)) == $anio) {
            if ($semanaActual >= $semanaIni && $semanaActual <= $semanaFin) {
                $semanaFin = $semanaActual;
            }
        }

        return [$semanaIni, $semanaFin];
    }

    /**
     * Construye la tabla HTML completa del informe de obreros.
     * Lógica migrada directamente de postExportarInformePersonalizadoObreros en GruposController.php (MANANTIAL).
     */
    private function construirTablaObreros(
        \App\Models\Grupo $grupoSeleccionado,
        array $arrayTiposGrupos,
        int $anio,
        int $semanaIni,
        int $semanaFin,
        array $arraySemanasDeshabilitadas,
        $camposInfoPrincipalSeleccionados,
        $camposExtraGrupoSeleccionados,
        int $cantidadCamposExtraGrupo,
        int $cantidadCamposInfoSeleccionados,
        array $arrayNombreCampos,
        bool $incluirEncargados,
        bool $incluirAsistentes,
        string $estiloInforme = 'bloques',
    ): string {
        if ($estiloInforme === 'plano') {
            return $this->construirTablaObrerosPlano(
                $grupoSeleccionado,
                $arrayTiposGrupos,
                $anio,
                $semanaIni,
                $semanaFin,
                $arraySemanasDeshabilitadas,
                $camposInfoPrincipalSeleccionados,
                $camposExtraGrupoSeleccionados,
                $cantidadCamposExtraGrupo,
                $cantidadCamposInfoSeleccionados,
                $arrayNombreCampos,
                $incluirEncargados,
                $incluirAsistentes
            );
        }

        $gruposIds = $grupoSeleccionado->gruposMinisterio()
            ->where('grupos.dado_baja', false)
            ->whereIn('tipo_grupo_id', $arrayTiposGrupos)
            ->pluck('id')
            ->toArray();

        if (in_array($grupoSeleccionado->tipoGrupo->id, $arrayTiposGrupos)) {
            $gruposIds[] = $grupoSeleccionado->id;
        }

        $grupos = Grupo::whereIn('id', $gruposIds)
            ->with(['encargados'])
            ->select('id', 'codigo', 'nombre', 'tipo_grupo_id')
            ->orderBy('id', 'asc')
            ->get();

        $tablaBody = '<table>';

        $coloresGrupo = ['#d9ead3', '#cfe2f3', '#fce5cd', '#e2d0f9', '#d0e0e3'];
        $indexColor = 0;

        foreach ($grupos as $grupo) {
            $color = $coloresGrupo[$indexColor % count($coloresGrupo)];
            $indexColor++;

            // PRE-CALCULAR LOS DATOS DE LAS SEMANAS
            $semanasHtml = '';
            $arraySemanasDatos = [];

            for ($i = $semanaIni; $i <= $semanaFin; $i++) {
                if (in_array($i, $arraySemanasDeshabilitadas)) {
                    continue;
                }

                $primer = new \DateTime;
                $ultimo = new \DateTime;
                $primer->modify("{$anio}W".sprintf('%02d', $i));
                $ultimo->modify("{$anio}W".sprintf('%02d', $i).' +6 days');

                $semanasHtml .= "<td style='background-color: {$color}; border: 1px solid #000; text-align: center; font-weight: bold;'>ASISTENCIA SEMANA {$i}</td>";

                $arraySemanasDatos[] = [
                    'semana' => $i,
                    'primerDia' => $primer->format('Y-m-d'),
                    'ultimoDia' => $ultimo->format('Y-m-d'),
                ];
            }

            // ─── BLOQUE 1: INFO CONDENSADA DEL GRUPO ───
            $tablaBody .= '<tr>';
            $tablaBody .= "<td colspan='10' style='background-color: {$color}; font-weight: bold;'>BLOQUE 1: INFORMACION CONDENSADA GRUPO - {$grupo->nombre}</td>";
            $tablaBody .= '</tr>';

            $tablaBody .= '<tr>';
            $tablaBody .= "<td style='background-color: {$color}; border: 1px solid #000; font-weight: bold;'>NOMBRE DEL GRUPO</td>";
            $tablaBody .= "<td style='background-color: {$color}; border: 1px solid #000; font-weight: bold;'>NOMBRE ENCARGADO DEL GRUPO</td>";
            $tablaBody .= "<td style='background-color: {$color}; border: 1px solid #000; font-weight: bold;'>TIPO GRUPO</td>";
            $tablaBody .= "<td style='background-color: {$color}; border: 1px solid #000; font-weight: bold;'>TOTAL ASISTENCIAS</td>";
            $tablaBody .= $semanasHtml;
            $tablaBody .= '</tr>';

            $tablaBody .= '<tr>';
            $tablaBody .= "<td>{$grupo->nombre}</td>";

            $nombresEncargados = $grupo->encargados->pluck('primer_nombre')->join(', ');
            $tablaBody .= "<td>{$nombresEncargados}</td>";

            $tipoGrupoNombre = $grupo->tipoGrupo->nombre ?? 'Sin Asignar';
            $tablaBody .= "<td>{$tipoGrupoNombre}</td>";

            // Calcular totales semanales del grupo
            $totalesSemanaHtml = '';
            $totalAsistenciasGrupo = 0;

            foreach ($arraySemanasDatos as $datoSemana) {
                $reportesSemana = ReporteGrupo::where('fecha', '>=', $datoSemana['primerDia'])
                    ->where('fecha', '<=', $datoSemana['ultimoDia'])
                    ->where('grupo_id', $grupo->id)
                    ->get();

                $sumaSemana = $reportesSemana->sum('cantidad_asistencias');

                $totalAsistenciasGrupo += $sumaSemana;
                $totalesSemanaHtml .= "<td>{$sumaSemana}</td>";
            }

            $tablaBody .= "<td>{$totalAsistenciasGrupo}</td>";
            $tablaBody .= $totalesSemanaHtml;
            $tablaBody .= '</tr>';
            $tablaBody .= '<tr></tr>';

            // Closure para generar las tablas de encargados y asistentes
            $generarSubTabla = function ($titulo, $idsRelacion) use (
                &$tablaBody, $color, $camposInfoPrincipalSeleccionados, $camposExtraGrupoSeleccionados,
                $semanasHtml, $arraySemanasDatos, $grupo
            ) {
                if (empty($idsRelacion)) {
                    return;
                }

                $usuarios = \App\Models\User::whereIn('id', $idsRelacion)->get();

                if ($usuarios->isEmpty()) {
                    return;
                }

                $tablaBody .= "<tr><td colspan='10' style='background-color: {$color}; font-weight: bold;'>{$titulo}</td></tr>";
                $tablaBody .= '<tr>';
                $tablaBody .= "<td style='background-color: {$color}; border: 1px solid #000; font-weight: bold;'>NOMBRE ASISTENTE</td>";

                foreach ($camposInfoPrincipalSeleccionados as $campo) {
                    $tablaBody .= "<td style='background-color: {$color}; border: 1px solid #000; font-weight: bold;'>{$campo->nombre_campo_informe}</td>";
                }
                foreach ($camposExtraGrupoSeleccionados as $campoExtra) {
                    $tablaBody .= "<td style='background-color: {$color}; border: 1px solid #000; font-weight: bold;'>{$campoExtra->nombre}</td>";
                }

                $tablaBody .= $semanasHtml;
                $tablaBody .= "<td style='background-color: {$color}; border: 1px solid #000; font-weight: bold;'>TOTAL ASISTENCIAS</td>";
                $tablaBody .= '</tr>';

                foreach ($usuarios as $usuario) {
                    $asistenciasAsistente = 0;
                    $celdasAsistenciaHtml = '';

                    foreach ($arraySemanasDatos as $datoSemana) {
                        $reportesSemana = ReporteGrupo::where('fecha', '>=', $datoSemana['primerDia'])
                            ->where('fecha', '<=', $datoSemana['ultimoDia'])
                            ->where('grupo_id', $grupo->id)
                            ->select('id', 'informacion_encargado_grupo')
                            ->get();

                        $asistio = 'NO';

                        if ($reportesSemana->count() > 0) {
                            foreach ($reportesSemana as $reporte) {
                                // Verificar si es encargado y asistió
                                $infoEncargados = is_string($reporte->informacion_encargado_grupo)
                                    ? json_decode($reporte->informacion_encargado_grupo, true)
                                    : $reporte->informacion_encargado_grupo;
                                $infoEncargados = $infoEncargados ?: [];

                                foreach ($infoEncargados as $infoEncargado) {
                                    $encargadoId = is_array($infoEncargado) ? ($infoEncargado['id'] ?? null) : ($infoEncargado->id ?? null);
                                    $encargadoAsistio = is_array($infoEncargado) ? ($infoEncargado['asistio'] ?? false) : ($infoEncargado->asistio ?? false);

                                    if ($encargadoId == $usuario->id && $encargadoAsistio == true) {
                                        $asistio = 'SI';
                                        $asistenciasAsistente++;
                                        break 2;
                                    }
                                }

                                // Verificar si es asistente y asistió
                                $asistioComoAsistente = $reporte->usuarios()
                                    ->wherePivot('asistio', true)
                                    ->where('users.id', $usuario->id)
                                    ->exists();

                                if ($asistioComoAsistente) {
                                    $asistio = 'SI';
                                    $asistenciasAsistente++;
                                    break;
                                }
                            }
                        }

                        $celdasAsistenciaHtml .= "<td>{$asistio}</td>";
                    }

                    $tablaBody .= '<tr>';
                    $tablaBody .= "<td>{$usuario->primer_nombre} {$usuario->primer_apellido}</td>";

                    // Campos de info principal
                    foreach ($camposInfoPrincipalSeleccionados as $campo) {
                        if ($campo->nombre_campo_bd == 'tipo_usuario_id') {
                            $tipoUsuario = \App\Models\TipoUsuario::find($usuario->tipo_usuario_id);
                            $tablaBody .= '<td>'.($tipoUsuario?->nombre ?? 'Sin Asignar').'</td>';
                        } elseif ($campo->nombre_campo_bd == 'grupo_id') {
                            $encargadoPrincipal = $grupo->encargados()->first();
                            if ($encargadoPrincipal) {
                                $gruposDirectos = $encargadoPrincipal->gruposEncargados()->get();
                                $tablaBody .= '<td>'.$gruposDirectos->pluck('nombre')->join(' / ').'</td>';
                            } else {
                                $tablaBody .= '<td>Sin Asignar</td>';
                            }
                        } else {
                            if ($campo->nombre_campo_bd === 'edad' && method_exists($usuario, 'edad')) {
                                $valor = $usuario->edad();
                            } else {
                                $valor = $usuario->{$campo->nombre_campo_bd} ?? 'Sin Asignar';
                            }
                            $tablaBody .= "<td>{$valor}</td>";
                        }
                    }

                    // Campos extra de grupo
                    foreach ($camposExtraGrupoSeleccionados as $campo) {
                        $campoExtraGrupo = $grupo->camposExtras()
                            ->where('campos_extra_grupo.id', $campo->id)
                            ->first();

                        $tablaBody .= $this->renderCampoExtra($campo, $campoExtraGrupo);
                    }

                    $tablaBody .= $celdasAsistenciaHtml;
                    // Total asistencias de este usuario
                    $tablaBody .= "<td>{$asistenciasAsistente}</td>";

                    $tablaBody .= '</tr>';
                }
            };

            // ─── BLOQUE 2: ENCARGADOS ───
            if ($incluirEncargados) {
                $idsEncargados = $grupo->encargados()->pluck('users.id')->toArray();
                $generarSubTabla('BLOQUE 2: DETALLE ENCARGADOS', $idsEncargados);
            }

            // ─── BLOQUE 3: ASISTENTES ───
            if ($incluirAsistentes) {
                $idsAsistentes = $grupo->asistentes()->pluck('users.id')->toArray();
                $generarSubTabla('BLOQUE 3: DETALLE ASISTENTES', $idsAsistentes);
            }

            $tablaBody .= '<tr></tr><tr></tr>';
        }

        $tablaBody .= '</table>';

        return $tablaBody;
    }

    private function obtenerNombreMesCorto(string $mes): string
    {
        $meses = [
            '01' => 'Ene', '02' => 'Feb', '03' => 'Mar', '04' => 'Abr',
            '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago',
            '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dic'
        ];
        return $meses[$mes] ?? '';
    }

    private function construirTablaObrerosPlano(
        \App\Models\Grupo $grupoSeleccionado,
        array $arrayTiposGrupos,
        int $anio,
        int $semanaIni,
        int $semanaFin,
        array $arraySemanasDeshabilitadas,
        $camposInfoPrincipalSeleccionados,
        $camposExtraGrupoSeleccionados,
        int $cantidadCamposExtraGrupo,
        int $cantidadCamposInfoSeleccionados,
        array $arrayNombreCampos,
        bool $incluirEncargados,
        bool $incluirAsistentes,
    ): string {
        $gruposIds = $grupoSeleccionado->gruposMinisterio()
            ->where('grupos.dado_baja', false)
            ->whereIn('tipo_grupo_id', $arrayTiposGrupos)
            ->pluck('id')
            ->toArray();

        if (in_array($grupoSeleccionado->tipoGrupo->id, $arrayTiposGrupos)) {
            $gruposIds[] = $grupoSeleccionado->id;
        }

        $grupos = \App\Models\Grupo::whereIn('id', $gruposIds)
            ->with(['encargados', 'asistentes'])
            ->select('id', 'codigo', 'nombre', 'tipo_grupo_id')
            ->orderBy('id', 'asc')
            ->get();

        $tablaBody = '<table>';

        // ─── CABECERAS PLANAS ───
        $tablaBody .= '<tr>';
        $tablaBody .= "<td style='background-color: #d9ead3; border: 1px solid #000; font-weight: bold;'>NOMBRE ASISTENTE</td>";

        foreach ($camposInfoPrincipalSeleccionados as $campo) {
            $tablaBody .= "<td style='background-color: #d9ead3; border: 1px solid #000; font-weight: bold;'>{$campo->nombre_campo_informe}</td>";
        }
        foreach ($camposExtraGrupoSeleccionados as $campoExtra) {
            $tablaBody .= "<td style='background-color: #d9ead3; border: 1px solid #000; font-weight: bold;'>{$campoExtra->nombre}</td>";
        }

        // PRE-CALCULAR LOS DATOS DE LAS SEMANAS
        $semanasHtml = '';
        $arraySemanasDatos = [];

        for ($i = $semanaIni; $i <= $semanaFin; $i++) {
            if (in_array($i, $arraySemanasDeshabilitadas)) {
                continue;
            }

            $primer = new \DateTime;
            $ultimo = new \DateTime;
            $primer->modify("{$anio}W".sprintf('%02d', $i));
            $ultimo->modify("{$anio}W".sprintf('%02d', $i).' +6 days');

            $nombreMesPri = $this->obtenerNombreMesCorto($primer->format('m'));
            $rangoSemanaTexto = $nombreMesPri . " " . $primer->format('d') . "-" . $ultimo->format('d') . " Semana " . sprintf('%02d', $i);

            $tablaBody .= "<td style='background-color: #d9ead3; border: 1px solid #000; text-align: center; font-weight: bold;'>{$rangoSemanaTexto}</td>";

            $arraySemanasDatos[] = [
                'semana' => $i,
                'primerDia' => $primer->format('Y-m-d'),
                'ultimoDia' => $ultimo->format('Y-m-d'),
            ];
        }

        $tablaBody .= "<td style='background-color: #d9ead3; border: 1px solid #000; font-weight: bold;'>TOTAL ASISTENCIAS</td>";
        $tablaBody .= '</tr>';

        // ─── RECORRER TODOS LOS USUARIOS ───
        foreach ($grupos as $grupo) {
            $idsAProcesar = [];
            if ($incluirEncargados) {
                $idsAProcesar = array_merge($idsAProcesar, $grupo->encargados()->pluck('users.id')->toArray());
            }
            if ($incluirAsistentes) {
                $idsAProcesar = array_merge($idsAProcesar, $grupo->asistentes()->pluck('users.id')->toArray());
            }

            if (empty($idsAProcesar)) {
                continue;
            }

            $idsAProcesar = array_unique($idsAProcesar);
            $usuarios = \App\Models\User::whereIn('id', $idsAProcesar)->get();

            foreach ($usuarios as $usuario) {
                $asistenciasAsistente = 0;
                $celdasAsistenciaHtml = '';

                foreach ($arraySemanasDatos as $datoSemana) {
                    $reporteGrupo = \App\Models\ReporteGrupo::where('fecha', '>=', $datoSemana['primerDia'])
                        ->where('fecha', '<=', $datoSemana['ultimoDia'])
                        ->where('grupo_id', $grupo->id)
                        ->first();

                    if (!$reporteGrupo) {
                        $celdasAsistenciaHtml .= "<td>Sin Reporte</td>";
                        continue;
                    }

                    // Verificar si asistió como encargado
                    $asistio = false;
                    $esEncargado = $grupo->encargados()->where('users.id', $usuario->id)->exists();
                    if ($esEncargado) {
                        $infoEncargados = is_string($reporteGrupo->informacion_encargado_grupo)
                            ? json_decode($reporteGrupo->informacion_encargado_grupo, true)
                            : (array)$reporteGrupo->informacion_encargado_grupo;

                        if (is_array($infoEncargados)) {
                            foreach ($infoEncargados as $infoEncargado) {
                                $infoArray = (array)$infoEncargado;
                                if (isset($infoArray['id']) && $infoArray['id'] == $usuario->id) {
                                    if (isset($infoArray['asistio']) && ($infoArray['asistio'] === true || $infoArray['asistio'] === 1 || $infoArray['asistio'] === '1')) {
                                        $asistio = true;
                                        break;
                                    }
                                }
                            }
                        }
                    }

                    // Si no asistió como encargado, revisar como asistente
                    if (!$asistio) {
                        $esAsistente = $reporteGrupo->usuarios()->where('users.id', $usuario->id)->wherePivot('asistio', true)->exists();
                        if ($esAsistente) {
                            $asistio = true;
                        }
                    }

                    if ($asistio) {
                        $asistenciasAsistente++;
                        $celdasAsistenciaHtml .= "<td>SI</td>";
                    } else {
                        $celdasAsistenciaHtml .= "<td>NO</td>";
                    }
                }

                $tablaBody .= '<tr>';

                $nombreCompleto = trim($usuario->primer_nombre . ' ' . $usuario->segundo_nombre . ' ' . $usuario->primer_apellido . ' ' . $usuario->segundo_apellido);
                $tablaBody .= "<td>{$nombreCompleto}</td>";

                foreach ($camposInfoPrincipalSeleccionados as $campo) {
                    if ($campo->nombre_campo_bd === 'tipo_usuario_id') {
                        $tipoUsuario = $usuario->tipoUsuario->nombre ?? 'Sin Asignar';
                        $tablaBody .= "<td>{$tipoUsuario}</td>";
                    } elseif ($campo->nombre_campo_bd === 'grupo_id') {
                        $tablaBody .= "<td>{$grupo->nombre}</td>";
                    } elseif ($campo->nombre_campo_bd === 'grupo_pertenece') {
                        $encargadoPrincipal = $usuario->encargadoPrincipal;
                        if ($encargadoPrincipal) {
                            $gruposDirectos = $encargadoPrincipal->gruposEncargados()->get();
                            $tablaBody .= '<td>'.$gruposDirectos->pluck('nombre')->join(' / ').'</td>';
                        } else {
                            $tablaBody .= '<td>Sin Asignar</td>';
                        }
                    } else {
                        if ($campo->nombre_campo_bd === 'edad' && method_exists($usuario, 'edad')) {
                            $valor = $usuario->edad();
                        } else {
                            $valor = $usuario->{$campo->nombre_campo_bd} ?? 'Sin Asignar';
                        }
                        $tablaBody .= "<td>{$valor}</td>";
                    }
                }

                foreach ($camposExtraGrupoSeleccionados as $campo) {
                    $campoExtraGrupo = $grupo->camposExtras()
                        ->where('campos_extra_grupo.id', $campo->id)
                        ->first();

                    $tablaBody .= $this->renderCampoExtra($campo, $campoExtraGrupo);
                }

                $tablaBody .= $celdasAsistenciaHtml;
                $tablaBody .= "<td>{$asistenciasAsistente}</td>";
                $tablaBody .= '</tr>';
            }
        }

        $tablaBody .= '</table>';

        return $tablaBody;
    }

    /**
     * Renderiza el valor de un campo extra de grupo según su tipo.
     */
    private function renderCampoExtra(mixed $campo, mixed $campoExtraGrupo): string
    {
        $valor = $campoExtraGrupo?->pivot?->valor ?? null;

        if ($valor === null || $valor === '') {
            return '<td>Sin Asignar</td>';
        }

        // Tipo 1 y 2: texto/número
        if (in_array($campo->tipo_de_campo, [1, 2])) {
            return "<td>{$valor}</td>";
        }

        // Tipo 3: select simple
        if ($campo->tipo_de_campo == 3) {
            $opciones = json_decode($campo->opciones_select ?? '[]');

            foreach ($opciones as $opcion) {
                if ($opcion->value == $valor) {
                    return "<td>{$opcion->nombre}</td>";
                }
            }

            return '<td>Sin Asignar</td>';
        }

        // Tipo 4: select múltiple
        if ($campo->tipo_de_campo == 4) {
            $seleccionadas = json_decode($valor ?? '[]');
            $opciones = json_decode($campo->opciones_select ?? '[]');
            $resultado = '';

            foreach ($opciones as $opcion) {
                if (in_array($opcion->value, (array) $seleccionadas)) {
                    $resultado .= "{$opcion->nombre} / ";
                }
            }

            return '<td>'.rtrim($resultado, ' / ').'</td>';
        }

        return '<td>Sin Asignar</td>';
    }
}
