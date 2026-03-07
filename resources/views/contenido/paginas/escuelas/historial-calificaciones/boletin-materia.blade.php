<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boletín de Calificaciones</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; }
        .header, .footer { text-align: center; }
        .header h1 { margin: 0; }
        .header p { margin: 5px 0; }
        .info-alumno { border: 1px solid #ccc; padding: 10px; margin-bottom: 20px; border-radius: 5px; }
        .summary-table, .details-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .summary-table td { padding: 8px; border: 1px solid #ddd; text-align: center; }
        .details-table th, .details-table td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        .details-table th { background-color: #f2f2f2; }
        .aprobado { color: green; font-weight: bold; }
        .no-aprobado { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Boletín de Calificaciones</h1>
        <p>{{ $registro->materia->escuela->nombre }}</p>
    </div>

    <div class="info-alumno">
        <p><strong>Alumno:</strong> {{ $alumno->nombre(3) }}</p>
        <p><strong>Materia:</strong> {{ $registro->materia->nombre }}</p>
        <p><strong>Periodo:</strong> {{ $registro->periodo->nombre }}</p>
         <p><strong>Maestro:</strong> {{ $nombreMaestro }}</p>
    </div>

    <h3>Resumen final</h3>
    <table class="summary-table">
        <tr>
            <td><strong>Resultado final</strong></td>
            <td><strong>Nota definitiva</strong></td>
            <td><strong>Total asistencias</strong></td>
        </tr>
        <tr>
            <td class="{{ $registro->aprobado ? 'aprobado' : 'no-aprobado' }}">
                {{ $registro->aprobado ? 'APROBADO' : 'NO APROBADO' }}
            </td>
            <td>{{ number_format($registro->nota_final, 2) }}</td>
            <td>{{ $registro->total_asistencias }}</td>
        </tr>
    </table>

    <h3>Detalle de calificaciones</h3>
    <table class="details-table">
        <thead>
            <tr>
                <th>Corte</th>
                <th>Ítem de evaluación</th>
                <th>Porcentaje</th>
                <th>Nota obtenida</th>
            </tr>
        </thead>
        <tbody>
            @forelse($notasDetalladas as $nota)
            <tr>
                <td>{{ $nota->itemCalificado->cortePeriodo->corteEscuela->nombre }}</td>
                <td>{{ $nota->itemCalificado->nombre }}</td>
                <td>{{ $nota->itemCalificado->porcentaje }}%</td>
                <td>{{ $nota->nota_obtenida ?? 'N/A' }}</td>
            </tr>
            @empty
            <tr><td colspan="4">No hay calificaciones detalladas registradas.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h3>Detalle de asistencias</h3>
    <table class="details-table">
        <thead>
            <tr>
                <th>Fecha de la clase</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($asistenciaDetallada as $asistencia)
            <tr>
                <td>{{ $asistencia->reporteClase->fecha_clase_reportada->format('d/m/Y') }}</td>
                <td>{{ $asistencia->asistio ? 'Asistió' : 'No Asistió' }}</td>
            </tr>
            @empty
            <tr><td colspan="2">No hay registros de asistencia para esta materia.</td></tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>