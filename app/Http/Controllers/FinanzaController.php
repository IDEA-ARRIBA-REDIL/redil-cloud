<?php

namespace App\Http\Controllers;

use App\Models\Sede;
use App\Models\Moneda;
use App\Models\Ingreso;
use App\Models\Iglesia;
use App\Models\Ofrenda;
use App\Models\TipoOfrenda;
use Illuminate\Http\Request;
use App\Models\CajaFinanzas;
use App\Models\Configuracion;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\TipoIdentificacion;
use App\Models\CentroDeCostosEgresos;
use App\Models\CentroDeCostosIngresos;

use App\Exports\IngresosExport;
use App\Models\DocumentoEquivalente;
use App\Models\Egreso;
use App\Models\User;
use App\Models\Proveedor;
use App\Models\TipoEgreso;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Excel as BaseExcel;

class FinanzaController extends Controller
{
  // Vista nuevoIngreso
  public function ingreso()
  {
    $centroDeCostosIngresos = CentroDeCostosIngresos::get();
    $identificaciones = TipoIdentificacion::get();
    $ofrendas = TipoOfrenda::get();
    $cajas = CajaFinanzas::get();
    $monedas = Moneda::get();
    $sedes = Sede::get();
    $tipoPersona = true;

    return view('contenido.paginas.finanzas.nuevo-ingreso', [
      'identificaciones' => $identificaciones,
      'ofrendas' => $ofrendas,
      'monedas' => $monedas,
      'cajas' => $cajas,
      'sedes' => $sedes,
      'tipoPersona' => $tipoPersona,
      'centroDeCostosIngresos' => $centroDeCostosIngresos
    ]);
  }

  public function nuevo()
  {
    $identificaciones = TipoIdentificacion::get();
    $ofrendas = TipoOfrenda::get();
    $cajas = CajaFinanzas::get();
    $monedas = Moneda::get();
    $sedes = Sede::get();
    $tipoPersona = true;

    return view('contenido.paginas.finanzas.documentos', [
      'identificaciones' => $identificaciones,
      'ofrendas' => $ofrendas,
      'monedas' => $monedas,
      'cajas' => $cajas,
      'sedes' => $sedes,
      'tipoPersona' => $tipoPersona
    ]);
  }

  public function crearIngreso(Request $request)
  {
    $request->validate([
      'fecha' => 'required',
      'tipoPersona' => 'required',
      'identificacion' => 'required|string|max:100',
      'tipoIdentificacion' => 'required|string|max:100',
      'sede' => 'required',
      'valor' => 'required|numeric|min:0',
      'descripcion' => 'nullable|string',
      'tipoOfrenda' => 'required',
      'caja' => 'required',
      'centro_de_costos_ingresos' => 'required',
      'moneda' => 'required',
      'campoAdicional1' => 'nullable|string|max:255',
      'campoAdicional2' => 'nullable|string|max:255',
    ]);

    $nombres = '';
    if ($request->tipoPersona == 1) {
      $id = $request->persona;
      $user = User::find($id);
      $nombres = $user->nombre(3);
    } else {
      $nombres = $request->nombre;
    }

    $ingreso = new Ingreso();
    $ingreso->fecha = $request->fecha;
    $ingreso->nombre = $nombres;
    $ingreso->identificacion = $request->identificacion;
    $ingreso->tipo_identificacion = $request->tipoIdentificacion;
    $ingreso->sede_id = $request->sede;
    $ingreso->user_id = $request->persona;
    $ingreso->telefono = $request->telefono;
    $ingreso->direccion = $request->direccion;
    $ingreso->valor = $request->valor;
    $ingreso->descripcion = $request->descripcion;
    $ingreso->tipo_ofrenda_id = $request->tipoOfrenda;
    $ingreso->caja_finanzas_id = $request->caja;
    $ingreso->centro_de_costos_ingresos_id = $request->centro_de_costos_ingresos;
    $ingreso->moneda_id = $request->moneda;
    $ingreso->campo_adicional1 = $request->campoAdicional1;
    $ingreso->campo_adicional2 = $request->campoAdicional2;

    $ingreso->save();

    return back()->with('success', 'Ingreso creado exitosamente.');
  }

  // Vista gestionarIngresos
  public function gestionarIngresos(Request $request)
  {
    $ofrendas = TipoOfrenda::get();
    $cajas = CajaFinanzas::get();
    $centroDeCostosIngresos = CentroDeCostosIngresos::get();
    $monedas = Moneda::get();

    $fechaInicio = $request->fechaInicio;
    $fechaFin = $request->fechaFin;
    $tipoOfrendaId = $request->tipoOfrenda;
    $monedaId = $request->moneda;
    $cajaFinanzasId = $request->caja;
    $centroDeCostosIngresosId = $request->centro_de_costos_ingresos;

    $ingresos = Ingreso::query()->orderBy('id', 'desc');

    if ($fechaInicio) {
      $ingresos = $ingresos->where('fecha', '>=', $fechaInicio);
    }

    if ($fechaFin) {
      $ingresos = $ingresos->where('fecha', '<=', $fechaFin);
    }

    if (!empty($tipoOfrendaId)) {
      $ingresos = $ingresos->where('tipo_ofrenda', $request->tipoOfrenda);
    }

    if (!empty($cajaFinanzasId)) {
      $ingresos = $ingresos->where('caja_finanzas_id', $request->caja);
    }

    if (!empty($monedaId)) {
      $ingresos = $ingresos->where('moneda_id', $request->moneda);
    }

    if (!empty($centroDeCostosIngresosId)) {
      $ingresos = $ingresos->where('centro_de_costos_ingresos_id', $request->centro_de_costos_ingresos);
    }

    $ingresos = $ingresos->with(['tipoOfrendas', 'cajasFinanzas', 'centroDeCostosIngresos'])->paginate(10);

    return view('contenido.paginas.finanzas.gestionar-ingresos', [
      'ofrendas' => $ofrendas,
      'ingresos' => $ingresos,
      'cajas' => $cajas,
      'centroDeCostosIngresos' => $centroDeCostosIngresos,
      'monedas' => $monedas,
      'fechaInicio' => $fechaInicio,
      'fechaFin' => $fechaFin,
      'tipoOfrendaId' => $tipoOfrendaId,
      'cajaFinanzasId' => $cajaFinanzasId,
      'centroDeCostosIngresosId' => $centroDeCostosIngresosId,
      'monedaId' => $monedaId
    ]);
  }

  public function limpiarFiltros()
  {
    return redirect()->route('finanzas.gestionarIngresos');
  }

  public function limpiarFiltrosEgresos()
  {
    return redirect()->route('finanzas.gestionarEgresos');
  }

  public function exportarIngresosExcel(Request $request)
  {
    $query = \App\Models\Ingreso::query();

    if ($request->fechaInicio) {
      $query->whereDate('fecha', '>=', $request->fechaInicio);
    }

    if ($request->fechaFin) {
      $query->whereDate('fecha', '<=', $request->fechaFin);
    }

    if ($request->tipoOfrenda) {
      $query->where('tipo_ofrenda_id', $request->tipoOfrenda);
    }

    if ($request->caja) {
      $query->where('caja_id', $request->caja);
    }

    if ($request->centro_de_costos_ingresos) {
      $query->where('centro_de_costos_ingresos_id', $request->centro_de_costos_ingresos);
    }

    if ($request->moneda) {
      $query->where('moneda_id', $request->moneda);
    }

    $ingresos = $query->with('tipoOfrendas', 'cajasFinanzas', 'moneda')->get();

    $datos = $ingresos->map(function ($ingreso) {
      return [
        'Fecha'           => $ingreso->fecha,
        'Nombre'          => $ingreso->nombre,
        'Tipo de Ofrenda' => $ingreso->tipoOfrendas->nombre ?? '',
        'Caja'            => $ingreso->cajasFinanzas->nombre ?? 'Sin caja',
        'Centro de costos' => $ingreso->centroDeCostosIngresos->nombre ?? 'Si centro de costos',
        'Estado'          => $ingreso->anulado ? 'Anulado' : 'Aprobado',
        'Moneda'          => $ingreso->moneda->nombre ?? '',
      ];
    });

    return Excel::download(new class($datos) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
      protected $datos;

      public function __construct($datos)
      {
        $this->datos = $datos;
      }

      public function collection()
      {
        return new Collection($this->datos);
      }

      public function headings(): array
      {
        return [
          'Fecha',
          'Nombre',
          'Tipo de Ofrenda',
          'Caja',
          'Centro de costos',
          'Estado',
          'Moneda',
        ];
      }
    }, 'ingresos.xlsx', BaseExcel::XLSX);
  }

  public function exportarEgresosExcel(Request $request)
  {
    $query = \App\Models\Egreso::query();

    if ($request->fechaInicio) {
      $query->whereDate('fecha', '>=', $request->fechaInicio);
    }

    if ($request->fechaFin) {
      $query->whereDate('fecha', '<=', $request->fechaFin);
    }

    if ($request->tipoEgreso) {
      $query->where('tipo_egreso_id', $request->tipoEgreso);
    }

    if ($request->proveedor) {
      $query->where('proveedor_id', $request->proveedor);
    }

    if ($request->cajaFinanzas) {
      $query->where('caja_finanzas_id', $request->cajaFinanzas);
    }

    if ($request->centroDeCostosEgresos) {
      $query->where('centro_de_costos_egresos_id', $request->centroDeCostosEgresos);
    }

    $egresos = $query->with('proveedor', 'tipoEgreso')->get();

    $datos = $egresos->map(function ($egreso) {
      return [
        'Fecha'           => $egreso->fecha,
        'Proveedor'       => $egreso->proveedor->nombre ?? '',
        'Valor'           => $egreso->valor,
        'Tipo de Egreso'  => $egreso->tipoEgreso->nombre ?? '',
        'Caja'            => $egreso->cajaFinanzas->nombre ?? '',
        'Centro de costos' => $egreso->centroDeCostosEgresos->nombre ?? '',
        'Estado'          => $egreso->anulado ? 'Anulado' : 'Aprobado',
      ];
    });

    // Creamos el Excel "a mano" usando `Excel::download`
    return Excel::download(new class($datos) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
      protected $datos;

      public function __construct($datos)
      {
        $this->datos = $datos;
      }

      public function collection()
      {
        return new Collection($this->datos);
      }

      public function headings(): array
      {
        return [
          'Fecha',
          'Proveedor',
          'Valor',
          'Tipo de Egreso',
          'Caja',
          'Centro de costos',
          'Estado',
        ];
      }
    }, 'egresos.xlsx', BaseExcel::XLSX);
  }

  public function anular(Request $request)
  {
    $request->validate([
      'ingreso_id' => 'required|exists:ingresos,id',
      'justificacion' => 'required|string|max:1000',
    ]);

    $ingreso = Ingreso::find($request->ingreso_id);
    $ingreso->anulado = true;
    $ingreso->motivo_anulacion = $request->justificacion;
    $ingreso->save();

    return redirect()->back()->with('success', 'Ingreso anulado correctamente.');
  }

  public function anularEgreso(Request $request)
  {
    $request->validate([
      'justificacion' => 'required|string|max:1000',
    ]);

    $egreso = Egreso::find($request->egreso_id);

    if (!$egreso) {
      return redirect()->back()->with('error', 'Egreso no encontrado.');
    }

    $egreso->anulado = true;
    $egreso->motivo_anulacion = $request->justificacion;
    $egreso->save();

    return redirect()->back()->with('success', 'Egreso anulado correctamente');
  }

  // Vista nuevoEgreso
  public function egreso()
  {
    $proveedores = Proveedor::get();
    $documentos = DocumentoEquivalente::get();
    $cajas = CajaFinanzas::get();
    $centroDeCostosEgresos = CentroDeCostosEgresos::get();
    $tipoEgresos = TipoEgreso::get();
    $sedes = Sede::get();
    $monedas = Moneda::get();

    $tipoIdentificaciones = TipoIdentificacion::get();


    return view('contenido.paginas.finanzas.nuevo-egreso', [
      'proveedores' => $proveedores,
      'documentos' => $documentos,
      'cajas' => $cajas,
      'monedas' => $monedas,
      'centroDeCostosEgresos' => $centroDeCostosEgresos,
      'tipoEgresos' => $tipoEgresos,
      'sedes' => $sedes,
      'tipoIdentificaciones' => $tipoIdentificaciones
    ]);
  }

  public function crearEgreso(Request $request)
  {
    $request->validate([
      'fecha' => 'required',
      'proveedor' => 'required',
      'documento' => 'string|max:100',
      'caja' => 'required|string|max:100',
      'centro_de_costos_egresos' => 'required',
      'tipoEgreso' => 'required|exists:tipo_egresos,id',
      'sede' => 'required|string',
      'valor' => 'required|numeric|min:0',
      'campoAdicional1' => 'nullable|string|max:255',
      'campoAdicional2' => 'nullable|string|max:255',
      'descripcion' => 'nullable|max:255'
    ]);

    $egreso = new Egreso();
    $egreso->fecha = $request->fecha;
    $egreso->valor = $request->valor;
    $egreso->moneda_id = $request->moneda;
    $egreso->proveedor_id = $request->proveedor;
    $egreso->tipo_egreso_id = $request->tipoEgreso;
    $egreso->sede_id = $request->sede;
    $egreso->descripcion = $request->descripcion;
    $egreso->documento_equivalente_id = $request->documento;
    $egreso->caja_finanzas_id = $request->caja;
    $egreso->centro_de_costos_egresos_id = $request->centro_de_costos_egresos;
    $egreso->campo_adicional1 = $request->campoAdicional1;
    $egreso->campo_adicional2 = $request->campoAdicional2;

    $egreso->save();

    return back()->with('success', 'Egreso creado exitosamente.');
  }

  public function gestionarEgresos(Request $request)
  {
    $egresos = Egreso::get();
    $tipoEgresos = TipoEgreso::get();
    $proveedores = Proveedor::get();
    $cajas = CajaFinanzas::get();
    $centroDeCostosEgresos = CentroDeCostosEgresos::get();
    $fechaInicio = $request->fechaInicio ?? '';
    $fechaFin = $request->fechaFin ?? '';

    $tipoEgresoId = $request->tipoEgreso ?? '';
    $proveedorId = $request->proveedor ?? '';
    $cajaFinanzasId = $request->caja;
    $centroDeCostosEgresosId = $request->centro_de_costos_egresos;

    $egresos = Egreso::query()->orderBy('id', 'desc');

    if ($fechaInicio) {
      $egresos = $egresos->where('fecha', '>=', $fechaInicio);
    }

    if ($fechaFin) {
      $egresos = $egresos->where('fecha', '<=', $fechaFin);
    }

    if (!empty($tipoEgresoId)) {
      $egresos = $egresos->where('tipo_egreso_id', $request->tipoEgreso);
    }

    if (!empty($proveedorId)) {
      $egresos = $egresos->where('proveedor_id', $request->proveedor);
    }

    if (!empty($cajaFinanzasId)) {
      $egresos = $egresos->where('caja_finanzas_id', $request->cajaFinanzas);
    }

    if (!empty($centroDeCostosEgresosId)) {
      $egresos = $egresos->where('centro_de_costos_egresos_id', $request->centroDeCostosEgresos);
    }

    $egresos = $egresos->with(['tipoEgreso', 'proveedor'])->paginate(10);


    return view('contenido.paginas.finanzas.gestionar-egresos', [
      'fechaInicio' => $fechaInicio,
      'fechaFin' => $fechaFin,
      'egresos' => $egresos,
      'proveedores' => $proveedores,
      'tipoEgresos' => $tipoEgresos,
      'cajas' => $cajas,
      'centroDeCostosEgresos' => $centroDeCostosEgresos,
      'tipoEgresoId' => $tipoEgresoId,
      'proveedorId' => $proveedorId,
      'cajaFinanzasId' => $cajaFinanzasId,
      'centroDeCostosEgresosId' => $centroDeCostosEgresosId,
    ]);
  }

  public function eliminarEgreso()
  {
    return back()->with('success', 'Egreso eliminado exitosamente.');
  }

  public function crearProveedor(Request $request)
  {
    $request->validate([
      'nombre' => 'required|string',
      'identificacion' => 'required|string',
      'tipoIdentificacion' => 'required',
      'telefono' => 'nullable|string',
      'direccion' => 'nullable|string'
    ]);

    $proveedor = new Proveedor();
    $proveedor->nombre = $request->nombre;
    $proveedor->identificacion = $request->identificacion;
    $proveedor->tipo_identificacion = $request->tipoIdentificacion;
    $proveedor->telefono = $request->telefono;
    $proveedor->direccion = $request->direccion;

    $proveedor->save();

    return back()->with('success', 'Proveedor creado exitosamente');
  }

  public function crearDocumentos(Request $request)
  {
    $request->validate([
      'nombre' => 'required',
      'identificacion' => 'required',
      'cantidad' => 'required|number',
      'detalle' => 'nullable|string',
      'telefono' => 'nullable|string',
      'direccion' => 'nullable|string',
      'valor' => 'required|number'
    ]);

    $documentoEquivalente = new DocumentoEquivalente();
    $documentoEquivalente->nombre = $request->nombre;
    $documentoEquivalente->identificacion = $request->identificacion;
    $documentoEquivalente->cantidad = $request->cantidad;
    $documentoEquivalente->detalle = $request->detalle;
    $documentoEquivalente->telefono = $request->telefono;
    $documentoEquivalente->direccion = $request->direccion;
    $documentoEquivalente->valor = $request->valor;

    $documentoEquivalente->save();

    return back()->with('success', 'Documento equivalente creado exitosamente.');
  }

  public function documento(Request $request)
  {
    $request->validate([
      'nombre' => 'required',
      'identificacion' => 'required',
      'cantidad' => 'required|number',
      'detalle' => 'nullable|string',
      'telefono' => 'nullable|string',
      'direccion' => 'nullable|string',
      'valor' => 'required|number'
    ]);

    $documentoEquivalente = new DocumentoEquivalente();
    $documentoEquivalente->nombre = $request->nombre;
    $documentoEquivalente->identificacion = $request->identificacion;
    $documentoEquivalente->cantidad = $request->cantidad;
    $documentoEquivalente->detalle = $request->detalle;
    $documentoEquivalente->telefono = $request->telefono;
    $documentoEquivalente->direccion = $request->direccion;
    $documentoEquivalente->valor = $request->valor;

    $documentoEquivalente->save();

    return back()->with('success', 'Documento equivalente creado exitosamente.');
  }

  public function imprimirEgreso(Egreso $egreso)
  {
    // 1. Obtienes los datos adicionales que necesitas para el PDF
    $configuracion = Configuracion::find(1);
    $iglesia = Iglesia::find(1);

    // 2. Preparas un array con todos los datos para la vista
    $data = [
      'egreso' => $egreso,
      'configuracion' => $configuracion,
      'iglesia' => $iglesia
    ];

    // 3. ¡LA LÍNEA CLAVE! Aquí le dices a DomPDF que cargue tu vista Blade.
    // El nombre 'finanzas.recibo-egreso' es la instrucción para Laravel.
    $pdf = Pdf::loadView('contenido.paginas.finanzas.recibo-egreso', $data);

    // 4. (Opcional) Configuras el tamaño del papel
    $pdf->setPaper('letter', 'portrait');

    // 5. Envías el PDF generado al navegador para que se muestre.
    return $pdf->stream('recibo-egreso-' . $egreso->id . '.pdf');
  }

  public function imprimirIngreso(Ingreso $ingreso)
  {
    // 1. Obtienes los datos adicionales que necesitas para el PDF
    $configuracion = Configuracion::find(1);
    $iglesia = Iglesia::find(1);

    // 2. Preparas un array con todos los datos para la vista
    $data = [
      'ingreso' => $ingreso,
      'configuracion' => $configuracion,
      'iglesia' => $iglesia
    ];

    // 3. ¡LA LÍNEA CLAVE! Aquí le dices a DomPDF que cargue tu vista Blade.
    // El nombre 'finanzas.recibo-egreso' es la instrucción para Laravel.
    $pdf = Pdf::loadView('contenido.paginas.finanzas.recibo-ingreso', $data);

    // 4. (Opcional) Configuras el tamaño del papel
    $pdf->setPaper('letter', 'portrait');

    // 5. Envías el PDF generado al navegador para que se muestre.
    return $pdf->stream('recibo-ingreso-' . $ingreso->id . '.pdf');
  }

  public function estadisticas(Request $request)
  {
    $fechaInicio = $request->input('fechaInicio');
    $fechaFinal = $request->input('fechaFinal');

    $todasLasCajas = CajaFinanzas::orderBy('nombre')->get();
    $monedasActivas = Moneda::get(); // O filtra por monedas con transacciones si prefieres

    $datosCompletosPorMoneda = [];

    foreach ($monedasActivas as $moneda) {
      // --- Base de Consultas para la moneda actual ---
      $ingresosQuery = Ingreso::where('anulado', false)
        ->where('moneda_id', $moneda->id);
      $egresosQuery = Egreso::where('anulado', false)
        ->where('moneda_id', $moneda->id);

      // Aplicar filtro de fecha
      if ($fechaInicio && $fechaFinal) {
        $ingresosQuery->whereBetween('fecha', [$fechaInicio, $fechaFinal]);
        $egresosQuery->whereBetween('fecha', [$fechaInicio, $fechaFinal]);
      } elseif ($fechaInicio) {
        $ingresosQuery->where('fecha', '>=', $fechaInicio);
        $egresosQuery->where('fecha', '>=', $fechaInicio);
      } elseif ($fechaFinal) {
        $ingresosQuery->where('fecha', '<=', $fechaFinal);
        $egresosQuery->where('fecha', '<=', $fechaFinal);
      }

      $ingresosPorCajaEstaMoneda = $ingresosQuery
        ->groupBy('caja_finanzas_id')
        ->selectRaw('caja_finanzas_id, SUM(valor) as total_valor')
        ->pluck('total_valor', 'caja_finanzas_id');

      $egresosPorCajaEstaMoneda = $egresosQuery // Ahora $egresosQuery no tiene la condición de moneda_id
        ->groupBy('caja_finanzas_id')
        ->selectRaw('caja_finanzas_id, SUM(valor) as total_valor')
        ->pluck('total_valor', 'caja_finanzas_id');

      // --- Preparar Datos para gráficos de esta moneda ---
      $labelsCajasUnicos = []; // Nombres de todas las cajas
      $datosIngresosParaGraficoEstaMoneda = [];
      $datosEgresosParaGraficoEstaMoneda = [];

      foreach ($todasLasCajas as $caja) {
        // Usamos $caja->nombre para los labels solo una vez si $labelsCajasUnicos está vacío
        // O si queremos asegurar que todas las cajas aparezcan para cada moneda.
        // Si $labelsCajasUnicos ya se llenó con todos los nombres de caja, no necesitamos recalcularlo.
        // Pero para consistencia en los gráficos de Ingresos/Egresos por caja, es bueno tenerlos.
        $labelsCajasUnicos[] = $caja->nombre;
        $datosIngresosParaGraficoEstaMoneda[] = $ingresosPorCajaEstaMoneda->get($caja->id, 0);
        $datosEgresosParaGraficoEstaMoneda[] = $egresosPorCajaEstaMoneda->get($caja->id, 0);
      }
      // Si prefieres que $labelsCajasUnicos se defina solo una vez fuera del loop de monedas:
      // if (empty($labelsParaTodasLasCajas)) { // $labelsParaTodasLasCajas definido antes del loop de monedas
      //     foreach($todasLasCajas as $caja) { $labelsParaTodasLasCajas[] = $caja->nombre; }
      // }
      // Y luego usar $labelsParaTodasLasCajas aquí. Por simplicidad, lo mantengo dentro.

      $totalIngresosGeneralEstaMoneda = $ingresosPorCajaEstaMoneda->sum();
      $totalEgresosGeneralEstaMoneda = $egresosPorCajaEstaMoneda->sum();
      $balanceGeneralEstaMoneda = $totalIngresosGeneralEstaMoneda - $totalEgresosGeneralEstaMoneda;

      // Colores (puedes tener arrays más grandes o generar colores dinámicamente)
      $coloresBase = ['#00cfe8', '#7367f0', '#28c76f', '#ff9f43', '#ea5455', '#1e9ff2', '#ffc107', '#fd7e14', '#20c997', '#6f42c1'];

      $datosCompletosPorMoneda[$moneda->codigo ?? $moneda->id] = [ // Usa un identificador único de moneda como 'COP', 'USD'
        'infoMoneda' => $moneda, // Pasar el objeto moneda completo
        'general' => [
          'labels' => ['Total Ingresos', 'Total Egresos', 'Balance'],
          'data'   => [$totalIngresosGeneralEstaMoneda, $totalEgresosGeneralEstaMoneda, $balanceGeneralEstaMoneda],
          'colors' => array_slice($coloresBase, 0, 3)
        ],
        'ingresos' => [
          'labels' => $labelsCajasUnicos, // Usar los nombres de todas las cajas
          'data'   => $datosIngresosParaGraficoEstaMoneda,
          'colors' => $coloresBase
        ],
        'egresos' => [
          'labels' => $labelsCajasUnicos, // Usar los nombres de todas las cajas
          'data'   => $datosEgresosParaGraficoEstaMoneda,
          'colors' => array_reverse($coloresBase) // Para variar un poco
        ]
      ];
    }

    return view('contenido.paginas.finanzas.estadisticas', [
      'datosGraficosPorMoneda' => $datosCompletosPorMoneda, // Nueva variable principal
      'fechaInicio' => $fechaInicio,
      'fechaFinal' => $fechaFinal,
      // 'cajas' => $todasLasCajas, // Ya no es necesario pasarla por separado si está en $labelsCajasUnicos
    ]);
  }
}
