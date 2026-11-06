@extends('layouts/layoutMaster')

@section('title', 'Consulta de transacciones')

@section('page-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss'])
    <style>
        .select2-container .select2-selection--multiple {
            min-height: 38px;
        }

        .consulta-transacciones .table-responsive table {
            min-width: 1400px;
        }

        .consulta-transacciones .table-responsive th {
            white-space: nowrap;
        }
    </style>
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-script')
    <script>
        window.initConsultaSapSelect2 = function (elemento, wire, propiedad, marcador) {
            const inicializar = () => {
                if (!elemento.isConnected) {
                    return;
                }

                if (!window.jQuery || !window.jQuery.fn.select2) {
                    window.setTimeout(inicializar, 50);
                    return;
                }

                const selector = window.jQuery(elemento);
                selector.val(wire.get(propiedad) || []);
                selector.select2({
                    width: '100%',
                    placeholder: marcador,
                    allowClear: true,
                    closeOnSelect: false,
                    language: {
                        noResults: () => 'No se encontraron resultados',
                        searching: () => 'Buscando...'
                    }
                });
                selector.off('change.consultaSap').on('change.consultaSap', function () {
                    wire.set(propiedad, selector.val() || [], false);
                });
            };

            inicializar();
        };
    </script>
@endsection

@section('content')
    <div class="d-flex align-items-center gap-3 mb-4">
        <div class="avatar avatar-lg bg-label-primary rounded p-2">
            <i class="ti ti-receipt fs-2"></i>
        </div>
        <div>
            <h4 class="fw-semibold text-primary mb-1">Transacciones de actividades y ofrendas</h4>
            <p class="text-muted mb-0">Consulta los movimientos registrados desde cualquier canal.</p>
        </div>
    </div>
    @livewire('finanzas.consulta-transacciones')
@endsection
