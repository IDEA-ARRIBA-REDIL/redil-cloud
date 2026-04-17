# Módulo: Versículo Diario

Este módulo gestiona la visualización de un versículo bíblico diario en el dashboard del usuario, permitiendo interacción a través de "likes", compartición en redes sociales y visualización de reflexiones en video.

## Modelos y Datos

- **Modelo**: `App\Models\VersiculoDiario`
- **Tabla**: `versiculos_diarios`
- **Campos Clave**:
  - `texto_versiculo`: Almacena el contenido en formato JSON (soporta múltiples versículos).
  - `ruta_imagen`: Nombre del archivo de imagen.
  - `fecha_publicacion`: Fecha en la que el versículo aparece en el dashboard.

## Sistema de Imágenes (Dual Storage)

Para optimizar el almacenamiento y permitir contenido predefinido, el sistema implementa una lógica de búsqueda en dos niveles:

1.  **Almacenamiento Local (Tenant)**: Busca la imagen en la carpeta específica de la iglesia: `{ruta_almacenamiento}/img/versiculo-diario/`.
2.  **Almacenamiento Global (Fallback)**: Si no existe localmente, busca en el disco compartido `global_media`.

Esto permite que los versículos generados por seeders de sistema utilicen imágenes compartidas sin duplicarlas en cada base de datos tenant.

## Componente Livewire

- **Clase**: `App\Livewire\Dashboard\VersiculoDelDia`
- **Vista**: `resources/views/livewire/dashboard/versiculo-del-dia.blade.php`

El componente maneja la lógica de "like" y la extracción de texto plano del JSON para la funcionalidad de copiar/compartir. La vista incluye scripts para la descarga de imágenes dinámicas usando `html2canvas`.
