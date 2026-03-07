# Contexto de Agente: Módulo de Escuelas

### C. Gestión de Asistencia

La asistencia se modela como un reporte por clase, con detalle por alumno.

- **Cabecera**: `ReporteAsistenciaClase`
  - Vincula `HorarioMateriaPeriodo` + Fecha (`fecha_clase_reportada`).
  - Campos calculados: `presentes_count`, `ausentes_count`.
- **Detalle**: `ReporteAsistenciaAlumnos`
  - Vincula: Cabecera -> Alumno (`user_id`).
  - Estado: `asistio` (bool), `motivo_inasistencia_id`.

## 3. Diagrama de Relaciones (Verificación)

```mermaid
classDiagram
    class Escuela {
        +String nombre
        +hasMany Periodo
        +hasMany Materia
        +hasMany NivelEscuela
    }
    class Periodo {
        +Date fecha_inicio
        +Date fecha_fin
        +belongsTo Escuela
        +hasMany MateriaPeriodo
    }
    class Materia {
        +String nombre
        +Boolean habilitar_calificaciones
        +belongsTo Escuela
        +hasMany ItemPlantilla
    }
    class Aula {
        +String nombre
        +belongsTo Sede
    }
    class HorarioMateriaPeriodo {
        +belongsTo MateriaPeriodo
        +belongsTo Aula
        +belongsTo Maestro
        +hasMany Matricula (Pivot)
    }
    class User {
        +String name
        +belongsToMany HorarioMateriaPeriodo (via Matricula)
    }
    class Matricula {
        +Pivot Table
        +belongsTo User
        +belongsTo HorarioMateriaPeriodo
        +belongsTo Periodo
        +decimal valor_pagado
        +hasOne EstadoAcademico
    }
    class Calificaciones {
        +decimal nota
        +belongsTo AlumnoRespuestaItem
    }

    Escuela "1" *-- "many" Periodo : Contiene
    Escuela "1" *-- "many" Materia : Oferta
    Periodo "1" --> "many" Materia : Se instancia en
    Materia "1" --> "many" ItemPlantilla : Define Evaluacion

    User "many" --> "many" HorarioMateriaPeriodo : Se matricula en
    (User, HorarioMateriaPeriodo) .. Matricula : Pivot

    Matricula --> "1" Periodo : Pertenece a
    HorarioMateriaPeriodo --> "1" Aula : Ocupa

    Note for Matricula "Pago + Academico"
```

## 4. Notas Técnicas para el Agente

- **Validación de Roles**: Siempre verificar `rolActivo` en los controladores antes de permitir acciones de administración.
- **Transacciones**: Usar `DB::transaction` al tocar matrículas, pagos o reportes de asistencia masivos.
- **Navegación**: El routing de Livewire suele estar en `routes/web.php` bajo el prefijo `escuelas/` o `admin/escuelas`.
- **Integridad de Asistencia**: Un reporte de asistencia valide que el alumno pertenezca al `HorarioMateriaPeriodo` (vía Matrícula activa) en la fecha reportada.
