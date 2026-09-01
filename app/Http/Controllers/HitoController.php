<?php

namespace App\Http\Controllers;

use App\Models\Escuela;
use App\Models\Hito;
use App\Models\HitoDenuncia;
use App\Models\HitoFoto;
use App\Models\HitoLike;
use App\Models\Materia;
use App\Models\NivelEscuela;
use App\Services\HitoTriggerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HitoController extends Controller
{
    /**
     * Renderiza el Muro / Línea de Vida interactiva (Túnel del Tiempo 3D).
     */
    public function muro()
    {
        $user = auth()->user();

        // Obtener hitos activos validados para el usuario autenticado
        $hitosQuery = Hito::query()
            ->activos()
            ->with(['tipoHito', 'autor', 'fotosAdmin', 'fotosUsuario', 'likes', 'usuariosAsignados']);

        if ($user) {
            $hitosQuery->forUser($user);
        }

        $hitos = $hitosQuery->orderBy('fecha_evento', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // Mapear cada hito al formato de la experiencia interactiva
        $zigZagX = [24, 73, 34, 79, 28, 66, 20, 75];
        $tilts = [-1.3, 1.1, -0.6, 1.5, -1.0, 0.7, -1.5, 1.0];

        $events = $hitos->values()->map(function ($hito, $index) use ($user, $zigZagX, $tilts) {
            // Si el usuario tiene una asignación personalizada en este hito (Reconocimiento manual)
            $asignacion = $user ? $hito->usuariosAsignados->firstWhere('id', $user->id) : null;
            $fechaPersonalizada = null;

            if ($asignacion && ! empty($asignacion->pivot->fecha)) {
                $fechaPersonalizada = \Carbon\Carbon::parse($asignacion->pivot->fecha);
            } elseif ($user && $hito->trigger_modulo === 'escuelas' && is_array($hito->trigger_config)) {
                if (! empty($hito->trigger_config['materia_id'])) {
                    $regMateria = MateriaAprobadaUsuario::where('user_id', $user->id)
                        ->where('materia_id', $hito->trigger_config['materia_id'])
                        ->where('aprobado', MateriaAprobadaUsuario::ESTADO_APROBADO)
                        ->first();
                    if ($regMateria) {
                        $fechaAprob = $regMateria->fecha_homologacion_aprobacion
                            ?? $regMateria->fecha_homologacion
                            ?? $regMateria->created_at;
                        if ($fechaAprob) {
                            $fechaPersonalizada = \Carbon\Carbon::parse($fechaAprob);
                        }
                    }
                } elseif (! empty($hito->trigger_config['nivel_id'])) {
                    $regNivel = NivelAprobadoUsuario::where('user_id', $user->id)
                        ->where('nivel_id', $hito->trigger_config['nivel_id'])
                        ->where('aprobado', NivelAprobadoUsuario::ESTADO_APROBADO)
                        ->first();
                    if ($regNivel) {
                        $fechaAprob = $regNivel->fecha_homologacion_aprobacion
                            ?? $regNivel->fecha_homologacion
                            ?? $regNivel->created_at;
                        if ($fechaAprob) {
                            $fechaPersonalizada = \Carbon\Carbon::parse($fechaAprob);
                        }
                    }
                }
            }

            if (! $fechaPersonalizada) {
                $fechaPersonalizada = $hito->fecha_evento;
            }

            $year = $fechaPersonalizada ? $fechaPersonalizada->format('Y') : date('Y');
            $dateFormatted = $fechaPersonalizada ? $fechaPersonalizada->translatedFormat('d M, Y') : 'Fecha general';

            $mensajePersonalizado = ($asignacion && ! empty($asignacion->pivot->nota_personalizada))
                ? $asignacion->pivot->nota_personalizada
                : $hito->mensaje_usuario;

            // Likes
            $likesCount = $hito->likes->count();
            $liked = $user ? $hito->likes->where('user_id', $user->id)->isNotEmpty() : false;

            // Galería de fotos (oficiales + usuario aprobadas)
            $galeria = $hito->fotosAdmin->map(fn ($f) => ['id' => $f->id, 'url' => $f->url, 'es_admin' => true])
                ->concat($hito->fotosUsuario->where('aprobada', true)->map(fn ($f) => ['id' => $f->id, 'url' => $f->url, 'es_admin' => false, 'user_id' => $f->user_id]))
                ->values();

            // Identificar datos de la materia o nivel requerido si es de escuelas
            $detalleRequisito = null;
            if ($hito->trigger_modulo === 'escuelas' && is_array($hito->trigger_config)) {
                if (! empty($hito->trigger_config['materia_id'])) {
                    $mat = Materia::find($hito->trigger_config['materia_id']);
                    if ($mat) {
                        $esc = Escuela::find($mat->escuela_id);
                        $detalleRequisito = '📚 Materia Requerida: '.$mat->nombre.($esc ? ' ('.$esc->nombre.')' : '');
                    }
                } elseif (! empty($hito->trigger_config['nivel_id'])) {
                    $niv = NivelEscuela::find($hito->trigger_config['nivel_id']);
                    if ($niv) {
                        $detalleRequisito = '🎓 Nivel Requerido: '.$niv->nombre;
                    }
                }
            }

            $descripcionFinal = $hito->descripcion ?: 'Sin descripción detallada.';
            if ($detalleRequisito) {
                $descripcionFinal .= "\n\n[".$detalleRequisito.']';
            }

            return [
                'id' => $hito->id,
                'year' => (int) $year,
                'title' => $hito->titulo,
                'category' => $hito->tipoHito->nombre ?? 'General',
                'color' => $hito->tipoHito->color ?? '#f2c66d',
                'icon' => $hito->tipoHito->icono ?? 'ti ti-award',
                'date' => $dateFormatted,
                'summary' => $hito->descripcion ? Str::limit(strip_tags($hito->descripcion), 100) : 'Momento especial en tu camino espiritual.',
                'description' => $descripcionFinal,
                'detalles_trigger' => $detalleRequisito,
                'mensaje_usuario' => $mensajePersonalizado,
                'image' => $hito->portada_path ? $hito->portada_url : asset('assets/img/illustrations/page-pricing-enterprise.png'),
                'video_url' => $hito->video_url,
                'permite_fotos_usuario' => (bool) $hito->permite_fotos_usuario,
                'max_fotos_usuario' => (int) ($hito->max_fotos_usuario ?? 3),
                'likes' => $likesCount,
                'liked' => $liked,
                'fotos' => $galeria,
                'xPosition' => $zigZagX[$index % count($zigZagX)],
                'tilt' => $tilts[$index % count($tilts)],
            ];
        });

        return view('contenido.paginas.hitos.muro-demo', [
            'events' => $events,
            'hitosRaw' => $hitos,
        ]);
    }

    /**
     * Alterna el estado de Me Gusta (Like) para el usuario autenticado.
     */
    public function toggleLike(Hito $hito): JsonResponse
    {
        $userId = auth()->id();
        if (! $userId) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        $like = HitoLike::where('hito_id', $hito->id)->where('user_id', $userId)->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            HitoLike::create([
                'hito_id' => $hito->id,
                'user_id' => $userId,
            ]);
            $liked = true;
        }

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'total_likes' => $hito->likes()->count(),
        ]);
    }

    /**
     * Permite al feligrés subir fotos a un hito (si está habilitado).
     */
    public function subirFoto(Request $request, Hito $hito): JsonResponse
    {
        $userId = auth()->id();
        if (! $userId) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        if (! $hito->permite_fotos_usuario) {
            return response()->json(['error' => 'Este hito no permite fotos de usuarios'], 403);
        }

        $maxPesoKb = $hito->max_peso_kb ?? 2048;
        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png,webp|max:'.$maxPesoKb,
        ]);

        $fotosUsuarioCount = $hito->fotosUsuario()->where('user_id', $userId)->count();
        if ($fotosUsuarioCount >= ($hito->max_fotos_usuario ?? 3)) {
            return response()->json(['error' => 'Has alcanzado el límite máximo de fotos para este hito'], 422);
        }

        $directorio = 'img/hitos/fotos';
        $file = $request->file('foto');
        $extension = $file->getClientOriginalExtension();
        $nombreArchivo = 'hito-'.$hito->id.'-user-'.$userId.'-'.time().'-'.Str::random(6).'.'.$extension;

        $file->storeAs($directorio, $nombreArchivo, 'public');

        $foto = HitoFoto::create([
            'hito_id' => $hito->id,
            'user_id' => $userId,
            'ruta' => $nombreArchivo,
            'orden' => $fotosUsuarioCount,
            'es_admin' => false,
            'aprobada' => true,
        ]);

        return response()->json([
            'success' => true,
            'mensaje' => 'Foto subida correctamente',
            'foto' => [
                'id' => $foto->id,
                'url' => $foto->url,
            ],
        ]);
    }

    /**
     * Registra una denuncia o reporte de contenido/foto.
     */
    public function denunciar(Request $request, Hito $hito): JsonResponse
    {
        $userId = auth()->id();
        if (! $userId) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        $request->validate([
            'motivo' => 'required|string|max:255',
            'foto_id' => 'nullable|exists:hito_fotos,id',
        ]);

        HitoDenuncia::create([
            'hito_id' => $hito->id,
            'foto_id' => $request->foto_id,
            'user_id' => $userId,
            'motivo' => $request->motivo,
            'estado' => 'pendiente',
        ]);

        return response()->json([
            'success' => true,
            'mensaje' => 'Tu reporte ha sido enviado al equipo de moderación. Gracias por ayudarnos a mantener segura la comunidad.',
        ]);
    }

    /**
     * Ejecuta la asignación retroactiva para un hito automático.
     */
    public function migrarRetroactivo(Hito $hito): JsonResponse
    {
        $this->authorize('migrarRetroactivo', $hito);

        $count = app(HitoTriggerService::class)->migrarRetroactivo($hito);

        return response()->json([
            'success' => true,
            'asignados' => $count,
            'mensaje' => "Se asignó el hito retroactivamente a {$count} usuario(s) que cumplían los requisitos.",
        ]);
    }

    /**
     * Activa o desactiva un hito (toggle).
     */
    public function toggleActivo(Hito $hito): JsonResponse
    {
        $this->authorize('editar', $hito);

        $hito->activo = ! $hito->activo;
        $hito->save();

        return response()->json([
            'success' => true,
            'activo' => $hito->activo,
            'mensaje' => $hito->activo ? 'Hito activado' : 'Hito desactivado',
        ]);
    }
}
