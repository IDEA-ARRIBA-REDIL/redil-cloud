<table>
    <thead>
        <tr>
            <th>ID Compra</th>
            <th>Fecha</th>
            <th>Comprador</th>
            <th>Identificación Comp.</th>
            <th>Email Comp.</th>
            <th>Teléfono Comp.</th>
            <th>Método Pago</th>
            <th>Estado Pago</th>
            <th>Valor</th>
            <th>Moneda</th>
            <th>Actividad</th>
            <th>Ref. Transacción</th>
            <th>Sucursal</th>
            <th>Inscrito(s)</th>
            <th>ID Inscrito(s)</th>
            <th>Edad Inscrito(s)</th>
            <th>Categoría(s)</th>
            <th>Pago Interno ID</th>
        </tr>
    </thead>
    <tbody>
        @php
            $monedaDefault = \App\Models\Moneda::find(1);
        @endphp
        @foreach($compras as $compra)
            @php
                $inscritosNames = $compra->inscripciones->pluck('nombre_inscrito')->join(', ');

                // Categoría de la compra obtenida prioritariamente desde los pagos de la compra
                $categoriaNombre = '';
                $pagoConCat = $compra->pagos->first(fn($p) => !empty($p->actividadCategoria?->nombre));
                if ($pagoConCat) {
                    $categoriaNombre = $pagoConCat->actividadCategoria->nombre;
                }

                if (empty($categoriaNombre) && $compra->categorias && $compra->categorias->isNotEmpty()) {
                    $catCompra = $compra->categorias->first(fn($c) => !empty($c->actividadCategoria?->nombre));
                    if ($catCompra) {
                        $categoriaNombre = $catCompra->actividadCategoria->nombre;
                    }
                }

                if (empty($categoriaNombre) && $compra->inscripciones && $compra->inscripciones->isNotEmpty()) {
                    $insc = $compra->inscripciones->first(fn($i) => !empty($i->actividadCategoria?->nombre) || !empty($i->categoriaActividad?->nombre));
                    if ($insc) {
                        $categoriaNombre = $insc->actividadCategoria?->nombre ?? $insc->categoriaActividad?->nombre ?? '';
                    }
                }
                $categoriasNames = $categoriaNombre;

                $estadoNombre = $compra->estadoPago ? $compra->estadoPago->nombre : 'Sin Estado';

                if($compra->abonos->isNotEmpty() && $compra->estadoPago && $compra->estadoPago->estado_pendiente && $compra->actividad && $compra->actividad->tipo && $compra->actividad->tipo->permite_abonos) {
                    $estadoNombre .= ' (Abonada)';
                }

                // Logic for ID and Age
                $idsInscritos = [];
                $edadesInscritos = [];

                foreach($compra->inscripciones as $inscripcion) {
                    $userTarget = null;
                    if ($inscripcion->user) {
                        $userTarget = $inscripcion->user;
                    } elseif ($compra->user) {
                        $userTarget = $compra->user;
                    }

                    if ($userTarget) {
                        $idsInscritos[] = $userTarget->identificacion;
                        try {
                            $edadesInscritos[] = \Carbon\Carbon::parse($userTarget->fecha_nacimiento)->age;
                        } catch (\Exception $e) {
                            $edadesInscritos[] = 'N/A';
                        }
                    } else {
                        $idsInscritos[] = 'N/A';
                        $edadesInscritos[] = 'N/A';
                    }
                }

                $idsInscritosStr = implode(', ', $idsInscritos);
                $edadesInscritosStr = implode(', ', $edadesInscritos);

                // Método de pago desde relacion de pagos
                $metodosPagoStr = $compra->pagos->map(fn($p) => $p->tipoPago?->nombre)->filter()->unique()->join(', ');
                if (empty($metodosPagoStr)) {
                    $metodosPagoStr = 'N/A';
                }

                // Enumeración simple únicamente de los IDs de los pagos (ej: 2, 3)
                $pagosInternos = $compra->pagos->pluck('id')->join(', ');
                $pagosPasarela = $compra->pagos->pluck('referencia_pago')->filter()->join(', ');

                // Moneda Name
                $nombreMoneda = $compra->moneda ? $compra->moneda->nombre : ($monedaDefault ? $monedaDefault->nombre : '');
            @endphp
            <tr>
                <td>{{ $compra->id }}</td>
                <td>{{ $compra->fecha }}</td>
                <td>{{ $compra->nombre_completo_comprador }}</td>
                <td>{{ $compra->identificacion_comprador }}</td>
                <td>{{ $compra->email_comprador }}</td>
                <td>{{ $compra->telefono_comprador }}</td>
                <td>{{ $metodosPagoStr }}</td>
                <td>{{ $estadoNombre }}</td>
                <td>{{ $compra->valor }}</td>
                <td>{{ $nombreMoneda }}</td>
                <td>{{ $compra->actividad ? $compra->actividad->nombre : '' }}</td>
                <td>{{ $pagosPasarela }}</td>
                <td>{{ $compra->destinatario ? $compra->destinatario->nombre : '' }}</td>
                <td>{{ $inscritosNames }}</td>
                <td>{{ $idsInscritosStr }}</td>
                <td>{{ $edadesInscritosStr }}</td>
                <td>{{ $categoriasNames }}</td>
                <td>{{ $pagosInternos }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
