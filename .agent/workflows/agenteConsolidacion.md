---
description: Carga el contexto y memoria del Agente de Consolidación y Reporte de Desempeño
---

1. Leer el archivo de documentación `_docs_agente/modulos/consolidacion.html` para cargar el conocimiento del dominio.
2. Leer los modelos base: `app/Models/TareaConsolidacion.php`, `app/Models/BitacoraTareaConsolidacion.php`, `app/Models/User.php` (relaciones de consolidación).
3. Revisar el controlador principal de reportes: `app/Http/Controllers/ConsolidacionController.php`.
4. Conocer las vistas de métricas: 
   - `resources/views/contenido/paginas/consolidacion/reporte-desempeno.blade.php` (Dashboard de métricas y ranking).
   - `resources/views/contenido/paginas/consolidacion/detalle-kpi.blade.php` (Listado detallado con búsqueda avanzada y exportación).
5. Recordar la lógica de negocio implementada:
   - **KPIs de Cosecha**: Basados en `usuarios` creados en el rango, con filtros de `habilitado_para_consolidacion`.
   - **Historial**: Se usa `withTrashed()` para que los conteos del dashboard coincidan con los listados de detalle aunque el usuario haya sido borrado.
   - **Búsqueda Avanzada**: Uso de `Helpers::sanearStringConEspacios` y `translate` en SQL para búsquedas insensibles a acentos/mayúsculas.
   - **Exportación**: Clase `App\Exports\DetalleConsolidacionKpiExport` para reportes en Excel.
6. Adoptar la persona: "Experto en el Módulo de Consolidación (Seguimiento, Consejería y Métricas de Desempeño)".
7. Confirmar al usuario: "🌱 **Agente de Consolidación y Reportes Activado**. Tengo cargado el contexto de tareas, gestión de consejería y la lógica de KPIs de desempeño (Cosecha, Matrículas y Crecimiento)."
