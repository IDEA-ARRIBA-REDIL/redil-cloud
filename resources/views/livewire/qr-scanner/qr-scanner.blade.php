<div>
    {{-- 📲 Contenido del escáner que va dentro del modal --}}
    <div class="scanner-container">
        <div class="row text-center">
            <div class="col-12 mb-3">
                <label for="camera-select" class="form-label fw-semibold">Seleccionar cámara</label>
                <select id="camera-select" class="form-select w-75 mx-auto">
                    <option value="">Cargando cámaras...</option>
                </select>
            </div>

            <div id="reader" style="width:100%; max-width: 500px; min-height: 250px;" class="mx-auto border rounded p-0 overflow-hidden bg-black position-relative"></div>

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
            let isPausedForProcessing = false;
            let ultimaLectura = null;
            let timerLectura = null;

            const qrModalElement = document.getElementById('qrScannerModal');
            const cameraSelect = document.getElementById('camera-select');
            const readerDiv = document.getElementById('reader');

            const loadCameras = async () => {
                if (!cameraSelect) return;
                cameraSelect.innerHTML = '<option value="">Cargando cámaras...</option>';
                try {
                    const devices = await Html5Qrcode.getCameras();
                    if (devices && devices.length) {
                        const selectedBefore = cameraSelect.value;
                        cameraSelect.innerHTML = '<option value="" disabled>Seleccione una cámara</option>';

                        let backCameraIndex = -1;
                        devices.forEach((device, index) => {
                            const option = document.createElement('option');
                            option.value = device.id;
                            option.text = device.label || `Cámara ${index + 1}`;
                            cameraSelect.add(option);

                            const labelLower = (device.label || '').toLowerCase();
                            if (labelLower.includes('back') || labelLower.includes('trasera') || labelLower.includes('environment')) {
                                backCameraIndex = index;
                            }
                        });

                        if (selectedBefore && Array.from(cameraSelect.options).some(o => o.value === selectedBefore)) {
                            cameraSelect.value = selectedBefore;
                        } else if (backCameraIndex !== -1) {
                            cameraSelect.selectedIndex = backCameraIndex + 1;
                        } else if (cameraSelect.options.length > 1) {
                            cameraSelect.selectedIndex = 1;
                        }
                    } else {
                        cameraSelect.innerHTML = '<option value="">No se encontraron cámaras</option>';
                        Swal.fire({
                            title: 'Error de Cámara',
                            text: 'No se encontraron cámaras disponibles en este dispositivo.',
                            icon: 'error'
                        });
                    }
                } catch (err) {
                    cameraSelect.innerHTML = '<option value="">Error al cargar cámaras</option>';
                    Swal.fire({
                        title: 'Permisos de Cámara',
                        text: 'No se pudo acceder a las cámaras. Por favor, asegúrate de haber concedido los permisos en tu navegador.',
                        icon: 'error'
                    });
                }
            };

            const startScanner = async () => {
                if (isScanning || !cameraSelect || !cameraSelect.value) return;

                try {
                    if (!html5QrcodeScanner) {
                        html5QrcodeScanner = new Html5Qrcode("reader");
                    }

                    isScanning = true;
                    isPausedForProcessing = false;

                    await html5QrcodeScanner.start(
                        cameraSelect.value,
                        {
                            fps: 10,
                            qrbox: {
                                width: 250,
                                height: 250
                            }
                        },
                        (decodedText, decodedResult) => {
                            // Si ya estamos procesando o es la misma lectura inmediata, ignorar
                            if (isPausedForProcessing) return;
                            if (decodedText === ultimaLectura) return;

                            isPausedForProcessing = true;
                            ultimaLectura = decodedText;

                            // Pausamos el escáner para evitar lecturas duplicadas mientras el backend responde
                            try {
                                if (html5QrcodeScanner) {
                                    html5QrcodeScanner.pause(true);
                                }
                            } catch (e) {
                                console.warn("Error al pausar scanner:", e);
                            }

                            // Enviamos el código QR a Livewire
                            Livewire.dispatch('qrCodeScanned', {
                                qrText: decodedText
                            });

                            // Limpiar ultimaLectura después de 3 segundos
                            clearTimeout(timerLectura);
                            timerLectura = setTimeout(() => {
                                ultimaLectura = null;
                            }, 3000);
                        },
                        (errorMessage) => {
                            // Ignorar frames sin detección
                        }
                    );

                } catch (err) {
                    isScanning = false;
                    isPausedForProcessing = false;
                    console.error("Error al iniciar el scanner:", err);
                    Swal.fire({
                        title: 'Error',
                        text: `No se pudo iniciar el escáner: ${err.message || err}`,
                        icon: 'error'
                    });
                }
            };

            const resumeScanner = () => {
                isPausedForProcessing = false;
                setTimeout(() => {
                    ultimaLectura = null;
                }, 1000);

                if (html5QrcodeScanner && isScanning) {
                    try {
                        if (typeof html5QrcodeScanner.getState === 'function') {
                            if (html5QrcodeScanner.getState() === 3) {
                                html5QrcodeScanner.resume();
                            }
                        } else {
                            html5QrcodeScanner.resume();
                        }
                    } catch (e) {
                        console.warn("No se pudo reanudar el scanner automáticamente:", e);
                    }
                }
            };

            const stopScanner = async () => {
                if (html5QrcodeScanner && isScanning) {
                    try {
                        await html5QrcodeScanner.stop();
                    } catch (e) {
                        console.warn("El scanner ya estaba detenido o falló al detenerse:", e);
                    } finally {
                        isScanning = false;
                        isPausedForProcessing = false;
                        ultimaLectura = null;
                        html5QrcodeScanner = null;
                        if (readerDiv) {
                            readerDiv.innerHTML = '';
                        }
                    }
                }
            };

            if (cameraSelect) {
                cameraSelect.addEventListener('change', async () => {
                    if (isScanning) {
                        await stopScanner();
                        await startScanner();
                    }
                });
            }

            // ========================================================== //
            //       Lógica de Control por Eventos del Modal              //
            // ========================================================== //
            if (qrModalElement) {
                qrModalElement.addEventListener('shown.bs.modal', async () => {
                    await loadCameras();
                    await startScanner();
                });

                qrModalElement.addEventListener('hidden.bs.modal', async () => {
                    await stopScanner();
                });
            }

            // ========================================================== //
            //       Listeners de Livewire                                //
            // ========================================================== //
            Livewire.on('showAlert', (data) => {
                const alertData = Array.isArray(data) ? data[0] : data;
                const isInteractive = alertData.interactive ?? false;

                Swal.fire({
                    title: alertData.title || '',
                    text: alertData.text || '',
                    html: alertData.html || undefined,
                    icon: alertData.icon || 'info',
                    timer: isInteractive ? undefined : 2000,
                    timerProgressBar: !isInteractive,
                    showConfirmButton: isInteractive,
                    confirmButtonText: alertData.confirmButtonText || 'Aceptar',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-primary rounded-pill px-4 waves-effect waves-light'
                    },
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then(() => {
                    resumeScanner();
                });
            });

            Livewire.on('showFormAlert', (data) => {
                const alertData = Array.isArray(data) ? data[0] : data;

                Swal.fire({
                    title: alertData.title || 'Información de Asistencia',
                    html: alertData.html || '',
                    icon: alertData.icon || 'info',
                    showConfirmButton: true,
                    confirmButtonText: alertData.confirmButtonText || 'Aceptar y continuar',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-primary rounded-pill px-4 waves-effect waves-light'
                    },
                    allowOutsideClick: false
                }).then(() => {
                    resumeScanner();
                });
            });

            Livewire.on('confirmarAsistenciaConInvitados', (event) => {
                const detail = Array.isArray(event) ? event[0] : event;

                Swal.fire({
                    title: 'Participante con Invitados',
                    html: `<b>${detail.nombre}</b> tiene <b>${detail.cantidadInvitados}</b> invitado(s) asociado(s).<br><br>¿Deseas registrar la asistencia solo para el participante principal?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, registrar solo al principal',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        confirmButton: 'btn btn-primary me-2',
                        cancelButton: 'btn btn-label-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch('confirmacionAsistenciaRecibida', {
                            inscripcionId: detail.inscripcionId
                        });
                    } else {
                        resumeScanner();
                    }
                });
            });

        });
    </script>
    @endpush
</div>
