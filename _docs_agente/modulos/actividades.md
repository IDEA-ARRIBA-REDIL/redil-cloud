# Contexto del Módulo: Actividades

## 1. Concepto General

El módulo de **Actividades** gestiona eventos, conferencias, y también sirve como "contenedor" para la lógica de **Escuelas** (cuando `TipoActividad->tipo_escuelas = true`). Es el núcleo de la oferta de valor para el usuario final, permitiendo inscripciones, pagos y control de asistencia.

## 2. Arquitectura de Datos

### Entidades Principales

- **`Actividad`**: La entidad padre. Define fechas, configuración visual (banner, colores), y reglas de negocio globales.

  - _Relaciones_: `TipoActividad`, `ActividadCategoria` (1:N), `Sedes` (N:N), `Inscripcion` (indirectamente).
  - _Flags Clave_: `restriccion_por_categoria` (determina dónde se validan los requisitos), `punto_de_pago` (si permite pago online/taquilla).

- **`ActividadCategoria`**: Subdivisiones de una actividad (ej. "General", "VIP", o en escuelas: "Primer Semestre").

  - _Función_: Contiene la configuración de **Precios** (`monedas`), **Aforo** y **Requisitos Específicos** (Edad, Género, Nivel Espiritual).
  - _Escuelas_: Se vincula a `MateriaPeriodo` para la lógica académica.

- **`Inscripcion`**: El registro final de un usuario en un evento.

  - _Relaciones_: `User`, `ActividadCategoria`, `Compra`.
  - _Invitados_: Soporta inscripciones anidadas (`inscripcion_asociada`) para registrar invitados bajo un titular.

- **`Compra` y `Pago`**: Manejan la transacción financiera. Una compra puede incluir múltiples items (aunque en flujo simple suele ser 1 inscripción).

### Diagrama Simplificado

```mermaid
erDiagram
    Actividad ||--|{ ActividadCategoria : contiene
    ActividadCategoria }|--|{ Moneda : tiene_precios
    ActividadCategoria ||--o{ Inscripcion : genera
    User ||--o{ Inscripcion : realiza
    Inscripcion }|--|| Compra : pertenece
    Compra ||--|{ Pago : tiene
    ActividadCategoria }|--|{ PasoCrecimiento : requiere (Validación)
```

### 2.1 Inventario Completo de Relaciones (Modelos)

**Actividad (Raíz)**

- `tipo`: BelongsTo `TipoActividad`.
- `periodo`: BelongsTo `Periodo`.
- `categorias`: HasMany `ActividadCategoria`.
- **Configuración Global**:
  - `monedas`: BelongsToMany `Moneda`.
  - `tiposPago`: BelongsToMany `TipoPago`.
  - `sedes`: BelongsToMany `Sede`.
  - `banner`: HasOne `ActividadBanner`.
  - `camposAdicionales`: BelongsToMany `CamposAdicionalesActividad`.
  - `elementos`: HasMany `ElementoFormularioActividad` (Formulario dinámico).
- **Validaciones / Requisitos Globales**:
  - `rangosEdad`: BelongsToMany `RangoEdad`.
  - `tipoUsuarios`: BelongsToMany `TipoUsuario`.
  - `estadosCiviles`: BelongsToMany `EstadoCivil`.
  - `tipoServicios`: BelongsToMany `TipoServicioGrupo`.
  - `procesosRequisito`: BelongsToMany `PasoCrecimiento`.
  - `tareasRequisito`: HasMany `ActividadTareaRequisito`.
- **Efectos al Completar**:
  - `procesosCulminados`: BelongsToMany `PasoCrecimiento`.
  - `tareasCulminadas`: HasMany `ActividadTareaCulminada`.

**ActividadCategoria (Nodo Oferta)**

- `actividad`: BelongsTo `Actividad`.
- `nivel`: BelongsTo `Nivel`.
- **Escuelas**:
  - `materiaPeriodo`: BelongsTo `MateriaPeriodo`.
- **Economía**:
  - `monedas`: BelongsToMany `Moneda` (con atributo `valor`).
  - `abonos`: BelongsToMany `Abono`.
- **Validaciones Específicas (Override o Adicionales)**:
  - `sedes`, `rangosEdad`, `tipoUsuarios`, `estadosCiviles`, `tipoServicios` (Similar a Actividad).
  - `procesosRequisito`: BelongsToMany `PasoCrecimiento`.
  - `tareasRequisito`: HasMany `ActividadCategoriaTareaRequisito`.

## 3. Lógica de Negocio Crítica

### 3.1 Motor de Validación (`Actividad.php`)

El método central es `validarAccesoGlobal(User $usuario)`. Este orquestador decide qué estrategia de validación usar:

1.  **Estrategia Escuela** (`validarCategoriasEscuelaParaTaquilla`):

    - Si es tipo escuela, valida prerrequisitos académicos (materias aprobadas, notas, asistencias).
    - Revisa cupos académicos.

2.  **Estrategia por Categoría** (`verificarDisponibilidadCategorias`):

    - Si `restriccion_por_categoria = true`.
    - Itera cada categoría y verifica: Género, Edad, Sede, Tipo Usuario, Pasos de Crecimiento.

3.  **Estrategia General** (`validarUsuarioEnCategoria`):
    - Si las restricciones son a nivel de `Actividad`.

### 3.2 Flujo de Inscripción

1.  **Visualización**: El usuario ve actividades en `proximas` (filtradas por fecha de visualización/cierre).
2.  **Validación**: Al intentar inscribirse, se ejecuta el motor de validación.
3.  **Carrito**: Si pasa, se crea un `ActividadCarritoCompra`.
4.  **Checkout**:
    - Se crea `Compra` (estado pendiente/pagada).
    - Se crea `Pago`.
    - Se crea `Inscripcion`.
    - (Si es Escuela) Se crea `Matricula` y `EstadoAcademico`.

### 3.3 Gestión de Invitados

El sistema permite inscribir terceros.

- En la BD, se crea una `Inscripcion` con `persona_externa_id` o datos de invitado, y se vincula a la `inscripcion_asociada` del pagador principal.

## 4. Controladores Clave

- **`ActividadController`**: Maneja el listado (`index`, `proximas`), la creación/edición administrativa, y la validación para mostrar el botón de inscripción.
- **`CarritoController`**: Maneja el flujo de compra.
- **`TaquillaController`**: (Probable) Interfaz para operadores que inscriben usuarios presencialmente.
