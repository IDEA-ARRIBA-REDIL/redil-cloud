<div>
    <div>
        {{--
        Controles visibles en la página principal.
        El usuario primero selecciona la cámara y luego presiona el botón.
    --}}
        <div wire:ignore class="row flex items-end justify-content gap-4 my-4">

            <div class="col-auto">
                <label for="camera-select" class="form-label">Cámara del dispositivo</label>
                <select id="camera-select" class="form-select" disabled>
                    <option value="">Cargando cámaras...</option>
                </select>
            </div>

            <div class="col-auto">
                <button type="button" class="px-4 mt-3 py-2 font-bold text-white bg-green-500 rounded-pill shadow-lg btn btn-primary hover:bg-green-600" onclick="abrirModalScanner()">
                    Iniciar Scanner
                </button>
            </div>

        </div>

        {{--
        MODIFICADO: Estructura del Modal más simple.
        Ahora solo contiene el visor de la cámara.
    --}}
        <div wire:ignore.self class="modal fade" id="scannerModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="scannerModalLabel">Escanear Código QR </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        {{-- El selector ya no está aquí. Solo el visor. --}}
                        <div id="reader" style="width:100%;" class="mx-auto"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Assets... sin cambios --}}
        @assets
        @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
        @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js'])
        @endassets
    </div>

    @push('scripts')
    {{-- Librería para escanear QR --}}
    <script type="module" src="https://unpkg.com/html5-qrcode"></script>

    <script>
        function abrirModalScanner() {
            const selectedDeviceId = document.getElementById('camera-select').value;

            if (!selectedDeviceId) {
                Swal.fire({
                    title: "Atención"
                    , text: "Por favor, selecciona una cámara antes de iniciar el scanner."
                    , icon: "warning"
                    , timer: 2500
                    , showConfirmButton: false
                    , allowOutsideClick: false
                    , allowEscapeKey: false
                    , allowEnterKey: false
                    , focusConfirm: false // 🔑 Esto evita robar el foco
                });
                return;
            }

            const modalEl = document.getElementById('scannerModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }

        // Listener de Alertas de Livewire (sin cambios)
        if (!window.livewireAlertListenerRegistered) {
            window.livewireAlertListenerRegistered = true;

            Livewire.on('showAlert', (data) => {
                const alertData = Array.isArray(data) ? data[0] : data;
                Swal.fire({
                    title: alertData.title
                    , text: alertData.text
                    , icon: alertData.icon
                    , timer: 2500
                    , showConfirmButton: false
                    , allowOutsideClick: false
                    , allowEscapeKey: false
                    , allowEnterKey: false
                    , focusConfirm: false // 🔑 Esto evita robar el foco
                });
            });
        }

        document.addEventListener('livewire:initialized', () => {
            const scannerModalEl = document.getElementById('scannerModal');
            const cameraSelect = document.getElementById('camera-select');
            let html5QrCode = null;

            // --- LÓGICA DE INICIALIZACIÓN: Cargar cámaras al inicio ---
            // Se ejecuta una vez que la página ha cargado.
            const initializeCameraSelector = () => {
                Html5Qrcode.getCameras().then(devices => {
                    const selectedBefore = cameraSelect.value; // ✅ Guardamos la cámara previa

                    if (devices && devices.length) {
                        cameraSelect.innerHTML = '<option value="" disabled>Selecciona una cámara</option>';
                        devices.forEach(device => {
                            const isSelected = selectedBefore === device.id ? 'selected' : '';
                            cameraSelect.innerHTML += `<option value="${device.id}" ${isSelected}>${device.label}</option>`;
                        });

                        cameraSelect.disabled = false;
                    }
                }).catch(err => {
                    cameraSelect.innerHTML = '<option value="">No se encontraron cámaras</option>';
                    Swal.fire("Error", "No se encontraron cámaras.", "error");
                });
            };

            // Llamamos a la función para poblar el selector de cámaras
            initializeCameraSelector();

            // --- FUNCIONES PRINCIPALES ---

            const startScanner = () => {
                if (html5QrCode && html5QrCode.isScanning) return;

                const selectedDeviceId = cameraSelect.value;
                if (!selectedDeviceId) {
                    Swal.fire("Atención", "Por favor, selecciona una cámara antes de iniciar el scanner.", "warning");
                    return;
                }

                html5QrCode = new Html5Qrcode("reader");

                html5QrCode.start(
                    selectedDeviceId, {
                        fps: 10
                        , qrbox: {
                            width: 250
                            , height: 250
                        }
                    }
                    , (decodedText) => {
                        // Detenemos el scanner primero
                        html5QrCode.stop().then(() => {
                            html5QrCode = null;
                            document.getElementById('reader').innerHTML = '';

                            // Luego llamamos a Livewire
                            // @this.handleSuccessfulScan(decodedText);

                            // Cerramos el modal si todo fue bien
                            const modal = bootstrap.Modal.getInstance(scannerModalEl);
                            // if (modal) modal.hide();
                        }).catch(err => {
                            console.error("Error al detener el scanner antes de procesar el resultado", err);
                        });
                    }
                    , (errorMessage) => {
                        /* Ignorar */
                    }
                ).catch((err) => {
                    console.error("No se pudo iniciar el scanner", err);
                    Swal.fire("Error", "No se pudo iniciar el scanner.", "error");
                });
            };

            const stopScanner = () => {
                if (html5QrCode && html5QrCode.isScanning) {
                    html5QrCode.stop()
                        .then(() => {
                            html5QrCode = null;
                            document.getElementById('reader').innerHTML = '';
                        })
                        .catch(err => console.error("Error al detener el scanner.", err));
                }
            };

            // --- EVENTOS DEL MODAL ---

            // MODIFICADO: Ahora el scanner se inicia cuando el modal está a punto de mostrarse.
            scannerModalEl.addEventListener('show.bs.modal', () => {
                startScanner();
            });

            // SIN CAMBIOS: El scanner se detiene cuando el modal se oculta. Esto es crucial.

        });

    </script>
    @endpush
</div>
