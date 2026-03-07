<div>
    {{-- 📲 Contenido del escáner que irá dentro del modal --}}
    <div class="scanner-container">
        <div class="row text-center">
            <div class="col-12 mb-3">
                <label for="camera-select" class="form-label">Seleccionar cámara</label>
                <select id="camera-select" class="form-select w-75 mx-auto">
                    <option value="">Cargando cámaras...</option>
                </select>
            </div>

            <div id="reader" style="width:100%; max-width: 500px;" class="mx-auto border rounded p-0"></div>

            <div class="mt-3">
                <small class="text-muted">Apunte la cámara al código QR del asistente.</small>
            </div>
        </div>
    </div>


    @assets
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
    @endassets

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <script>
        document.addEventListener('livewire:initialized', () => {
            let html5QrcodeScanner = null;
            let isScanning = false;

            const qrModalElement = document.getElementById('qrScannerModal');
            const cameraSelect = document.getElementById('camera-select');
            const readerDiv = document.getElementById('reader');

            const loadCameras = async () => {
                // Limpiamos el selector antes de llenarlo para evitar duplicados.
                cameraSelect.innerHTML = '<option value="">Cargando cámaras...</option>';
                try {
                    const devices = await Html5Qrcode.getCameras();
                    if (devices && devices.length) {
                        cameraSelect.innerHTML = '<option value="" disabled>Seleccione una cámara</option>';
                        devices.forEach((device) => {
                            const option = document.createElement('option');
                            option.value = device.id;
                            option.text = device.label || `Cámara ${device.id}`;
                            cameraSelect.add(option);
                        });
                        if (cameraSelect.options.length > 1) {
                            cameraSelect.selectedIndex = 1;
                        }
                    }
                } catch (err) {
                    Swal.fire('Error de Cámara'
                        , 'No se pudieron cargar las cámaras. Asegúrate de haber otorgado los permisos.'
                        , 'error');
                }
            };

            const startScanner = async () => {
                if (isScanning || !cameraSelect.value) return;

                try {
                    if (html5QrcodeScanner === null) {
                        html5QrcodeScanner = new Html5Qrcode("reader");
                    }

                    isScanning = true;
                    await html5QrcodeScanner.start(
                        cameraSelect.value, // Usar el ID de la cámara seleccionada
                        {
                            fps: 10
                            , qrbox: {
                                width: 250
                                , height: 250
                            }
                        }
                        , (decodedText, decodedResult) => {
                            if (isScanning) {
                                html5QrcodeScanner.pause(true); // Pausar para procesar
                                Livewire.dispatch('qrCodeScanned', {
                                    qrText: decodedText
                                });
                            }
                        }
                        , (errorMessage) => {
                            /* Ignorar errores de no detección */
                        }
                    );

                } catch (err) {
                    isScanning = false;
                    Swal.fire({
                        title: 'Error'
                        , text: `No se pudo iniciar el scanner: ${err}`
                        , icon: 'error'
                    });
                }
            };

            const stopScanner = async () => {
                if (html5QrcodeScanner && isScanning) {
                    try {
                        await html5QrcodeScanner.stop();
                    } catch (e) {
                        console.warn("El scanner ya estaba detenido o no pudo detenerse limpiamente.", e);
                    } finally {
                        isScanning = false;
                        html5QrcodeScanner = null;
                        readerDiv.innerHTML = ''; // Limpiar el visor
                    }
                }
            };

            cameraSelect.addEventListener('change', () => {
                if (isScanning) {
                    stopScanner().then(startScanner);
                }
            });

            // ========================================================== //
            //       Lógica de Control por Eventos del Modal              //
            // ========================================================== //
            qrModalElement.addEventListener('show.bs.modal', async () => {
                await loadCameras();
                startScanner(); // Iniciar al mostrar
            });

            qrModalElement.addEventListener('hide.bs.modal', () => {
                stopScanner(); // Detener al cerrar
            });

            // Se asegura de que el código se ejecute después de que Livewire se haya inicializado.
            document.addEventListener('livewire:init', () => {

                // Listener para el SweetAlert de confirmación
                Livewire.on('confirmarAsistenciaConInvitados', (event) => {
                    const detail = Array.isArray(event) ? event[0] : event;

                    Swal.fire({
                        title: 'Participante con Invitados'
                        , html: `<b>${detail.nombre}</b> tiene <b>${detail.cantidadInvitados}</b> invitado(s) asociado(s).<br><br>¿Deseas registrar la asistencia solo para el participante principal?`
                        , icon: 'question'
                        , showCancelButton: true
                        , confirmButtonText: 'Sí, registrar solo al principal'
                        , cancelButtonText: 'Cancelar'
                        , customClass: {
                            confirmButton: 'btn btn-primary me-2'
                            , cancelButton: 'btn btn-label-secondary'
                        }
                        , buttonsStyling: false
                    }).then((result) => {
                        // Si el administrador hace clic en "Sí", se envía un evento de vuelta al componente.
                        if (result.isConfirmed) {
                            Livewire.dispatch('confirmacionAsistenciaRecibida', {
                                inscripcionId: detail.inscripcionId
                            });
                        }
                    });
                });
            });

            Livewire.on('showAlert', (data) => 
            {
                const alertData = Array.isArray(data) ? data[0] : data;

                // Construimos la configuración de SweetAlert
                let swalConfig = {
                    title:  alertData.title
                    , text: alertData.text
                    , html: alertData.html, // Para poder mostrar HTML
                    icon: alertData.icon
                    , allowOutsideClick: false // No permitir cerrar haciendo clic fuera
                };

                // Si la alerta es interactiva, no tendrá temporizador y mostrará un botón
                if (alertData.interactive) {
                    swalConfig.showConfirmButton = true;
                    swalConfig.showCancelButton = false;
                    swalConfig.showDenyButton = false;
                    swalConfig.confirmButtonText = alertData.confirmButtonText || 'Entendido';
                } else {
                    swalConfig.showConfirmButton = false;
                    swalConfig.timer = 1500;
                    swalConfig.timerProgressBar = true;
                }

                });
            });

            // Listener dedicado para alertas de formulario con configuración estricta
            Livewire.on('showFormAlert', (data) => {
                const alertData = Array.isArray(data) ? data[0] : data;
                Swal.fire({
                    title: alertData.title,
                    text: alertData.text,
                    html: alertData.html,
                    icon: alertData.icon,
                    allowOutsideClick: false,
                    showConfirmButton: true,
                    showCancelButton: false,
                    showDenyButton: false,
                    confirmButtonText: alertData.confirmButtonText || 'Cerrar',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-primary waves-effect waves-light',
                        cancelButton: 'd-none',
                        denyButton: 'd-none'
                    }
                }).then(() => {
                    if (html5QrcodeScanner && isScanning) {
                        html5QrcodeScanner.resume();
                    }
                });
            });
        

    </script>
    @endpush
</div>
