<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VersiculoDiario extends Model
{
    use HasFactory;

    protected $table = 'versiculos_diarios';

    /*protected $fillable = [
        'version_uri',
        'libro_nombre',
        'cita_referencia',
        'texto_versiculo',
        'ruta_imagen',
        'url_video_reflexion',
        'fecha_publicacion',
    ];*/

    protected $guarded = [];

    protected $casts = [
        'fecha_publicacion' => 'date',
        'texto_versiculo' => 'array',
    ];

    /**
     * Get the long citation (Full Book Name + Reference).
     * Automatically removes book abbreviation from the reference if present to avoid duplication.
     */
    public function getCitaLargaAttribute(): string
    {
        $partes = explode(' ', $this->cita_referencia, 2);
        
        // Si la primera parte es una abreviatura (solo mayúsculas, ej: NM, GEN, JN)
        if (count($partes) > 1 && preg_match('/^[A-Z]{1,5}$/', $partes[0])) {
            return $this->libro_nombre . ' ' . $partes[1];
        }

        return $this->libro_nombre . ' ' . $this->cita_referencia;
    }

    /**
     * Users who liked this verse.
     */
    public function usuariosQueDieronLike(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'versiculo_usuario_like', 'versiculo_diario_id', 'usuario_id')
                    ->withTimestamps();
    }
    
    public function getImagenUrlAttribute(): ?string
    {
        if (!$this->ruta_imagen) {
            return null;
        }

        $path = 'img/versiculo-diario/' . $this->ruta_imagen;

        // 1. Prioridad: Tenant Storage
        if (\Illuminate\Support\Facades\Storage::disk()->exists($path)) {
            return tenant_asset($path);
        }

        // 2. Fallback: Global Media
        if (\Illuminate\Support\Facades\Storage::disk('global_media')->exists($this->ruta_imagen)) {
            return \Illuminate\Support\Facades\Storage::disk('global_media')->url($this->ruta_imagen);
        }

        // 3. Fallback: Global Media con extensiones alternativas
        $baseName = pathinfo($this->ruta_imagen, PATHINFO_FILENAME);
        foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
            $altPath = $baseName . '.' . $ext;
            if (\Illuminate\Support\Facades\Storage::disk('global_media')->exists($altPath)) {
                return \Illuminate\Support\Facades\Storage::disk('global_media')->url($altPath);
            }
        }

        return null;
    }
}
