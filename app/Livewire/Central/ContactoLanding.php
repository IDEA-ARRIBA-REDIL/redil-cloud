<?php

namespace App\Livewire\Central;

use App\Mail\SolicitudDemoLandingMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

class ContactoLanding extends Component
{
    public string $nombreIglesia = '';

    public string $nombreContacto = '';

    public string $email = '';

    public string $whatsapp = '';

    public string $pais = '';

    public string $tamanoCongregacion = '';

    public string $moduloInteres = 'Plataforma completa';

    public string $mensaje = '';

    /** Campo honeypot invisible para mitigar spam automatizado. */
    public string $website = '';

    public bool $enviado = false;

    public bool $correoEnviado = false;

    public string $whatsappConfirmacionUrl = '';

    public string $errorLimite = '';

    /**
     * @return array<int, string>
     */
    public function paises(): array
    {
        return [
            'Colombia',
            'México',
            'Estados Unidos',
            'España',
            'Argentina',
            'Chile',
            'Perú',
            'Ecuador',
            'Costa Rica',
            'Panamá',
            'República Dominicana',
            'Guatemala',
            'Honduras',
            'El Salvador',
            'Nicaragua',
            'Bolivia',
            'Paraguay',
            'Uruguay',
            'Venezuela',
            'Puerto Rico',
            'Otro país',
        ];
    }

    /**
     * @return array<int, string>
     */
    public function tamanos(): array
    {
        return [
            'Menos de 100 miembros',
            '100 a 300 miembros',
            '300 a 1.000 miembros',
            '1.000 a 3.000 miembros',
            'Más de 3.000 miembros / Multi-sede',
        ];
    }

    /**
     * @return array<int, string>
     */
    public function modulosDisponibles(): array
    {
        return [
            'Plataforma completa',
            'Gestión de Miembros y Grupos',
            'Iglesia Infantil con QR',
            'Escuelas y Campus LMS',
            'Finanzas, Cajas y Eventos',
            'Crecimiento Espiritual y PWA',
            'Marca Blanca y Multi-sede',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'nombreIglesia' => ['required', 'string', 'min:2', 'max:120'],
            'nombreContacto' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'whatsapp' => ['required', 'regex:/^\+?[0-9 ()-]{7,25}$/'],
            'pais' => ['required', 'string', 'max:80'],
            'tamanoCongregacion' => ['required', 'string', 'max:80'],
            'moduloInteres' => ['required', 'string', 'max:100'],
            'mensaje' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'nombreIglesia.required' => 'Indica el nombre de tu iglesia o ministerio.',
            'nombreContacto.required' => 'Indica el nombre del pastor o líder de contacto.',
            'email.required' => 'Ingresa un correo electrónico para responderte.',
            'email.email' => 'Ingresa un correo electrónico válido (ej. pastor@iglesia.org).',
            'whatsapp.required' => 'Ingresa tu número de WhatsApp o teléfono con indicativo.',
            'whatsapp.regex' => 'Usa un formato de teléfono válido (ej. +57 300 123 4567).',
            'pais.required' => 'Selecciona tu país.',
            'tamanoCongregacion.required' => 'Selecciona el tamaño aproximado de tu congregación.',
        ];
    }

    #[On('seleccionar-modulo-interes')]
    public function seleccionarModulo(string $modulo): void
    {
        if (in_array($modulo, $this->modulosDisponibles(), true)) {
            $this->moduloInteres = $modulo;
        }
    }

    public function getWhatsappDirectoUrlProperty(): string
    {
        return $this->construirWhatsappUrl();
    }

    public function enviarSolicitud(): void
    {
        $this->errorLimite = '';

        // 1. Mitigación silenciosa de bots mediante honeypot
        if (trim($this->website) !== '') {
            $this->enviado = true;
            $this->whatsappConfirmacionUrl = $this->construirWhatsappUrl();

            return;
        }

        // 2. Control de tasa por IP para evitar abuso del endpoint de correo
        $llaveLimite = 'landing:contacto:'.hash('sha256', (string) request()->ip());

        if (RateLimiter::tooManyAttempts($llaveLimite, 5)) {
            $this->errorLimite = 'Has enviado varias solicitudes recientes. Puedes escribirnos de inmediato por WhatsApp o intentar de nuevo en unos minutos.';

            return;
        }

        $datos = $this->validate();
        RateLimiter::hit($llaveLimite, 3600);

        $urlWhatsappComercial = $this->construirWhatsappUrl($datos);
        $urlRespuestaCliente = $this->construirWhatsappRespuestaCliente($datos);

        $payloadCorreo = [
            'nombreIglesia' => trim($datos['nombreIglesia']),
            'nombreContacto' => trim($datos['nombreContacto']),
            'email' => trim($datos['email']),
            'whatsapp' => trim($datos['whatsapp']),
            'pais' => trim($datos['pais']),
            'tamanoCongregacion' => trim($datos['tamanoCongregacion']),
            'moduloInteres' => trim($datos['moduloInteres']),
            'mensaje' => trim((string) ($datos['mensaje'] ?? '')),
            'whatsappUrl' => $urlRespuestaCliente,
        ];

        $correoDestino = (string) config('services.landing.contact_email', 'contacto@redilcloud.com');
        $this->correoEnviado = false;

        try {
            if ($correoDestino !== '') {
                Mail::to($correoDestino)->send(new SolicitudDemoLandingMail($payloadCorreo));
                $this->correoEnviado = true;
            }
        } catch (Throwable $exception) {
            Log::warning('No se pudo enviar el correo de solicitud demo desde la landing.', [
                'error' => $exception->getMessage(),
                'iglesia' => $payloadCorreo['nombreIglesia'],
                'email' => $payloadCorreo['email'],
            ]);
        }

        $this->whatsappConfirmacionUrl = $urlWhatsappComercial;
        $this->enviado = true;

        $this->reset([
            'nombreIglesia',
            'nombreContacto',
            'email',
            'whatsapp',
            'pais',
            'tamanoCongregacion',
            'mensaje',
            'website',
        ]);
        $this->moduloInteres = 'Plataforma completa';
    }

    public function nuevaSolicitud(): void
    {
        $this->enviado = false;
        $this->correoEnviado = false;
        $this->errorLimite = '';
        $this->resetValidation();
    }

    /**
     * Construye el enlace de WhatsApp hacia el equipo comercial de REDIL Cloud.
     *
     * @param  array<string, mixed>|null  $datos
     */
    protected function construirWhatsappUrl(?array $datos = null): string
    {
        $numero = preg_replace('/\D+/', '', (string) config('services.landing.whatsapp_number', '573000000000')) ?: '573000000000';
        $iglesia = trim((string) ($datos['nombreIglesia'] ?? $this->nombreIglesia));
        $contacto = trim((string) ($datos['nombreContacto'] ?? $this->nombreContacto));
        $pais = trim((string) ($datos['pais'] ?? $this->pais));
        $tamano = trim((string) ($datos['tamanoCongregacion'] ?? $this->tamanoCongregacion));
        $modulo = trim((string) ($datos['moduloInteres'] ?? $this->moduloInteres));

        if ($iglesia === '' && $contacto === '') {
            $texto = (string) config('services.landing.whatsapp_default_message', 'Hola, quiero agendar una videollamada para conocer todas las ventajas de REDIL Cloud para mi iglesia.');

            return 'https://wa.me/'.$numero.'?text='.rawurlencode($texto);
        }

        $lineas = [
            'Hola equipo de REDIL Cloud, quiero agendar una videollamada para conocer las ventajas del software.',
        ];

        if ($iglesia !== '') {
            $lineas[] = 'Iglesia: '.$iglesia;
        }
        if ($contacto !== '') {
            $lineas[] = 'Contacto: '.$contacto;
        }
        if ($pais !== '') {
            $lineas[] = 'País: '.$pais;
        }
        if ($tamano !== '') {
            $lineas[] = 'Congregación: '.$tamano;
        }
        if ($modulo !== '') {
            $lineas[] = 'Interés principal: '.$modulo;
        }

        return 'https://wa.me/'.$numero.'?text='.rawurlencode(implode("\n", $lineas));
    }

    /**
     * Construye el enlace de WhatsApp para que el equipo comercial responda al cliente.
     *
     * @param  array<string, mixed>  $datos
     */
    protected function construirWhatsappRespuestaCliente(array $datos): string
    {
        $numeroCliente = preg_replace('/\D+/', '', (string) ($datos['whatsapp'] ?? ''));

        if ($numeroCliente === '') {
            return $this->construirWhatsappUrl($datos);
        }

        $saludo = 'Hola '.trim((string) $datos['nombreContacto']).', te escribimos del equipo de REDIL Cloud por tu solicitud de videollamada para '.trim((string) $datos['nombreIglesia']).'. ¿En qué horario te queda mejor conversar?';

        return 'https://wa.me/'.$numeroCliente.'?text='.rawurlencode($saludo);
    }

    public function render(): View
    {
        return view('livewire.central.contacto-landing', [
            'paises' => $this->paises(),
            'tamanos' => $this->tamanos(),
            'modulosDisponibles' => $this->modulosDisponibles(),
        ]);
    }
}
