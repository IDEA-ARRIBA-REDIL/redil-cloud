---
description: Agente para la gestión de Niveles de Agrupación (Sistema Escolar Grados) - NIVEL-ESCUELAS
---

# Agente de Niveles (Grados Escolares)

Este agente se especializa en la gestión de **Grados** (internamente llamados `NivelEscuela`), permitiendo una configuración avanzada de requisitos, procesos, tareas y la gestión de materias unificadas.

## 1. Directivas Críticas del Usuario

- **SIN COMANDOS ARTISAN**: No intentes ejecutar `php artisan` (migraciones, seeders, etc.). El usuario se encarga de ejecutarlos manualmente en el servidor.
- **SIN VALIDACIONES AUTOMÁTICAS**: No ejecutes scripts de validación automática. La verificación es responsabilidad del usuario en su entorno.
- **NOMENCLATURA**: Todos los archivos y procedimientos deben asociarse con la identidad `niveles-escuelas`.
- **COMENTARIOS OBLIGATORIOS**: Debes comentar detalladamente cada bloque de código y procedimiento para que el usuario entienda exactamente qué se está haciendo.
- **IDIOMA**: Toda la comunicación, comentarios y documentación debe ser estrictamente en **ESPAÑOL**.

## 2. Contexto de Arquitectura Actual

- **Modelo Principal**: `app/Models/NivelEscuela.php` (Tabla: `niveles_escuelas`).
- **Modelo de Materias**: `app/Models/Materia.php` (Tabla: `materias`).
- **Controlador**: `app/Http/Controllers/NivelesEscuelasController.php`.

### Gestión Unificada de Materias
Las materias de un grado ya no usan una tabla separada; se registran en la tabla `materias` oficial con un `nivel_id` asociado.

- **Herencia de Configuración**: El modelo `Materia.php` implementa una lógica de herencia. Si la materia tiene un `nivel_id`, las propiedades como `habilitar_asistencias`, `habilitar_calificaciones`, etc., se obtienen dinámicamente del Grado (Nivel) superior.
- **Funcionalidades Extendidas**: Al estar unificadas, las materias de un grado tienen acceso nativo a:
    - **Horarios**: `/materias/{id}/horarios`.
    - **Modelo Calificativo**: `/materias/{id}/modelo`.

## 3. Integración con Periodos Académicos (Estructura Jerárquica)

Para escuelas con `tipo_matricula === 'niveles_agrupados'`, la gestión de periodos se vuelve jerárquica: **Periodo -> Grado -> Materia**.

- **Estructura de Datos**:
    - **Tabla `niveles_periodo`**: Asocia un Grado (`NivelEscuela`) con un `Periodo`.
    - **Tabla `materia_periodo`**: Cada registro ahora incluye un `nivel_id` que vincula la instancia de la materia al grado correspondiente dentro del periodo.
- **Modelos**:
    - `NivelPeriodo.php`: Gestiona la asociación Grado-Periodo.
    - `Periodo.php`: Relación `nivelesPeriodo()` (HasMany).
    - `MateriaPeriodo.php`: Relación `nivel()` (BelongsTo a `NivelEscuela`).

## 4. UI y Flujo de Gestión

- **Navegación Dinámica**: La pestaña de gestión en `materias-periodo.blade.php` cambia su etiqueta a **"Grados"** si la escuela es de niveles agrupados.
- **Componentes Livewire**:
    - `NivelesPeriodo`: Lista los grados asociados al periodo, muestra el conteo de materias por grado y permite añadir/quitar grados.
    - `MateriaPeriodo`: Al gestionar materias de un grado, el componente recibe un `nivel_id` explícito para filtrar tanto el listado actual como las materias disponibles para añadir.
- **Regla de Integridad**: No se permite desvincular un Grado de un Periodo si ya existen materias asociadas a dicho grado en ese periodo.

## 5. Sistema de Duplicación (Clonación Profunda)

Se ha implementado un motor de duplicación inteligente que permite migrar la configuración de un periodo a otro:

- **Estrategia de Replicación**: Usa el método `replicate()` de Eloquent para crear copias limpias (`finalizado = false`, reseteo de cupos).
- **Alcance de Clonación**:
    - **Grados**: Registra el `NivelPeriodo` si no existe.
    - **Materias**: Copia instancias de `MateriaPeriodo` vinculadas al grado.
    - **Horarios**: Replica los `HorarioMateriaPeriodo` vinculados a cada materia.
    - **Evaluación**: Clona los `ItemCorteMateriaPeriodo` (exámenes/tareas), mapeándolos automáticamente a los `CortePeriodo` del periodo destino mediante la relación con el `corte_escuela_id`.
- **Validación de Existencia**: El sistema comprueba mediante `exists()` o `firstOrCreate()` para evitar duplicados si la materia o grado ya estaba configurado parcialmente en el destino.

## 6. Protocolo de Trabajo

1.  **Análisis**: Antes de modificar, revisa la herencia en `Materia.php` y las relaciones en `NivelEscuela.php`.
2.  **Contexto de Periodos**: Si trabajas en periodos, verifica siempre si la escuela usa `tipo_matricula === 'niveles_agrupados'`.
3.  **Persistencia Livewire**: Al filtrar materias por grado, asegúrate de pasar el `nivel_id` de forma explícita al componente `MateriaPeriodo` para mantener el contexto en el ciclo de vida de Livewire.
4.  **UI Premium**: Mantener el diseño limpio, iconos `text-black` y alertas informativas claras.
5.  **Recursión**: Evitar el uso de `$this->propiedad` dentro de un accessor con el mismo nombre; usar siempre el valor `$value` proporcionado por Eloquent o el método `getConfigProp`.
6.  **Notificación**: Informar siempre las rutas de verificación (ej. `/periodos/{id}/materias?nivel_id={id}`).

---
*Nota: Este agente garantiza una gestión jerárquica robusta, asegurando que la configuración de los grados se herede correctamente y que la navegación en los periodos sea intuitiva y contextualizada.*
