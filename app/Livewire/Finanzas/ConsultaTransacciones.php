<?php

namespace App\Livewire\Finanzas;

use App\Exports\ConsultaTransaccionesFinanzasExport;
use App\Models\Actividad;
use App\Models\Ofrenda;
use App\Models\Pago;
use App\Models\PuntoDePago;
use App\Models\TipoPago;
use App\Services\SapTransferBorrador;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class ConsultaTransacciones extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $fechaInicio = '';

    public string $fechaFin = '';

    public string $tipoIngreso = 'todos';

    /** @var array<int, string> */
    public array $metodoPagoIds = [];

    /** @var array<int, string> */
    public array $formasPago = [];

    /** @var array<int, string> */
    public array $actividadIds = [];

    /** @var array<int, string> */
    public array $puntoPagoIds = [];

    public bool $mostrarResultados = false;

    public int $versionFiltros = 0;

    /** @var array<string, int> */
    public array $faltantesSap = [];

    public string $mensajeExportacion = '';

    public function mount(): void
    {
        $this->autorizar();
        $this->fechaInicio = now()->startOfMonth()->toDateString();
        $this->fechaFin = now()->toDateString();
    }

    public function buscar(): void
    {
        $this->autorizar();
        $this->validate([
            'fechaInicio' => ['required', 'date'],
            'fechaFin' => ['required', 'date', 'after_or_equal:fechaInicio'],
            'tipoIngreso' => ['required', Rule::in(['todos', 'ofrenda', 'actividad'])],
        ]);

        $this->metodoPagoIds = [];
        $this->formasPago = [];
        $this->actividadIds = [];
        $this->puntoPagoIds = [];
        $this->versionFiltros++;
        $this->faltantesSap = [];
        $this->mensajeExportacion = '';
        $this->mostrarResultados = true;
        $this->resetPage();
    }

    public function filtrar(): void
    {
        $this->autorizar();
        $this->validate([
            'fechaInicio' => ['required', 'date'],
            'fechaFin' => ['required', 'date', 'after_or_equal:fechaInicio'],
            'tipoIngreso' => ['required', Rule::in(['todos', 'ofrenda', 'actividad'])],
            'metodoPagoIds' => ['array', 'max:1000'],
            'metodoPagoIds.*' => ['integer', 'distinct', 'exists:tipos_pago,id'],
            'formasPago' => ['array', 'max:1000'],
            'formasPago.*' => ['distinct', Rule::in(['29', '32', '51', 'sin_codigo'])],
            'actividadIds' => ['array', 'max:1000'],
            'actividadIds.*' => ['integer', 'distinct', 'exists:actividades,id'],
            'puntoPagoIds' => ['array', 'max:1000'],
            'puntoPagoIds.*' => ['integer', 'distinct', 'exists:puntos_de_pago,id'],
        ]);

        if ($this->tipoIngreso === 'ofrenda') {
            $this->actividadIds = [];
            $this->puntoPagoIds = [];
        }

        $this->resetPage();
    }

    public function render(): View
    {
        $this->autorizar();

        $movimientos = null;
        $cantidad = 0;
        $total = 0;

        if ($this->mostrarResultados) {
            $consulta = $this->consultaMovimientos();
            $cantidad = (clone $consulta)->count();
            $total = (clone $consulta)->sum('valor');
            $movimientos = $consulta->orderByDesc('fecha')->orderByDesc('origen')->orderByDesc('id')->paginate(25);
        }

        return view('livewire.finanzas.consulta-transacciones', [
            'movimientos' => $movimientos,
            'cantidad' => $cantidad,
            'total' => $total,
            'metodosPago' => TipoPago::query()->orderBy('nombre')->get(['id', 'nombre']),
            'actividades' => Actividad::query()->orderBy('nombre')->get(['id', 'nombre']),
            'puntosPago' => PuntoDePago::withTrashed()->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function exportarExcel(): BinaryFileResponse
    {
        $this->filtrar();
        abort_unless($this->mostrarResultados, 422);

        return Excel::download(
            new ConsultaTransaccionesFinanzasExport($this->consultaMovimientos()),
            'transacciones_finanzas_'.$this->sufijoFechasExportacion().'.xlsx'
        );
    }

    public function exportarBorradorSap(SapTransferBorrador $generador): ?BinaryFileResponse
    {
        $this->filtrar();
        abort_unless($this->mostrarResultados, 422);
        $movimientos = $this->consultaMovimientosSap()->limit(10001)->get();
        if ($movimientos->isEmpty() || $movimientos->count() > 10000) {
            $this->mensajeExportacion = $movimientos->isEmpty()
                ? 'No hay movimientos para generar el borrador.'
                : 'El borrador admite hasta 10.000 movimientos por descarga. Reduce el rango de fechas.';

            return null;
        }

        $resultado = $generador->generar($movimientos);
        $this->faltantesSap = $resultado['faltantes'];
        $this->mensajeExportacion = '';

        $ruta = tempnam(sys_get_temp_dir(), 'sap_borrador_');
        $zip = new ZipArchive;
        if ($zip->open($ruta, ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('No fue posible crear el borrador SAP.');
        }
        $sufijoFechas = $this->sufijoFechasExportacion();
        $zip->addFromString('encabezado_borrador_sap_'.$sufijoFechas.'.txt', $resultado['encabezado']);
        $zip->addFromString('detalle_borrador_sap_'.$sufijoFechas.'.txt', $resultado['detalle']);
        $zip->close();

        return response()->download($ruta, 'borrador_sap_'.$sufijoFechas.'.zip')->deleteFileAfterSend(true);
    }

    private function sufijoFechasExportacion(): string
    {
        return str_replace('-', '', $this->fechaInicio).'_'.str_replace('-', '', $this->fechaFin);
    }

    private function consultaMovimientosSap(): Builder
    {
        $columnaDestinatarioOfrenda = Schema::hasColumn('ofrendas', 'destinatario_id')
            ? 'sap_ofrendas.destinatario_id'
            : 'sap_ofrendas.destinatario';

        return DB::query()->fromSub($this->consultaMovimientos(), 'm')
            ->leftJoin('ofrendas as sap_ofrendas', function (JoinClause $join): void {
                $join->on('sap_ofrendas.id', '=', 'm.id')->where('m.origen', '=', 'ofrenda');
            })
            ->leftJoin('pagos as sap_pagos', function (JoinClause $join): void {
                $join->on('sap_pagos.id', '=', 'm.id')->where('m.origen', '=', 'actividad');
            })
            ->leftJoin('compras as sap_compras', 'sap_compras.id', '=', 'sap_pagos.compra_id')
            ->leftJoin('actividades as sap_actividades', DB::raw('CAST(sap_actividades.id AS VARCHAR)'), '=', DB::raw('CAST(m.actividad_id AS VARCHAR)'))
            ->leftJoin('tipo_ofrenda as sap_tipos_ofrenda', 'sap_tipos_ofrenda.id', '=', 'sap_ofrendas.tipo_ofrenda_id')
            ->leftJoin('destinatarios as sap_dest_ofrenda', 'sap_dest_ofrenda.id', '=', $columnaDestinatarioOfrenda)
            ->leftJoin('destinatarios as sap_dest_compra', 'sap_dest_compra.id', '=', 'sap_compras.destinatario_id')
            ->selectRaw('m.*, COALESCE(sap_ofrendas.moneda_id, sap_pagos.moneda_id) as moneda_id, COALESCE(sap_tipos_ofrenda.codigo_sap, sap_actividades.codigo_sap) as cuenta_sap, COALESCE(sap_dest_ofrenda.sucursal_sap, sap_dest_compra.sucursal_sap) as sucursal_sap, COALESCE(sap_dest_ofrenda.centro_costo_sap, sap_dest_compra.centro_costo_sap) as centro_costo_sap');
    }

    private function autorizar(): void
    {
        abort_unless(auth()->user()?->hasPermissionTo('finanzas.consultar_transacciones'), 403);
    }

    private function consultaMovimientos(): Builder
    {
        $ofrendas = Ofrenda::query()
            ->leftJoin('tipo_ofrenda as tipos_ofrenda', 'tipos_ofrenda.id', '=', 'ofrendas.tipo_ofrenda_id')
            ->leftJoin('users as donantes', 'donantes.id', '=', 'ofrendas.user_id')
            ->leftJoin('tipos_pago as medios_ofrenda', 'medios_ofrenda.id', '=', 'ofrendas.tipo_pago_id')
            ->leftJoin('estados_pago as estados_ofrenda', 'estados_ofrenda.id', '=', 'ofrendas.estado_pago_id')
            ->selectRaw("'ofrenda' as origen, ofrendas.id, ofrendas.fecha, ofrendas.valor, ofrendas.referencia_donacion as referencia, COALESCE(NULLIF(ofrendas.nombre_donante, ''), donantes.primer_nombre, 'Sin nombre') as persona, COALESCE(ofrendas.identificacion_donante, donantes.identificacion) as identificacion, tipos_ofrenda.nombre as concepto, medios_ofrenda.nombre as metodo_pago, ofrendas.tipo_pago_id, ofrendas.int_id_forma_pago, estados_ofrenda.nombre as estado, NULL as actividad_id, NULL as punto_pago_id, NULL as punto_pago")
            ->whereBetween('ofrendas.fecha', [$this->fechaInicio, $this->fechaFin]);

        $pagos = Pago::withTrashed()
            ->join('compras', 'compras.id', '=', 'pagos.compra_id')
            ->leftJoin('actividades', DB::raw('CAST(actividades.id AS VARCHAR)'), '=', 'compras.actividad_id')
            ->leftJoin('tipos_pago as medios_pago', 'medios_pago.id', '=', 'pagos.tipo_pago_id')
            ->leftJoin('estados_pago as estados_pago', 'estados_pago.id', '=', 'pagos.estado_pago_id')
            ->leftJoin('cajas', 'cajas.id', '=', 'pagos.registro_caja_id')
            ->leftJoin('puntos_de_pago', 'puntos_de_pago.id', '=', 'cajas.punto_de_pago_id')
            ->selectRaw("'actividad' as origen, pagos.id, pagos.fecha, pagos.valor, pagos.referencia_pago as referencia, compras.nombre_completo_comprador as persona, compras.identificacion_comprador as identificacion, actividades.nombre as concepto, medios_pago.nombre as metodo_pago, pagos.tipo_pago_id, pagos.int_id_forma_pago, CASE WHEN pagos.deleted_at IS NOT NULL OR compras.deleted_at IS NOT NULL THEN 'Eliminado' ELSE estados_pago.nombre END as estado, actividades.id as actividad_id, puntos_de_pago.id as punto_pago_id, puntos_de_pago.nombre as punto_pago")
            ->whereBetween('pagos.fecha', [$this->fechaInicio, $this->fechaFin]);

        $union = match ($this->tipoIngreso) {
            'ofrenda' => $ofrendas,
            'actividad' => $pagos,
            default => $ofrendas->unionAll($pagos),
        };

        $consulta = DB::query()->fromSub($union, 'movimientos');

        if ($this->metodoPagoIds !== []) {
            $consulta->whereIn('tipo_pago_id', array_map('intval', $this->metodoPagoIds));
        }
        if ($this->formasPago !== []) {
            $codigosPasarela = array_map('intval', array_filter($this->formasPago, fn (string $forma): bool => $forma !== 'sin_codigo'));
            $consulta->where(function (Builder $filtro) use ($codigosPasarela): void {
                if ($codigosPasarela !== []) {
                    $filtro->whereIn('int_id_forma_pago', $codigosPasarela);
                }
                if (in_array('sin_codigo', $this->formasPago, true)) {
                    $filtro->orWhereNull('int_id_forma_pago')->orWhere('int_id_forma_pago', 0);
                }
            });
        }
        if ($this->tipoIngreso !== 'ofrenda' && $this->actividadIds !== []) {
            $consulta->whereIn('actividad_id', array_map('intval', $this->actividadIds));
        }
        if ($this->tipoIngreso !== 'ofrenda' && $this->puntoPagoIds !== []) {
            $consulta->whereIn('punto_pago_id', array_map('intval', $this->puntoPagoIds));
        }

        return $consulta;
    }
}
