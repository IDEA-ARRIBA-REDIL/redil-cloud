@extends('layouts/layoutMaster')

@section('title', 'Mi Línea de Vida')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.scss', 'resources/assets/vendor/libs/apex-charts/apex-charts.scss'])
    <style>
        :root {
            --timeline-color: #7c5cfc;
            --timeline-glow: rgba(124, 92, 252, 0.3);
        }

        .timeline-line-demo {
            position: absolute;
            left: 50%;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, transparent, var(--timeline-glow) 5%, var(--timeline-color) 50%, var(--timeline-glow) 95%, transparent);
            transform: translateX(-50%);
            z-index: 0;
        }

        .timeline-line-demo::after {
            content: '';
            position: absolute;
            left: 50%;
            top: 0;
            transform: translateX(-50%);
            width: 6px;
            height: 100%;
            background: linear-gradient(to bottom, transparent, var(--timeline-glow) 20%, var(--timeline-glow) 80%, transparent);
            filter: blur(6px);
        }

        .hito-wrapper-demo {
            position: relative;
            display: flex;
            margin-bottom: 32px;
        }

        .hito-wrapper-demo:nth-child(odd) {
            flex-direction: row;
            padding-right: calc(50% + 28px);
        }

        .hito-wrapper-demo:nth-child(even) {
            flex-direction: row-reverse;
            padding-left: calc(50% + 28px);
        }

        .hito-node-demo {
            position: absolute;
            left: 50%;
            top: 28px;
            transform: translateX(-50%);
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid var(--timeline-color);
            z-index: 2;
        }

        .hito-card-demo {
            width: 100%;
            background: #fff;
            border: 1px solid #dbdade;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
            transition: all 0.3s ease;
        }

        .hito-card-demo:hover {
            box-shadow: 0 8px 40px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .hito-portada-demo {
            position: relative;
            width: 100%;
            aspect-ratio: 16 / 9;
            overflow: hidden;
        }

        .hito-portada-demo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hito-portada-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, transparent 60%);
            pointer-events: none;
        }

        .hito-module-badge-demo {
            position: absolute;
            top: 14px;
            left: 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.1);
            color: #fff;
        }

        .badge-escuelas-demo { background: rgba(59,130,246,0.6); }
        .badge-grupos-demo { background: rgba(16,185,129,0.6); }
        .badge-consolidacion-demo { background: rgba(245,158,11,0.6); }
        .badge-pasos-demo { background: rgba(168,85,247,0.6); }
        .badge-general-demo { background: rgba(100,116,139,0.6); }

        .hito-body-demo {
            padding: 18px 20px;
        }

        .hito-title-demo {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2a2a3a;
            margin-bottom: 4px;
        }

        .hito-date-demo {
            font-size: 0.8rem;
            color: #8b8b9e;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .hito-description-demo {
            font-size: 0.85rem;
            color: #5a5a6e;
            line-height: 1.5;
        }

        /* Mensaje personalizado destacado para el usuario */
        .hito-mensaje-demo {
            position: relative;
            margin: 12px -20px;
            padding: 14px 20px;
            background: linear-gradient(135deg, rgba(124, 92, 252, 0.06) 0%, rgba(124, 92, 252, 0.02) 100%);
            border-left: 3px solid #7c5cfc;
            border-radius: 0;
            font-size: 0.88rem;
            color: #4a3a8a;
            line-height: 1.5;
            font-style: italic;
        }

        .hito-mensaje-demo::before {
            content: '\f6a8';
            font-family: 'remixicon';
            position: absolute;
            top: 12px;
            right: 16px;
            font-size: 1.2rem;
            color: #7c5cfc;
            opacity: 0.3;
        }

        .hito-mensaje-demo strong {
            display: block;
            font-style: normal;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #7c5cfc;
            margin-bottom: 4px;
        }

        .hito-gallery-demo {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 4px;
            margin-top: 12px;
        }

        .gallery-item-demo {
            position: relative;
            aspect-ratio: 1;
            border-radius: 8px;
            overflow: hidden;
        }

        .gallery-item-demo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hito-footer-demo {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 20px;
            border-top: 1px solid #f0f0f5;
        }

        .hito-stat-demo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .hito-stat-demo span {
            font-size: 0.82rem;
            color: #5a5a6e;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .hito-actions-demo {
            display: flex;
            gap: 6px;
        }

        .btn-action-demo {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 600;
            border: none;
            background: rgba(255, 59, 92, 0.1);
            color: #ff3b5c;
        }

        .btn-action-demo.liked {
            background: rgba(255, 59, 92, 0.2);
        }

        .hero-demo {
            position: relative;
            padding: 60px 0 40px;
            text-align: center;
            background: linear-gradient(135deg, #fafafc 0%, #fff 100%);
        }

        .hero-demo h1 {
            font-size: 2.4rem;
            font-weight: 800;
            background: linear-gradient(135deg, #2a2a3a 0%, #7c5cfc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 6px;
        }

        .hero-demo p {
            color: #5a5a6e;
            font-size: 1.05rem;
        }

        .hero-stats-demo {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 28px;
        }

        .hero-stat-value-demo {
            font-size: 1.8rem;
            font-weight: 700;
            color: #7c5cfc;
        }

        .hero-stat-label-demo {
            font-size: 0.75rem;
            color: #5a5a6e;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Demo banner para distinguir esta vista */
        .demo-banner {
            background: linear-gradient(135deg, #7c5cfc 0%, #5a3fd9 100%);
            color: #fff;
            padding: 10px 20px;
            text-align: center;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .demo-banner i {
            margin-right: 6px;
        }

        .demo-controls {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .demo-controls a {
            padding: 10px 16px;
            background: #7c5cfc;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(124, 92, 252, 0.3);
            display: flex;
            align-items: center;
            gap: 6px;
        }
    </style>
@endsection

@section('content')



    {{-- Hero --}}
    <section class="hero-demo">
        <h1><i class="ti ti-album"></i> Mi Línea de Vida</h1>
        <p>Cada paso cuenta. Esta es tu historia en la iglesia.</p>
        <div class="hero-stats-demo">
            <div>
                <div class="hero-stat-value-demo">12</div>
                <div class="hero-stat-label-demo">Hitos</div>
            </div>
            <div>
                <div class="hero-stat-value-demo">47</div>
                <div class="hero-stat-label-demo">Fotos</div>
            </div>
            <div>
                <div class="hero-stat-value-demo">183</div>
                <div class="hero-stat-label-demo">Likes</div>
            </div>
        </div>
    </section>

    {{-- Timeline --}}
    <div class="container py-5" style="max-width: 820px; position: relative;">
        <div class="timeline-line-demo"></div>

        {{-- Hito 1: Bautismo (Consolidación) --}}
        <div class="hito-wrapper-demo">
            <div class="hito-node-demo"></div>
            <div class="hito-card-demo">
                <div class="hito-portada-demo">
                    <img src="https://images.unsplash.com/photo-1504052434569-70ad5836ab65?w=800&q=80" alt="Bautismo">
                    <div class="hito-portada-overlay"></div>
                    <span class="hito-module-badge-demo badge-consolidacion-demo">
                        <i class="ti ti-plant"></i> Consolidación
                    </span>
                </div>
                <div class="hito-body-demo">
                    <div class="hito-title-demo">Bautismo</div>
                    <div class="hito-date-demo"><i class="ti ti-calendar"></i> 15 de Marzo, 2024</div>
                    <p class="hito-description-demo">El día que decidí dar el paso de fe más importante de mi vida.</p>
                    <div class="hito-mensaje-demo">
                        <strong><i class="ti ti-message-circle"></i> Mensaje de la iglesia</strong>
                        ¡Felicidades por tu bautismo! 🌊 Hoy diste el paso más importante de tu vida. Que este nuevo comienzo esté lleno de fe, esperanza y propósito. ¡Dios tiene grandes planes para ti!
                    </div>
                    <div class="hito-gallery-demo">
                        <div class="gallery-item-demo"><img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&q=80" alt=""></div>
                        <div class="gallery-item-demo"><img src="https://images.unsplash.com/photo-1519681393784-d120267933ba?w=200&q=80" alt=""></div>
                        <div class="gallery-item-demo"><img src="https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=200&q=80" alt=""></div>
                    </div>
                </div>
                <div class="hito-footer-demo">
                    <div class="hito-stat-demo">
                        <span><i class="ti ti-heart-filled" style="color: #ff3b5c;"></i> 87</span>
                        <span><i class="ti ti-photo"></i> 3</span>
                    </div>
                    <div class="hito-actions-demo">
                        <button class="btn-action-demo"><i class="ti ti-heart"></i> Me gusta</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Hito 2: Retiro Juvenil (General) --}}
        <div class="hito-wrapper-demo">
            <div class="hito-node-demo"></div>
            <div class="hito-card-demo">
                <div class="hito-portada-demo">
                    <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80" alt="Retiro">
                    <div class="hito-portada-overlay"></div>
                    <span class="hito-module-badge-demo badge-general-demo">
                        <i class="ti ti-calendar-event"></i> Evento
                    </span>
                </div>
                <div class="hito-body-demo">
                    <div class="hito-title-demo">Retiro Juvenil 2024</div>
                    <div class="hito-date-demo"><i class="ti ti-calendar"></i> 20 de Junio, 2024</div>
                    <p class="hito-description-demo">Un fin de semana increíble de comunión, worship y crecimiento espiritual.</p>
                    <div class="hito-mensaje-demo">
                        <strong><i class="ti ti-message-circle"></i> Mensaje de la iglesia</strong>
                        ¡Gracias por ser parte de este retiro! Momentos como este transforman vidas y construyen amistades eternas.
                    </div>
                    <div class="hito-gallery-demo">
                        <div class="gallery-item-demo"><img src="https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=200&q=80" alt=""></div>
                        <div class="gallery-item-demo"><img src="https://images.unsplash.com/photo-1517457373958-b7bdd4587205?w=200&q=80" alt=""></div>
                        <div class="gallery-item-demo"><img src="https://images.unsplash.com/photo-1528605248644-14dd04022da1?w=200&q=80" alt=""></div>
                    </div>
                </div>
                <div class="hito-footer-demo">
                    <div class="hito-stat-demo">
                        <span><i class="ti ti-heart-filled" style="color: #ff3b5c;"></i> 45</span>
                        <span><i class="ti ti-photo"></i> 5</span>
                    </div>
                    <div class="hito-actions-demo">
                        <button class="btn-action-demo liked"><i class="ti ti-heart-filled"></i> Te gusta</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Hito 3: Graduación (Escuelas) --}}
        <div class="hito-wrapper-demo">
            <div class="hito-node-demo"></div>
            <div class="hito-card-demo">
                <div class="hito-portada-demo">
                    <img src="https://images.unsplash.com/photo-1523050854058-8df90110c476?w=800&q=80" alt="Graduación">
                    <div class="hito-portada-overlay"></div>
                    <span class="hito-module-badge-demo badge-escuelas-demo">
                        <i class="ti ti-school"></i> Escuelas
                    </span>
                </div>
                <div class="hito-body-demo">
                    <div class="hito-title-demo">Graduación — Primer Año Bíblico</div>
                    <div class="hito-date-demo"><i class="ti ti-calendar"></i> 10 de Diciembre, 2024</div>
                    <p class="hito-description-demo">Completé el primer año de la Escuela Bíblica. Un logro que requirió dedicación.</p>
                    <div class="hito-mensaje-demo">
                        <strong><i class="ti ti-message-circle"></i> Mensaje de la iglesia</strong>
                        ¡Felicitaciones por tu graduación! 🎓 Tu esfuerzo y dedicación han dado fruto. Sigue creciendo en el conocimiento de la Palabra.
                    </div>
                </div>
                <div class="hito-footer-demo">
                    <div class="hito-stat-demo">
                        <span><i class="ti ti-heart-filled" style="color: #ff3b5c;"></i> 112</span>
                    </div>
                    <div class="hito-actions-demo">
                        <button class="btn-action-demo"><i class="ti ti-heart"></i> Me gusta</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Hito 4: Líder de Grupo (Grupos) --}}
        <div class="hito-wrapper-demo">
            <div class="hito-node-demo"></div>
            <div class="hito-card-demo">
                <div class="hito-portada-demo">
                    <img src="https://images.unsplash.com/photo-1521737711867-e3b97375f902?w=800&q=80" alt="Líder">
                    <div class="hito-portada-overlay"></div>
                    <span class="hito-module-badge-demo badge-grupos-demo">
                        <i class="ti ti-users"></i> Grupos
                    </span>
                </div>
                <div class="hito-body-demo">
                    <div class="hito-title-demo">Asignado como Líder de Grupo</div>
                    <div class="hito-date-demo"><i class="ti ti-calendar"></i> 5 de Enero, 2025</div>
                    <p class="hito-description-demo">Un honor y una responsabilidad. Lidero el Grupo "Generación de Fuego" los viernes.</p>
                    <div class="hito-mensaje-demo">
                        <strong><i class="ti ti-message-circle"></i> Mensaje de la iglesia</strong>
                        ¡Felicidades por tu designación como líder! 👑 Una nueva etapa de servicio y crecimiento comienza para ti.
                    </div>
                    <div class="hito-gallery-demo">
                        <div class="gallery-item-demo"><img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=200&q=80" alt=""></div>
                        <div class="gallery-item-demo"><img src="https://images.unsplash.com/photo-1552664730-d307ca884978?w=200&q=80" alt=""></div>
                        <div class="gallery-item-demo"><img src="https://images.unsplash.com/photo-1517048676732-d65bc937f952?w=200&q=80" alt=""></div>
                    </div>
                </div>
                <div class="hito-footer-demo">
                    <div class="hito-stat-demo">
                        <span><i class="ti ti-heart-filled" style="color: #ff3b5c;"></i> 34</span>
                        <span><i class="ti ti-photo"></i> 3</span>
                    </div>
                    <div class="hito-actions-demo">
                        <button class="btn-action-demo"><i class="ti ti-heart"></i> Me gusta</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Hito 5: Cena Navidad (General) --}}
        <div class="hito-wrapper-demo">
            <div class="hito-node-demo"></div>
            <div class="hito-card-demo">
                <div class="hito-portada-demo">
                    <img src="https://images.unsplash.com/photo-1482517967863-66e05f5a334c?w=800&q=80" alt="Cena">
                    <div class="hito-portada-overlay"></div>
                    <span class="hito-module-badge-demo badge-general-demo">
                        <i class="ti ti-calendar-event"></i> Evento
                    </span>
                </div>
                <div class="hito-body-demo">
                    <div class="hito-title-demo">Cena de Navidad 2024</div>
                    <div class="hito-date-demo"><i class="ti ti-calendar"></i> 24 de Diciembre, 2024</div>
                    <p class="hito-description-demo">Una velada mágica con toda la iglesia. Música, comida y mucha alegría.</p>
                    <div class="hito-mensaje-demo">
                        <strong><i class="ti ti-message-circle"></i> Mensaje de la iglesia</strong>
                        ¡Gracias por celebrar juntos la navidad! 🎄 Que la paz y el amor de Cristo llenen tu hogar en este nuevo año.
                    </div>
                    <div class="hito-gallery-demo">
                        <div class="gallery-item-demo"><img src="https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=200&q=80" alt=""></div>
                        <div class="gallery-item-demo"><img src="https://images.unsplash.com/photo-1480796927426-f609979314bd?w=200&q=80" alt=""></div>
                        <div class="gallery-item-demo"><img src="https://images.unsplash.com/photo-1549451371-64aa98a6f660?w=200&q=80" alt=""></div>
                    </div>
                </div>
                <div class="hito-footer-demo">
                    <div class="hito-stat-demo">
                        <span><i class="ti ti-heart-filled" style="color: #ff3b5c;"></i> 67</span>
                        <span><i class="ti ti-photo"></i> 6</span>
                    </div>
                    <div class="hito-actions-demo">
                        <button class="btn-action-demo liked"><i class="ti ti-heart-filled"></i> Te gusta</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Botón flotante para ir al form admin --}}
    <div class="demo-controls">
        <a href="{{ url('/contenido/paginas/hitos/crear.blade.php') }}" onclick="event.preventDefault(); alert('Esta es la vista del usuario (feligrés). Para ver el formulario admin, abre el archivo: resources/views/contenido/paginas/hitos/crear.blade.php');">
            <i class="ti ti-info-circle"></i> Vista admin
        </a>
    </div>
@endsection
