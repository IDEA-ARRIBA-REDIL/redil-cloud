<?php

namespace App\Livewire\Ofrendas;

use App\Models\Configuracion;
use App\Models\Destinatario;
use App\Models\EstadoPago;
use App\Models\Iglesia;
use App\Models\Moneda;
use App\Models\Ofrenda;
use App\Models\Pais;
use App\Models\TipoIdentificacion;
use App\Models\TipoOfrenda;
use App\Models\TipoPago;
use App\Models\User;
use App\Services\ZonaPagosOfrendaService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class DonacionesOnline extends Component
{
    public int $pestanaActiva = 1;

    // Pestaña 1: Modalidad e Identificación
    public string $modalidad = 'registrado';

    public ?int $tipoIdentificacionBusqueda = null;

    public string $identificacionBusqueda = '';

    public bool $usuarioNoEncontrado = false;

    // Pestaña 2: Información Básica del Donante
    public bool $esRegistrado = false;

    public ?int $userId = null;

    public bool $usuarioEstaLogueado = false;

    public string $nombreCompleto = '';

    public ?int $tipoIdentificacionId = null;

    public string $identificacion = '';

    public string $telefono = '';

    public string $email = '';

    public ?int $paisId = null;

    public string $direccion = '';

    public bool $aceptaTerminos = false;

    // Pestaña 3: Detalles de la Donación
    public ?int $tipoOfrendaId = null;

    public ?int $destinatarioId = null;

    public ?int $monedaId = null;

    public string $valorDonacion = '';

    public ?string $mensajeErrorPasarela = null;

    // Catálogos para los selectores
    public Collection $tiposIdentificacion;

    public Collection $paises;

    public Collection $tiposOfrenda;

    public Collection $destinatariosDisponibles;

    public Collection $monedas;

    public string $terminosCondicionesTexto = '';

    public function mount(): void
    {
        $this->tiposIdentificacion = TipoIdentificacion::where('formulario_donacion', true)
            ->orderBy('nombre')
            ->get();

        if ($this->tiposIdentificacion->isEmpty()) {
            $this->tiposIdentificacion = TipoIdentificacion::orderBy('nombre')->get();
        }

        $this->paises = Pais::orderBy('nombre')->get();

        $this->tiposOfrenda = TipoOfrenda::with('destinatarios')
            ->where('formulario_donaciones', true)
            ->orderBy('nombre')
            ->get();

        $this->destinatariosDisponibles = collect();

        $this->monedas = Moneda::where('habilitada_donacion', true)
            ->orderBy('id')
            ->get();

        if ($this->monedas->isEmpty()) {
            $this->monedas = Moneda::orderBy('id')->get();
        }

        if ($this->monedas->isNotEmpty()) {
            $this->monedaId = (int) $this->monedas->first()->id;
        }

        if ($this->tiposIdentificacion->isNotEmpty()) {
            $this->tipoIdentificacionBusqueda = (int) $this->tiposIdentificacion->first()->id;
        }

        $iglesia = Iglesia::first();
        $configuracion = Configuracion::first();
        $this->terminosCondicionesTexto = $configuracion?->terminos_condiciones
            ?: 'Autorizo de manera libre, previa, expresa e informada a '.($iglesia?->nombre ?? 'la Iglesia').' para realizar el tratamiento de mis datos personales con la finalidad de gestionar mi donación y enviarme el soporte correspondiente, conforme a la política de tratamiento de datos personales.';
    }

    /**
     * Cambia la modalidad en la Pestaña 1 ("registrado" o "independiente").
     */
    public function seleccionarModalidad(string $nuevaModalidad): void
    {
        $this->modalidad = $nuevaModalidad;
        $this->usuarioNoEncontrado = false;
        $this->resetValidation();
    }

    /**
     * Busca el número de identificación en la tabla users cuando el usuario indica que ya está registrado.
     */
    public function buscarUsuarioRegistrado(): void
    {
        $this->usuarioNoEncontrado = false;
        $this->mensajeErrorPasarela = null;

        $this->validate([
            'identificacionBusqueda' => ['required', 'string', 'min:3', 'max:50'],
        ], [
            'identificacionBusqueda.required' => 'Por favor ingresa tu número de identificación (cédula).',
            'identificacionBusqueda.min' => 'El número de identificación debe tener al menos 3 caracteres.',
        ]);

        // 1. Buscar al usuario registrado en la tabla users por su número de identificación
        $documentoLimpio = trim($this->identificacionBusqueda);
        $usuario = User::where('identificacion', $documentoLimpio)->first();

        // 2. Si no se encuentra en la tabla users, notificar y permitir pasar a donación independiente
        if (! $usuario) {
            $this->usuarioNoEncontrado = true;

            return;
        }

        // 3. Cargar los datos del usuario en modo lectura (bloqueados)
        $this->esRegistrado = true;
        $this->userId = (int) $usuario->id;
        $this->usuarioEstaLogueado = auth()->check() && (int) auth()->id() === (int) $usuario->id;

        $this->nombreCompleto = trim((string) $usuario->nombre(3));
        $this->tipoIdentificacionId = $usuario->tipo_identificacion_id
            ? (int) $usuario->tipo_identificacion_id
            : $this->tipoIdentificacionBusqueda;
        $this->identificacion = (string) ($usuario->identificacion ?? $documentoLimpio);
        $this->telefono = (string) ($usuario->telefono_movil ?: ($usuario->telefono_fijo ?? ''));
        $this->email = (string) ($usuario->email ?? '');
        $this->paisId = $usuario->pais_id ? (int) $usuario->pais_id : null;
        $this->direccion = (string) ($usuario->direccion ?? '');

        $this->pestanaActiva = 2;
    }

    /**
     * Permite continuar como donación personal (independiente) desde la Pestaña 1.
     */
    public function continuarComoIndependiente(): void
    {
        $this->modalidad = 'independiente';
        $this->esRegistrado = false;
        $this->userId = null;
        $this->usuarioEstaLogueado = false;
        $this->usuarioNoEncontrado = false;
        $this->mensajeErrorPasarela = null;

        // Si había escrito un documento en la búsqueda, lo conservamos para facilitarle el llenado
        $this->tipoIdentificacionId = $this->tipoIdentificacionBusqueda;
        $this->identificacion = trim($this->identificacionBusqueda);
        $this->nombreCompleto = '';
        $this->telefono = '';
        $this->email = '';
        $this->paisId = null;
        $this->direccion = '';

        $this->resetValidation();
        $this->pestanaActiva = 2;
    }

    /**
     * Valida la Pestaña 2 (Datos del Donante) y avanza a la Pestaña 3.
     */
    public function continuarAPestana3(): void
    {
        $this->mensajeErrorPasarela = null;

        if ($this->esRegistrado) {
            // Para usuarios registrados los datos ya están precargados y bloqueados; solo validamos aceptación de términos
            $this->validate([
                'aceptaTerminos' => ['accepted'],
            ], [
                'aceptaTerminos.accepted' => 'Debes aceptar la política de tratamiento de datos personales para continuar.',
            ]);
        } else {
            // Para donantes independientes todos los campos son obligatorios
            $this->validate([
                'nombreCompleto' => ['required', 'string', 'min:3', 'max:150'],
                'tipoIdentificacionId' => ['required', 'integer', 'exists:tipo_identificaciones,id'],
                'identificacion' => ['required', 'string', 'min:3', 'max:50'],
                'telefono' => ['required', 'string', 'min:6', 'max:50'],
                'email' => ['required', 'email', 'max:150'],
                'paisId' => ['required', 'integer', 'exists:paises,id'],
                'direccion' => ['required', 'string', 'min:3', 'max:255'],
                'aceptaTerminos' => ['accepted'],
            ], [
                'nombreCompleto.required' => 'El nombre completo es obligatorio.',
                'tipoIdentificacionId.required' => 'Selecciona tu tipo de identificación.',
                'identificacion.required' => 'El número de identificación es obligatorio.',
                'telefono.required' => 'El teléfono móvil es obligatorio.',
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'Ingresa un correo electrónico válido.',
                'paisId.required' => 'Selecciona tu país.',
                'direccion.required' => 'La dirección es obligatoria.',
                'aceptaTerminos.accepted' => 'Debes aceptar la política de tratamiento de datos personales para continuar.',
            ]);
        }

        $this->pestanaActiva = 3;
    }

    /**
     * Permite regresar a una pestaña anterior ya completada.
     */
    public function irAPestana(int $numeroPestana): void
    {
        if ($numeroPestana < $this->pestanaActiva && $numeroPestana >= 1) {
            $this->pestanaActiva = $numeroPestana;
            $this->mensajeErrorPasarela = null;
        }
    }

    /**
     * Cuando cambia el Tipo de Ofrenda seleccionado en la Pestaña 3, carga sus destinatarios asociados.
     */
    public function updatedTipoOfrendaId($valor): void
    {
        $this->destinatarioId = null;

        if (! $valor) {
            $this->destinatariosDisponibles = collect();

            return;
        }

        $tipoOfrenda = TipoOfrenda::with('destinatarios')->find((int) $valor);

        if ($tipoOfrenda && $tipoOfrenda->destinatarios->isNotEmpty()) {
            $this->destinatariosDisponibles = $tipoOfrenda->destinatarios;
        } else {
            // Si el tipo de ofrenda aún no tiene destinatarios filtrados en la pivote, mostramos todos los destinatarios
            $this->destinatariosDisponibles = Destinatario::orderBy('nombre')->get();
        }

        if ($this->destinatariosDisponibles->count() === 1) {
            $this->destinatarioId = (int) $this->destinatariosDisponibles->first()->id;
        }
    }

    /**
     * Valida los detalles de la donación en la Pestaña 3, crea la Ofrenda y redirige a ZonaPagos.
     */
    public function procesarDonacion(ZonaPagosOfrendaService $zonaPagosOfrendaService): void
    {
        $this->mensajeErrorPasarela = null;

        $monedaSeleccionada = $this->monedaId ? Moneda::find($this->monedaId) : null;
        $tipoPago = TipoPago::where('key_reservada', 'zona')
            ->where('activo', true)
            ->first();

        $valorMinimo = (float) ($tipoPago?->valor_minimo_donacion ?: 1000);
        $valorMaximo = (float) ($monedaSeleccionada?->donacion_maxima ?: 0);

        $reglasValor = ['required', 'numeric', 'min:'.$valorMinimo];
        if ($valorMaximo > 0) {
            $reglasValor[] = 'max:'.$valorMaximo;
        }

        $this->validate([
            'tipoOfrendaId' => ['required', 'integer', 'exists:tipo_ofrenda,id'],
            'destinatarioId' => ['required', 'integer', 'exists:destinatarios,id'],
            'monedaId' => ['required', 'integer', 'exists:monedas,id'],
            'valorDonacion' => $reglasValor,
        ], [
            'tipoOfrendaId.required' => 'Por favor selecciona el tipo de donación.',
            'destinatarioId.required' => 'Por favor selecciona el destinatario de tu donación.',
            'monedaId.required' => 'Por favor selecciona la moneda.',
            'valorDonacion.required' => 'Por favor ingresa el valor a donar.',
            'valorDonacion.numeric' => 'El valor a donar debe ser numérico.',
            'valorDonacion.min' => 'El valor mínimo permitido para donar es $'.number_format($valorMinimo, 0, ',', '.').'.',
            'valorDonacion.max' => 'El valor máximo permitido por transacción es $'.number_format($valorMaximo, 0, ',', '.').'.',
        ]);

        // 1. Obtener el estado inicial "Pendiente" para ZonaPagos
        $estadoPendiente = EstadoPago::where('estado_pendiente', true)->first();

        // 2. Crear el registro en la tabla ofrendas (ingresada_por = 3 identifica donación online)
        $ofrenda = Ofrenda::create([
            'tipo_ofrenda_id' => $this->tipoOfrendaId,
            'valor' => (float) $this->valorDonacion,
            'valor_real' => (float) $this->valorDonacion,
            'fecha' => now()->toDateString(),
            'ingresada_por' => 3,
            'observacion' => $this->esRegistrado
                ? 'Donación online (Usuario registrado)'
                : 'Donación online personal: '.$this->nombreCompleto,
            'user_id' => $this->esRegistrado ? $this->userId : null,
            'estado_pago_id' => $estadoPendiente?->id,
            'tipo_pago_id' => $tipoPago?->id,
            'moneda_id' => $this->monedaId,
            'destinatario_id' => $this->destinatarioId,
            'nombre_donante' => trim($this->nombreCompleto),
            'identificacion_donante' => trim($this->identificacion),
            'tipo_identificacion_donante_id' => $this->tipoIdentificacionId,
            'telefono_donante' => trim($this->telefono),
            'email_donante' => trim($this->email),
            'pais_donante_id' => $this->paisId,
            'direccion_donante' => trim($this->direccion),
            'correo_confirmacion_enviado' => false,
        ]);

        $ofrenda->load(['tipoOfrenda', 'tipoPago']);

        // 3. Iniciar pago en ZonaPagos con prefijo O-{id}
        $resultado = $zonaPagosOfrendaService->iniciarPago($ofrenda, [
            'nombre' => $this->nombreCompleto,
            'identificacion' => $this->identificacion,
            'email' => $this->email,
            'telefono' => $this->telefono,
        ]);

        if ($resultado['success'] && ! empty($resultado['payment_url'])) {
            $ofrenda->update([
                'gateway_response' => [
                    'inicio_pago' => $resultado['gateway_response'] ?? [],
                ],
            ]);

            $this->redirect($resultado['payment_url'], navigate: false);

            return;
        }

        // 4. Si ZonaPagos retorna error al iniciar, registramos la respuesta y mostramos alerta amigable
        $ofrenda->update([
            'gateway_response' => [
                'error_inicio' => $resultado['gateway_response'] ?? ['message' => $resultado['message'] ?? ''],
            ],
        ]);

        $this->mensajeErrorPasarela = $resultado['message']
            ?? 'No fue posible iniciar la conexión con la pasarela de pagos en este momento. Por favor intenta nuevamente.';
    }

    public function render(): View
    {
        return view('livewire.ofrendas.donaciones-online');
    }
}
