<div class="container my-5" style="padding-bottom: 100px;">
    <div class="col-12 col-sm-10 offset-sm-1 col-lg-8 offset-lg-2">

        @if($guardando)
            <div class="text-center w-100 py-10">
                <div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 300px;">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <h4 class="fw-semibold text-black">Reprogramando cita, por favor espere...</h4>
                    <p class="text-black">Esto puede tomar unos segundos.</p>
                </div>
            </div>
        @else
            <div wire:loading.remove wire:target="reprogramar">
                <div  class="card mb-5 shadow-sm" style="background-color: #f8f7fa">
                    <div class="card-header pb-1">
                        <h6 class="card-title mb-0 fw-semibold">Reprogramar cita</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mt-3">
                            <div class="col-12 col-md-12 mb-3">
                                <small class="">Fecha y hora</small>
                                <p class="fw-semibold text-black mb-0">{{ $cita->fecha_hora_inicio->isoFormat('D [de] MMMM [de] YYYY - HH:mm A') }} ({{ config('app.timezone') }})</p>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <small class="">Paciente</small>
                                <p class="fw-semibold text-black mb-0">{{ $cita->user->nombre(3) }}</p>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <small class="">Tipo</small>
                                <p class="fw-semibold text-black mb-0">{{ $cita->tipoConsejeria->nombre }}</p>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <small class="">Consejero</small>
                                <p class="fw-semibold text-black mb-0">{{ $cita->consejero->usuario->nombre(3) }}</p>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <small class="">Medio</small>
                                <p class="fw-semibold text-black mb-0">{{ $cita->medio == 1 ? 'Presencial' : 'Virtual' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

        
                <div id="divHorario" class="card mb-5 shadow-sm" style="background-color: #f8f7fa">
                    <div class="card-header pb-1">
                        <h6 class="card-title mb-0 fw-semibold">Seleccione el nuevo horario</h6>
                    </div>
                    <div class="card-body">

                        @if(!empty($horariosDisponibles))

                            <div class="d-flex align-items-center gap-2" x-data>

                                <button type="button"
                                    class="btn btn-icon btn-sm btn-outline-secondary rounded-pill"
                                    style="flex-shrink: 0;" {{-- Evita que el botón se encoja --}}
                                    @click="$refs.dayScroller.scrollBy({ left: -250, behavior: 'smooth' })">
                                <span class="ti ti-chevron-left"></span>
                                </button>

                                <div class="day-scroll-container mb-0" x-ref="dayScroller">
                                    <div class="d-inline-flex gap-2 py-1">

                                        @foreach(array_keys($horariosDisponibles) as $fecha)
                                            <button type="button"
                                                    wire:click="seleccionarDia('{{ $fecha }}')"
                                                    class="btn btn-sm text-center {{ $diaSeleccionado == $fecha ? 'btn-primary' : 'btn-outline-primary' }}"
                                                    style="white-space: nowrap;">
                                                    {{ \Carbon\Carbon::parse($fecha)->isoFormat('ddd') }} <br>
                                                    {{ \Carbon\Carbon::parse($fecha)->isoFormat('D MMM') }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                <button type="button"
                                    class="btn btn-icon btn-sm btn-outline-secondary rounded-pill"
                                    style="flex-shrink: 0;" {{-- Evita que el botón se encoja --}}
                                    @click="$refs.dayScroller.scrollBy({ left: 250, behavior: 'smooth' })">
                                    <span class="ti ti-chevron-right"></span>
                                </button>
                            </div>
                            <hr class="my-2">

                                @if($diaSeleccionado && isset($horariosDisponibles[$diaSeleccionado]))
                                            @php
                                            $fecha = $diaSeleccionado;
                                            $horas = $horariosDisponibles[$diaSeleccionado];
                                            @endphp

                                            <small class="fw-bold mb-3 d-block">{{ \Carbon\Carbon::parse($fecha)->isoFormat('dddd, D [de] MMMM') }}</small>

                                            <div class="row mb-2">
                                                @foreach($horas as $hora)
                                                <div class="col-6 col-md-3 mb-2">
                                                    <div class="form-check custom-option custom-option-basic rounded-3 shadow-sm border">
                                                        <label class="form-check-label custom-option-content p-3">
                                                            <span class="custom-option-header m-0 pb-0">
                                                                <span class="h6 mb-0 d-flex align-items-center">
                                                                    <i class="ti ti-calendar-time me-3"></i>
                                                                    {{ \Carbon\Carbon::parse($hora)->format('g:i a') }}
                                                                </span>
                                                                <input wire:model.live="horarioSeleccionado" class="form-check-input" type="radio" value="{{ $fecha }} {{ $hora }}" id="horario-{{ $fecha }}-{{ $hora }}" />
                                                            </span>
                                                        </label>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>

                                        @endif              
                        @else
                            <div class="col-12">
                                <p class="text-center text-muted">No hay horarios disponibles para este consejero.</p>
                            </div>
                        @endif

                        @error('horarioSeleccionado') <div class="text-danger form-label mt-2">{{ $message }}</div> @enderror

                    </div>
                </div>


                
                    <div class="w-100 fixed-bottom py-5 px-6 px-sm-0 border-top" style="background-color: #f8f7fa">
                        <div class="col-12 col-sm-8 offset-sm-2 col-lg-6 offset-lg-3 d-grid gap-2 d-sm-flex justify-content-sm-end">         

                            <button wire:click="reprogramar" 
                                    class="btn btn-primary rounded-pill px-7 py-2" 
                                    @if(!$horarioSeleccionado) disabled @endif>
                                Confirmar 
                            </button>
                        </div>
                    </div>
            </div>

            <!-- SPINNER DE CARGA (Visible al reprogramar) -->
            <div wire:loading wire:target="reprogramar" class="text-center w-100 py-10">
                <div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 300px;">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <h4 class="fw-semibold text-black">Reprogramando cita, por favor espere...</h4>
                    <p class="text-black">Esto puede tomar unos segundos.</p>
                </div>
            </div>
        @endif
        
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const scrollContainer = document.getElementById('dayScrollContainer');
        const leftBtn = document.getElementById('scrollLeftBtn');
        const rightBtn = document.getElementById('scrollRightBtn');

        if(scrollContainer && leftBtn && rightBtn) {
            leftBtn.addEventListener('click', () => {
                scrollContainer.scrollBy({ left: -200, behavior: 'smooth' });
            });

            rightBtn.addEventListener('click', () => {
                scrollContainer.scrollBy({ left: 200, behavior: 'smooth' });
            });
        }
    });
</script>
