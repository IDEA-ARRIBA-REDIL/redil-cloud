<table>
    <thead>
        <tr>
            <th style="font-weight: bold; background-color: #f2f2f2;">ID Pago</th>
            <th style="font-weight: bold; background-color: #f2f2f2;">Fecha</th>
            <th style="font-weight: bold; background-color: #f2f2f2;">Actividad</th>
            <th style="font-weight: bold; background-color: #f2f2f2;">Comprador</th>
            <th style="font-weight: bold; background-color: #f2f2f2;">Cédula Comprador</th>
            <th style="font-weight: bold; background-color: #f2f2f2;">Email Comprador</th>
            <th style="font-weight: bold; background-color: #f2f2f2;">Medio de Pago</th>
            <th style="font-weight: bold; background-color: #f2f2f2;">Caja</th>
            <th style="font-weight: bold; background-color: #f2f2f2;">Valor Pago</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pagos as $pago)
            <tr>
                <td>{{ $pago->id }}</td>
                <td>{{ \Carbon\Carbon::parse($pago->fecha)->format('Y-m-d H:i:s') }}</td>
                <td>{{ $pago->compra->actividad->nombre ?? 'N/A' }}</td>
                <td>{{ $pago->compra->nombre_completo_comprador ?? 'N/A' }}</td>
                <td>{{ $pago->compra->identificacion_comprador ?? 'N/A' }}</td>
                <td>{{ $pago->compra->email_comprador ?? 'N/A' }}</td>
                <td>{{ $pago->tipoPago->nombre ?? 'N/A' }}</td>
                <td>{{ $pago->caja->nombre ?? 'N/A' }}</td>
                <td>{{ $pago->valor }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
