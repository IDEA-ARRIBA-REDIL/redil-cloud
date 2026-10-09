<?php

namespace App\Livewire\Configuracion;

use App\Models\BloqueInforme;
use App\Models\EstadoCivil;
use App\Models\EstadoPago;
use App\Models\EstadoPasoCrecimientoUsuario;
use App\Models\Informe;
use App\Models\ItemSubseccionInforme;
use App\Models\PasoCrecimiento;
use App\Models\SeccionInforme;
use App\Models\Sede;
use App\Models\SubseccionInforme;
use App\Models\TipoBajaAlta;
use App\Models\TipoInforme;
use App\Models\TipoVinculacion;
use Livewire\Attributes\On;
use Livewire\Component;

class GestionarPlantillasInformes extends Component
{
    // =========================================================================
    // ESTADO GENERAL
    // =========================================================================
    public ?int $informeId = null;

    public string $tabActiva = 'columnas'; // 'columnas' | 'bloques'

    // =========================================================================
    // FORMULARIO: INFORME / PLANTILLA
    // =========================================================================
    public ?int $editInformeId = null;
    public string $informeNombre = '';
    public string $informeDescripcion = '';
    public string $informeLink = 'informes-personalizados.mega-informe.show';
    public string $informeNombreBoton = 'Configurar y Solicitar';
    public ?int $informeTipoInformeId = 1;
    public bool $formInformeActivo = true;
    public bool $informeAddIdUrl = true;
    public bool $informeSeleccioneDiaCorte = false;
    public bool $informeClasificaciones = false;

    // =========================================================================
    // FORMULARIO: SECCIÓN
    // =========================================================================
    public ?int $seccionId = null;
    public string $seccionNombre = '';
    public int $seccionOrden = 0;

    // =========================================================================
    // FORMULARIO: SUBSECCIÓN
    // =========================================================================
    public ?int $subseccionId = null;
    public ?int $subseccionSeccionId = null;
    public string $subseccionNombre = '';
    public int $subseccionOrden = 0;

    // =========================================================================
    // FORMULARIO: BLOQUE (SEDES)
    // =========================================================================
    public ?int $bloqueId = null;
    public string $bloqueNombre = '';
    public array $bloqueSedesIds = [];

    // =========================================================================
    // FORMULARIO: ITEM (MÉTRICAS / FÓRMULAS)
    // =========================================================================
    public ?int $itemId = null;
    public ?int $itemSubseccionId = null;
    public string $itemNombre = '';
    public int $itemOrden = 0;
    public string $itemTipo = 'metrica'; // 'metrica' | 'operacion' | 'totalizador'

    // Temporalidad
    public string $itemDesgloseTemporal = 'ninguno'; // 'ninguno' | 'mes' | 'semanas'
    public bool $itemVisualizarPorMes = false;
    public bool $itemVisualizarPorSemanas = false;
    public bool $itemFechaCreacion = false;

    // Paso 1
    public ?int $itemPasoCrecimientoId = null;
    public ?int $itemEstadoPasoCrecimiento = null;
    public bool $itemFiltrarFechaPasoCrecimiento = false;

    // Comparación Paso 2 / Dilación
    public ?string $itemParametroComparacion = null; // 'paso-crecimiento' | 'fecha-creacion' | null
    public ?int $itemPasoCrecimientoId2 = null;
    public bool $itemNoExistePasoCrecimientoId2 = false;
    public ?int $itemEstadoPasoCrecimiento2 = null;
    public bool $itemFiltrarFechaPasoCrecimiento2 = false;
    public ?int $itemCantidadDiasDilacion = null;

    // Personas & Demografía
    public int $itemGrupoPersonas = 1; // 1: Todos, 2: Alta/Activos, 3: Baja/Eliminados
    public array $itemTiposVinculacionIds = [];
    public array $itemEstadosCivilesIds = [];

    // Bajas / Altas
    public ?int $itemTipoBajaAltaId = null;
    public ?int $itemEstadoReporteDadoBaja = null; // null, 0 (alta), 1 (baja)
    public bool $itemFiltrarFechaReporteBajaAlta = false;

    // Matrículas
    public ?string $itemEstadoMatricula = null;
    public bool $itemFiltroFechaMatricula = false;

    // Operaciones & Totalizadores
    public ?int $itemOperacion = null; // 1: +, 2: -, 3: *, 4: /, 5: Promedio
    public ?int $itemItemA = null;
    public ?int $itemItemB = null;
    public array $itemTotalizarItemsIds = [];

    public function mount(): void
    {
        $primerInforme = Informe::where('usa_plantilla', true)->first();
        if ($primerInforme) {
            $this->informeId = $primerInforme->id;
        }
    }

    public function cambiarInforme(int $id): void
    {
        $this->informeId = $id;
    }

    public function cambiarTab(string $tab): void
    {
        $this->tabActiva = $tab;
    }

    // =========================================================================
    // CRUD: INFORME / PLANTILLA
    // =========================================================================
    public function crearInforme(): void
    {
        $this->reset([
            'editInformeId', 'informeNombre', 'informeDescripcion',
            'informeLink', 'informeNombreBoton', 'informeTipoInformeId',
            'formInformeActivo', 'informeAddIdUrl',
            'informeSeleccioneDiaCorte', 'informeClasificaciones'
        ]);
        $this->informeLink = 'informes-personalizados.mega-informe.show';
        $this->informeNombreBoton = 'Configurar y Solicitar';
        $this->informeTipoInformeId = 1;
        $this->formInformeActivo = true;
        $this->informeAddIdUrl = true;

        $this->dispatch('abrir-offcanvas-informe');
    }

    public function editarInforme(int $id): void
    {
        $inf = Informe::findOrFail($id);
        $this->editInformeId = $inf->id;
        $this->informeNombre = $inf->nombre;
        $this->informeDescripcion = $inf->descripcion ?? '';
        $this->informeLink = $inf->link ?? 'informes-personalizados/mega-informe';
        $this->informeNombreBoton = $inf->nombre_boton ?? 'Configurar y Solicitar';
        $this->informeTipoInformeId = $inf->tipo_informe_id ?? 1;
        $this->formInformeActivo = (bool) $inf->activo;
        $this->informeAddIdUrl = (bool) $inf->add_id_a_la_url;
        $this->informeSeleccioneDiaCorte = (bool) $inf->seleccione_dia_corte;
        $this->informeClasificaciones = (bool) $inf->clasificaciones;

        $this->dispatch('abrir-offcanvas-informe');
    }

    public function guardarInforme(): void
    {
        $this->validate([
            'informeNombre' => 'required|string|max:255',
            'informeTipoInformeId' => 'required|exists:tipo_informes,id',
        ], [
            'informeNombre.required' => 'El nombre de la plantilla es obligatorio.',
            'informeTipoInformeId.required' => 'Selecciona la categoría del informe.',
        ]);

        $link = ! empty($this->informeLink) ? $this->informeLink : 'informes-personalizados.mega-informe.show';
        $nombreBoton = ! empty($this->informeNombreBoton) ? $this->informeNombreBoton : 'Configurar y Solicitar';

        $data = [
            'nombre' => $this->informeNombre,
            'descripcion' => $this->informeDescripcion,
            'link' => $link,
            'nombre_boton' => $nombreBoton,
            'tipo_informe_id' => $this->informeTipoInformeId,
            'usa_plantilla' => true,
            'activo' => true,
            'add_id_a_la_url' => true,
            'seleccione_dia_corte' => false,
            'clasificaciones' => false,
        ];

        if ($this->editInformeId) {
            $inf = Informe::findOrFail($this->editInformeId);
            $inf->update($data);
            $mensaje = 'Plantilla de informe actualizada con éxito.';
        } else {
            $inf = Informe::create($data);
            $this->informeId = $inf->id;
            $mensaje = 'Plantilla de informe creada con éxito.';
        }

        $this->dispatch('cerrar-offcanvas-informe');
        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Operación Exitosa!',
            'texto' => $mensaje,
        ]);
    }

    public function confirmarEliminarInforme(int $id): void
    {
        $inf = Informe::findOrFail($id);
        $this->dispatch('mostrar-confirmacion-eliminar', [
            'tipo' => 'informe',
            'id' => $id,
            'titulo' => '¿Eliminar Plantilla de Informe?',
            'texto' => "Se eliminará '{$inf->nombre}' y toda su estructura jerárquica asociada (secciones, subsecciones, métricas y bloques).",
        ]);
    }

    public function eliminarInforme(int $id): void
    {
        $inf = Informe::findOrFail($id);
        $inf->delete();

        $primer = Informe::where('usa_plantilla', true)->first();
        $this->informeId = $primer?->id;

        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Eliminado!',
            'texto' => 'La plantilla de informe ha sido eliminada correctamente.',
        ]);
    }

    public function duplicarInforme(int $id): void
    {
        $original = Informe::with(['secciones.subsecciones.items', 'bloques'])->findOrFail($id);

        $nuevoInforme = Informe::create([
            'nombre' => $original->nombre . ' (Copia)',
            'descripcion' => $original->descripcion,
            'link' => $original->link,
            'nombre_boton' => $original->nombre_boton,
            'tipo_informe_id' => $original->tipo_informe_id ?? 1,
            'usa_plantilla' => true,
            'activo' => true,
            'add_id_a_la_url' => $original->add_id_a_la_url,
            'seleccione_dia_corte' => $original->seleccione_dia_corte,
            'clasificaciones' => $original->clasificaciones,
        ]);

        foreach ($original->secciones as $sec) {
            $nuevaSec = SeccionInforme::create([
                'informe_id' => $nuevoInforme->id,
                'nombre' => $sec->nombre,
                'orden' => $sec->orden,
            ]);

            foreach ($sec->subsecciones as $subsec) {
                $nuevaSubsec = SubseccionInforme::create([
                    'seccion_informe_id' => $nuevaSec->id,
                    'nombre' => $subsec->nombre,
                    'orden' => $subsec->orden,
                ]);

                foreach ($subsec->items as $item) {
                    $itemData = $item->toArray();
                    unset($itemData['id'], $itemData['created_at'], $itemData['updated_at']);
                    $itemData['subseccion_informe_id'] = $nuevaSubsec->id;
                    ItemSubseccionInforme::create($itemData);
                }
            }
        }

        foreach ($original->bloques as $bloque) {
            BloqueInforme::create([
                'informe_id' => $nuevoInforme->id,
                'nombre' => $bloque->nombre,
                'ids_sedes' => $bloque->ids_sedes ?? $bloque->sedes,
            ]);
        }

        $this->informeId = $nuevoInforme->id;

        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Plantilla Duplicada!',
            'texto' => "Se ha duplicado '{$original->nombre}' con toda su estructura.",
        ]);
    }

    // =========================================================================
    // CRUD: SECCIONES (NIVEL 1)
    // =========================================================================
    public function crearSeccion(): void
    {
        if (! $this->informeId) {
            return;
        }

        $this->reset(['seccionId', 'seccionNombre', 'seccionOrden']);
        $maxOrden = SeccionInforme::where('informe_id', $this->informeId)->max('orden') ?? 0;
        $this->seccionOrden = $maxOrden + 1;

        $this->dispatch('abrir-offcanvas-seccion');
    }

    public function editarSeccion(int $id): void
    {
        $sec = SeccionInforme::findOrFail($id);
        $this->seccionId = $sec->id;
        $this->seccionNombre = $sec->nombre;
        $this->seccionOrden = $sec->orden;

        $this->dispatch('abrir-offcanvas-seccion');
    }

    public function guardarSeccion(): void
    {
        $this->validate([
            'seccionNombre' => 'required|string|max:255',
            'seccionOrden' => 'required|integer',
        ], [
            'seccionNombre.required' => 'El nombre de la sección es obligatorio.',
        ]);

        if ($this->seccionId) {
            $sec = SeccionInforme::findOrFail($this->seccionId);
            $sec->update([
                'nombre' => $this->seccionNombre,
                'orden' => $this->seccionOrden,
            ]);
            $mensaje = 'Sección actualizada correctamente.';
        } else {
            SeccionInforme::create([
                'informe_id' => $this->informeId,
                'nombre' => $this->seccionNombre,
                'orden' => $this->seccionOrden,
            ]);
            $mensaje = 'Sección creada correctamente.';
        }

        $this->dispatch('cerrar-offcanvas-seccion');
        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Operación Exitosa!',
            'texto' => $mensaje,
        ]);
    }

    public function confirmarEliminarSeccion(int $id): void
    {
        $sec = SeccionInforme::findOrFail($id);
        $this->dispatch('mostrar-confirmacion-eliminar', [
            'tipo' => 'seccion',
            'id' => $id,
            'titulo' => '¿Eliminar Sección?',
            'texto' => "Se eliminará '{$sec->nombre}' y todas las subsecciones y métricas que contiene.",
        ]);
    }

    public function eliminarSeccion(int $id): void
    {
        $sec = SeccionInforme::findOrFail($id);
        $sec->delete();

        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Eliminado!',
            'texto' => 'La sección ha sido eliminada con éxito.',
        ]);
    }

    // =========================================================================
    // CRUD: SUBSECCIONES (NIVEL 2)
    // =========================================================================
    public function crearSubseccion(int $seccionId): void
    {
        $this->reset(['subseccionId', 'subseccionNombre', 'subseccionOrden']);
        $this->subseccionSeccionId = $seccionId;
        $maxOrden = SubseccionInforme::where('seccion_informe_id', $seccionId)->max('orden') ?? 0;
        $this->subseccionOrden = $maxOrden + 1;

        $this->dispatch('abrir-offcanvas-subseccion');
    }

    public function editarSubseccion(int $id): void
    {
        $subsec = SubseccionInforme::findOrFail($id);
        $this->subseccionId = $subsec->id;
        $this->subseccionSeccionId = $subsec->seccion_informe_id;
        $this->subseccionNombre = $subsec->nombre;
        $this->subseccionOrden = $subsec->orden;

        $this->dispatch('abrir-offcanvas-subseccion');
    }

    public function guardarSubseccion(): void
    {
        $this->validate([
            'subseccionSeccionId' => 'required|exists:secciones_informes,id',
            'subseccionNombre' => 'required|string|max:255',
            'subseccionOrden' => 'required|integer',
        ], [
            'subseccionNombre.required' => 'El nombre de la subsección es obligatorio.',
        ]);

        if ($this->subseccionId) {
            $subsec = SubseccionInforme::findOrFail($this->subseccionId);
            $subsec->update([
                'seccion_informe_id' => $this->subseccionSeccionId,
                'nombre' => $this->subseccionNombre,
                'orden' => $this->subseccionOrden,
            ]);
            $mensaje = 'Subsección actualizada correctamente.';
        } else {
            SubseccionInforme::create([
                'seccion_informe_id' => $this->subseccionSeccionId,
                'nombre' => $this->subseccionNombre,
                'orden' => $this->subseccionOrden,
            ]);
            $mensaje = 'Subsección creada correctamente.';
        }

        $this->dispatch('cerrar-offcanvas-subseccion');
        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Operación Exitosa!',
            'texto' => $mensaje,
        ]);
    }

    public function confirmarEliminarSubseccion(int $id): void
    {
        $subsec = SubseccionInforme::findOrFail($id);
        $this->dispatch('mostrar-confirmacion-eliminar', [
            'tipo' => 'subseccion',
            'id' => $id,
            'titulo' => '¿Eliminar Subsección?',
            'texto' => "Se eliminará '{$subsec->nombre}' y todas sus métricas.",
        ]);
    }

    public function eliminarSubseccion(int $id): void
    {
        $subsec = SubseccionInforme::findOrFail($id);
        $subsec->delete();

        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Eliminado!',
            'texto' => 'La subsección ha sido eliminada con éxito.',
        ]);
    }

    // =========================================================================
    // CRUD: BLOQUES DE SEDES
    // =========================================================================
    public function crearBloque(): void
    {
        if (! $this->informeId) {
            return;
        }

        $this->reset(['bloqueId', 'bloqueNombre', 'bloqueSedesIds']);
        $this->dispatch('abrir-offcanvas-bloque');
    }

    public function editarBloque(int $id): void
    {
        $bloque = BloqueInforme::findOrFail($id);
        $this->bloqueId = $bloque->id;
        $this->bloqueNombre = $bloque->nombre;
        $this->bloqueSedesIds = $bloque->sedes_ids_array;

        $this->dispatch('abrir-offcanvas-bloque');
    }

    public function guardarBloque(): void
    {
        $this->validate([
            'bloqueNombre' => 'required|string|max:255',
            'bloqueSedesIds' => 'required|array|min:1',
        ], [
            'bloqueNombre.required' => 'El nombre del bloque de sedes es obligatorio.',
            'bloqueSedesIds.required' => 'Debes seleccionar al menos una sede para el bloque.',
        ]);

        $idsSedesCsv = implode(',', array_map('strval', $this->bloqueSedesIds));

        if ($this->bloqueId) {
            $bloque = BloqueInforme::findOrFail($this->bloqueId);
            $bloque->update([
                'nombre' => $this->bloqueNombre,
                'ids_sedes' => $idsSedesCsv,
            ]);
            $mensaje = 'Bloque de sedes actualizado correctamente.';
        } else {
            BloqueInforme::create([
                'informe_id' => $this->informeId,
                'nombre' => $this->bloqueNombre,
                'ids_sedes' => $idsSedesCsv,
            ]);
            $mensaje = 'Bloque de sedes creado correctamente.';
        }

        $this->dispatch('cerrar-offcanvas-bloque');
        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Operación Exitosa!',
            'texto' => $mensaje,
        ]);
    }

    public function confirmarEliminarBloque(int $id): void
    {
        $bloque = BloqueInforme::findOrFail($id);
        $this->dispatch('mostrar-confirmacion-eliminar', [
            'tipo' => 'bloque',
            'id' => $id,
            'titulo' => '¿Eliminar Bloque de Sedes?',
            'texto' => "Se eliminará el bloque '{$bloque->nombre}'.",
        ]);
    }

    public function eliminarBloque(int $id): void
    {
        $bloque = BloqueInforme::findOrFail($id);
        $bloque->delete();

        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Eliminado!',
            'texto' => 'El bloque de sedes ha sido eliminado.',
        ]);
    }

    // =========================================================================
    // CRUD: ITEMS / MÉTRICAS (NIVEL 3)
    // =========================================================================
    public function crearItem(int $subseccionId): void
    {
        $this->reset([
            'itemId', 'itemNombre', 'itemOrden', 'itemTipo',
            'itemDesgloseTemporal', 'itemVisualizarPorMes', 'itemVisualizarPorSemanas', 'itemFechaCreacion',
            'itemPasoCrecimientoId', 'itemEstadoPasoCrecimiento', 'itemFiltrarFechaPasoCrecimiento',
            'itemParametroComparacion', 'itemPasoCrecimientoId2', 'itemNoExistePasoCrecimientoId2',
            'itemEstadoPasoCrecimiento2', 'itemFiltrarFechaPasoCrecimiento2', 'itemCantidadDiasDilacion',
            'itemGrupoPersonas', 'itemTiposVinculacionIds', 'itemEstadosCivilesIds',
            'itemTipoBajaAltaId', 'itemEstadoReporteDadoBaja', 'itemFiltrarFechaReporteBajaAlta',
            'itemEstadoMatricula', 'itemFiltroFechaMatricula',
            'itemOperacion', 'itemItemA', 'itemItemB', 'itemTotalizarItemsIds'
        ]);

        $this->itemSubseccionId = $subseccionId;
        $maxOrden = ItemSubseccionInforme::where('subseccion_informe_id', $subseccionId)->max('orden') ?? 0;
        $this->itemOrden = $maxOrden + 1;
        $this->itemGrupoPersonas = 1;
        $this->itemDesgloseTemporal = 'ninguno';

        $this->dispatch('abrir-offcanvas-item');
    }

    public function editarItem(int $id): void
    {
        $item = ItemSubseccionInforme::findOrFail($id);
        $this->itemId = $item->id;
        $this->itemSubseccionId = $item->subseccion_informe_id;
        $this->itemNombre = $item->nombre;
        $this->itemOrden = $item->orden;

        // Determinar tipo
        if (! empty($item->totalizar_items)) {
            $this->itemTipo = 'totalizador';
        } elseif (! empty($item->operacion) || $item->con_operacion) {
            $this->itemTipo = 'operacion';
        } else {
            $this->itemTipo = 'metrica';
        }

        if ($item->visualizar_por_mes) {
            $this->itemDesgloseTemporal = 'mes';
        } elseif ($item->visualizar_por_semanas) {
            $this->itemDesgloseTemporal = 'semanas';
        } else {
            $this->itemDesgloseTemporal = 'ninguno';
        }

        $this->itemVisualizarPorMes = (bool) $item->visualizar_por_mes;
        $this->itemVisualizarPorSemanas = (bool) $item->visualizar_por_semanas;
        $this->itemFechaCreacion = (bool) $item->fecha_creacion;

        $this->itemPasoCrecimientoId = $item->paso_crecimiento_id;
        $this->itemEstadoPasoCrecimiento = $item->estado_paso_crecimiento;
        $this->itemFiltrarFechaPasoCrecimiento = (bool) $item->filtrar_fecha_paso_crecimiento;

        $this->itemParametroComparacion = $item->parametro_de_comparacion;
        $this->itemPasoCrecimientoId2 = $item->paso_crecimiento_id_2;
        $this->itemNoExistePasoCrecimientoId2 = (bool) $item->no_existe_paso_crecimiento_id_2;
        $this->itemEstadoPasoCrecimiento2 = $item->estado_paso_crecimiento_2;
        $this->itemFiltrarFechaPasoCrecimiento2 = (bool) $item->filtrar_fecha_paso_crecimiento_2;
        $this->itemCantidadDiasDilacion = $item->cantidad_dias_dilacion;

        $this->itemGrupoPersonas = (int) ($item->grupo_de_personas ?: 1);
        $this->itemTiposVinculacionIds = ! empty($item->filtrar_tipo_vinculacion)
            ? array_map('intval', explode(',', $item->filtrar_tipo_vinculacion))
            : [];
        $this->itemEstadosCivilesIds = ! empty($item->filtrar_estado_civil)
            ? array_map('intval', explode(',', $item->filtrar_estado_civil))
            : [];

        $this->itemTipoBajaAltaId = $item->tipo_baja_alta_id;
        $this->itemEstadoReporteDadoBaja = $item->estado_reporte_dado_baja !== null ? (int) $item->estado_reporte_dado_baja : null;
        $this->itemFiltrarFechaReporteBajaAlta = (bool) $item->filtrar_fecha_reporte_baja_alta;

        $this->itemEstadoMatricula = $item->estado_matricula;
        $this->itemFiltroFechaMatricula = (bool) $item->filtro_fecha_matricula;

        $this->itemOperacion = $item->operacion;
        $this->itemItemA = $item->item_a;
        $this->itemItemB = $item->item_b;
        $this->itemTotalizarItemsIds = ! empty($item->totalizar_items)
            ? array_map('intval', explode(',', $item->totalizar_items))
            : [];

        $this->dispatch('abrir-offcanvas-item');
    }

    public function guardarItem(): void
    {
        $this->validate([
            'itemSubseccionId' => 'required|exists:subsecciones_informes,id',
            'itemNombre' => 'required|string|max:255',
            'itemOrden' => 'required|integer',
        ], [
            'itemNombre.required' => 'El nombre de la métrica / ítem es obligatorio.',
        ]);

        $conOperacion = ($this->itemTipo === 'operacion');
        $operacionVal = ($this->itemTipo === 'operacion') ? $this->itemOperacion : null;
        $itemAVal = ($this->itemTipo === 'operacion') ? $this->itemItemA : null;
        $itemBVal = ($this->itemTipo === 'operacion') ? $this->itemItemB : null;
        $totalizarCsv = ($this->itemTipo === 'totalizador' && ! empty($this->itemTotalizarItemsIds))
            ? implode(',', $this->itemTotalizarItemsIds)
            : null;

        $tiposVinculacionCsv = ! empty($this->itemTiposVinculacionIds) ? implode(',', $this->itemTiposVinculacionIds) : null;
        $estadosCivilesCsv = ! empty($this->itemEstadosCivilesIds) ? implode(',', $this->itemEstadosCivilesIds) : null;

        $visualizarPorMes = ($this->itemDesgloseTemporal === 'mes');
        $visualizarPorSemanas = ($this->itemDesgloseTemporal === 'semanas');

        $data = [
            'subseccion_informe_id' => $this->itemSubseccionId,
            'nombre' => $this->itemNombre,
            'orden' => $this->itemOrden,
            'visualizar_por_mes' => $visualizarPorMes,
            'visualizar_por_semanas' => $visualizarPorSemanas,
            'fecha_creacion' => $this->itemFechaCreacion,
            'paso_crecimiento_id' => $this->itemPasoCrecimientoId,
            'estado_paso_crecimiento' => $this->itemEstadoPasoCrecimiento,
            'filtrar_fecha_paso_crecimiento' => $this->itemFiltrarFechaPasoCrecimiento,
            'parametro_de_comparacion' => $this->itemParametroComparacion,
            'paso_crecimiento_id_2' => $this->itemPasoCrecimientoId2,
            'no_existe_paso_crecimiento_id_2' => $this->itemNoExistePasoCrecimientoId2,
            'estado_paso_crecimiento_2' => $this->itemEstadoPasoCrecimiento2,
            'filtrar_fecha_paso_crecimiento_2' => $this->itemFiltrarFechaPasoCrecimiento2,
            'cantidad_dias_dilacion' => $this->itemCantidadDiasDilacion,
            'grupo_de_personas' => $this->itemGrupoPersonas,
            'filtrar_tipo_vinculacion' => $tiposVinculacionCsv,
            'filtrar_estado_civil' => $estadosCivilesCsv,
            'tipo_baja_alta_id' => $this->itemTipoBajaAltaId,
            'estado_reporte_dado_baja' => $this->itemEstadoReporteDadoBaja !== null ? (bool) $this->itemEstadoReporteDadoBaja : null,
            'filtrar_fecha_reporte_baja_alta' => $this->itemFiltrarFechaReporteBajaAlta,
            'estado_matricula' => $this->itemEstadoMatricula,
            'filtro_fecha_matricula' => $this->itemFiltroFechaMatricula,
            'con_operacion' => $conOperacion,
            'operacion' => $operacionVal,
            'item_a' => $itemAVal,
            'item_b' => $itemBVal,
            'totalizar_items' => $totalizarCsv,
        ];

        if ($this->itemId) {
            $item = ItemSubseccionInforme::findOrFail($this->itemId);
            $item->update($data);
            $mensaje = 'Métrica / Ítem actualizado con éxito.';
        } else {
            ItemSubseccionInforme::create($data);
            $mensaje = 'Métrica / Ítem creado con éxito.';
        }

        $this->dispatch('cerrar-offcanvas-item');
        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Operación Exitosa!',
            'texto' => $mensaje,
        ]);
    }

    public function confirmarEliminarItem(int $id): void
    {
        $item = ItemSubseccionInforme::findOrFail($id);
        $this->dispatch('mostrar-confirmacion-eliminar', [
            'tipo' => 'item',
            'id' => $id,
            'titulo' => '¿Eliminar Métrica / Ítem?',
            'texto' => "Se eliminará la columna de métrica '{$item->nombre}'.",
        ]);
    }

    public function eliminarItem(int $id): void
    {
        $item = ItemSubseccionInforme::findOrFail($id);
        $item->delete();

        $this->dispatch('msn', [
            'icono' => 'success',
            'titulo' => '¡Eliminado!',
            'texto' => 'La métrica / ítem ha sido eliminado.',
        ]);
    }

    // =========================================================================
    // REORDENAMIENTO CON DRAG & DROP (SORTABLEJS)
    // =========================================================================
    #[On('actualizarOrdenSecciones')]
    public function actualizarOrdenSecciones(mixed $orden): void
    {
        $lista = is_string($orden) ? json_decode($orden, true) : $orden;
        if (is_array($lista)) {
            foreach ($lista as $item) {
                if (isset($item['id'], $item['orden'])) {
                    SeccionInforme::where('id', $item['id'])->update(['orden' => (int) $item['orden']]);
                }
            }
        }
    }

    #[On('actualizarOrdenSubsecciones')]
    public function actualizarOrdenSubsecciones(int $seccionId, mixed $orden): void
    {
        $lista = is_string($orden) ? json_decode($orden, true) : $orden;
        if (is_array($lista)) {
            foreach ($lista as $item) {
                if (isset($item['id'], $item['orden'])) {
                    SubseccionInforme::where('id', $item['id'])
                        ->where('seccion_informe_id', $seccionId)
                        ->update(['orden' => (int) $item['orden']]);
                }
            }
        }
    }

    #[On('actualizarOrdenItems')]
    public function actualizarOrdenItems(int $subseccionId, mixed $orden): void
    {
        $lista = is_string($orden) ? json_decode($orden, true) : $orden;
        if (is_array($lista)) {
            foreach ($lista as $item) {
                if (isset($item['id'], $item['orden'])) {
                    ItemSubseccionInforme::where('id', $item['id'])
                        ->where('subseccion_informe_id', $subseccionId)
                        ->update(['orden' => (int) $item['orden']]);
                }
            }
        }
    }

    public function render()
    {
        $informes = Informe::where('usa_plantilla', true)->orderBy('nombre')->get();
        $informeSeleccionado = $this->informeId
            ? Informe::with(['secciones.subsecciones.items', 'bloques'])->find($this->informeId)
            : null;

        // Catálogos para los formularios
        $tiposInforme = TipoInforme::orderBy('nombre')->get();
        $pasosCrecimiento = PasoCrecimiento::orderBy('nombre')->get();
        $estadosPasos = EstadoPasoCrecimientoUsuario::all();
        $tiposBajaAlta = TipoBajaAlta::all();
        $estadosMatricula = EstadoPago::all();
        $tiposVinculacion = TipoVinculacion::all();
        $estadosCiviles = EstadoCivil::all();
        $sedes = Sede::orderBy('nombre')->get();

        // Lista de ítems disponibles del informe actual para operaciones aritméticas
        $itemsDelInforme = [];
        if ($informeSeleccionado) {
            $itemsDelInforme = ItemSubseccionInforme::whereHas('subseccion.seccion', function ($q) {
                $q->where('informe_id', $this->informeId);
            })->when($this->itemId, function ($q) {
                $q->where('id', '!=', $this->itemId);
            })->orderBy('nombre')->get();
        }

        return view('livewire.configuracion.gestionar-plantillas-informes', [
            'informes' => $informes,
            'informeSeleccionado' => $informeSeleccionado,
            'tiposInforme' => $tiposInforme,
            'pasosCrecimiento' => $pasosCrecimiento,
            'estadosPasos' => $estadosPasos,
            'tiposBajaAlta' => $tiposBajaAlta,
            'estadosMatricula' => $estadosMatricula,
            'tiposVinculacion' => $tiposVinculacion,
            'estadosCiviles' => $estadosCiviles,
            'sedes' => $sedes,
            'itemsDelInforme' => $itemsDelInforme,
        ]);
    }
}
