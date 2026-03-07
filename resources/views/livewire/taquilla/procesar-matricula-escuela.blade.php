<div class="row g-4" {{--
    Inicialización de Alpine.js
    (SIN CAMBIOS)
  --}} x-data="{
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
                <h5 class="mb-0">Detalles de la matrícula</h5>
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
                    Volver a gestionar compras
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
                        <label class="form-label">Comprador (quien paga):</label>
                        {{-- ¡CORREGIDO! Ahora usa $comprador --}}
                        <input type="text" class="form-control" readonly
                            value="{{ $comprador->nombre(3) }} (ID: {{ $comprador->id }})">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Inscrito (quien asiste):</label>
                        {{-- ¡CORREGIDO! Ahora usa $inscrito --}}
                        <input type="text" class="form-control" readonly
                            value="{{ $inscrito->nombre(3) }} (ID: {{ $inscrito->id }})">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Escuela</label>
                        <input type="text" class="form-control" readonly value="{{ $actividad->nombre }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Materia seleccionada</label>
                        <input type="text" class="form-control" readonly value="{{ $categoria->nombre }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Tarjeta 2: Lógica de Escuela (SIN CAMBIOS) --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">2. Selección de horario y material</h5>
                <small class="card-subtitle">Selecciona el horario y la sede para el material.</small>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    {{-- (Selector Sede Matrícula) --}}
                    <div class="col-md-6">
                        <label for="sedeSeleccionada" class="form-label">Sede de la matrícula*</label>
                        <select id="sedeSeleccionada" class="form-select" wire:model.live="sedeSeleccionada">
                            <option value="">Selecciona una sede...</option>
                            @foreach ($sedes as $sede)
                                <option value="{{ $sede['id'] }}">{{ $sede['nombre'] }}</option>
                            @endforeach
                        </select>
                        @error('sedeSeleccionada')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    {{-- (Selector Tipo Aula) --}}
                    <div class="col-md-6">
                        <label class="form-label">Tipo de aula*</label>

                        @if (count($tiposAula) > 0)
                            <div class="d-flex flex-wrap">
                                @foreach ($tiposAula as $tipo)
                                    <div class="form-check me-3">

                                        <input class="form-check-input" type="radio" name="tipoAulaRadio"
                                            id="tipoAula-{{ $tipo['id'] }}" value="{{ $tipo['id'] }}"
                                            wire:model.live="tipoAulaSeleccionado">
                                        <label class="form-check-label" for="tipoAula-{{ $tipo['id'] }}">
                                            {{ $tipo['nombre'] }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="form-control-static text-muted small">Selecciona una sede para ver los tipos de
                                aula.</p>
                        @endif
                    </div>
                    {{--
            ===================================================================
            ¡INICIO DE LA CORRECCIÓN!
            Añadimos un 'wire:key' al div contenedor del selector de horarios.
            ===================================================================
          --}}
                    <div class="col-12" {{--
              Esta 'key' única (basada en el tipo de aula seleccionado)
              fuerza a Livewire a destruir y redibujar este bloque
              cada vez que 'tipoAulaSeleccionado' cambia.
            --}}
                        wire:key="bloque-horarios-{{ $tipoAulaSeleccionado ?? 'ninguno' }}">


                        <label for="horarioSeleccionado" class="form-label">Horario disponible*</label>
                        <select id="horarioSeleccionado" class="form-select" wire:model.live="horarioSeleccionado"
                            @if (count($horarios) == 0) disabled @endif>
                            <option value="">Selecciona un horario...</option>
                            {{-- Este bucle ahora SÍ se refrescará --}}
                            @foreach ($horarios as $horario)
                                <option value="{{ $horario['id'] }}">{{ $horario['label'] }}</option>
                            @endforeach
                        </select>
                        @error('horarioSeleccionado')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    {{-- =================================================================== --}}
                    {{-- ¡FIN DE LA CORRECCIÓN! --}}
                    {{-- =================================================================== --}}
                    {{-- (Selector Sede Material) --}}
                    <div class="col-12">
                        <label for="materialSedeId" class="form-label">Sede para envío de material*</label>
                        <select id="materialSedeId" class="form-select" wire:model="materialSedeId">
                            <option value="">Selecciona una sede...</option>
                            @foreach ($sedesDelPeriodo as $sede)
                                <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                            @endforeach
                        </select>
                        @error('materialSedeId')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Tarjeta 3: Campos Adicionales (SIN CAMBIOS) --}}
        @if ($camposAdicionalesModelo->isNotEmpty())
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">3. Información adicional</h5>
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
    COLUMNA DERECHA: Resumen de Pago (Funcional)
    (SIN CAMBIOS)
    ==================================================================
  --}}
    <div class="col-12 col-lg-5 col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Resumen de pago</h5>
            </div>
            <div class="card-body">

                {{-- Total a Pagar --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Total a pagar:</h6>
                    <h4 class="fw-bold text-primary mb-0">
                        {{ $moneda->nombre_corto }} ${{ number_format($precioTotal, 0, ',', '.') }}
                    </h4>
                </div>

                {{-- Valor Restante --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Restante:</h6>
                    <h4 class="fw-bold text-dark mb-0">
                        {{ $moneda->nombre_corto }} ${{ number_format($valorRestante, 0, ',', '.') }}
                    </h4>
                </div>

                <hr class="my-3">

                {{-- Lógica de Pagos Divididos --}}
                <h6 class="mb-3">Añadir método de pago</h6>
                <div class="row g-2 mb-3">
                    {{-- Selector de Método --}}
                    <div class="col-12">
                        <label for="select-tipo-pago" class="form-label">Método</label>
                        <select id="select-tipo-pago" class="form-select" x-model="tipoSeleccionado">
                            @foreach ($tiposPagoDisponibles as $tipo)
                                <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Input de Valor --}}
                    <div class="col-12">
                        <label for="input-valor-pago" class="form-label">Valor a pagar</label>
                        <input type="number" id="input-valor-pago" class="form-control" placeholder="0"
                            wire:model="nuevoPagoValor" wire:keydown.enter="anadirPago">
                    </div>

                    {{-- Campo de Voucher Condicional --}}
                    <div class="col-12"
                        x-show="tiposPago[tipoSeleccionado] && tiposPago[tipoSeleccionado].codigo_datafono"
                        x-transition>
                        <label for="input-voucher-pago" class="form-label">Código de voucher</label>
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
                                    $porcentaje >= 90 ? 'bg-danger' : ($porcentaje >= 75 ? 'bg-warning' : 'bg-success');
                            @endphp
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar {{ $colorBarra }}" role="progressbar"
                                    style="width: {{ $porcentaje }}%" aria-valuenow="{{ $porcentaje }}"
                                    aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Botón Añadir Pago --}}
                <button type="button" id="btn-anadir-pago" class="btn btn-outline-primary w-100 mb-3"
                    wire:click="anadirPago" wire:loading.attr="disabled">
                    Añadir pago
                </button>

                {{-- Lista de Pagos Añadidos --}}
                <div id="lista-pagos-anadidos" class="mb-3">
                    @foreach ($pagos as $index => $pago)
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <small>{{ $pago['nombre'] }}</small>
                                @if ($pago['codigo_vaucher'])
                                    <small class="d-block text-muted">Voucher: {{ $pago['codigo_vaucher'] }}</small>
                                @endif
                            </div>
                            <small class="fw-bold">{{ $moneda->nombre_corto }}
                                ${{ number_format($pago['valor'], 0, ',', '.') }}</small>
                            <button type="button" class="btn btn-xs btn-danger ti ti-x p-1"
                                wire:click="quitarPago({{ $index }})"></button>
                        </div>
                    @endforeach
                </div>

                {{-- Botón de Confirmación --}}
                <hr class="my-3">
                <button type="button" id="btn-confirmar-transaccion" class="btn btn-success w-100"
                    wire:click="confirmarMatricula" wire:loading.attr="disabled"
                    @if (($precioTotal > 0 && $valorRestante > 0) || ($precioTotal > 0 && count($pagos) == 0)) disabled @endif>
                    <span wire:loading.remove wire:target="confirmarMatricula">
                        Confirmar matrícula
                    </span>
                    <span wire:loading wire:target="confirmarMatricula">
                        Procesando...
                    </span>
                </button>

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
