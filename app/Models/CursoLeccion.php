<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CursoLeccion extends Model
{
    use HasFactory;

    protected $table = 'curso_lecciones';

    protected $fillable = [
        'contenido_html',
        'video_url',
        'video_plataforma',
        'archivo_path',
        'iframe_code',
    ];

    public function item()
    {
        return $this->morphOne(CursoItem::class, 'itemable');
    }

    public function getVideoIdAttribute()
    {
        if ($this->video_plataforma === 'youtube') {
            parse_str(parse_url($this->video_url, PHP_URL_QUERY), $params);
            return $params['v'] ?? null;
        } elseif ($this->video_plataforma === 'vimeo') {
            return (int) substr(parse_url($this->video_url, PHP_URL_PATH), 1);
        }
        return null; 
    }

    public function getFileExtensionAttribute()
    {
        return $this->archivo_path ? pathinfo($this->archivo_path, PATHINFO_EXTENSION) : null;
    }

    public function getEsImagenAttribute()
    {
        return in_array(strtolower($this->file_extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    public function getEsPdfAttribute()
    {
        return strtolower($this->file_extension) === 'pdf';
    }

    public function getEsPresentacionAttribute()
    {
        return in_array(strtolower($this->file_extension), ['ppt', 'pptx']);
    }
}
