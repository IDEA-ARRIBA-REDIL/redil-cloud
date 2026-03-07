<!DOCTYPE html>
<html>
<head>
    <title>Confirmación de Compra de Cursos</title>
    <style>
        body { font-family: sans-serif; background-color: #f8f9fa; padding: 20px; }
        .container { background-color: #ffffff; padding: 30px; border-radius: 8px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h2 { color: #2b3445; text-align: center; }
        .details { background-color: #f1f5f9; padding: 15px; border-radius: 6px; margin: 20px 0; }
        .total { font-size: 18px; font-weight: bold; color: #0d6efd; text-align: right; margin-top: 15px; }
        ul { list-style-type: none; padding: 0; }
        li { padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
        li:last-child { border-bottom: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>¡Hola, {{ $nombreComprador }}!</h2>
        <p>Hemos recibido tu pago y confirmamos tu inscripción a tus nuevos cursos en <strong>CRECER</strong>.</p>
        
        <div class="details">
            <h4 style="margin-top:0;">Resumen de tu compra:</h4>
            <ul>
                @foreach($carrito->items as $item)
                    <li><strong>{{ $item['nombre'] }}</strong> <span style="float:right;">${{ number_format($item['precio'], 0, ',', '.') }}</span></li>
                @endforeach
            </ul>
            <div class="total">
                Total Pagado: ${{ number_format($carrito->total, 0, ',', '.') }}
            </div>
        </div>

        <p>Ya puedes acceder a ellos desde tu plataforma educativa y comenzar tu aprendizaje de crecimiento y consolidación.</p>
        <p style="text-align: center; color: #64748b; font-size: 14px; margin-top: 30px;">¡Gracias por aprender con nosotros!</p>
    </div>
</body>
</html>
