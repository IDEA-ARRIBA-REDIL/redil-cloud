<x-mail::message>
    # Proceso Completado

    Hola,

    Te informamos que el proceso de finalización para el periodo académico **"{{ $periodo->nombre }}"** ha concluido exitosamente.

    Ya puedes consultar los resultados y el estado final de los alumnos en el sistema.

    Gracias,<br>
    {{ config('app.name') }}
</x-mail::message>