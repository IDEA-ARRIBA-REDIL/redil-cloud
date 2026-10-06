<?php

namespace App\Http\Controllers;

use App\Mail\DefaultMail;
use App\Models\Configuracion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use stdClass;

class CumpleanosController extends Controller
{
    /**
     * Muestra la lista completa de cumpleaños según los filtros aplicados.
     */
    public function listarCumpleanos(Request $request)
    {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
        $configuracion = Configuracion::first();
        $personas = collect();

        if ($rolActivo) {
            if (
                $rolActivo->hasPermissionTo('personas.lista_asistentes_todos') ||
                $rolActivo->hasPermissionTo('personas.lista_asistentes_solo_ministerio')
            ) {
                // 1. Si solo tiene permiso de ministerio, obtenemos sus discípulos activos
                if ($rolActivo->hasPermissionTo('personas.lista_asistentes_solo_ministerio')) {
                    $personas = auth()->user()->discipulos('sin-eliminados', false);
                }

                // 2. Si tiene permiso general, consultamos todos los usuarios activos con fecha de nacimiento
                if ($rolActivo->hasPermissionTo('personas.lista_asistentes_todos')) {
                    $personas = User::query()
                        ->whereNotNull('fecha_nacimiento')
                        ->get();
                }
            }
        }

        // --- LÓGICA DE FILTRADO DE FECHAS Y PROYECCIÓN ---
        $diasFiltro = (int) $request->input('dias', 30);
        $nombreFiltro = trim($request->input('nombre', ''));
        $rangoFechaFiltro = $request->input('fecha_rango');

        $hoy = now();
        $fechaInicioFiltro = $hoy->clone()->startOfDay();
        $fechaFinFiltro = $hoy->clone()->addDays($diasFiltro)->endOfDay();
        $usandoRangoFijo = false;

        if ($rangoFechaFiltro) {
            $fechas = preg_split('/\s+(?:-|to)\s+/', trim($rangoFechaFiltro));
            if (count($fechas) === 2) {
                try {
                    $formato = str_contains($fechas[0], '-') ? 'Y-m-d' : 'd/m/Y';
                    $fechaInicioFiltro = Carbon::createFromFormat($formato, trim($fechas[0]))->startOfDay();
                    $fechaFinFiltro = Carbon::createFromFormat($formato, trim($fechas[1]))->endOfDay();
                    $usandoRangoFijo = true;
                } catch (\Exception $e) {
                    $usandoRangoFijo = false;
                }
            }
        }

        // 1. Proyectamos el cumpleaños al año en curso o próximo
        $cumpleanosProximos = $personas
            ->whereNotNull('fecha_nacimiento')
            ->map(function ($usuario) use ($hoy) {
                $cumpleEsteAnio = $usuario->fecha_nacimiento->copy()->setYear($hoy->year);
                $proximoCumple = $cumpleEsteAnio->isBefore($hoy->startOfDay()) ? $cumpleEsteAnio->addYear() : $cumpleEsteAnio;

                $usuario->proximo_cumpleanos = $proximoCumple;

                return $usuario;
            });

        // 2. Filtrado final por fecha y nombre
        $cumpleanosFiltrados = $cumpleanosProximos->filter(function ($usuario) use ($nombreFiltro, $fechaInicioFiltro, $fechaFinFiltro, $usandoRangoFijo) {
            $enRango = false;

            if ($usandoRangoFijo) {
                // Proyección al año de inicio del rango
                $cumpleEnAnioFiltro = $usuario->fecha_nacimiento->copy()->setYear($fechaInicioFiltro->year);

                if ($cumpleEnAnioFiltro->between($fechaInicioFiltro, $fechaFinFiltro)) {
                    $enRango = true;
                    $usuario->proximo_cumpleanos = $cumpleEnAnioFiltro;
                }

                // Manejo de cruce de años (ej. Diciembre a Enero)
                if (! $enRango && $fechaInicioFiltro->year !== $fechaFinFiltro->year) {
                    $cumpleEnAnioFin = $usuario->fecha_nacimiento->copy()->setYear($fechaFinFiltro->year);
                    if ($cumpleEnAnioFin->between($fechaInicioFiltro, $fechaFinFiltro)) {
                        $enRango = true;
                        $usuario->proximo_cumpleanos = $cumpleEnAnioFin;
                    }
                }
            } else {
                $fechaCumpleanos = $usuario->proximo_cumpleanos;

                if ($fechaFinFiltro->greaterThanOrEqualTo($fechaInicioFiltro)) {
                    $enRango = $fechaCumpleanos->between($fechaInicioFiltro, $fechaFinFiltro);
                } else {
                    $finAnio = $fechaInicioFiltro->copy()->endOfYear();
                    $inicioAnio = $fechaFinFiltro->copy()->startOfYear();
                    $enRango = $fechaCumpleanos->between($fechaInicioFiltro, $finAnio) ||
                              $fechaCumpleanos->between($inicioAnio, $fechaFinFiltro);
                }
            }

            if (! $enRango) {
                return false;
            }

            // Filtro por nombre
            if (! empty($nombreFiltro)) {
                $nombreCompleto = "{$usuario->primer_nombre} {$usuario->segundo_nombre} {$usuario->primer_apellido} {$usuario->segundo_apellido}";
                if (! \Illuminate\Support\Str::contains(strtolower($nombreCompleto), strtolower($nombreFiltro))) {
                    return false;
                }
            }

            return true;
        })->sortBy('proximo_cumpleanos');

        $rangoFechaTexto = $fechaInicioFiltro->format('Y-m-d').' - '.$fechaFinFiltro->format('Y-m-d');

        return view('contenido.paginas.cumpleanos.listar-cumpleanos', [
            'cumpleanosProximos30Dias' => $cumpleanosFiltrados,
            'configuracion' => $configuracion,
            'fechaInicioFiltro' => $fechaInicioFiltro,
            'fechaFinFiltro' => $fechaFinFiltro,
            'rangoFechaTexto' => $rangoFechaTexto,
            'diasFiltro' => $rangoFechaFiltro ? 0 : $diasFiltro,
        ]);
    }

    /**
     * Envía un correo de felicitación al cumpleañero.
     */
    public function enviarCorreo(Request $request)
    {
        $request->validate([
            'recipient_email' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
            'recipient_name' => 'required|string',
        ]);

        try {
            $mailData = new stdClass();
            $mailData->subject = $request->subject;
            $mailData->nombre = $request->recipient_name;
            $mailData->mensaje = $request->message;

            Mail::to($request->recipient_email)->send(new DefaultMail($mailData));

            return back()->with('success', '¡Correo enviado con éxito a '.$request->recipient_name.'!');
        } catch (\Exception $e) {
            report($e);

            return back()->with('danger', 'Error al enviar el correo. Por favor, verifica la configuración SMTP.');
        }
    }
}
