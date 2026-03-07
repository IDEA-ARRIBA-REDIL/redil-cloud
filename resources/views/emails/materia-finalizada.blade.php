<x-mail::message>
# Proceso de Materia Completado

Hola,

Te informamos que el proceso de finalización para la materia:
**"{{ $materiaPeriodo->materia->nombre }}"**

dentro del periodo:
**"{{ $materiaPeriodo->periodo->nombre }}"**

ha concluido exitosamente.

Ya puedes consultar los resultados y el estado final de los alumnos para esta materia en el sistema.

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>