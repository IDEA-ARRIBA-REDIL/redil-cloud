<div class="row g-4" {{-- ... (x-data de Alpine.js SIN CAMBIOS) ... --}} x-data="{
    tiposPago: {{ $tiposPagoDisponibles->keyBy('id')->toJson() }},
    tipoSeleccionado: @entangle('nuevoPagoTipoId').live
}">

    {{--
    ==================================================================
    COLUMNA IZQUIERDA: Detalles y Campos
    ==================================================================
  --}}
    <div class="col-12 col-lg-7 col-xl-8">

        {{-- Tarjeta 1: Detalles del Usuario y Actividad (¡MODIFICADA!) --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detalles del abono</h5>
                {{--
          ¡BOTÓN VOLVER CORREGIDO!
          Usa la ruta 'taquilla.operar' y pasa los IDs correctos.
        --}}
                <a href="{{ route('taquilla.operar', [
                    'cajaActiva' => $cajaActiva,
                    'user_id' => $comprador->id, // ID del Comprador
                    'inscrito_id' => $inscrito->id, // ID del Inscrito
                    'actividad_id' => $actividad->id,
                    'verificar' => 'true',
                ]) }}"
                    class="btn btn-sm btn-outline-secondary rounded-pill">
                    <i class="ti ti-chevron-left me-1"></i>
                    Volver a categorías
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    {{--
            ========================================================
            ¡CAMPOS DE USUARIO CORREGIDOS!

            ========================================================
          --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Comprador (Quien Paga):</label>
                        {{-- ¡CORREGIDO! Ahora usa $comprador --}}
                        <input type="text" class="form-control" readonly
                            value="{{ $comprador->nombre(3) }} (ID: {{ $comprador->id }})">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Inscrito (Quien Asiste):</label>
                        {{-- ¡CORREGIDO! Ahora usa $inscrito --}}
                        <input type="text" class="form-control" readonly
                            value="{{ $inscrito->nombre(3) }} (ID: {{ $inscrito->id }})">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Actividad</label>
                        <input type="text" class="form-control" readonly value="{{ $actividad->nombre }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Categoría seleccionada</label>
                        <input type="text" class="form-control" readonly value="{{ $categoria->nombre }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Tarjeta 2: Campos Adicionales (SIN CAMBIOS) --}}
        @if ($primeraVez && $camposAdicionalesModelo->isNotEmpty())
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">2. Información adicional</h5>
                    <small class="card-subtitle">Completa los campos requeridos por la actividad.</small>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach ($camposAdicionalesModelo as $campo)
                            <div class="col-md-6">
                                <label for="campo-adicional-{{ $campo->id }}" class="form-label">
                                    {{ $campo->nombre }}
                                    @if ($campo->obligatorio)
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>
                                <input type="text" id="campo-adicional-{{ $campo->id }}"
                                    class="form-control @error('camposAdicionales.' . $campo->id) is-invalid @enderror"
                                    placeholder="Respuesta para {{ $campo->nombre }}..."
                                    wire:model.blur="camposAdicionales.{{ $campo->id }}">
                                @error('camposAdicionales.' . $campo->id)
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

    </div>

    {{--
    ==================================================================
    COLUMNA DERECHA: Resumen de Pago
    (Esta sección ya era correcta y cumple tu requisito
     de mostrar el estado de los abonos)

    ==================================================================
  --}}
    <div class="col-12 col-lg-5 col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Resumen de pago</h5>
            </div>
            <div class="card-body">

                @if ($abonosFinalizados)
                    <div class="alert alert-success text-center">
                        <i class="ti ti-circle-check ti-lg mb-2"></i>
                        <h5 class="alert-heading">Pagos completados</h5>
                        <p class="mb-0">El usuario ya ha pagado el valor total de esta inscripción.</p>
                    </div>
                @else
                    {{-- Total de la Categoría --}}
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted">Valor total del tiquete:</small>
                        <small class="text-dark fw-semibold">
                            {{ $moneda->nombre_corto }} ${{ number_format($valorTotalCategoria, 0, ',', '.') }}
                        </small>
                    </div>

                    {{-- Total Ya Pagado --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <small class="text-muted">Total pagado (anterior):</small>
                        <small class="text-success fw-semibold">
                            - {{ $moneda->nombre_corto }} ${{ number_format($totalYaPagado, 0, ',', '.') }}
                        </small>
                    </div>

                    {{-- Total Restante (General) --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Total restante (Max):</h6>
                        <h4 class="fw-bold text-primary mb-0">
                            {{ $moneda->nombre_corto }} ${{ number_format($valorMaximoAbono, 0, ',', '.') }}
                        </h4>
                    </div>

                    {{-- Valor Mínimo Requerido HOY --}}
                    @if ($valorMinimoAbono > 0)
                        <div class="alert alert-info p-2 text-center">
                            <h6 class="alert-heading mb-1">Abono mínimo requerido</h6>
                            <p class="mb-0">El usuario debe pagar al menos <strong>{{ $moneda->nombre_corto }}
                                    ${{ number_format($valorMinimoAbono, 0, ',', '.') }}</strong> hoy.</p>
                            @if ($mensajeAbono)
                                <small class="d-block mt-1">{{ $mensajeAbono }}</small>
                            @endif
                        </div>
                    @endif

                    <hr class="my-3">

                    {{-- Lógica de Pagos Divididos (SIN CAMBIOS) --}}
                    <h6 class="mb-3">Añadir método de pago (para esta transacción)</h6>
                    <div class="row g-2 mb-3">
                        {{-- Método --}}
                        <div class="col-12">
                            <label for="select-tipo-pago" class="form-label">Método</label>
                            <select id="select-tipo-pago" class="form-select" x-model="tipoSeleccionado">
                                @foreach ($tiposPagoDisponibles as $tipo)
                                    <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Valor --}}
                        <div class="col-12">
                            <label for="input-valor-pago" class="form-label">Valor a pagar</label>
                            <input @if($actividad->pagos_abonos_con_valores_cerrados)disabled @endif type="number" id="input-valor-pago" class="form-control" placeholder="0"
                                wire:model="nuevoPagoValor" wire:keydown.enter="anadirPago"
                                @if($actividad->pagos_abonos_con_valores_cerrados) readonly @endif>
                        </div>
                        {{-- Voucher (SIN CAMBIOS) --}}
                        <div class="col-12"
                            x-show="tiposPago[tipoSeleccionado] && tiposPago[tipoSeleccionado].codigo_datafono"
                            x-transition>
                            <label for="input-voucher-pago" class="form-label">Código de voucher*</label>
                            <input type="text" id="input-voucher-pago"
                                class="form-control @error('nuevoPagoVoucher') is-invalid @enderror"
                                placeholder="Ingresa el código..." wire:model="nuevoPagoVoucher"
                                wire:keydown.enter="anadirPago">
                            @error('nuevoPagoVoucher')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Mensaje de Límite de Dinero --}}
                        <div class="col-12"
                            x-show="tiposPago[tipoSeleccionado] && tiposPago[tipoSeleccionado].tiene_limite_dinero_acumulado"
                            x-transition>
                            <small class="text-black">Dinero acumulado / Límite</small>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="fw-semibold text-primary">
                                    ${{ number_format($cajaActiva->dinero_acumulado, 0) }}
                                </small>
                                <small class="text-muted">
                                    /
                                    {{ $cajaActiva->limite_dinero_acumulado ? '$' . number_format($cajaActiva->limite_dinero_acumulado, 0) : 'Sin límite' }}
                                </small>
                            </div>
                            @if ($cajaActiva->limite_dinero_acumulado > 0)
                                @php
                                    $porcentaje = min(
                                        100,
                                        ($cajaActiva->dinero_acumulado / $cajaActiva->limite_dinero_acumulado) * 100,
                                    );
                                    $colorBarra =
                                        $porcentaje >= 90
                                            ? 'bg-danger'
                                            : ($porcentaje >= 75
                                                ? 'bg-warning'
                                                : 'bg-success');
                                @endphp
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar {{ $colorBarra }}" role="progressbar"
                                        style="width: {{ $porcentaje }}%" aria-valuenow="{{ $porcentaje }}"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- (Botón Añadir Pago - SIN CAMBIOS) --}}
                    <button type="button" id="btn-anadir-pago" class="btn btn-outline-primary w-100 mb-3"
                        wire:click="anadirPago" wire:loading.attr="disabled">
                        Añadir pago
                    </button>

                    {{-- (Lista de Pagos Añadidos - SIN CAMBIOS) --}}
                    <div id="lista-pagos-anadidos" class="mb-3">
                        @foreach ($pagos as $index => $pago)
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div>
                                    <small>{{ $pago['nombre'] }}</small>
                                    @if ($pago['codigo_vaucher'])
                                        <small class="d-block text-muted">Voucher:
                                            {{ $pago['codigo_vaucher'] }}</small>
                                    @endif
                                </div>
                                <small class="fw-bold">{{ $moneda->nombre_corto }}
                                    ${{ number_format($pago['valor'], 0, ',', '.') }}</small>
                                <button type="button" class="btn btn-xs btn-danger ti ti-x p-1"
                                    wire:click="quitarPago({{ $index }})"></button>
                            </div>
                        @endforeach
                    </div>

                    {{-- Botón de Confirmación (SIN CAMBIOS) --}}
                    <hr class="my-3">
                    <button type="button" id="btn-confirmar-transaccion" class="btn btn-success w-100"
                        wire:click="confirmarAbono" wire:loading.attr="disabled"
                        @if (collect($pagos)->sum('valor') < $valorMinimoAbono) disabled @endif>
                        <span wire:loading.remove wire:target="confirmarAbono">
                            Confirmar abono
                        </span>
                        <span wire:loading wire:target="confirmarAbono">
                            Procesando...
                        </span>
                    </button>

                @endif {{-- Fin de @if ($abonosFinalizados) --}}
            </div>
        </div>
    </div>

</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('notificacion', (event) => {
                Swal.fire({
                    title: event.titulo,
                    text: event.mensaje,
                    icon: event.tipo,
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText: 'Cerrar'
                })
            });
        });
    </script>
@endpush
