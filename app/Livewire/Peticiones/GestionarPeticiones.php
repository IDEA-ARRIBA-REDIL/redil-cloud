<?php

namespace App\Livewire\Peticiones;

use App\Helpers\Helpers;
use App\Mail\DefaultMail;
use App\Models\BibliaLibro;
use App\Models\BibliaVersiculo;
use App\Models\BibliaVersion;
use App\Models\Configuracion;
use App\Models\Iglesia;
use App\Models\Peticion;
use App\Models\SeguimientoPeticion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use stdClass;

class GestionarPeticiones extends Component
{
    public $titulo = '';

    // modalResponder
    public $peticionResponderId;

    public $peticionAsignarId;

    public $peticionesAsignarIds = [];

    public $intercesorSeleccionadoId;

    public $descripcionRespuesta = '';

    public $versiculosRecomendados;

    public $libros;

    public $librosAt;

    public $librosNt;

    public $versiones;

    public $versionSeleccionadaId;

    public $estadoSiguiente = 1; // 1 = Pendiente, 3 = En proceso, 2 = Cerrada

    // modalBiblia
    public $listadoVersiculos;

    public function mount()
    {
        try {
            $this->versiones = BibliaVersion::where('activo', true)->orderBy('orden')->get();
            $this->versionSeleccionadaId = $this->versiones->firstWhere('slug', 'rvr1960')?->id ?? $this->versiones->first()?->id;
        } catch (\Throwable $e) {
            $this->versiones = collect();
            $this->versionSeleccionadaId = null;
        }

        try {
            $librosDB = BibliaLibro::orderBy('orden')->get();
            if ($librosDB->isNotEmpty()) {
                $this->libros = $librosDB;
                $this->librosAt = $librosDB->where('testamento', 'Antiguo Testamento');
                $this->librosNt = $librosDB->where('testamento', 'Nuevo Testamento');
            } else {
                $this->libros = Helpers::libros();
                $this->librosAt = collect();
                $this->librosNt = collect();
            }
        } catch (\Throwable $e) {
            $this->libros = Helpers::libros();
            $this->librosAt = collect();
            $this->librosNt = collect();
        }

        $this->listadoVersiculos = '<div class="text-center text-muted p-4"><i class="ti ti-book-2 fs-2 mb-2 d-block"></i><p class="mb-0">Selecciona un pasaje bíblico o busca versículos por palabra clave.</p></div>';
    }

    #[On('modalResponder')]
    public function modalResponder($peticionId, $personaId)
    {
        $peticion = Peticion::find($peticionId);

        if ($peticion->user_id) {
            $usuario = User::withTrashed()->select('id', 'primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido')->find($personaId);
            $nombre = $usuario->nombre(3);
        } else {
            $nombre = $peticion->nombre_externo;
        }

        $this->titulo = 'Responder a <b>'.$nombre.'</b>';

        // Autoseleccionamos el estado actual si lo tiene, para que no lo cambie sin querer
        $this->estadoSiguiente = $peticion->estado;

        $this->peticionResponderId = $peticionId;
        $this->descripcionRespuesta = '<p>¡Hola! <b>'.$nombre.'</b>. </p>';

        $this->versiculosRecomendados = '<p>Cargando versiculos recomendados</p>
    <div class="spinner-border spinner-border-lg text-primary mt-1" role="status">
    <span class="visually-hidden">Loading...</span>
    </div>';

        $this->dispatch('textoInicialRespuesta', textoInicial: $this->descripcionRespuesta);
        $this->dispatch('abrirModal', nombreModal: 'modalResponder');
        $this->dispatch('cargarVersiculosRecomendados', peticionId: $peticionId);
    }

    public function addResponder()
    {
        $configuracion = Configuracion::find(1);
        $peticion = Peticion::find($this->peticionResponderId);
        $usuario_logueado = auth()->user();
        if ($peticion->user_id) {
            $usuario = User::withTrashed()->select('id', 'primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido', 'telefono_movil', 'email', 'pais_id')->find($peticion->user_id);

            $email = $usuario->email;
            $telefono = $usuario->telefono_movil;
            $nombre = $usuario->nombre(3);

            if (isset($usuario->pais->prefijo)) {
                $prefijo = $usuario->pais->prefijo;
            } else {
                $iglesia = Iglesia::find(1);
                $prefijo = $iglesia->pais->prefijo;
            }
        } else {
            $email = $peticion->email_externo;
            $telefono = $peticion->telefono_externo;
            $nombre = $peticion->nombre_externo;
            $prefijo = $peticion->pais->prefijo ?? Iglesia::find(1)->pais->prefijo;
        }

        // Crear el nuevo seguimiento (hace veces de respuesta)
        $seguimiento = new SeguimientoPeticion;
        $seguimiento->peticion_id = $peticion->id;
        $seguimiento->fecha = Carbon::now()->format('Y-m-d');
        $seguimiento->descripcion = $this->descripcionRespuesta;
        $seguimiento->usuario_id = $usuario_logueado->id;
        $seguimiento->save();

        // Enviar el correo
        if ($email != '') {
            $mensaje = $this->descripcionRespuesta;
            $mailData = new stdClass;
            $mailData->subject = 'Actualización en tu petición';
            $mailData->nombre = $nombre;
            $mailData->mensaje = $mensaje;

            if ($peticion->tipoPeticion->banner_email_url != '') {
                $mailData->banner = $peticion->tipoPeticion->banner_email_url;
            }

            try {
                Mail::to($email)->send(new DefaultMail($mailData));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Error enviando correo por interacción de petición ID {$peticion->id}: ".$e->getMessage());
            }
        }

        // Se actualiza la peticion al nuevo estado indicado
        $peticion->estado = $this->estadoSiguiente;
        $peticion->save();

        $respuesta = 'La interacción y actualización de estado fueron guardados con éxito. ';

        if ($telefono != '') {
            $validarTelefono = strpos($telefono, '+');

            if ($validarTelefono === false) {
                $pefijoTelefono = $prefijo.$telefono;
            } else {
                $pefijoTelefono = $telefono;
            }

            $mensajeWhatsapp = 'Hola '.$nombre.' DIOS te bendiga ';

            $respuesta .= ' Si deseas puedes escribirle a <b>'.$nombre.'</b>
			a través de WhatsApp dando clic aquí <a target="_blank" href="https://api.whatsapp.com/send?phone='.preg_replace('/[^0-9]/', '', $pefijoTelefono).'&text='.$mensajeWhatsapp.'" ><i class="ti ti-brand-whatsapp"></i> '.$telefono.'</a>';

        }

        return redirect(request()->header('Referer'))->with('success', $respuesta);
    }

    #[On('cargarVersiculosRecomendados')]
    public function versiculosSegunTipoPeticion($peticionId): void
    {
        $peticion = Peticion::find($peticionId);
        if (! $peticion || ! $peticion->tipoPeticion) {
            $this->versiculosRecomendados = '';

            return;
        }
        $tipoPeticion = $peticion->tipoPeticion;
        $versiculos = json_decode($tipoPeticion->json_versiculos);

        $this->versiculosRecomendados = '<p class="text-black">Añade el versículo dando clic sobre él</p>';
        if ($versiculos) {
            foreach ($versiculos as $versiculo) {
                $respuestaText = $versiculo->cita ?? '';
                $titulo = $versiculo->titulo ?? '';

                $this->versiculosRecomendados = $this->versiculosRecomendados.' <button type="button" class="btn rounded-pill btn-outline-primary waves-effect btn-sm mt-1 add-versiculo" data-toggle="tooltip" data-placement="top" title="'.e($respuestaText).'" data-verso="'.e($respuestaText).'" data-cita="'.e($titulo).'" >'.e($titulo).'</button>';
            }
        }
    }

    #[On('buscarBibliaCita')]
    public function buscarBibliaCita($libro, $capitulo, $versiculo, $versionId = null): void
    {
        try {
            $version = null;
            if ($versionId) {
                $version = BibliaVersion::find($versionId);
            }
            if (! $version) {
                $version = BibliaVersion::where('slug', 'rvr1960')->first() ?? BibliaVersion::first();
            }

            $libroModel = null;
            if (is_numeric($libro)) {
                $libroModel = BibliaLibro::find($libro);
            }
            if (! $libroModel) {
                $slug = Str::slug($libro);
                $libroModel = BibliaLibro::where('slug', $slug)
                    ->orWhere('nombre', 'ILIKE', $libro)
                    ->orWhere('abreviatura', 'ILIKE', $libro)
                    ->orWhere('usfm', 'ILIKE', $libro)
                    ->first();
            }

            if (! $libroModel || ! $version) {
                $this->listadoVersiculos = '<div class="text-center text-muted p-4"><i class="ti ti-search fs-2 mb-2 d-block"></i><h6 class="text-secondary mb-1">Libro no encontrado</h6><p class="small mb-0">No se pudo identificar el libro solicitado.</p></div>';

                return;
            }

            $versiculoClean = trim((string) $versiculo);
            $queryVersiculos = BibliaVersiculo::query()
                ->where('version_id', $version->id)
                ->where('libro_id', $libroModel->id)
                ->where('capitulo', (int) $capitulo);

            $citaVerso = $versiculoClean;

            if (str_contains($versiculoClean, '-')) {
                $partes = explode('-', $versiculoClean);
                $inicio = (int) trim($partes[0]);
                $fin = (int) trim($partes[1] ?? $inicio);
                if ($fin < $inicio) {
                    $temp = $inicio;
                    $inicio = $fin;
                    $fin = $temp;
                }
                $queryVersiculos->whereBetween('versiculo', [$inicio, $fin]);
                $citaVerso = "{$inicio}-{$fin}";
            } elseif (str_contains($versiculoClean, ',')) {
                $lista = array_map('intval', array_filter(explode(',', $versiculoClean)));
                $queryVersiculos->whereIn('versiculo', $lista);
                $citaVerso = implode(', ', $lista);
            } elseif (is_numeric($versiculoClean)) {
                $num = (int) $versiculoClean;
                $queryVersiculos->where('versiculo', $num);
                $citaVerso = (string) $num;
            }

            $versiculosDB = $queryVersiculos->orderBy('versiculo', 'asc')->get();

            if ($versiculosDB->isNotEmpty()) {
                $textos = [];
                foreach ($versiculosDB as $v) {
                    if ($versiculosDB->count() > 1) {
                        $textos[] = "({$v->versiculo}) ".$v->texto;
                    } else {
                        $textos[] = $v->texto;
                    }
                }
                $textoCompleto = implode(' ', $textos);
                $cita = "{$libroModel->nombre} {$capitulo}:{$citaVerso}";
                $siglaVersion = $version->sigla ?? $version->abreviatura ?? 'RVR1960';

                $this->listadoVersiculos = '
        <div class="card border shadow-none mb-0">
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start gap-3">
              <div class="flex-grow-1">
                <p class="mb-2 text-dark fs-6" style="line-height: 1.6;">“'.e($textoCompleto).'”</p>
                <span class="badge bg-label-primary fw-bold"><i class="ti ti-book me-1"></i>'.e($cita).' ('.e($siglaVersion).')</span>
              </div>
              <div class="flex-shrink-0">
                <button type="button" class="btn btn-sm btn-success rounded-pill waves-effect waves-light add-versiculo"
                  data-verso="'.e($textoCompleto).'"
                  data-cita="'.e($cita).'"
                  data-version="'.e($siglaVersion).'">
                  <i class="ti ti-plus me-1"></i>Añadir a la respuesta
                </button>
              </div>
            </div>
          </div>
        </div>';

                return;
            }
        } catch (\Throwable $e) {
            // Manejo silencioso de error
        }

        $this->listadoVersiculos = '<div class="text-center text-muted p-4"><i class="ti ti-search fs-2 mb-2 d-block"></i><h6 class="text-secondary mb-1">La búsqueda no arrojó ningún resultado</h6><p class="small mb-0">Verifica que el libro, capítulo y versículo existan en la versión seleccionada.</p></div>';
    }

    #[On('buscarBibliaPalabraClave')]
    public function buscarBibliaPalabraClave($palabrasClaves, $versionId = null): void
    {
        $palabrasClaves = trim((string) $palabrasClaves);
        if (empty($palabrasClaves)) {
            $this->listadoVersiculos = '<div class="text-center text-muted p-4"><i class="ti ti-search fs-2 mb-2 d-block"></i><p class="mb-0">Ingresa una o más palabras para buscar.</p></div>';

            return;
        }

        try {
            $version = null;
            if ($versionId) {
                $version = BibliaVersion::find($versionId);
            }
            if (! $version) {
                $version = BibliaVersion::where('slug', 'rvr1960')->first() ?? BibliaVersion::first();
            }

            if (! $version) {
                $this->listadoVersiculos = '<div class="text-center text-muted p-4"><i class="ti ti-alert-circle fs-2 mb-2 d-block"></i><p class="mb-0">No hay versiones bíblicas disponibles.</p></div>';

                return;
            }

            $palabras = array_filter(explode(' ', $palabrasClaves), fn ($w) => mb_strlen(trim($w)) >= 2);
            if (empty($palabras)) {
                $palabras = [trim($palabrasClaves)];
            }

            $query = BibliaVersiculo::query()
                ->where('version_id', $version->id);

            foreach ($palabras as $p) {
                $query->where('texto', 'ILIKE', '%'.trim($p).'%');
            }

            $resultados = $query->with('libro')
                ->orderBy('libro_id', 'asc')
                ->orderBy('capitulo', 'asc')
                ->orderBy('versiculo', 'asc')
                ->limit(35)
                ->get();

            if ($resultados->isNotEmpty()) {
                $siglaVersion = $version->sigla ?? $version->abreviatura ?? 'RVR1960';
                $totalCount = $resultados->count();

                $html = '<div class="d-flex justify-content-between align-items-center mb-2 px-1">';
                $html .= '<span class="badge bg-label-primary"><i class="ti ti-check me-1"></i>'.$totalCount.' versículos encontrados en '.e($siglaVersion).'</span>';
                $html .= '<small class="text-muted">Mostrando hasta 35 coincidencias</small>';
                $html .= '</div>';

                $html .= '<div class="list-group list-group-flush">';
                foreach ($resultados as $v) {
                    $libroNombre = $v->libro->nombre ?? 'Libro';
                    $cita = "{$libroNombre} {$v->capitulo}:{$v->versiculo}";
                    $textoPlano = $v->texto;

                    // Resaltar palabras clave con <mark>
                    $textoResaltado = e($textoPlano);
                    foreach ($palabras as $p) {
                        $pEscaped = preg_quote(e($p), '/');
                        $textoResaltado = preg_replace('/('.$pEscaped.')/iu', '<mark class="bg-warning-subtle text-dark px-1 py-0 rounded">$1</mark>', $textoResaltado);
                    }

                    $html .= '
          <div class="list-group-item px-2 py-3 border-bottom">
            <div class="d-flex justify-content-between align-items-start gap-3">
              <div class="flex-grow-1">
                <div class="mb-1 text-dark" style="line-height: 1.5;">“'.$textoResaltado.'”</div>
                <span class="badge bg-label-secondary fw-semibold">'.e($cita).' ('.e($siglaVersion).')</span>
              </div>
              <div class="flex-shrink-0">
                <button type="button" class="btn btn-sm btn-outline-success rounded-pill waves-effect add-versiculo"
                  data-verso="'.e($textoPlano).'"
                  data-cita="'.e($cita).'"
                  data-version="'.e($siglaVersion).'">
                  <i class="ti ti-plus me-1"></i>Añadir
                </button>
              </div>
            </div>
          </div>';
                }
                $html .= '</div>';

                $this->listadoVersiculos = $html;

                return;
            }
        } catch (\Throwable $e) {
            // Manejo silencioso de error
        }

        $this->listadoVersiculos = '
    <div class="text-center text-muted p-4">
      <i class="ti ti-file-search fs-2 mb-2 d-block"></i>
      <h6 class="text-secondary mb-1">No se encontraron versículos</h6>
      <p class="small mb-0">No hubo resultados para <b>"'.e($palabrasClaves).'"</b> en la versión seleccionada. Prueba con otra palabra clave o versión.</p>
    </div>';
    }

    #[On('modalAsignarIntercesor')]
    public function modalAsignarIntercesor($peticionId)
    {
        $this->peticionAsignarId = $peticionId;
        $this->peticionesAsignarIds = [$peticionId];
        $this->intercesorSeleccionadoId = null;
        $this->dispatch('limpiar-seleccion');
        $this->dispatch('abrirModal', nombreModal: 'modalAsignarIntercesor');
    }

    #[On('modalAsignarIntercesorMasivo')]
    public function modalAsignarIntercesorMasivo(array $peticionesIds)
    {
        $this->peticionesAsignarIds = $peticionesIds;
        $this->peticionAsignarId = null;
        $this->intercesorSeleccionadoId = null;
        $this->dispatch('limpiar-seleccion');
        $this->dispatch('abrirModal', nombreModal: 'modalAsignarIntercesor');
    }

    #[On('usuario-seleccionado')]
    public function usuarioSeleccionado($id, $buscadorId = null)
    {
        if ($buscadorId === 'intercesor_asignado_id') {
            $this->intercesorSeleccionadoId = $id;
        }
    }

    public function asignarIntercesorConfirmado()
    {
        $rolActivo = auth()->user()?->roles()->wherePivot('activo', true)->first();
        if (! $rolActivo || ! $rolActivo->hasPermissionTo('peticiones.subitem_gestionar_intercesores')) {
            abort(403, 'No tienes privilegios para asignar intercesores.');
        }

        if ($this->intercesorSeleccionadoId) {
            $ids = ! empty($this->peticionesAsignarIds)
              ? $this->peticionesAsignarIds
              : ($this->peticionAsignarId ? [$this->peticionAsignarId] : []);

            if (! empty($ids)) {
                Peticion::whereIn('id', $ids)->update([
                    'asignacion_peticion_id' => $this->intercesorSeleccionadoId,
                ]);

                $this->dispatch('cerrarModal', nombreModal: 'modalAsignarIntercesor');
                $intercesor = User::find($this->intercesorSeleccionadoId);
                $nombreIntercesor = $intercesor ? $intercesor->nombre(3) : 'Intercesor';

                $this->dispatch('limpiar-seleccion');
                $this->intercesorSeleccionadoId = null;
                $cantidad = count($ids);
                $this->peticionesAsignarIds = [];
                $this->peticionAsignarId = null;

                $mensaje = $cantidad > 1
                  ? "Se asignaron <b>{$cantidad}</b> peticiones a <b>{$nombreIntercesor}</b> con éxito."
                  : "La petición fue asignada a <b>{$nombreIntercesor}</b> con éxito.";

                return redirect(request()->header('Referer'))->with('success', $mensaje);
            }
        }
    }

    public function render()
    {
        return view('livewire.peticiones.gestionar-peticiones');
    }
}
