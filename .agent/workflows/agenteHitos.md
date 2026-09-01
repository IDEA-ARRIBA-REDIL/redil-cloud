# Agente Hitos — Documentación y Plan de Implementación

> **Módulo**: Hitos ("Mi Línea de Vida")  
> **Ubicación**: REDIL Cloud  
> **Fecha de Actualización**: Agosto 2026  
> **Estado**: **Secciones 1, 2, 3, 4, 5 y 6 (CRUD, Triggers, Muro 3D y Backend) COMPLETADAS**  

---

## 1. Visión General y Arquitectura

El módulo **Hitos** permite a las iglesias y organizaciones registrar, celebrar y visualizar los momentos espirituales y ministeriales más importantes en la vida de un creyente (bautismos, graduaciones bíblicas, ascensos de liderazgo, asistencia a retiros, pasos de consolidación, reconocimientos pastorales).

### Principio Arquitectónico: Evaluación Dinámica (Cero Duplicación de Datos)
A diferencia de crear millones de registros redundantes en base de datos al crear un hito para miles de usuarios:

1. **Hitos Automáticos (Escuelas, Pasos, Consolidación y Grupos)**:
   - **Evaluación en tiempo real (Escuelas)**: Se evalúan dinámicamente contra los registros académicos reales (`materias_aprobada_usuario`, `niveles_aprobado_usuario`, `crecimiento_usuarios`).
   - **Estados de Aprobación y Homologación (`aprobado`)**:
     - `0` (`ESTADO_REPROBADO`): Materia o homologación reprobada/rechazada ➔ **No otorga hito**.
     - `1` (`ESTADO_APROBADO`): Materia u homologación culminada con éxito ➔ **Otorga hito automáticamente**.
     - `2` (`ESTADO_EN_PROCESO`): Trámite de homologación en curso o materia cursando ➔ **No otorga hito** hasta su aprobación definitiva.
   - **Hitos de Grupos (Integrantes y Líderes)**:
     - **Disparo en tiempo real**: Se activan al asignar un feligrés como integrante (`User::cambiarGrupo()`) o al designarlo como líder (`Grupo::asignarEncargado()`).
     - **Registro permanente del logro**: Se asientan en `hito_usuario` con la fecha real del evento.
     - **Unicidad e Idempotencia Estricta**: Un usuario **solo puede recibir un hito específico 1 sola vez en su vida**. Si en el futuro se traslada a otro grupo del mismo tipo, el sistema verifica que ya posee el hito y **evita cualquier duplicación**.
   - **Resolución de Fechas en el Muro**: El timeline mapea la fecha exacta del logro desde `fecha_homologacion_aprobacion`, `fecha_homologacion`, `created_at` o la fecha del registro en `hito_usuario`.
   - **Compatibilidad total con PostgreSQL**: Las consultas JSON se ejecutan usando la sintaxis nativa y agnóstica de Laravel (`trigger_config->materia_id`), asegurando compatibilidad multiplataforma sin depender de funciones específicas de MySQL (`JSON_EXTRACT`).

2. **Hitos de Reconocimiento / Asignación Manual**:
   - **Uso exclusivo de `hito_usuario`**: Reservado para distinciones otorgadas directamente por pastores/administradores a feligreses específicos.
   - **Asignaciones acumulativas en el tiempo**: Permite crear un solo hito (ej: *"Servidor Destacado"*) y asignarlo a N personas hoy, y a más personas en fechas posteriores.
   - **Metadatos individuales**: Cada persona asignada cuenta con su propia fecha de entrega (`fecha`) y dedicatoria pastoral personalizada (`nota_personalizada`).
   - **Segregación estricta**: Si un usuario no está en la lista de asignados de un reconocimiento manual, el hito **no le aparece bajo ninguna circunstancia**.

3. **Hitos de Actividades**:
   - *Sin asistencia obligatoria*: Visibles según las restricciones de la actividad.
   - *Con asistencia*: Se validan dinámicamente contra la tabla `actividad_asistencias` del usuario autenticado.

4. **Hitos Generales (Congregacionales)**:
   - Visibles para toda la iglesia mediante el scope dinámico `Hito::forUser($user)`, respetando restricciones demográficas (sedes, estados civiles, rangos de edad, tipos de usuario).

---

## 2. Modelo Extensible: `TipoHito`

| Slug | Nombre | Dinámico | Comportamiento y Reglas |
| :--- | :--- | :---: | :--- |
| `general` | General / Conmemorativo | Sí | Celebraciones abiertas o segmentadas por sede/edad. Panel 4 visible. |
| `automatico` | Automático / Logro Espiritual | Sí | Disparado por Escuelas, Pasos, Consolidación o Grupos. Panel 4 visible. |
| `actividad` | Actividad Eclesial | Sí | Enlazado a Actividades (con o sin asistencia). Panel 4 visible. |
| `manual` | Asignación Manual / Reconocimiento | No | Otorgado con nombre y apellido mediante buscador de usuarios. **Panel 4 oculto automáticamente**. |

---

## 3. Estructura de Base de Datos

### Migraciones Tenant (`database/migrations/tenant/`)
1. `2026_08_14_000001_create_tipo_hitos_table.php`: Tabla `tipo_hitos`.
2. `2026_08_14_000002_create_hitos_table.php`: Tabla principal `hitos`.
3. `2026_08_14_000003_create_hito_fotos_table.php`: Fotos oficiales (admin) y fotos de feligreses aprobadas.
4. `2026_08_14_000004_create_hito_likes_table.php`: Likes únicos por usuario.
5. `2026_08_14_000005_create_hito_denuncias_table.php`: Bandeja de moderación de fotos y contenido.
6. `2026_08_14_000006_create_hito_usuario_table.php`: Pivote para asignaciones manuales individuales, fechas y dedicatorias (`fecha`, `nota_personalizada`, `asignado_por`, `origen_tipo`, `origen_id`).
7. `2026_08_14_000007_create_hito_restricciones_tables.php`: Tablas pivote de segmentación (`hito_sedes`, `hito_estados_civiles`, `hito_rangos_edad`, `hito_tipos_usuarios`, `hito_grupo_tipos`).

### Modelos Eloquent (`app/Models/`)
- `Hito.php`:
  - Scopes: `scopeActivos()`, `scopePorSlugTipo()`, `scopeForUser(User $user)`.
  - Helpers de tipo: `esGeneral()`, `esAutomatico()`, `esDeActividad()`, `esManual()`, `esManualIndividual()`.
  - Accessors: `portada_url`, `video_embed_url`.
- `TipoHito.php`: Catálogo dinámico de tipos y estilos visuales (colores, iconos).
- `HitoFoto.php`: Galería de imágenes (oficiales y de usuarios).
- `HitoLike.php`: Interacción social.
- `HitoDenuncia.php`: Reportes de moderación.
- `HitoUsuario.php`: Asignaciones directas individuales.

---

## 4. Componentes y Flujos de Trabajo Implementados

### A. Formulario de Creación y Edición (`CrearEditarHito.php` & Blade)
* **Panel 1: Información General**: Título, descripción, tipo de hito, fecha, estado activo/inactivo, mensaje pastoral general.
* **Panel 2: Multimedia y Fotos**: Portada cuadrada 1:1, URL de video, galería de fotos oficiales del administrador, configuración de fotos permitidas a usuarios.
* **Panel 3: Configuración de Activación / Triggers / Asignación**:
  * **Si es Automático**: Configuración de módulos (Escuelas con distinción de niveles agrupados vs materias independientes, Pasos de Crecimiento, Consolidaciones, Grupos).
  * **Si es Actividad**: Selector de actividad y check de exigencia de asistencia confirmada.
  * **Si es Manual / Reconocimiento**: Integra `@livewire('usuarios.usuarios-para-busqueda')` y una tabla interactiva con avatar, fecha individual, dedicatoria personalizada y botón de eliminación.
  * **Si es General**: Mensaje informativo de apertura congregacional.
* **Panel 4: Restricciones Demográficas y Segmentación**:
  * Selectores Select2 múltiples de Sedes, Tipos de Usuario, Estados Civiles, Rangos de Edad y Tipos de Grupo.
  * **Ocultación Inteligente**: Se oculta automáticamente cuando el hito es de tipo `manual` para evitar reglas redundantes.

### B. Gestión Administrativa de Hitos (`GestionarHitos.php` & Blade)
* Grid responsivo con tarjetas tipo post en proporción 1:1.
* **Cabecera blindada con Bootstrap Grid**: `row g-2 align-items-start` con columna de título flexible (`col` + `text-truncate` + `min-width: 0`) y columna de menú dropdown de 3 puntos fija (`col-auto`), garantizando visualización perfecta sin desbordamientos en pantallas de 13" de MacBook y dispositivos móviles.
* Filtros combinados por texto, rango de fechas, tipo de hito y estado activo/inactivo.
* Acciones por hito: Editar, Ver más, Control de Asistencias (si aplica), Migración Retroactiva y Eliminación.

### C. Muro / Línea de Vida 3D (`HitoController.php` & `muro-demo.blade.php`)
* Experiencia interactiva de **Túnel del Tiempo 3D**:
  * Consume datos reales procesados por `Hito::activos()->forUser(auth()->user())`.
  * Mapeo de badges informativos con el nombre de la materia/escuela requerida (ej. `📚 Materia Requerida: Discipulado I (Escuela de Líderes)`).
  * Mapeo de dedicatorias pastorales personalizadas y fechas de entrega individuales para reconocimientos manuales.
  * Interacción de Me Gusta (Likes) y visualización de galería oficial + fotos comunitarias aprobadas.

---

## 5. Mapeo de Archivos Clave del Módulo

| Componente / Archivo | Ubicación | Responsabilidad |
| :--- | :--- | :--- |
| **Controlador Muro** | `app/Http/Controllers/HitoController.php` | Renderiza el muro 3D, gestiona likes, subida de fotos y moderación. |
| **Modelo Principal** | `app/Models/Hito.php` | Lógica de negocio, relaciones, helpers de tipo y scope `forUser()`. |
| **Trigger Service** | `app/Services/HitoTriggerService.php` | Detección de eventos académicos, pasos, consolidación y grupos. |
| **Livewire CRUD** | `app/Livewire/Hitos/CrearEditarHito.php` | Formulario reactivo de 4 paneles con buscador de usuarios. |
| **Livewire Listado** | `app/Livewire/Hitos/GestionarHitos.php` | Grid administrativo de tarjetas y filtros. |
| **Vista Listado** | `resources/views/livewire/hitos/gestionar-hitos.blade.php` | Diseño responsivo con Bootstrap Grid. |
| **Vista Formulario** | `resources/views/livewire/hitos/crear-editar-hito.blade.php` | Vistas de los 4 paneles con buscador y tabla de asignaciones. |
| **Vista Muro 3D** | `resources/views/contenido/paginas/hitos/muro-demo.blade.php` | Línea de vida interactiva en túnel 3D con Three.js / CSS3D. |
