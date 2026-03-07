<div>
    {{-- The whole world belongs to you. --}}
</div>

@script
    <script>
        $wire.on('abrirModal', () => {
            $('#' + event.detail.nombreModal).modal('show');
        });

        $wire.on('msnTieneRegistros', data => {
            Swal.fire({
                title: data.msnTitulo,
                html: data.msnTexto,
                icon: data.msnIcono,
                showCancelButton: false,
                confirmButtonText: 'Si, dar de baja',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $wire.$call('darBajaAlta', data.id, 'baja');
                }
            })
        });

        $wire.on('msnDarDeBajaAlta', data => {
            Swal.fire({
                title: data.msnTitulo,
                html: data.msnTexto,
                icon: data.msnIcono,
                showCancelButton: false,
                confirmButtonText: (data.tipo == 'baja') ?
                    'Si, dar de baja' : 'Si, dar de alta',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $wire.$call('darBajaAlta', data.id, data.tipo);
                }
            })
        });

        $wire.on('msnConfirmarEliminacion', data => {
            Swal.fire({
                title: data.msnTitulo,
                html: data.msnTexto,
                icon: data.msnIcono,
                showCancelButton: false,
                confirmButtonText: 'Si, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $wire.$call('eliminacionForzada', data.id);
                }
            })
        });
    </script>
@endscript
