<div class="landing-contact-card">
    @if ($enviado)
        <div class="landing-contact-success text-center py-4 px-2" role="status" aria-live="polite">
            <div class="landing-success-badge mx-auto mb-4" aria-hidden="true">
                <i class="bi bi-check-lg"></i>
            </div>
            <h3 class="fw-bolder text-dark mb-2" style="font-size: 1.75rem;">¡Solicitud recibida!</h3>
            <p class="text-secondary mb-4 mx-auto" style="max-width: 440px;">
                Nuestro equipo revisará los datos de tu congregación y te responderá en menos de 24 horas hábiles.
                Si prefieres atención inmediata, continúa ahora mismo por WhatsApp con tu información prellenada.
            </p>
            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3 mb-4">
                <a href="{{ $whatsappConfirmacionUrl }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="btn landing-btn-whatsapp rounded-pill px-4 py-3 fw-bold d-inline-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-whatsapp fs-5" aria-hidden="true"></i>
                    <span>Continuar por WhatsApp</span>
                </a>
                <button type="button"
                        wire:click="nuevaSolicitud"
                        class="btn btn-outline-secondary rounded-pill px-4 py-3 fw-semibold">
                    Enviar otra consulta
                </button>
            </div>
            @if (! $correoEnviado && app()->environment('local'))
                <p class="small text-muted mb-0">
                    Modo local: configura <code>MAIL_MAILER</code> y <code>LANDING_CONTACT_EMAIL</code> en <code>.env</code> para recibir el correo en tu bandeja.
                </p>
            @endif
        </div>
    @else
        <div class="mb-4">
            <span class="landing-form-pill mb-2 d-inline-flex align-items-center gap-2">
                <i class="bi bi-lightning-charge-fill" aria-hidden="true"></i>
                Respuesta en menos de 24 horas
            </span>
            <h3 id="titulo-form-contacto" class="fw-bolder text-dark mb-1" style="font-size: 1.75rem;">
                Agenda tu videollamada
            </h3>
            <p class="text-secondary small mb-0">
                Te mostramos todas las ventajas de REDIL Cloud adaptadas al tamaño y estructura de tu iglesia. Sin compromiso.
            </p>
        </div>

        @if ($errorLimite !== '')
            <div class="alert alert-warning rounded-4 d-flex align-items-start gap-2 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill mt-1" aria-hidden="true"></i>
                <div>{{ $errorLimite }}</div>
            </div>
        @endif

        <form wire:submit="enviarSolicitud" aria-labelledby="titulo-form-contacto" novalidate>
            {{-- Honeypot accesible para bloquear bots sin afectar lectores de pantalla --}}
            <div class="visually-hidden" aria-hidden="true">
                <label for="landing-website">Sitio web</label>
                <input type="text"
                       id="landing-website"
                       name="website"
                       wire:model="website"
                       tabindex="-1"
                       autocomplete="off">
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label for="landing-iglesia" class="form-label fw-bold text-dark small mb-1">
                        Nombre de la iglesia <span class="text-danger" aria-hidden="true">*</span>
                    </label>
                    <input type="text"
                           id="landing-iglesia"
                           name="nombreIglesia"
                           wire:model.blur="nombreIglesia"
                           autocomplete="organization"
                           enterkeyhint="next"
                           required
                           aria-invalid="{{ $errors->has('nombreIglesia') ? 'true' : 'false' }}"
                           @error('nombreIglesia') aria-describedby="error-nombreIglesia" @enderror
                           class="form-control landing-input @error('nombreIglesia') is-invalid @enderror"
                           placeholder="Ej. Iglesia Vida Nueva">
                    @error('nombreIglesia')
                        <div id="error-nombreIglesia" class="invalid-feedback d-flex align-items-center gap-1" role="alert">
                            <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                </div>

                <div class="col-sm-6">
                    <label for="landing-contacto" class="form-label fw-bold text-dark small mb-1">
                        Pastor o líder de contacto <span class="text-danger" aria-hidden="true">*</span>
                    </label>
                    <input type="text"
                           id="landing-contacto"
                           name="nombreContacto"
                           wire:model.blur="nombreContacto"
                           autocomplete="name"
                           enterkeyhint="next"
                           required
                           aria-invalid="{{ $errors->has('nombreContacto') ? 'true' : 'false' }}"
                           @error('nombreContacto') aria-describedby="error-nombreContacto" @enderror
                           class="form-control landing-input @error('nombreContacto') is-invalid @enderror"
                           placeholder="Ej. Pastor Andrés Gómez">
                    @error('nombreContacto')
                        <div id="error-nombreContacto" class="invalid-feedback d-flex align-items-center gap-1" role="alert">
                            <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label for="landing-email" class="form-label fw-bold text-dark small mb-1">
                        Correo electrónico <span class="text-danger" aria-hidden="true">*</span>
                    </label>
                    <input type="email"
                           id="landing-email"
                           name="email"
                           wire:model.blur="email"
                           autocomplete="email"
                           autocorrect="off"
                           autocapitalize="none"
                           spellcheck="false"
                           enterkeyhint="next"
                           required
                           aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                           @error('email') aria-describedby="error-email" @enderror
                           class="form-control landing-input @error('email') is-invalid @enderror"
                           placeholder="pastor@tuiglesia.org">
                    @error('email')
                        <div id="error-email" class="invalid-feedback d-flex align-items-center gap-1" role="alert">
                            <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                </div>

                <div class="col-sm-6">
                    <label for="landing-whatsapp" class="form-label fw-bold text-dark small mb-1">
                        WhatsApp / Teléfono <span class="text-danger" aria-hidden="true">*</span>
                    </label>
                    <input type="tel"
                           id="landing-whatsapp"
                           name="whatsapp"
                           wire:model.blur="whatsapp"
                           autocomplete="tel"
                           inputmode="tel"
                           enterkeyhint="next"
                           required
                           aria-invalid="{{ $errors->has('whatsapp') ? 'true' : 'false' }}"
                           @error('whatsapp') aria-describedby="error-whatsapp" @enderror
                           class="form-control landing-input @error('whatsapp') is-invalid @enderror"
                           placeholder="+57 300 123 4567">
                    @error('whatsapp')
                        <div id="error-whatsapp" class="invalid-feedback d-flex align-items-center gap-1" role="alert">
                            <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label for="landing-pais" class="form-label fw-bold text-dark small mb-1">
                        País <span class="text-danger" aria-hidden="true">*</span>
                    </label>
                    <select id="landing-pais"
                            name="pais"
                            wire:model.blur="pais"
                            autocomplete="country-name"
                            required
                            aria-invalid="{{ $errors->has('pais') ? 'true' : 'false' }}"
                            @error('pais') aria-describedby="error-pais" @enderror
                            class="form-select landing-input @error('pais') is-invalid @enderror">
                        <option value="">Selecciona tu país...</option>
                        @foreach ($paises as $opcionPais)
                            <option value="{{ $opcionPais }}">{{ $opcionPais }}</option>
                        @endforeach
                    </select>
                    @error('pais')
                        <div id="error-pais" class="invalid-feedback d-flex align-items-center gap-1" role="alert">
                            <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                </div>

                <div class="col-sm-6">
                    <label for="landing-tamano" class="form-label fw-bold text-dark small mb-1">
                        Tamaño de la congregación <span class="text-danger" aria-hidden="true">*</span>
                    </label>
                    <select id="landing-tamano"
                            name="tamanoCongregacion"
                            wire:model.blur="tamanoCongregacion"
                            required
                            aria-invalid="{{ $errors->has('tamanoCongregacion') ? 'true' : 'false' }}"
                            @error('tamanoCongregacion') aria-describedby="error-tamanoCongregacion" @enderror
                            class="form-select landing-input @error('tamanoCongregacion') is-invalid @enderror">
                        <option value="">Número aproximado de miembros...</option>
                        @foreach ($tamanos as $opcionTamano)
                            <option value="{{ $opcionTamano }}">{{ $opcionTamano }}</option>
                        @endforeach
                    </select>
                    @error('tamanoCongregacion')
                        <div id="error-tamanoCongregacion" class="invalid-feedback d-flex align-items-center gap-1" role="alert">
                            <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="landing-modulo" class="form-label fw-bold text-dark small mb-1">
                    ¿Qué área te interesa priorizar?
                </label>
                <select id="landing-modulo"
                        name="moduloInteres"
                        wire:model="moduloInteres"
                        class="form-select landing-input">
                    @foreach ($modulosDisponibles as $opcionModulo)
                        <option value="{{ $opcionModulo }}">{{ $opcionModulo }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label for="landing-mensaje" class="form-label fw-bold text-dark small mb-1">
                    Cuéntanos sobre tu iglesia <span class="text-muted fw-normal">(opcional)</span>
                </label>
                <textarea id="landing-mensaje"
                          name="mensaje"
                          wire:model.blur="mensaje"
                          rows="3"
                          maxlength="1000"
                          enterkeyhint="done"
                          class="form-control landing-input @error('mensaje') is-invalid @enderror"
                          placeholder="Ej. Tenemos 3 sedes, varios grupos y queremos digitalizar nuestras escuelas bíblicas..."></textarea>
                @error('mensaje')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex flex-column flex-sm-row gap-3 align-items-stretch align-items-sm-center">
                <button type="submit"
                        wire:loading.attr="disabled"
                        wire:target="enviarSolicitud"
                        class="btn landing-btn-primary rounded-pill px-4 py-3 fw-bold flex-grow-1 d-inline-flex align-items-center justify-content-center gap-2">
                    <span wire:loading.remove wire:target="enviarSolicitud">Agendar videollamada</span>
                    <span wire:loading.inline-flex wire:target="enviarSolicitud" class="align-items-center gap-2">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Enviando solicitud...
                    </span>
                    <i wire:loading.remove wire:target="enviarSolicitud" class="bi bi-arrow-right" aria-hidden="true"></i>
                </button>

                <a href="{{ $this->whatsappDirectoUrl }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="btn landing-btn-whatsapp-outline rounded-pill px-4 py-3 fw-bold d-inline-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-whatsapp fs-5" aria-hidden="true"></i>
                    <span>Ir a WhatsApp</span>
                </a>
            </div>
        </form>
    @endif
</div>
