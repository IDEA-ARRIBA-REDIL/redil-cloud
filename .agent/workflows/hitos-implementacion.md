# Plan de Implementación — Módulo Hitos (REDIL Cloud)

> **Fecha**: Junio 2026 (revisión 2)
> **Estado**: Pendiente de ejecución
> **Prototipo UI**: `hitos-prototype.html` (desktop) y `hitos-prototype-mobile.html` (móvil)
> **Módulos relacionados**: `pasos_crecimiento`, `tareas_consolidacion`, `escuelas`, `grupos`, `actividades`

---

## 0. Resumen del Módulo

El módulo **Hitos** permite a las iglesias crear y gestionar eventos especiales importantes en la vida espiritual de un usuario (fiel). Los hitos se dividen en **3 orígenes**:

| Origen | Descripción |
|--------|-------------|
| **General** | Creado manualmente por personal administrativo desde cero. |
| **Automático** | Generado al cumplirse condiciones en módulos: pasos de crecimiento, tareas de consolidación, escuelas, grupos. |
| **Actividad** | Asociado a una actividad existente (con o sin requisito de asistencia). |

### Reglas de Negocio Confirmadas

1. **Pasos de Crecimiento**: Se dispara cuando el estado del paso de crecimiento cambia a un estado específico (ej: "Bienvenida" → "Finalizado").
2. **Tareas de Consolidación**: Se dispara cuando el estado de la tarea cambia a un estado específico (confirmado por un asesor).
3. **Actividades SIN asistencia**: Se asigna a todos los usuarios restringidos al crear la actividad.
4. **Actividades CON asistencia**: Solo aparece al usuario si tiene un **registro de asistencia** en la tabla de asistencias de la actividad.
5. **Escuelas**: Admin selecciona escuela → materia. Se dispara al aprobar esa materia. Opcionalmente también al completar un nivel completo.
6. **Grupos**: **Dos hitos diferentes** — uno para integrante y otro para líder/encargado.

### Características Adicionales

- Fotos (admin puede subir ilimitadas, usuario máximo 3 de 2MB).
- Videos YouTube/Vimeo.
- Likes (1 por usuario).
- Denuncias con admin de reportes.
- Restricciones de visibilidad: sede, estado civil, rango de edad, tipo de usuario, tipo de grupo.
- Hitos desactivables sin eliminar.

---

## 1. Convenciones Aplicadas

- **Multi-tenant**: Archivos via `storeAs($directorio, $nombre, 'public')` y leídos via `tenant_asset()`.
- **Default images**: `Storage::disk('global_media')` para placeholders.
- **No ejecutar migraciones**: El desarrollador corre `php artisan tenant:migrate` manualmente.
- **No usar `wire:confirm`**: SweetAlert2 global.
- **Livewire 3** + Alpine.js + Bootstrap 5 + Select2 con prefijos únicos (`#select-hitos-materia`).
- **Permisos con Spatie**: `hitos.crear`, `hitos.gestionar`, etc.
- **Idioma**: Español nativo.
- **Variables/Funciones**: `camelCase`.

---

## 2. Estructura de Archivos a Crear

```
app/
├── Http/
│   └── Controllers/
│       └── HitoController.php                              [NUEVO]
├── Livewire/
│   └── Hitos/
│       ├── GestionarHitos.php                              [NUEVO] Listado admin
│       ├── CrearEditarHito.php                             [NUEVO] Form admin con tabs
│       ├── MuroHitos.php                                   [NUEVO] Feed usuario
│       ├── PerfilHitos.php                                 [NUEVO] Tab "Hitos" del perfil
│       ├── GestionarDenuncias.php                          [NUEVO] Admin de reportes
│       └── GestionarAsistencias.php                        [NUEVO] Admin: tomar asistencia de actividad
├── Models/
│   ├── Hito.php                                            [NUEVO]
│   ├── HitoFoto.php                                        [NUEVO]
│   ├── HitoLike.php                                        [NUEVO]
│   ├── HitoDenuncia.php                                    [NUEVO]
│   └── HitoUsuario.php                                     [NUEVO] Pivot
├── Services/
│   └── HitoTriggerService.php                              [NUEVO] Lógica de triggers
├── Policies/
│   └── HitoPolicy.php                                      [NUEVO]
└── Providers/
    └── AppServiceProvider.php                              [MODIFICAR] Registrar observers

database/
└── migrations/tenant/
    ├── 2026_06_24_000001_create_hitos_table.php            [NUEVO]
    ├── 2026_06_24_000002_create_hito_fotos_table.php       [NUEVO]
    ├── 2026_06_24_000003_create_hito_likes_table.php       [NUEVO]
    ├── 2026_06_24_000004_create_hito_denuncias_table.php   [NUEVO]
    ├── 2026_06_24_000005_create_hito_usuario_table.php    [NUEVO] Pivot
    ├── 2026_06_24_000006_create_hito_sedes_table.php       [NUEVO] Restricción
    ├── 2026_06_24_000007_create_hito_estados_civiles_table.php [NUEVO] Restricción
    ├── 2026_06_24_000008_create_hito_rangos_edad_table.php [NUEVO] Restricción
    ├── 2026_06_24_000009_create_hito_tipos_usuarios_table.php [NUEVO] Restricción
    └── 2026_06_24_000010_create_hito_grupo_tipos_table.php [NUEVO] Restricción grupos

resources/views/
├── contenido/paginas/hitos/
│   ├── gestionar.blade.php                                 [NUEVO] Vista admin CRUD
│   ├── crear-editar.blade.php                              [NUEVO] Form admin
│   ├── muro.blade.php                                      [NUEVO] Muro usuario
│   ├── perfil.blade.php                                    [NUEVO] Hitos del usuario consultado
│   ├── denuncias.blade.php                                 [NUEVO] Admin reportes
│   └── parciales/
│       ├── _card-hito.blade.php                            [NUEVO] Card reutilizable
│       ├── _galeria.blade.php                              [NUEVO] Galería
│       ├── _video.blade.php                                [NUEVO] Embed video
│       └── _form-trigger.blade.php                         [NUEVO] Form sub-triggers
└── livewire/hitos/
    ├── gestionar-hitos.blade.php                           [NUEVO]
    ├── crear-editar-hito.blade.php                         [NUEVO]
    ├── muro-hitos.blade.php                                [NUEVO]
    ├── perfil-hitos.blade.php                              [NUEVO]
    ├── gestionar-denuncias.blade.php                       [NUEVO]
    └── gestionar-asistencias.blade.php                     [NUEVO]

database/seeders/
└── PermisoHitoSeeder.php                                   [NUEVO]
```

---

## 3. Paso 1 — Migraciones (10 archivos nuevos)

### 3.1. `hitos` (tabla principal con campo `origen`)

```php
Schema::create('hitos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->comment('Admin que lo creó');

    // Contenido
    $table->string('titulo', 150);
    $table->text('descripcion')->nullable();
    $table->text('mensaje_usuario')->nullable()
        ->comment('Mensaje personalizado que se muestra al usuario en su muro (ej: "¡Felicidades por tu bautismo!")');
    $table->string('portada_path', 255)->nullable();
    $table->string('video_url', 500)->nullable();

    // ORIGEN del hito
    $table->enum('origen', ['general', 'automatico', 'actividad'])->default('general');

    // Para origen = 'actividad'
    $table->foreignId('actividad_id')->nullable()->constrained('actividades')->onDelete('set null');
    $table->boolean('requiere_asistencia')->default(false)
        ->comment('Si true, solo aparece a usuarios con registro de asistencia');
    $table->boolean('asignar_al_crear')->default(false)
        ->comment('Para actividades sin asistencia: asignar a todos al crear');

    // Para origen = 'automatico'
    $table->string('trigger_modulo', 30)->nullable()
        ->comment('pasos_crecimiento | tareas_consolidacion | escuelas | grupos');
    $table->string('trigger_tipo', 50)->nullable()
        ->comment('cambio_estado | aprobacion_materia | aprobacion_nivel | asignacion_integrante | designacion_lider');
    $table->json('trigger_config')->nullable()
        ->comment('Condiciones: {paso_id, estado_id, materia_id, nivel_id, tipo_grupo_id}');

    // Configuración general
    $table->boolean('permite_fotos_usuario')->default(false);
    $table->unsignedSmallInteger('max_fotos_usuario')->default(3);
    $table->unsignedSmallInteger('max_peso_kb')->default(2048);
    $table->boolean('requiere_sesion')->default(true);
    $table->boolean('activo')->default(true);

    $table->timestamps();
    $table->softDeletes();

    $table->index(['origen', 'activo']);
    $table->index('trigger_modulo');
    $table->index('actividad_id');
});
```

### 3.2. `hito_fotos` (fotos del hito)

```php
Schema::create('hito_fotos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('hito_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null')
        ->comment('null = foto admin, filled = foto usuario');
    $table->string('ruta', 255);
    $table->unsignedSmallInteger('orden')->default(0);
    $table->boolean('es_admin')->default(false);
    $table->boolean('aprobada')->default(true);
    $table->timestamps();

    $table->index(['hito_id', 'es_admin']);
});
```

### 3.3. `hito_likes` (likes de usuarios)

```php
Schema::create('hito_likes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('hito_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->timestamp('created_at')->useCurrent();

    $table->unique(['hito_id', 'user_id']);
});
```

### 3.4. `hito_denuncias` (reportes)

```php
Schema::create('hito_denuncias', function (Blueprint $table) {
    $table->id();
    $table->foreignId('hito_id')->constrained()->onDelete('cascade');
    $table->foreignId('foto_id')->nullable()->constrained('hito_fotos')->onDelete('set null');
    $table->foreignId('user_id')->constrained()->comment('Quien reporta');
    $table->foreignId('resuelto_por')->nullable()->constrained('users');
    $table->string('motivo', 255);
    $table->enum('estado', ['pendiente', 'resuelta'])->default('pendiente');
    $table->text('observaciones_admin')->nullable();
    $table->timestamps();

    $table->index(['estado', 'created_at']);
});
```

### 3.5. `hito_usuario` (pivote: hito asignado a usuario)

```php
Schema::create('hito_usuario', function (Blueprint $table) {
    $table->id();
    $table->foreignId('hito_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->date('fecha');
    $table->boolean('asistio')->default(true)
        ->comment('Para actividades con asistencia: confirmado por admin');
    $table->string('origen_tipo', 50)->nullable()
        ->comment('general | automatico | actividad | admin_manual');
    $table->unsignedBigInteger('origen_id')->nullable()
        ->comment('ID del registro que originó la asignación');
    $table->foreignId('asignado_por')->nullable()->constrained('users');
    $table->timestamps();

    // Evitar duplicados: mismo hito, usuario, origen
    $table->unique(['hito_id', 'user_id', 'origen_tipo', 'origen_id'], 'hito_usuario_unico');
    $table->index(['user_id', 'fecha']);
});
```

### 3.6 al 3.10. Pivotes de restricciones (igual que `post_sedes`)

```php
// hito_sedes
Schema::create('hito_sedes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('hito_id')->constrained()->onDelete('cascade');
    $table->foreignId('sede_id')->constrained()->onDelete('cascade');
    $table->timestamps();
});

// hito_estados_civiles
Schema::create('hito_estados_civiles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('hito_id')->constrained()->onDelete('cascade');
    $table->foreignId('estado_civil_id')->constrained('estados_civiles')->onDelete('cascade');
    $table->timestamps();
});

// hito_rangos_edad
Schema::create('hito_rangos_edad', function (Blueprint $table) {
    $table->id();
    $table->foreignId('hito_id')->constrained()->onDelete('cascade');
    $table->foreignId('rango_edad_id')->constrained('rangos_edad')->onDelete('cascade');
    $table->timestamps();
});

// hito_tipos_usuarios
Schema::create('hito_tipos_usuarios', function (Blueprint $table) {
    $table->id();
    $table->foreignId('hito_id')->constrained()->onDelete('cascade');
    $table->foreignId('tipo_usuario_id')->constrained('tipo_usuarios')->onDelete('cascade');
    $table->timestamps();
});

// hito_grupo_tipos
Schema::create('hito_grupo_tipos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('hito_id')->constrained()->onDelete('cascade');
    $table->foreignId('tipo_grupo_id')->constrained('tipo_grupos')->onDelete('cascade');
    $table->timestamps();
});
```

**Acción manual del desarrollador**:

```bash
cd /var/www/html/REDIL-CLOUD
php artisan tenant:migrate
```

---

## 4. Paso 2 — Modelos Eloquent

### 4.1. `app/Models/Hito.php`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Hito extends Model
{
    use SoftDeletes;

    protected $table = 'hitos';
    protected $guarded = [];

    protected $casts = [
        'permite_fotos_usuario' => 'boolean',
        'requiere_sesion' => 'boolean',
        'activo' => 'boolean',
        'requiere_asistencia' => 'boolean',
        'asignar_al_crear' => 'boolean',
        'trigger_config' => 'array',
    ];

    // ============================================
    // RELACIONES
    // ============================================

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(Actividad::class);
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(HitoFoto::class)->orderBy('orden');
    }

    public function fotosAdmin(): HasMany
    {
        return $this->hasMany(HitoFoto::class)->where('es_admin', true)->orderBy('orden');
    }

    public function fotosUsuario(): HasMany
    {
        return $this->hasMany(HitoFoto::class)->where('es_admin', false)->orderBy('orden');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(HitoLike::class);
    }

    public function denuncias(): HasMany
    {
        return $this->hasMany(HitoDenuncia::class);
    }

    public function usuariosAsignados(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'hito_usuario', 'hito_id', 'user_id')
            ->withPivot('fecha', 'asistio', 'origen_tipo', 'origen_id', 'asignado_por')
            ->withTimestamps();
    }

    public function sedes(): BelongsToMany
    {
        return $this->belongsToMany(Sede::class, 'hito_sedes')->withTimestamps();
    }

    public function estadosCiviles(): BelongsToMany
    {
        return $this->belongsToMany(EstadoCivil::class, 'hito_estados_civiles')->withTimestamps();
    }

    public function rangosEdad(): BelongsToMany
    {
        return $this->belongsToMany(RangoEdad::class, 'hito_rangos_edad')->withTimestamps();
    }

    public function tiposUsuarios(): BelongsToMany
    {
        return $this->belongsToMany(TipoUsuario::class, 'hito_tipos_usuarios')->withTimestamps();
    }

    public function grupoTipos(): BelongsToMany
    {
        return $this->belongsToMany(TipoGrupo::class, 'hito_grupo_tipos')->withTimestamps();
    }

    // ============================================
    // SCOPES
    // ============================================

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeGenerales($query)
    {
        return $query->where('origen', 'general');
    }

    public function scopeAutomaticos($query)
    {
        return $query->where('origen', 'automatico');
    }

    public function scopeDeActividad($query)
    {
        return $query->where('origen', 'actividad');
    }

    /**
     * Hitos visibles para un usuario específico (con restricciones).
     * Patrón idéntico a Post::scopeForUser.
     */
    public function scopeForUser($query, User $user)
    {
        $rangoEdadId = $user->rangoEdad() ? $user->rangoEdad()->id : null;

        return $query->where('activo', true)->where(function ($q) use ($user, $rangoEdadId) {
            $q->whereDoesntHave('sedes')
              ->whereDoesntHave('estadosCiviles')
              ->whereDoesntHave('rangosEdad')
              ->whereDoesntHave('tiposUsuarios')
              ->whereDoesntHave('grupoTipos')
              ->orWhere(function ($q2) use ($user, $rangoEdadId) {
                  $q2->where(function ($qSede) use ($user) {
                      $qSede->whereDoesntHave('sedes')
                            ->orWhereHas('sedes', fn($sq) => $sq->where('sedes.id', $user->sede_id));
                  })
                  ->where(function ($qEstado) use ($user) {
                      $qEstado->whereDoesntHave('estadosCiviles')
                              ->orWhereHas('estadosCiviles', fn($sq) => $sq->where('estados_civiles.id', $user->estado_civil_id));
                  })
                  ->where(function ($qRango) use ($rangoEdadId) {
                      $qRango->whereDoesntHave('rangosEdad')
                             ->orWhereHas('rangosEdad', fn($sq) => $sq->where('rangos_edad.id', $rangoEdadId));
                  })
                  ->where(function ($qTipo) use ($user) {
                      $qTipo->whereDoesntHave('tiposUsuarios')
                            ->orWhereHas('tiposUsuarios', fn($sq) => $sq->where('tipo_usuarios.id', $user->tipo_usuario_id));
                  });
              });
        });
    }

    // ============================================
    // ACCESSORS
    // ============================================

    public function getPortadaUrlAttribute(): string
    {
        if ($this->portada_path && $this->portada_path !== '') {
            return tenant_asset('img/hitos/portadas/' . $this->portada_path);
        }
        return Storage::disk('global_media')->url('hitos/default-portada.jpg');
    }

    /**
     * Mensaje personalizado para el usuario. Si no se configuró, devuelve
     * el mensaje por defecto según el origen.
     */
    public function getMensajeParaUsuarioAttribute(): string
    {
        if ($this->mensaje_usuario && trim($this->mensaje_usuario) !== '') {
            return $this->mensaje_usuario;
        }

        return match ($this->origen) {
            'automatico' => '¡Felicidades por este logro en tu camino espiritual!',
            'actividad' => 'Gracias por ser parte de este momento especial.',
            default => $this->descripcion ?? 'Este es un momento especial para ti.',
        };
    }

    public function getVideoEmbedUrlAttribute(): ?string
    {
        if (!$this->video_url) return null;
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $this->video_url, $m)) {
            return "https://www.youtube.com/embed/{$m[1]}";
        }
        if (preg_match('/vimeo\.com\/(\d+)/', $this->video_url, $m)) {
            return "https://player.vimeo.com/video/{$m[1]}";
        }
        return $this->video_url;
    }

    public function getTotalLikesAttribute(): int
    {
        return $this->likes()->count();
    }

    public function getTotalFotosAttribute(): int
    {
        return $this->fotos()->count();
    }

    public function esGeneral(): bool { return $this->origen === 'general'; }
    public function esAutomatico(): bool { return $this->origen === 'automatico'; }
    public function esDeActividad(): bool { return $this->origen === 'actividad'; }
}
```

### 4.2. `app/Models/HitoFoto.php`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HitoFoto extends Model
{
    protected $table = 'hito_fotos';
    protected $guarded = [];

    protected $casts = [
        'es_admin' => 'boolean',
        'aprobada' => 'boolean',
    ];

    public function hito(): BelongsTo
    {
        return $this->belongsTo(Hito::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getUrlAttribute(): string
    {
        return tenant_asset('img/hitos/fotos/' . $this->ruta);
    }

    public function scopeAdmin($query)
    {
        return $query->where('es_admin', true);
    }

    public function scopeUsuario($query)
    {
        return $query->where('es_admin', false);
    }

    public function scopeAprobadas($query)
    {
        return $query->where('aprobada', true);
    }
}
```

### 4.3. `app/Models/HitoLike.php`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HitoLike extends Model
{
    public $timestamps = false;
    protected $table = 'hito_likes';
    protected $guarded = [];

    protected $casts = ['created_at' => 'datetime'];

    public function hito(): BelongsTo { return $this->belongsTo(Hito::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
```

### 4.4. `app/Models/HitoDenuncia.php`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HitoDenuncia extends Model
{
    use SoftDeletes;
    protected $table = 'hito_denuncias';
    protected $guarded = [];

    public function hito(): BelongsTo { return $this->belongsTo(Hito::class); }
    public function foto(): BelongsTo { return $this->belongsTo(HitoFoto::class, 'foto_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function resueltoPor(): BelongsTo { return $this->belongsTo(User::class, 'resuelto_por'); }
}
```

### 4.5. `app/Models/HitoUsuario.php` (pivot)

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HitoUsuario extends Pivot
{
    public $incrementing = true;
    protected $table = 'hito_usuario';
    protected $guarded = [];

    public function hito(): BelongsTo { return $this->belongsTo(Hito::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
```

---

## 5. Paso 3 — Service de Triggers

### 5.1. `app/Services/HitoTriggerService.php`

```php
namespace App\Services;

use App\Models\Hito;
use App\Models\HitoUsuario;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class HitoTriggerService
{
    /**
     * Trigger: Cambio de estado en Pasos de Crecimiento.
     */
    public function onCrecimientoUsuarioCambio(int $userId, int $pasoCrecimientoId, int $estadoId): array
    {
        return $this->buscarYAsignar(
            'pasos_crecimiento',
            'cambio_estado',
            $userId,
            ['paso_crecimiento_id' => $pasoCrecimientoId, 'estado_id' => $estadoId]
        );
    }

    /**
     * Trigger: Cambio de estado en Tareas de Consolidación.
     */
    public function onTareaConsolidacionCambio(int $userId, int $tareaConsolidacionId, int $estadoId): array
    {
        return $this->buscarYAsignar(
            'tareas_consolidacion',
            'cambio_estado',
            $userId,
            ['tarea_consolidacion_id' => $tareaConsolidacionId, 'estado_id' => $estadoId]
        );
    }

    /**
     * Trigger: Aprobación de materia (o nivel) en Escuelas.
     */
    public function onMateriaAprobada(int $userId, int $materiaId, int $nivelId = null): array
    {
        $asignados = $this->buscarYAsignar(
            'escuelas',
            'aprobacion_materia',
            $userId,
            ['materia_id' => $materiaId]
        );

        // Si también completó el nivel
        if ($nivelId) {
            $asignadosNivel = $this->buscarYAsignar(
                'escuelas',
                'aprobacion_nivel',
                $userId,
                ['nivel_id' => $nivelId]
            );
            $asignados = array_merge($asignados, $asignadosNivel);
        }

        return $asignados;
    }

    /**
     * Trigger: Asignación a grupo (integrante).
     */
    public function onAsignacionGrupoIntegrante(int $userId, int $tipoGrupoId, int $grupoId): array
    {
        return $this->buscarYAsignar(
            'grupos',
            'asignacion_integrante',
            $userId,
            ['tipo_grupo_id' => $tipoGrupoId],
            'grupo',
            $grupoId
        );
    }

    /**
     * Trigger: Designación como líder/encargado de grupo.
     */
    public function onDesignacionLiderGrupo(int $userId, int $tipoGrupoId, int $grupoId): array
    {
        return $this->buscarYAsignar(
            'grupos',
            'designacion_lider',
            $userId,
            ['tipo_grupo_id' => $tipoGrupoId],
            'grupo',
            $grupoId
        );
    }

    /**
     * Lógica central: buscar hitos automáticos que coincidan y asignar.
     */
    private function buscarYAsignar(
        string $modulo,
        string $tipo,
        int $userId,
        array $condiciones,
        string $origenTipo = 'automatico',
        int $origenId = null
    ): array {
        $hitos = Hito::where('origen', 'automatico')
            ->where('activo', true)
            ->where('trigger_modulo', $modulo)
            ->where('trigger_tipo', $tipo)
            ->get();

        $asignados = [];
        foreach ($hitos as $hito) {
            if ($this->condicionesCumplen($hito->trigger_config, $condiciones)) {
                if ($this->asignar($hito, $userId, $origenTipo, $origenId)) {
                    $asignados[] = $hito;
                }
            }
        }

        if (!empty($asignados)) {
            Log::info("HitoTriggerService: user={$userId} módulo={$modulo} tipo={$tipo} → " . count($asignados) . " hitos");
        }

        return $asignados;
    }

    private function condicionesCumplen(?array $config, array $condiciones): bool
    {
        if (!$config || empty($config)) return true;
        foreach ($config as $key => $valor) {
            if (!isset($condiciones[$key]) || $condiciones[$key] != $valor) return false;
        }
        return true;
    }

    private function asignar(Hito $hito, int $userId, string $origenTipo, int $origenId = null): bool
    {
        // Evitar duplicados (hito + user + origen)
        $existe = HitoUsuario::where('hito_id', $hito->id)
            ->where('user_id', $userId)
            ->where('origen_tipo', $origenTipo)
            ->where('origen_id', $origenId)
            ->exists();

        if ($existe) return false;

        HitoUsuario::create([
            'hito_id' => $hito->id,
            'user_id' => $userId,
            'fecha' => now()->toDateString(),
            'origen_tipo' => $origenTipo,
            'origen_id' => $origenId,
            'asignado_por' => null, // automático
        ]);

        return true;
    }

    /**
     * Migración retroactiva: asignar hitos ya configurados a usuarios que
     * actualmente cumplen las condiciones.
     */
    public function migrarRetroactivo(Hito $hito): int
    {
        $count = 0;

        if ($hito->trigger_modulo === 'pasos_crecimiento') {
            $count = $this->migrarPasosCrecimiento($hito);
        } elseif ($hito->trigger_modulo === 'tareas_consolidacion') {
            $count = $this->migrarTareasConsolidacion($hito);
        } elseif ($hito->trigger_modulo === 'escuelas') {
            $count = $this->migrarEscuelas($hito);
        } elseif ($hito->trigger_modulo === 'grupos') {
            $count = $this->migrarGrupos($hito);
        }

        return $count;
    }

    private function migrarPasosCrecimiento(Hito $hito): int
    {
        $config = $hito->trigger_config ?? [];
        if (empty($config['paso_crecimiento_id']) || empty($config['estado_id'])) return 0;

        $query = CrecimientoUsuario::where('paso_crecimiento_id', $config['paso_crecimiento_id'])
            ->where('estado_id', $config['estado_id']);

        $count = 0;
        foreach ($query->get() as $crecimiento) {
            if ($this->asignar($hito, $crecimiento->user_id, 'migracion_retroactiva', $crecimiento->id)) {
                $count++;
            }
        }
        return $count;
    }

    private function migrarTareasConsolidacion(Hito $hito): int
    {
        $config = $hito->trigger_config ?? [];
        if (empty($config['tarea_consolidacion_id']) || empty($config['estado_id'])) return 0;

        $query = TareaConsolidacionUsuario::where('tarea_consolidacion_id', $config['tarea_consolidacion_id'])
            ->where('estado_tarea_consolidacion_id', $config['estado_id']);

        $count = 0;
        foreach ($query->get() as $tarea) {
            if ($this->asignar($hito, $tarea->user_id, 'migracion_retroactiva', $tarea->id)) {
                $count++;
            }
        }
        return $count;
    }

    private function migrarEscuelas(Hito $hito): int
    {
        $config = $hito->trigger_config ?? [];
        $count = 0;

        if (!empty($config['materia_id'])) {
            $query = MateriaAprobadaUsuario::where('materia_id', $config['materia_id'])
                ->where('aprobado', true);
            foreach ($query->get() as $materia) {
                if ($this->asignar($hito, $materia->user_id, 'migracion_retroactiva', $materia->id)) {
                    $count++;
                }
            }
        }

        if (!empty($config['nivel_id'])) {
            $query = NivelAprobadoUsuario::where('nivel_id', $config['nivel_id']);
            foreach ($query->get() as $nivel) {
                if ($this->asignar($hito, $nivel->user_id, 'migracion_retroactiva', $nivel->id)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    private function migrarGrupos(Hito $hito): int
    {
        $config = $hito->trigger_config ?? [];
        if (empty($config['tipo_grupo_id'])) return 0;

        // Buscar todos los usuarios asignados a grupos de este tipo
        $query = User::query()
            ->whereHas('gruposComoIntegrante', function ($q) use ($config) {
                $q->whereIn('tipo_grupo_id', [$config['tipo_grupo_id']]);
            });

        if ($hito->trigger_tipo === 'designacion_lider') {
            $query = User::query()
                ->whereHas('gruposComoLider', function ($q) use ($config) {
                    $q->whereIn('tipo_grupo_id', [$config['tipo_grupo_id']]);
                });
        }

        $count = 0;
        foreach ($query->get() as $user) {
            if ($this->asignar($hito, $user->id, 'migracion_retroactiva', null)) {
                $count++;
            }
        }
        return $count;
    }
}
```

---

## 6. Paso 4 — Integración con Módulos Existentes (Triggers)

### 6.1. Trigger en Pasos de Crecimiento

**Archivo a modificar**: `app/Models/CrecimientoUsuario.php`

```php
use App\Services\HitoTriggerService;
use App\Models\User;

protected static function booted()
{
    static::created(function ($crecimiento) {
        $user = User::find($crecimiento->user_id);
        BitacoraCrecimientoUsuario::create([ /* ... existente ... */ ]);

        // NUEVO: Disparar hitos automáticos en creación
        app(HitoTriggerService::class)->onCrecimientoUsuarioCambio(
            $crecimiento->user_id,
            $crecimiento->paso_crecimiento_id,
            $crecimiento->estado_id
        );
    });

    static::updated(function ($crecimiento) {
        if ($crecimiento->isDirty('estado_id')) {
            // ... bitácora existente ...

            // NUEVO: Disparar hitos automáticos en cambio de estado
            app(HitoTriggerService::class)->onCrecimientoUsuarioCambio(
                $crecimiento->user_id,
                $crecimiento->paso_crecimiento_id,
                $crecimiento->estado_id
            );
        }
    });
}
```

### 6.2. Trigger en Tareas de Consolidación

**Archivo a modificar**: `app/Models/TareaConsolidacionUsuario.php`

```php
use App\Services\HitoTriggerService;

protected static function booted()
{
    static::created(function ($model) {
        static::registrarBitacora($model, 'creacion');
        // NUEVO
        app(HitoTriggerService::class)->onTareaConsolidacionCambio(
            $model->user_id,
            $model->tarea_consolidacion_id,
            $model->estado_tarea_consolidacion_id
        );
    });

    static::updated(function ($model) {
        if ($model->isDirty('estado_tarea_consolidacion_id')) {
            static::registrarBitacora($model, 'actualizacion');
            // NUEVO
            app(HitoTriggerService::class)->onTareaConsolidacionCambio(
                $model->user_id,
                $model->tarea_consolidacion_id,
                $model->estado_tarea_consolidacion_id
            );
        }
    });
}
```

### 6.3. Trigger en Escuelas (Aprobación de Materia)

**Archivo a modificar**: `app/Traits/AplicaEfectosAprobacion.php`

En `aplicarEfectosCulminacion()`, después del bloque de aprobación de nivel:

```php
// --- F. Disparar Hitos Automáticos ---
app(\App\Services\HitoTriggerService::class)->onMateriaAprobada(
    $userId,
    $materiaId,
    $materia->nivel_id ?? null
);
```

### 6.4. Trigger en Grupos

**Archivos a modificar**:
- `app/Livewire/Usuarios/UsuariosParaBusqueda.php` (línea ~279)
- `app/Http/Controllers/UserController.php` (línea ~4029)

Después de las automatizaciones existentes de tipo_usuario y pasos_crecimiento, agregar:

```php
// Integrante
app(\App\Services\HitoTriggerService::class)->onAsignacionGrupoIntegrante(
    $userId,
    $tipoGrupo->id,
    $grupo->id
);

// Si es líder/encargado
if ($esLider) {
    app(\App\Services\HitoTriggerService::class)->onDesignacionLiderGrupo(
        $userId,
        $tipoGrupo->id,
        $grupo->id
    );
}
```

### 6.5. Trigger en Actividades (Asistencia)

**Comportamiento por tipo de actividad** (lógica en `HitoController` o `ActividadController`):

```php
// Al guardar ReporteActividad (asistencia confirmada)
public function guardarAsistencia(Actividad $actividad, array $asistentesUserIds)
{
    // Buscar hitos asociados a esta actividad que requieren asistencia
    $hitos = Hito::where('origen', 'actividad')
        ->where('actividad_id', $actividad->id)
        ->where('requiere_asistencia', true)
        ->get();

    foreach ($asistentesUserIds as $userId) {
        foreach ($hitos as $hito) {
            HitoUsuario::firstOrCreate([
                'hito_id' => $hito->id,
                'user_id' => $userId,
                'origen_tipo' => 'actividad',
                'origen_id' => $actividad->id,
            ], [
                'fecha' => $actividad->fecha,
                'asistio' => true,
                'asignado_por' => auth()->id(),
            ]);
        }
    }
}

// Al crear actividad SIN asistencia (asignar_al_crear=true)
public function crearActividadConHito(Actividad $actividad, Hito $hito)
{
    if ($hito->asignar_al_crear) {
        $usuarios = User::where('activo', true)->get();
        foreach ($usuarios as $user) {
            HitoUsuario::firstOrCreate([
                'hito_id' => $hito->id,
                'user_id' => $user->id,
                'origen_tipo' => 'actividad',
                'origen_id' => $actividad->id,
            ], [
                'fecha' => $actividad->fecha,
                'asistio' => true,
                'asignado_por' => auth()->id(),
            ]);
        }
    }
}
```

---

## 7. Paso 5 — Permisos (Spatie)

### 7.1. `database/seeders/PermisoHitoSeeder.php`

```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermisoHitoSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
            'hitos.ver_muro'              => 'Ver muro de hitos',
            'hitos.gestionar'             => 'CRUD de hitos',
            'hitos.crear'                 => 'Crear hitos',
            'hitos.editar'                => 'Editar hitos',
            'hitos.eliminar'              => 'Eliminar hitos',
            'hitos.gestionar_denuncias'   => 'Gestionar denuncias',
            'hitos.gestionar_asistencia'  => 'Tomar asistencia en hitos de actividades',
            'hitos.subir_fotos'           => 'Subir fotos a hitos',
            'hitos.like'                  => 'Dar like a hitos',
            'hitos.migrar_retroactivo'    => 'Aplicar hito retroactivamente',
        ];

        foreach ($permisos as $nombre => $descripcion) {
            Permission::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
        }

        $this->command->info('Permisos de Hitos creados: ' . count($permisos));
    }
}
```

### 7.2. `app/Policies/HitoPolicy.php`

```php
namespace App\Policies;

use App\Models\Hito;
use App\Models\User;

class HitoPolicy
{
    public function verMuro(User $user): bool
    {
        return $user->can('hitos.ver_muro');
    }

    public function gestionar(User $user): bool
    {
        return $user->can('hitos.gestionar');
    }

    public function crear(User $user): bool
    {
        return $user->can('hitos.crear');
    }

    public function editar(User $user, Hito $hito): bool
    {
        return $user->can('hitos.editar') || $hito->user_id === $user->id;
    }

    public function eliminar(User $user, Hito $hito): bool
    {
        return $user->can('hitos.eliminar') || $hito->user_id === $user->id;
    }

    public function gestionarDenuncias(User $user): bool
    {
        return $user->can('hitos.gestionar_denuncias');
    }

    public function gestionarAsistencia(User $user): bool
    {
        return $user->can('hitos.gestionar_asistencia');
    }

    public function subirFotos(User $user): bool
    {
        return $user->can('hitos.subir_fotos');
    }

    public function migrarRetroactivo(User $user): bool
    {
        return $user->can('hitos.migrar_retroactivo');
    }
}
```

---

## 8. Paso 6 — Rutas

**Archivo a modificar**: `routes/web.php` o `routes/app.php`

```php
use App\Http\Controllers\HitoController;
use App\Livewire\Hitos\{GestionarHitos, CrearEditarHito, MuroHitos, PerfilHitos, GestionarDenuncias, GestionarAsistencias};

Route::middleware(['auth'])->prefix('hitos')->name('hitos.')->group(function () {
    // Admin
    Route::get('/', GestionarHitos::class)->name('index');
    Route::get('/crear', CrearEditarHito::class)->name('crear');
    Route::get('/{hito}/editar', CrearEditarHito::class)->name('editar');
    Route::get('/denuncias', GestionarDenuncias::class)->name('denuncias');
    Route::get('/{hito}/asistencia', GestionarAsistencias::class)->name('asistencia');

    // Usuarios
    Route::get('/muro', MuroHitos::class)->name('muro');
    Route::get('/perfil/{user}', PerfilHitos::class)->name('perfil');

    // API endpoints
    Route::post('/{hito}/like', [HitoController::class, 'toggleLike'])->name('like');
    Route::post('/{hito}/fotos', [HitoController::class, 'subirFoto'])->name('subir-foto');
    Route::post('/{hito}/denunciar', [HitoController::class, 'denunciar'])->name('denunciar');
    Route::post('/{hito}/migrar', [HitoController::class, 'migrarRetroactivo'])->name('migrar');
});
```

---

## 9. Paso 7 — Controller Principal

### 9.1. `app/Http/Controllers/HitoController.php`

```php
namespace App\Http\Controllers;

use App\Models\Hito;
use App\Models\HitoLike;
use App\Models\HitoDenuncia;
use App\Models\HitoFoto;
use App\Models\HitoUsuario;
use App\Services\HitoTriggerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HitoController extends Controller
{
    public function subirFoto(Request $request, Hito $hito)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:' . ($hito->max_peso_kb / 1024),
        ]);

        $this->authorize('subirFotos', $hito);

        if (!$hito->permite_fotos_usuario) {
            return response()->json(['error' => 'Este hito no permite fotos de usuarios'], 403);
        }

        $fotosUsuarioCount = $hito->fotosUsuario()->count();
        if ($fotosUsuarioCount >= $hito->max_fotos_usuario) {
            return response()->json(['error' => 'Máximo de fotos alcanzado'], 403);
        }

        $directorio = 'img/hitos/fotos';
        $extension = $request->file('foto')->getClientOriginalExtension();
        $nombreArchivo = 'hito-' . $hito->id . '-user-' . auth()->id() . '-' . time() . '.' . $extension;

        $request->file('foto')->storeAs($directorio, $nombreArchivo, 'public');

        $foto = HitoFoto::create([
            'hito_id' => $hito->id,
            'user_id' => auth()->id(),
            'ruta' => $nombreArchivo,
            'orden' => $fotosUsuarioCount,
            'es_admin' => false,
            'aprobada' => true,
        ]);

        return response()->json(['success' => true, 'foto' => $foto]);
    }

    public function toggleLike(Hito $hito)
    {
        $userId = auth()->id();
        $like = HitoLike::where('hito_id', $hito->id)->where('user_id', $userId)->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            HitoLike::create(['hito_id' => $hito->id, 'user_id' => $userId]);
            $liked = true;
        }

        return response()->json(['liked' => $liked, 'count' => $hito->likes()->count()]);
    }

    public function denunciar(Request $request, Hito $hito)
    {
        $request->validate([
            'motivo' => 'required|string|max:255',
            'foto_id' => 'nullable|exists:hito_fotos,id',
        ]);

        HitoDenuncia::create([
            'hito_id' => $hito->id,
            'foto_id' => $request->foto_id,
            'user_id' => auth()->id(),
            'motivo' => $request->motivo,
        ]);

        return response()->json(['success' => true]);
    }

    public function migrarRetroactivo(Hito $hito)
    {
        $this->authorize('migrarRetroactivo', $hito);

        $count = app(HitoTriggerService::class)->migrarRetroactivo($hito);

        return response()->json([
            'success' => true,
            'asignados' => $count,
            'mensaje' => "{$count} usuarios recibieron el hito retroactivamente",
        ]);
    }
}
```

---

## 10. Paso 8 — Componentes Livewire

### 10.1. `app/Livewire/Hitos/CrearEditarHito.php`

Formulario con tabs: Datos, Multimedia, Restricciones, Origen/Trigger.

```php
namespace App\Livewire\Hitos;

use App\Models\Hito;
use App\Models\HitoFoto;
use App\Models\Actividad;
use App\Models\PasoCrecimiento;
use App\Models\TareaConsolidacion;
use App\Models\Materia;
use App\Models\Nivel;
use App\Models\TipoGrupo;
use App\Models\EstadoPasoCrecimientoUsuario;
use App\Models\EstadoTareaConsolidacion;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

class CrearEditarHito extends Component
{
    use WithFileUploads;

    public $hitoId = null;
    public $titulo, $descripcion, $mensaje_usuario, $portada, $video_url;
    public $origen = 'general';

    // Para origen = 'actividad'
    public $actividad_id, $requiere_asistencia, $asignar_al_crear;

    // Para origen = 'automatico'
    public $trigger_modulo, $trigger_tipo;
    public $trigger_paso_crecimiento_id, $trigger_estado_paso_id;
    public $trigger_tarea_consolidacion_id, $trigger_estado_tarea_id;
    public $trigger_escuela_id, $trigger_nivel_id, $trigger_materia_id;

    // Configuración general
    public $permite_fotos_usuario = false;
    public $max_fotos_usuario = 3;
    public $max_peso_kb = 2048;
    public $requiere_sesion = true;
    public $activo = true;

    // Pivotes
    public $sedesSeleccionadas = [];
    public $estadosCivilesSeleccionados = [];
    public $rangosEdadSeleccionados = [];
    public $tiposUsuariosSeleccionados = [];
    public $grupoTiposSeleccionados = [];

    public $fotosAdmin = [];
    public $fotosAdminActuales = [];

    protected $rules = [
        'titulo' => 'required|string|max:150',
        'descripcion' => 'nullable|string',
        'mensaje_usuario' => 'nullable|string|max:1000',
        'portada' => 'nullable|image|max:5120',
        'video_url' => 'nullable|url|max:500',
    ];

    public function mount($hito = null)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $rolActivo->verificacionDelPermiso($hito ? 'hitos.editar' : 'hitos.crear');

        if ($hito) {
            $this->hitoId = $hito->id;
            $this->titulo = $hito->titulo;
            $this->descripcion = $hito->descripcion;
            $this->mensaje_usuario = $hito->mensaje_usuario;
            $this->video_url = $hito->video_url;
            $this->origen = $hito->origen;
            $this->actividad_id = $hito->actividad_id;
            $this->requiere_asistencia = $hito->requiere_asistencia;
            $this->asignar_al_crear = $hito->asignar_al_crear;
            $this->trigger_modulo = $hito->trigger_modulo;
            $this->trigger_tipo = $hito->trigger_tipo;
            $this->permite_fotos_usuario = $hito->permite_fotos_usuario;
            $this->max_fotos_usuario = $hito->max_fotos_usuario;
            $this->max_peso_kb = $hito->max_peso_kb;
            $this->requiere_sesion = $hito->requiere_sesion;
            $this->activo = $hito->activo;

            $config = $hito->trigger_config ?? [];
            $this->trigger_paso_crecimiento_id = $config['paso_crecimiento_id'] ?? null;
            $this->trigger_estado_paso_id = $config['estado_id'] ?? null;
            $this->trigger_tarea_consolidacion_id = $config['tarea_consolidacion_id'] ?? null;
            $this->trigger_estado_tarea_id = $config['estado_id'] ?? null;
            $this->trigger_escuela_id = $config['escuela_id'] ?? null;
            $this->trigger_nivel_id = $config['nivel_id'] ?? null;
            $this->trigger_materia_id = $config['materia_id'] ?? null;

            $this->sedesSeleccionadas = $hito->sedes->pluck('id')->toArray();
            $this->estadosCivilesSeleccionados = $hito->estadosCiviles->pluck('id')->toArray();
            $this->rangosEdadSeleccionados = $hito->rangosEdad->pluck('id')->toArray();
            $this->tiposUsuariosSeleccionados = $hito->tiposUsuarios->pluck('id')->toArray();
            $this->grupoTiposSeleccionados = $hito->grupoTipos->pluck('id')->toArray();
            $this->fotosAdminActuales = $hito->fotosAdmin()->get();
        }
    }

    public function updatedTriggerModulo()
    {
        // Reset sub-campos al cambiar de módulo
        $this->trigger_tipo = null;
        $this->trigger_paso_crecimiento_id = null;
        $this->trigger_estado_paso_id = null;
        $this->trigger_tarea_consolidacion_id = null;
        $this->trigger_estado_tarea_id = null;
        $this->trigger_escuela_id = null;
        $this->trigger_nivel_id = null;
        $this->trigger_materia_id = null;
    }

    public function updatedTriggerEscuelaId()
    {
        $this->trigger_nivel_id = null;
        $this->trigger_materia_id = null;
    }

    public function guardar()
    {
        $this->validate();

        $data = [
            'user_id' => auth()->id(),
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'mensaje_usuario' => $this->mensaje_usuario,
            'video_url' => $this->video_url,
            'origen' => $this->origen,
            'permite_fotos_usuario' => $this->permite_fotos_usuario,
            'max_fotos_usuario' => $this->max_fotos_usuario,
            'max_peso_kb' => $this->max_peso_kb,
            'requiere_sesion' => $this->requiere_sesion,
            'activo' => $this->activo,
        ];

        // Según el origen
        if ($this->origen === 'actividad') {
            $data['actividad_id'] = $this->actividad_id;
            $data['requiere_asistencia'] = $this->requiere_asistencia;
            $data['asignar_al_crear'] = $this->asignar_al_crear;
            $data['trigger_modulo'] = null;
            $data['trigger_tipo'] = null;
            $data['trigger_config'] = null;
        } elseif ($this->origen === 'automatico') {
            $data['actividad_id'] = null;
            $data['requiere_asistencia'] = false;
            $data['asignar_al_crear'] = false;
            $data['trigger_modulo'] = $this->trigger_modulo;
            $data['trigger_tipo'] = $this->trigger_tipo;
            $data['trigger_config'] = $this->buildTriggerConfig();
        } else { // general
            $data['actividad_id'] = null;
            $data['requiere_asistencia'] = false;
            $data['asignar_al_crear'] = false;
            $data['trigger_modulo'] = null;
            $data['trigger_tipo'] = null;
            $data['trigger_config'] = null;
        }

        if ($this->portada) {
            $nombrePortada = Str::slug($this->titulo) . '-portada-' . time() . '.' . $this->portada->getClientOriginalExtension();
            $this->portada->storeAs('img/hitos/portadas', $nombrePortada, 'public');
            $data['portada_path'] = $nombrePortada;
        }

        if ($this->hitoId) {
            $hito = Hito::findOrFail($this->hitoId);
            if ($this->portada && $hito->portada_path) {
                Storage::disk('public')->delete('img/hitos/portadas/' . $hito->portada_path);
            }
            $hito->update($data);
        } else {
            $hito = Hito::create($data);
        }

        $hito->sedes()->sync($this->sedesSeleccionadas);
        $hito->estadosCiviles()->sync($this->estadosCivilesSeleccionados);
        $hito->rangosEdad()->sync($this->rangosEdadSeleccionados);
        $hito->tiposUsuarios()->sync($this->tiposUsuariosSeleccionados);
        $hito->grupoTipos()->sync($this->grupoTiposSeleccionados);

        foreach ($this->fotosAdmin as $index => $foto) {
            $nombreFoto = 'hito-' . $hito->id . '-admin-' . time() . '-' . $index . '.' . $foto->getClientOriginalExtension();
            $foto->storeAs('img/hitos/fotos', $nombreFoto, 'public');
            HitoFoto::create([
                'hito_id' => $hito->id,
                'user_id' => auth()->id(),
                'ruta' => $nombreFoto,
                'orden' => $index,
                'es_admin' => true,
                'aprobada' => true,
            ]);
        }

        session()->flash('success', 'Hito guardado exitosamente');
        return redirect()->route('hitos.index');
    }

    private function buildTriggerConfig(): ?array
    {
        $config = [];

        if ($this->trigger_modulo === 'pasos_crecimiento') {
            $this->trigger_tipo = 'cambio_estado';
            $config['paso_crecimiento_id'] = $this->trigger_paso_crecimiento_id;
            $config['estado_id'] = $this->trigger_estado_paso_id;
        } elseif ($this->trigger_modulo === 'tareas_consolidacion') {
            $this->trigger_tipo = 'cambio_estado';
            $config['tarea_consolidacion_id'] = $this->trigger_tarea_consolidacion_id;
            $config['estado_id'] = $this->trigger_estado_tarea_id;
        } elseif ($this->trigger_modulo === 'escuelas') {
            if ($this->trigger_materia_id) {
                $this->trigger_tipo = 'aprobacion_materia';
                $config['materia_id'] = $this->trigger_materia_id;
            } elseif ($this->trigger_nivel_id) {
                $this->trigger_tipo = 'aprobacion_nivel';
                $config['nivel_id'] = $this->trigger_nivel_id;
            }
        } elseif ($this->trigger_modulo === 'grupos') {
            $this->trigger_tipo = $this->trigger_tipo; // El usuario selecciona
            $config['tipo_grupo_id'] = $this->trigger_tipo_tipo_grupo_id ?? null;
        }

        return empty($config) ? null : $config;
    }

    public function render()
    {
        $actividades = Actividad::orderBy('nombre')->get();
        $pasosCrecimiento = PasoCrecimiento::orderBy('orden')->get();
        $estadosPasos = EstadoPasoCrecimientoUsuario::orderBy('puntaje')->get();
        $tareasConsolidacion = TareaConsolidacion::orderBy('orden')->get();
        $estadosTareas = EstadoTareaConsolidacion::orderBy('puntaje')->get();
        $escuelas = Escuela::orderBy('nombre')->get();
        $niveles = $this->trigger_escuela_id
            ? Nivel::where('escuela_id', $this->trigger_escuela_id)->get()
            : collect();
        $materias = $this->trigger_nivel_id
            ? Materia::where('nivel_id', $this->trigger_nivel_id)->get()
            : collect();
        $tiposGrupo = TipoGrupo::orderBy('nombre')->get();

        return view('livewire.hitos.crear-editar-hito', compact(
            'actividades', 'pasosCrecimiento', 'estadosPasos', 'tareasConsolidacion',
            'estadosTareas', 'escuelas', 'niveles', 'materias', 'tiposGrupo'
        ));
    }
}
```

### 10.2. `app/Livewire/Hitos/GestionarAsistencias.php`

Para que el admin tome asistencia de hitos basados en actividades:

```php
namespace App\Livewire\Hitos;

use App\Models\Hito;
use App\Models\HitoUsuario;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class GestionarAsistencias extends Component
{
    use WithPagination;

    public Hito $hito;
    public $search = '';

    public function mount(Hito $hito)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $rolActivo->verificacionDelPermiso('hitos.gestionar_asistencia');
        $this->hito = $hito;
    }

    public function marcarAsistente($userId, $asistio = true)
    {
        HitoUsuario::updateOrCreate(
            [
                'hito_id' => $this->hito->id,
                'user_id' => $userId,
                'origen_tipo' => 'actividad',
                'origen_id' => $this->hito->actividad_id,
            ],
            [
                'fecha' => $this->hito->actividad->fecha ?? now()->toDateString(),
                'asistio' => $asistio,
                'asignado_por' => auth()->id(),
            ]
        );

        $this->dispatch('msn', ['tipo' => 'success', 'mensaje' => $asistio ? 'Asistencia confirmada' : 'Asistencia removida']);
    }

    public function render()
    {
        $usuarios = User::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy('name')
            ->paginate(20);

        // Mapear estado de asignación
        $estadosAsistencia = HitoUsuario::where('hito_id', $this->hito->id)
            ->where('origen_tipo', 'actividad')
            ->whereIn('user_id', $usuarios->pluck('id'))
            ->pluck('asistio', 'user_id');

        return view('livewire.hitos.gestionar-asistencias', [
            'usuarios' => $usuarios,
            'estadosAsistencia' => $estadosAsistencia,
        ]);
    }
}
```

---

## 11. Paso 9 — Vistas Blade

### 11.1. Vista del formulario de creación con tabs (resumen)

```blade
{{-- resources/views/livewire/hitos/crear-editar-hito.blade.php --}}

<form wire:submit.prevent="guardar">
    <ul class="nav nav-tabs" role="tablist">
        <li><a class="nav-link active" data-bs-toggle="tab" href="#tab-datos">Datos</a></li>
        <li><a class="nav-link" data-bs-toggle="tab" href="#tab-multimedia">Multimedia</a></li>
        <li><a class="nav-link" data-bs-toggle="tab" href="#tab-origen">Origen</a></li>
        <li><a class="nav-link" data-bs-toggle="tab" href="#tab-restricciones">Restricciones</a></li>
    </ul>

    <div class="tab-content">
        {{-- TAB 1: DATOS --}}
        <div id="tab-datos" class="tab-pane active">
            <div class="mb-3">
                <label class="form-label">Título *</label>
                <input wire:model="titulo" class="form-control">
                @error('titulo') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea wire:model="descripcion" class="form-control" rows="4"></textarea>
            </div>
        </div>

        {{-- TAB 2: MULTIMEDIA --}}
        <div id="tab-multimedia" class="tab-pane">
            <div class="mb-3">
                <label class="form-label">Portada</label>
                <input type="file" wire:model="portada" accept="image/*" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">URL Video (YouTube/Vimeo)</label>
                <input wire:model="video_url" class="form-control" placeholder="https://...">
            </div>
            <div class="mb-3">
                <label class="form-label">Fotos administrativas (hasta 20)</label>
                <input type="file" wire:model="fotosAdmin" accept="image/*" multiple class="form-control">
            </div>
        </div>

        {{-- TAB 3: ORIGEN --}}
        <div id="tab-origen" class="tab-pane">
            <div class="mb-3">
                <label class="form-label">Tipo de Origen *</label>
                <select wire:model.live="origen" class="form-select">
                    <option value="general">General (Manual)</option>
                    <option value="automatico">Automático (Trigger)</option>
                    <option value="actividad">De una Actividad</option>
                </select>
            </div>

            @if($origen === 'actividad')
                <div class="mb-3">
                    <label class="form-label">Actividad asociada *</label>
                    <select wire:model="actividad_id" class="form-select" id="select-hitos-actividad">
                        <option value="">Seleccione...</option>
                        @foreach($actividades as $a)
                            <option value="{{ $a->id }}">{{ $a->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" wire:model="requiere_asistencia" id="reqAsistencia">
                    <label class="form-check-label" for="reqAsistencia">
                        Requiere confirmación de asistencia
                    </label>
                </div>
                @if(!$requiere_asistencia)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" wire:model="asignar_al_crear" id="asignarAlCrear">
                        <label class="form-check-label" for="asignarAlCrear">
                            Asignar a todos los usuarios al guardar
                        </label>
                    </div>
                @endif
            @endif

            @if($origen === 'automatico')
                <div class="mb-3">
                    <label class="form-label">Módulo disparador *</label>
                    <select wire:model.live="trigger_modulo" class="form-select">
                        <option value="">Seleccione...</option>
                        <option value="pasos_crecimiento">Pasos de Crecimiento</option>
                        <option value="tareas_consolidacion">Tareas de Consolidación</option>
                        <option value="escuelas">Escuelas</option>
                        <option value="grupos">Grupos</option>
                    </select>
                </div>

                @if($trigger_modulo === 'pasos_crecimiento')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Paso de crecimiento *</label>
                            <select wire:model="trigger_paso_crecimiento_id" class="form-select">
                                <option value="">Seleccione...</option>
                                @foreach($pasosCrecimiento as $p)
                                    <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estado requerido *</label>
                            <select wire:model="trigger_estado_paso_id" class="form-select">
                                <option value="">Seleccione...</option>
                                @foreach($estadosPasos as $e)
                                    <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif

                @if($trigger_modulo === 'tareas_consolidacion')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tarea *</label>
                            <select wire:model="trigger_tarea_consolidacion_id" class="form-select">
                                <option value="">Seleccione...</option>
                                @foreach($tareasConsolidacion as $t)
                                    <option value="{{ $t->id }}">{{ $t->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estado requerido *</label>
                            <select wire:model="trigger_estado_tarea_id" class="form-select">
                                <option value="">Seleccione...</option>
                                @foreach($estadosTareas as $e)
                                    <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif

                @if($trigger_modulo === 'escuelas')
                    <div class="mb-3">
                        <label class="form-label">Escuela *</label>
                        <select wire:model.live="trigger_escuela_id" class="form-select">
                            <option value="">Seleccione...</option>
                            @foreach($escuelas as $e)
                                <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Materia (opcional, específica)</label>
                        <select wire:model.live="trigger_materia_id" class="form-select" {{ !$trigger_escuela_id ? 'disabled' : '' }}>
                            <option value="">Todas las materias de la escuela</option>
                            @foreach($materias as $m)
                                <option value="{{ $m->id }}">{{ $m->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if($trigger_modulo === 'grupos')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de grupo *</label>
                            <select wire:model="trigger_tipo_grupo_id" class="form-select">
                                <option value="">Seleccione...</option>
                                @foreach($tiposGrupo as $t)
                                    <option value="{{ $t->id }}">{{ $t->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rol del usuario *</label>
                            <select wire:model="trigger_tipo" class="form-select">
                                <option value="asignacion_integrante">Integrante</option>
                                <option value="designacion_lider">Líder / Encargado</option>
                            </select>
                        </div>
                    </div>
                @endif
            @endif
        </div>

        {{-- TAB 4: RESTRICCIONES --}}
        <div id="tab-restricciones" class="tab-pane">
            {{-- Mismos selectores que Post::crear-editar --}}
            <div class="mb-3">
                <label class="form-label">Sedes</label>
                <select wire:model="sedesSeleccionadas" multiple class="form-select" id="select-hitos-sedes">
                    @foreach(\App\Models\Sede::all() as $sede)
                        <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                    @endforeach
                </select>
            </div>
            {{-- ... otras restricciones ... --}}
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">Guardar Hito</button>
        <a href="{{ route('hitos.index') }}" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

@script
<script>
    $(function() {
        $('#select-hitos-actividad, #select-hitos-sedes').select2({
            placeholder: 'Seleccione...',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endscript
```

---

## 12. Paso 10 — Migración Retroactiva

### 12.1. Botón en el admin de Hitos

**Vista**: `livewire/hitos/gestionar-hitos.blade.php`

```blade
@if($hito->esAutomatico())
    <button wire:click="migrar({{ $hito->id }})" class="btn btn-sm btn-warning">
        <i class="ri-history-line"></i> Aplicar a usuarios existentes
    </button>
@endif
```

### 12.2. Acción en `GestionarHitos`

```php
public function migrar($hitoId)
{
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('hitos.migrar_retroactivo');

    $hito = Hito::findOrFail($hitoId);
    $count = app(HitoTriggerService::class)->migrarRetroactivo($hito);

    $this->dispatch('msn', [
        'tipo' => 'success',
        'mensaje' => "{$count} usuarios recibieron el hito retroactivamente"
    ]);
}
```

---

## 13. Paso 11 — Integración con Tab "Hitos" del Perfil

**Archivo a modificar**: `resources/views/contenido/paginas/usuario/perfil.blade.php` (línea 499-501)

```blade
@can('verPerfilUsuarioPolitica', [$usuario, 'hitos'])
<li class="nav-item flex-fill">
    <a id="tap-otro3" href="{{ route('hitos.perfil', $usuario) }}"
       class="nav-link p-3 waves-effect waves-light" data-tap="otro3">
        <i class='ti-xs ti ti-album me-2'></i> Hitos
    </a>
</li>
@endcan
```

---

## 14. Paso 12 — Pruebas Manuales

### Por Tipo de Origen

| Caso | Acción | Resultado esperado |
|------|--------|-------------------|
| 1. General | Crear hito general con portada y 5 fotos admin | Aparece en muro de usuarios restringidos |
| 2. General | Subir 3 fotos de usuario (2MB) | Aparece en galería |
| 3. General | Intentar subir 4ta foto | Error: máximo alcanzado |
| 4. General | Subir foto de 3MB | Error: excede peso |
| 5. Actividad SIN asistencia | Crear hito de actividad con `asignar_al_crear=true` | Todos los usuarios restringidos reciben el hito |
| 6. Actividad CON asistencia | Crear hito de actividad con `requiere_asistencia=true` | NO aparece hasta confirmar asistencia |
| 7. Actividad CON asistencia | Confirmar asistencia de un usuario en la actividad | Ese usuario recibe el hito |
| 8. Pasos crecimiento | Crear hito automático: paso "Bienvenida" + estado "Finalizado" | Al cambiar al estado, se asigna |
| 9. Consolidación | Crear hito automático: tarea X + estado Y | Al cambiar al estado, se asigna |
| 10. Escuelas | Crear hito: materia "Bautismo" | Al aprobar la materia, se asigna |
| 11. Escuelas | Crear hito: nivel completo | Al completar todas las materias, se asigna |
| 12. Grupos integrante | Crear hito: tipo_grupo=1 + asignacion_integrante | Al agregar al grupo, se asigna |
| 13. Grupos líder | Crear hito: tipo_grupo=1 + designacion_lider | Al ser líder, se asigna (distinto del integrante) |
| 14. Migración retroactiva | Crear hito automático y aplicar | Usuarios existentes que ya cumplen reciben el hito |
| 15. Restricciones | Crear hito con sede=2 | Usuarios de otras sedes no lo ven |
| 16. Desactivar | Desactivar hito | No aparece en muro pero sigue en BD |
| 17. Like | Dar y quitar like | Contador se actualiza |
| 18. Denuncia | Denunciar hito | Aparece en admin de denuncias |
| 19. Admin resuelve | Marcar denuncia como resuelta | Desaparece de pendientes |
| 20. Admin elimina foto | Eliminar foto de usuario denunciada | Se borra del storage y BD |

---

## 15. Orden de Ejecución Recomendado

| # | Tarea | Tiempo |
|---|-------|--------|
| 1 | 10 migraciones | 1h |
| 2 | 5 modelos | 1.5h |
| 3 | `HitoTriggerService` con migración retroactiva | 2h |
| 4 | Hooks en modelos existentes (4 archivos) | 2h |
| 5 | Seeder de permisos + Policy | 30min |
| 6 | `HitoController` (4 acciones) | 1.5h |
| 7 | `CrearEditarHito` Livewire con 4 tabs | 4h |
| 8 | `GestionarHitos` Livewire | 1.5h |
| 9 | `MuroHitos` Livewire | 3h |
| 10 | `PerfilHitos` Livewire | 1.5h |
| 11 | `GestionarDenuncias` Livewire | 1.5h |
| 12 | `GestionarAsistencias` Livewire | 2h |
| 13 | Vistas Blade (crear-editar, muro, etc.) | 4h |
| 14 | JavaScript / Alpine / animaciones | 2h |
| 15 | Integración con tab "Hitos" del perfil | 30min |
| 16 | Pruebas manuales | 2h |
| 17 | Ajustes responsive (mobile) | 1.5h |
| **Total** | | **~30 horas** |

---

## 16. Comandos Manuales (desarrollador ejecuta)

```bash
cd /var/www/html/REDIL-CLOUD
php artisan tenant:migrate
php artisan tenant:db --seed --class=PermisoHitoSeeder
php artisan cache:clear
php artisan config:clear
php artisan view:clear
npm run build
```

---

**Última actualización**: Junio 2026 (revisión 2)
**Versión del documento**: 2.0
**Cambios vs 1.0**:
- Campo `origen` unificado (general/automático/actividad)
- Hitos de actividad con `requiere_asistencia` y `asignar_al_crear`
- Líder e integrante son **dos hitos diferentes**
- `HitoTriggerService` con `migrarRetroactivo()`
- 3 vistas y 1 componente nuevos
