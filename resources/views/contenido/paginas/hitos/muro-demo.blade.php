<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#080a19">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Mi Camino de Fe · Túnel del tiempo · REDIL Cloud</title>

<!-- Fonts & Icons -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

<style>
:root{
  --bg:#070916;
  --text:#f8f9ff;
  --muted:#a9adc3;
  --accent:#f2c66d;
  --border:rgba(255,255,255,.14);
  --font-display:'Fraunces', Georgia, serif;
  --font-body:'Inter', -apple-system, sans-serif;
}
*{box-sizing:border-box; margin:0; padding:0;}
html,body{
  width:100%; height:100%; overflow:hidden;
  background:var(--bg);
  font-family:var(--font-body);
  color:var(--text);
  -webkit-font-smoothing:antialiased;
}
button{font:inherit}
[x-cloak]{display:none!important}

.app{
  position:relative; width:100%; height:100dvh; overflow:hidden;
  background:linear-gradient(180deg,#171b3a 0%,#10142e 44%,#080a19 100%);
  display:flex; flex-direction:column;
  isolation:isolate;
  touch-action:none;
}

/* =========================================================
   CAPAS 2D DEL PAISAJE (PARALLAX CÓSMICO)
   ========================================================= */
.sky{
  position:absolute; z-index:-8; inset:0;
  background:
    radial-gradient(circle at 50% 25%,rgba(255,200,119,.15),transparent 22%),
    radial-gradient(circle at 50% 40%,rgba(133,103,255,.12),transparent 35%),
    linear-gradient(180deg,#24294f 0%,#141938 42%,#080a19 100%);
}
.stars{
  position:absolute; z-index:-7; inset:0; opacity:.25;
  background-image:
    radial-gradient(circle,#fff 0 1px,transparent 1.2px),
    radial-gradient(circle,#fff 0 1px,transparent 1.2px);
  background-size:83px 83px,127px 127px;
  background-position:15px 20px,70px 46px;
  mask-image:linear-gradient(180deg,#000,transparent 62%);
}
.layer{
  position:absolute; left:-18%; width:136%; pointer-events:none;
  will-change:transform;
}
.clouds{
  z-index:-5; top:6%; height:22%; opacity:.15;
  animation:cloudPan 36s linear infinite;
}
.mountains-back{
  z-index:-4; bottom:14%; height:46%; opacity:.38;
  animation:mountainBack 48s linear infinite alternate;
}
.mountains-front{
  z-index:-3; bottom:5%; height:40%; opacity:.75;
  animation:mountainFront 34s linear infinite alternate-reverse;
}
.layer svg{width:100%; height:100%; display:block}

@keyframes cloudPan{
  from{transform:translate3d(-4%,0,0)}
  to{transform:translate3d(18%,0,0)}
}
@keyframes mountainBack{
  from{transform:translate3d(-3%,0,0)}
  to{transform:translate3d(6%,0,0)}
}
@keyframes mountainFront{
  from{transform:translate3d(-5%,0,0)}
  to{transform:translate3d(4%,0,0)}
}

.horizon-glow{
  position:absolute; z-index:0; left:50%; top:22%;
  width:min(40vw,500px); aspect-ratio:1;
  transform:translate(-50%,-50%);
  border-radius:50%;
  background:radial-gradient(circle,rgba(255,217,143,.18),rgba(156,121,255,.09) 45%,transparent 72%);
  filter:blur(10px); pointer-events:none;
}
.vignette{
  position:absolute; z-index:3; inset:0; pointer-events:none;
  background:
    linear-gradient(180deg,rgba(2,3,11,.3),transparent 20%,transparent 75%,rgba(2,3,10,.65)),
    radial-gradient(ellipse at center,transparent 0 45%,rgba(3,4,12,.12) 65%,rgba(2,3,10,.75) 100%);
}

/* =========================================================
   TOPBAR
   ========================================================= */
.topbar{
  position:relative; z-index:40;
  display:flex; align-items:flex-start; justify-content:space-between; gap:16px;
  padding:20px clamp(18px,4vw,48px) 0;
  flex-shrink:0; pointer-events:none;
}
.eyebrow{
  display:flex; align-items:center; gap:8px;
  color:var(--accent); font-weight:800; font-size:11px;
  letter-spacing:.12em; text-transform:uppercase; margin-bottom:4px;
}
.topbar h1{
  font-family:var(--font-display); font-weight:700;
  font-size:clamp(22px,3.5vw,42px); color:#fff;
  line-height:.98; letter-spacing:-.02em;
}
.topbar h1 span{
  display:block; color:rgba(255,255,255,.5); font-family:var(--font-body);
  font-size:13px; font-weight:500; margin-top:4px; letter-spacing:0;
}
.topbar-actions{
  display:flex; align-items:center; gap:10px; pointer-events:auto;
}
.year-selector-wrap{
  position:relative;
}
.year-select{
  appearance:none; -webkit-appearance:none;
  background:rgba(12,16,38,.75);
  color:#f8f9ff;
  border:1px solid var(--border);
  border-radius:14px;
  padding:8px 32px 8px 14px;
  font-size:12px; font-weight:700;
  cursor:pointer;
  backdrop-filter:blur(12px);
  outline:none;
  transition:all .2s ease;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2.5'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
  background-repeat:no-repeat;
  background-position:right 12px center;
}
.year-select:hover{
  background-color:rgba(255,255,255,.12); border-color:rgba(255,255,255,.35);
}
.year-select option{
  background:#0c1026; color:#fff; font-weight:600;
}
.btn-back-redil{
  display:inline-flex; align-items:center; gap:7px;
  padding:8px 16px; border:1px solid var(--border); border-radius:14px;
  background:rgba(7,8,23,.55); color:#fff; text-decoration:none;
  font-size:12px; font-weight:700; backdrop-filter:blur(12px);
  transition:all .2s ease; white-space:nowrap;
}
.btn-back-redil:hover{
  background:rgba(255,255,255,.14); border-color:rgba(255,255,255,.3); color:#fff;
}

/* =========================================================
   TUNNEL ESCENE (ESCENARIO 3D INTERPOLADO)
   ========================================================= */
.tunnel-wrap{
  position:relative; flex:1 1 0%; min-height:0; width:100%;
}
.tunnel-scene{
  position:absolute; inset:0; overflow:hidden;
  cursor:grab; touch-action:none;
}
.tunnel-scene.dragging{ cursor:grabbing; }

.rail-svg{
  position:absolute; inset:0; width:100%; height:100%;
  pointer-events:none; z-index:1;
}
.tunnel-track{
  position:absolute; inset:0; z-index:2;
}

.tunnel-node{
  position:absolute; left:50%; top:0;
  width:290px;
  transform-origin:50% 100%;
  will-change:transform, opacity, top;
  cursor:pointer;
}

.year-tag{
  text-align:center; font-weight:800; font-size:12px;
  color:rgba(255,255,255,.75); margin-bottom:8px;
  letter-spacing:.04em; text-shadow:0 2px 8px rgba(0,0,0,.6);
}

.node-card{
  width:290px; background:#fcfcfe; color:#141724;
  border-radius:18px; overflow:hidden;
  border:2px solid transparent;
  box-shadow:0 14px 40px rgba(0,0,0,.45);
  transition:box-shadow .2s ease, border-color .2s ease;
}
.node-card.is-focus{
  box-shadow:0 24px 60px rgba(0,0,0,.6), 0 0 0 3px var(--c, #f2c66d), 0 0 40px color-mix(in srgb, var(--c, #f2c66d) 30%, transparent);
  border-color:var(--c, #f2c66d);
}

.card-img-wrap{ position:relative; aspect-ratio:16/9; background:#0b0e1e; overflow:hidden; }
.card-img{ width:100%; height:100%; object-fit:cover; display:block; }
.card-img-wrap::after{
  content:""; position:absolute; inset:0;
  background:linear-gradient(180deg,transparent 40%,rgba(6,9,20,.4));
}

.card-badge{
  position:absolute; z-index:2; left:10px; top:10px;
  padding:5px 9px; border-radius:999px;
  background:rgba(9,12,25,.82); color:#fff;
  font-size:9.5px; font-weight:800; letter-spacing:.05em; text-transform:uppercase;
  backdrop-filter:blur(6px); border:1px solid rgba(255,255,255,.14);
}
.card-year-badge{
  position:absolute; z-index:2; right:10px; top:10px;
  padding:4px 8px; border-radius:8px;
  background:var(--c, #f2c66d); color:#111624;
  font-size:11px; font-weight:900; box-shadow:0 4px 12px rgba(0,0,0,.25);
}

.card-body{ padding:14px 16px 15px; }
.card-title{
  font-family:var(--font-display); font-weight:600;
  font-size:17px; color:#141724; line-height:1.15;
}
.card-date{ color:#6c7284; font-size:11px; margin-top:3px; font-weight:600; }
.card-desc{ color:#505668; font-size:12px; margin-top:7px; line-height:1.45; }
.card-msg{
  margin-top:9px; border-left:3px solid var(--c, #f2c66d);
  background:color-mix(in srgb, var(--c, #f2c66d) 10%, #f7f8fc);
  padding:7px 10px; border-radius:0 8px 8px 0;
}
.card-msg p{ font-size:11px; color:#383d4e; font-style:italic; line-height:1.4; }
.card-footer{
  display:flex; align-items:center; justify-content:space-between;
  margin-top:10px; padding-top:9px; border-top:1px solid #e4e7ec;
  font-size:11px; color:#6b7280; font-weight:700;
}
.card-like-badge{ color:#e54d72; }

/* =========================================================
   NAV BUTTONS FLOTANTES
   ========================================================= */
.tunnel-nav{
  position:absolute; top:50%; transform:translateY(-50%);
  width:46px; height:46px; border-radius:50%;
  background:rgba(8,11,28,.75); border:1px solid var(--border);
  display:flex; align-items:center; justify-content:center;
  cursor:pointer; color:#fff; z-index:25;
  box-shadow:0 10px 28px rgba(0,0,0,.4); backdrop-filter:blur(8px);
  transition:all .2s ease;
}
.tunnel-nav:hover{
  background:var(--accent); color:#111624; border-color:var(--accent);
  transform:translateY(-50%) scale(1.08);
}
.tunnel-nav.prev{ left:20px; }
.tunnel-nav.next{ right:20px; }

/* =========================================================
   SCRUBBER / TIMELINE BAR FIJA EN EL PIE DE PÁGINA
   ========================================================= */
.scrubber{
  position:relative; z-index:35; flex-shrink:0;
  background:#080a1a;
  border-top:1px solid var(--border);
  box-shadow:0 -10px 30px rgba(0,0,0,.5);
}
.scrubber-ruler{
  position:relative; height:40px; padding:0 clamp(20px,5vw,50px);
  display:flex; align-items:center;
  background:rgba(12,15,36,.95);
  border-bottom:1px solid rgba(255,255,255,.08);
}
.scrubber-ruler-track{
  position:relative; flex:1; height:100%; cursor:pointer;
}
.scrubber-baseline{
  position:absolute; left:0; right:0; top:50%; height:2px;
  background:rgba(255,255,255,.2);
}
.scrubber-mark{
  position:absolute; top:50%; transform:translate(-50%,-50%);
  width:7px; height:7px; border-radius:50%;
  background:rgba(255,255,255,.45); cursor:pointer;
  transition:all .2s ease;
}
.scrubber-mark:hover{
  background:var(--accent); transform:translate(-50%,-50%) scale(1.6);
}
.scrubber-mark.era{
  width:2px; height:18px; border-radius:2px; background:var(--accent);
}
.scrubber-mark-label{
  position:absolute; top:18px; left:50%; transform:translateX(-50%);
  font-size:8.5px; color:var(--accent); font-weight:800;
  letter-spacing:.05em; text-transform:uppercase; white-space:nowrap;
}
.scrubber-playhead{
  position:absolute; top:-7px; width:2px; height:calc(100% + 14px);
  background:var(--accent); box-shadow:0 0 12px var(--accent);
  transform:translateX(-50%); pointer-events:none; z-index:4;
}
.scrubber-playhead::before{
  content:''; position:absolute; top:-5px; left:50%; transform:translateX(-50%);
  width:10px; height:10px; border-radius:50%; background:var(--accent);
  box-shadow:0 0 8px var(--accent);
}
.scrubber-legend{
  display:flex; align-items:center; gap:16px;
  padding:9px clamp(20px,5vw,50px); overflow-x:auto; white-space:nowrap;
  background:#080a1a;
}
.legend-chip{
  display:flex; align-items:center; gap:6px; font-size:11px;
  color:var(--muted); font-weight:700; flex-shrink:0;
}
.legend-chip .sw{
  width:9px; height:9px; border-radius:3px; flex-shrink:0;
}

/* =========================================================
   MODAL EXPANSIVO
   ========================================================= */
.modal-bg{
  position:fixed; z-index:100; inset:0;
  display:grid; place-items:center; padding:24px;
  background:rgba(2,3,10,.86);
  backdrop-filter:blur(18px);
}
.modal{
  position:relative; width:min(960px,100%); max-height:90dvh; overflow:auto;
  border:1px solid var(--border); border-radius:24px;
  background:#0d1028; box-shadow:0 30px 90px rgba(0,0,0,.6);
}
.close{
  position:absolute; z-index:4; right:16px; top:16px;
  width:40px; height:40px; border-radius:50%;
  border:1px solid var(--border); background:rgba(6,8,22,.8);
  color:#fff; font-size:24px; cursor:pointer;
  display:grid; place-items:center;
}
.modal-grid{ display:grid; grid-template-columns:1.1fr .9fr; }
.modal-media{
  position:relative; min-height:420px; background:#060916;
  display:flex; flex-direction:column;
}
.modal-media img{
  display:block; width:100%; height:100%; min-height:420px; object-fit:cover;
}
.modal-video-container{
  position:relative; width:100%; height:100%; min-height:420px;
  background:#000; display:flex; align-items:center; justify-content:center;
}
.modal-video-container iframe{
  width:100%; height:100%; min-height:420px; border:0;
}
.info{ padding:38px 32px; }
.tag{
  margin:0 0 6px; color:var(--accent); font-size:10.5px; font-weight:900;
  letter-spacing:.14em; text-transform:uppercase;
}
.year{
  margin:0; color:rgba(255,255,255,.18);
  font-size:72px; font-weight:950; line-height:.86; letter-spacing:-.07em;
}
.info h2{
  font-family:var(--font-display);
  font-size:28px; line-height:1.1; margin:16px 0 12px; letter-spacing:-.02em; color:#fff;
}
.desc{ color:var(--muted); font-size:13px; line-height:1.7; }
.mensaje-pastoral{
  margin:18px 0; padding:14px 16px;
  border-radius:14px; background:rgba(242,198,109,.08);
  border-left:4px solid var(--accent);
  color:#f8f9ff; font-size:12px; line-height:1.6;
}
.mensaje-pastoral-title{
  color:var(--accent); font-weight:800; font-size:10px;
  letter-spacing:.1em; text-transform:uppercase; margin-bottom:4px;
}
.gallery-grid{
  display:grid; grid-template-columns:repeat(auto-fill, minmax(75px, 1fr)); gap:8px;
  margin-top:14px;
}
.gallery-thumb{
  aspect-ratio:1/1; border-radius:8px; overflow:hidden; border:1px solid rgba(255,255,255,.12);
}
.gallery-thumb img{
  width:100%; height:100%; object-fit:cover; display:block;
}
.like{
  margin-top:18px; min-height:44px; padding:0 18px;
  border:1px solid var(--border); border-radius:12px;
  background:rgba(255,255,255,.06); color:#fff; cursor:pointer;
  display:inline-flex; align-items:center; gap:6px;
  transition:all .2s ease;
}
.like.liked{
  border-color:rgba(255,94,123,.5); background:rgba(255,94,123,.18); color:#ff5e7b;
}
.like b{margin-left:4px}

/* =========================================================
   RESPONSIVE EN MÓVILES (PANTALLAS <= 768px)
   ========================================================= */
@media(max-width:768px){
  .topbar{ padding:14px 16px 0; }
  .topbar h1{ font-size:20px; }
  .topbar h1 span{ font-size:11px; }
  .btn-back-redil{ padding:6px 10px; font-size:11px; border-radius:10px; }

  /* Tarjetas centradas en móviles */
  .tunnel-node{ width:260px; }
  .node-card{ width:260px; }
  .card-desc{ display:none; }
  .card-title{ font-size:15px; }

  /* Flechas visibles y accesibles en móvil */
  .tunnel-nav{ width:38px; height:38px; }
  .tunnel-nav.prev{ left:10px; }
  .tunnel-nav.next{ right:10px; }

  /* Ocultar barra inferior en móviles para limpiar la vista */
  .scrubber{ display:none!important; }

  /* Modal en móvil */
  .modal-bg{ padding:0; align-items:end; }
  .modal{ max-height:92dvh; border-radius:24px 24px 0 0; }
  .modal-grid{ grid-template-columns:1fr; }
  .modal-media,.modal-media img{ min-height:220px; max-height:260px; }
  .info{ padding:20px 18px 26px; }
  .year{ font-size:46px; }
}
</style>
</head>

<body>
<div class="app">
  <!-- Paisaje Parallax 2D -->
  <div class="sky"></div>
  <div class="stars"></div>

  <!-- Nubes animadas -->
  <div class="layer clouds" aria-hidden="true">
    <svg viewBox="0 0 1800 300" preserveAspectRatio="none">
      <g fill="#ffffff">
        <g transform="translate(60 55)">
          <ellipse cx="130" cy="95" rx="125" ry="30"/>
          <circle cx="125" cy="67" r="53"/>
          <circle cx="201" cy="82" r="43"/>
        </g>
        <g transform="translate(720 5) scale(.82)">
          <ellipse cx="130" cy="95" rx="125" ry="30"/>
          <circle cx="125" cy="67" r="53"/>
          <circle cx="201" cy="82" r="43"/>
        </g>
        <g transform="translate(1370 76) scale(.66)">
          <ellipse cx="130" cy="95" rx="125" ry="30"/>
          <circle cx="125" cy="67" r="53"/>
          <circle cx="201" cy="82" r="43"/>
        </g>
      </g>
    </svg>
  </div>

  <!-- Montañas fondo -->
  <div class="layer mountains-back" aria-hidden="true">
    <svg viewBox="0 0 1900 520" preserveAspectRatio="none">
      <path d="M0 520V300L160 195l125 95 185-175 165 162 220-210 165 185 165-135 145 145 205-230 185 202 150-115 180 190V520Z" fill="#3b3363"/>
    </svg>
  </div>

  <!-- Montañas frente -->
  <div class="layer mountains-front" aria-hidden="true">
    <svg viewBox="0 0 1900 520" preserveAspectRatio="none">
      <path d="M0 520V350l185-140 160 108 205-198 170 174 190-132 172 169 195-221 188 207 168-111 146 136 196-176 171 173V520Z" fill="#131732"/>
    </svg>
  </div>

  <div class="horizon-glow"></div>
  <div class="vignette"></div>

  <!-- 1. Cabecera Superior -->
  <header class="topbar">
    <div>
      <div class="eyebrow">
        <i class="ti ti-map-pin"></i> Mi Camino de Fe
      </div>
      <h1>
        Túnel del tiempo
        <span>Cada hito de tu historia, en profundidad interactiva.</span>
      </h1>
    </div>

    <div class="topbar-actions">
      <!-- Selector de Años para salto instantáneo -->
      <div class="year-selector-wrap" id="yearSelectorWrap" style="display:none;">
        <select id="yearSelect" class="year-select" onchange="jumpToYear(this.value)" aria-label="Ir al año">
          <option value="" disabled selected>📅 Saltar al año...</option>
        </select>
      </div>

      <!-- Botón Volver al Dashboard Principal -->
      <a href="{{ route('dashboard') }}" class="btn-back-redil" title="Volver al Inicio">
        <i class="ti ti-arrow-left"></i> Volver
      </a>
    </div>
  </header>

  <!-- 2. Escenario 3D del Túnel (Área central que ocupa todo el espacio restante) -->
  <div class="tunnel-wrap">
    <div class="tunnel-scene" id="scene">
      <svg class="rail-svg" id="railSvg"></svg>
      <div class="tunnel-track" id="track"></div>
    </div>

    <!-- Flechas flotantes siempre disponibles -->
    <button type="button" class="tunnel-nav prev" id="navPrev" aria-label="Anterior">
      <i class="ti ti-chevron-left fs-4"></i>
    </button>
    <button type="button" class="tunnel-nav next" id="navNext" aria-label="Siguiente">
      <i class="ti ti-chevron-right fs-4"></i>
    </button>
  </div>

  <!-- 3. Scrubber / Línea de Tiempo Fija en la Parte Inferior (Solo Desktop) -->
  <footer class="scrubber">
    <div class="scrubber-ruler">
      <div class="scrubber-ruler-track" id="scrubberRulerTrack">
        <div class="scrubber-baseline"></div>
        <div class="scrubber-playhead" id="playhead"></div>
      </div>
    </div>
    <div class="scrubber-legend" id="scrubberLegend"></div>
  </footer>

  <!-- Modal Detallado -->
  <div class="modal-bg" id="modalBg" style="display:none;" onclick="if(event.target===this) closeModal();">
    <article class="modal">
      <button class="close" type="button" onclick="closeModal()">×</button>
      <div class="modal-grid">
        <div class="modal-media">
          <!-- Reproductor de YouTube si el hito tiene video_url -->
          <div class="modal-video-container" id="modalVideoContainer" style="display:none;">
            <iframe id="modalVideoIframe" src="" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
          </div>
          <!-- Imagen de Portada si no tiene video o mientras carga -->
          <img id="modalImg" src="" alt="Hito" style="display:block;">
        </div>
        <div class="info">
          <p class="tag" id="modalTag"></p>
          <p class="year" id="modalYear"></p>
          <h2 id="modalTitle"></h2>
          <p class="desc" id="modalDesc"></p>

          <div class="mensaje-pastoral" id="modalMsgBox" style="display:none;">
            <div class="mensaje-pastoral-title">
              <i class="ti ti-message-2 me-1"></i> Mensaje para tu camino espiritual
            </div>
            <div id="modalMsg"></div>
          </div>

          <div id="modalGalleryBox" style="display:none;">
            <small class="text-muted fw-bold d-block mt-3 mb-1 text-uppercase" style="letter-spacing:.08em; font-size:9px;">
              Galería Oficial del Hito
            </small>
            <div class="gallery-grid" id="modalGalleryGrid"></div>
          </div>

          <div class="d-flex align-items-center gap-3 mt-3">
            <button type="button" class="like" id="modalLikeBtn" onclick="toggleModalLike()">
              <span id="modalLikeText">♡ Me gusta</span>
              <b id="modalLikesCount">0</b>
            </button>
          </div>
        </div>
      </div>
    </article>
  </div>
</div>

<script>
  // Datos reales inyectados desde Laravel Controller (Base de Datos)
  const events = @json($events ?? []);

  // Variables DOM
  const track = document.getElementById('track');
  const scene = document.getElementById('scene');
  const railSvg = document.getElementById('railSvg');
  const scrubberLegend = document.getElementById('scrubberLegend');
  const scrubberRulerTrack = document.getElementById('scrubberRulerTrack');
  const playhead = document.getElementById('playhead');

  function clamp(v, min, max){ return Math.max(min, Math.min(max, v)); }

  // Parámetros de la curva cónica y espaciado continuo
  const TAPER = 30;
  function railOffset(t, W){
    const isMobile = window.innerWidth <= 768;
    const taperVal = isMobile ? 22 : TAPER;
    const horizOffset = (W * taperVal / 100) / 2;
    const horizRange = (W - (W * taperVal / 100)) / 2;
    return horizOffset + horizRange * (t * t);
  }
  const TAPER_FRAC = TAPER / 100;
  function scaleForT(t, H){
    const base = TAPER_FRAC + (1 - TAPER_FRAC) * t * t;
    const globalScale = clamp(H / 600, 1.0, 1.45);
    return base * globalScale;
  }

  const SPACING = 620;
  const VISIBLE_RANGE = 1200;
  const PASSED_RANGE = 260;

  // Llenar selector de salto rápido por año
  const yearSelect = document.getElementById('yearSelect');
  const yearSelectorWrap = document.getElementById('yearSelectorWrap');
  if(yearSelect && events.length > 0){
    const uniqueYears = [...new Set(events.map(h => parseInt(h.year)).filter(y => !isNaN(y)))].sort((a, b) => b - a);
    if(uniqueYears.length > 1){
      uniqueYears.forEach(year => {
        const opt = document.createElement('option');
        opt.value = year;
        opt.textContent = `Año ${year}`;
        yearSelect.appendChild(opt);
      });
      yearSelectorWrap.style.display = 'block';
    }
  }

  function jumpToYear(year){
    const targetYear = parseInt(year);
    // Buscar el primer hito que corresponda a ese año
    const targetIdx = events.findIndex(h => parseInt(h.year) === targetYear);
    if(targetIdx !== -1){
      progress = targetIdx * SPACING;
    }
  }

  // Llenar leyenda de categorías
  if(scrubberLegend){
    const seenCategories = new Set();
    events.forEach(h => {
      if(!h.category || seenCategories.has(h.category)) return;
      seenCategories.add(h.category);
      const chip = document.createElement('div');
      chip.className = 'legend-chip';
      chip.innerHTML = `<span class="sw" style="background:${h.color || '#f2c66d'}"></span>${h.category}`;
      scrubberLegend.appendChild(chip);
    });
  }

  // Llenar marcadores en la regla inferior (Scrubber Ruler)
  if(scrubberRulerTrack){
    events.forEach((h, i) => {
      const pctPos = events.length <= 1 ? 0 : i / (events.length - 1);
      const isEra = (i % 3 === 0);
      const mark = document.createElement('div');
      mark.className = 'scrubber-mark' + (isEra ? ' era' : '');
      mark.style.left = (pctPos * 100) + '%';
      mark.title = h.title;
      if(isEra){
        const lbl = document.createElement('div');
        lbl.className = 'scrubber-mark-label';
        lbl.textContent = h.title.length > 15 ? h.title.slice(0, 14) + '…' : h.title;
        mark.appendChild(lbl);
      }
      mark.addEventListener('click', (e) => {
        e.stopPropagation();
        progress = i * SPACING;
      });
      scrubberRulerTrack.appendChild(mark);
    });

    // Scrubber Dragging
    let scrubDragging = false;
    scrubberRulerTrack.addEventListener('pointerdown', (e) => {
      scrubDragging = true;
      scrubToEvent(e);
    });
    window.addEventListener('pointermove', (e) => {
      if(scrubDragging) scrubToEvent(e);
    });
    window.addEventListener('pointerup', () => { scrubDragging = false; });

    function scrubToEvent(e){
      const rect = scrubberRulerTrack.getBoundingClientRect();
      const pct = clamp((e.clientX - rect.left) / rect.width, 0, 1);
      progress = pct * maxProgress;
    }
  }

  // Crear elementos visuales para cada Hito
  if (events.length === 0) {
    track.innerHTML = `
      <div style="position:absolute; top:40%; left:50%; transform:translate(-50%,-50%); text-align:center; color:#fff; z-index:10; width:90%; max-width:500px;">
        <i class="ti ti-award-off" style="font-size:4rem; color:#f2c66d; display:block; margin-bottom:1rem;"></i>
        <h3 style="color:#fff; font-weight:700;">No hay hitos en tu línea de vida</h3>
        <p style="color:rgba(255,255,255,0.7);">Los hitos de la congregación, tus logros espirituales y eventos especiales aparecerán aquí.</p>
      </div>`;
  }

  const els = events.map((h, i) => {
    const node = document.createElement('div');
    node.className = 'tunnel-node';
    node.dataset.baseZ = -i * SPACING;

    node.innerHTML = `
      <div class="year-tag">${h.date || h.year}</div>
      <div class="node-card" style="--c:${h.color || '#f2c66d'}">
        <div class="card-img-wrap">
          <img class="card-img" src="${h.image}" alt="${h.title}" loading="lazy">
          <span class="card-badge">${h.category}</span>
          <span class="card-year-badge">${h.year}</span>
        </div>
        <div class="card-body">
          <div class="card-title">${h.title}</div>
          <div class="card-date"><i class="ti ti-calendar me-1"></i>${h.date}</div>
          ${h.detalles_trigger ? `<div style="background:rgba(242,198,109,0.18); border-left:3px solid var(--c); padding:4px 8px; font-size:11px; margin:6px 0; border-radius:3px; color:#f2c66d; text-align:left;">${h.detalles_trigger}</div>` : ''}
          <p class="card-desc">${h.summary || ''}</p>
          ${h.mensaje_usuario ? `<div class="card-msg"><p>"${h.mensaje_usuario}"</p></div>` : ''}
          <div class="card-footer">
            <span class="card-like-badge">♥ ${h.likes}</span>
            <span style="color:${h.color || '#f2c66d'}">${h.category}</span>
          </div>
        </div>
      </div>`;

    node.addEventListener('click', () => openModal(h));
    track.appendChild(node);
    return node;
  });

  // Dibujar rieles curvos SVG y marcas laterales (Rail)
  function drawRail(){
    const W = scene.clientWidth, H = scene.clientHeight;
    railSvg.setAttribute('viewBox', `0 0 ${W} ${H}`);
    const N = 60;
    let left = 'M', right = 'M';
    for(let i=0; i<=N; i++){
      const t = i / N;
      const y = (t * H).toFixed(1);
      const off = railOffset(t, W);
      const xr = (W / 2 + off).toFixed(1);
      const xl = (W / 2 - off).toFixed(1);
      left += `${i ? ' L' : ''}${xl} ${y}`;
      right += `${i ? ' L' : ''}${xr} ${y}`;
    }
    railSvg.innerHTML = `
      <path d="${left}" fill="none" stroke="rgba(242,198,109,.3)" stroke-width="2"/>
      <path d="${right}" fill="none" stroke="rgba(242,198,109,.3)" stroke-width="2"/>
      <g id="railTicks"></g>`;
  }
  drawRail();
  window.addEventListener('resize', drawRail);

  const maxProgress = (events.length - 1) * SPACING;
  let progress = 0, current = 0;

  // Bucle de Render Continuo e Interpolación Fluida
  function render(){
    current += (progress - current) * 0.085;
    const W = scene.clientWidth, H = scene.clientHeight;
    const isMobile = W <= 768;
    const availH = Math.max(200, H - 20);
    let focusIdx = 0, focusDist = Infinity;
    let ticksSvg = '';

    els.forEach((el, i) => {
      const baseZ = parseFloat(el.dataset.baseZ);
      const depthDiff = baseZ + current;
      const dist = Math.abs(depthDiff);
      if(dist < focusDist){ focusDist = dist; focusIdx = i; }

      const rawT = (depthDiff + VISIBLE_RANGE) / VISIBLE_RANGE;
      const t = clamp(rawT, 0, 1);
      const scale = scaleForT(t, H);
      const yPx = t * availH;

      // En móviles van todos centrados uno detrás del otro (xFract = 0)
      // En desktop mantienen el zigzag natural (xFract = +0.58 / -0.58)
      const xFract = isMobile ? 0 : ((i % 2 === 0) ? 0.58 : -0.58);
      const xOff = isMobile ? 0 : (railOffset(t, W) * xFract);

      let opacity;
      if(depthDiff <= 0){
        opacity = clamp(t * 1.35, 0, 1);
      } else {
        opacity = clamp(1 - depthDiff / PASSED_RANGE, 0, 1);
      }

      const naturalH = el.offsetHeight || 380;
      el.style.top = (yPx - naturalH) + 'px';
      el.style.transform = `translate(-50%,0) translateX(${xOff.toFixed(1)}px) scale(${scale.toFixed(3)})`;
      el.style.opacity = opacity.toFixed(3);
      el.style.zIndex = Math.round(1000 + t * 1000);

      const card = el.querySelector('.node-card');
      if(card){
        card.classList.toggle('is-focus', t > 0.93 && depthDiff <= 45);
      }

      // Marcas laterales de velocidad en los rieles
      if(opacity > 0.04){
        const off = railOffset(t, W);
        const tickLen = 4 + t * 24;
        const tOp = (opacity * 0.55).toFixed(2);
        [[W / 2 - off], [W / 2 + off]].forEach(([x]) => {
          ticksSvg += `<line x1="${(x - tickLen).toFixed(1)}" y1="${(t * H).toFixed(1)}" x2="${(x + tickLen).toFixed(1)}" y2="${(t * H).toFixed(1)}" stroke="rgba(242,198,109,${tOp})" stroke-width="1.5"/>`;
        });
      }
    });

    const rt = document.getElementById('railTicks');
    if(rt) rt.innerHTML = ticksSvg;

    // Actualizar playhead de la línea de tiempo si está visible
    if(playhead){
      const pct = maxProgress <= 0 ? 0 : clamp(current, 0, maxProgress) / maxProgress;
      playhead.style.left = (pct * 100) + '%';
    }

    // Sincronizar el selector de años con el hito en foco
    if(yearSelect && events[focusIdx]){
      const currentHitoYear = String(events[focusIdx].year);
      if(yearSelect.value !== currentHitoYear && yearSelect.querySelector(`option[value="${currentHitoYear}"]`)){
        yearSelect.value = currentHitoYear;
      }
    }

    requestAnimationFrame(render);
  }
  requestAnimationFrame(render);

  // Navegación por rueda de ratón (Wheel)
  scene.addEventListener('wheel', (e) => {
    e.preventDefault();
    progress = clamp(progress + e.deltaY * 1.35, 0, maxProgress);
  }, { passive:false });

  // Arrastre con puntero / touch sobre el escenario (Swipe Vertical / Horizontal)
  let dragging = false, dragStartY = 0, dragStartX = 0, dragStartProgress = 0;
  scene.addEventListener('pointerdown', (e) => {
    dragging = true;
    dragStartY = e.clientY;
    dragStartX = e.clientX;
    dragStartProgress = progress;
    scene.classList.add('dragging');
  });
  window.addEventListener('pointermove', (e) => {
    if(!dragging) return;
    const dy = e.clientY - dragStartY;
    const dx = e.clientX - dragStartX;
    // Permite deslizar tanto vertical como horizontalmente
    const delta = Math.abs(dy) > Math.abs(dx) ? dy * 3.2 : dx * 3.2;
    progress = clamp(dragStartProgress - delta, 0, maxProgress);
  });
  window.addEventListener('pointerup', () => {
    dragging = false;
    scene.classList.remove('dragging');
  });

  // Botones flotantes de navegación (Adelante / Atrás)
  document.getElementById('navPrev').addEventListener('click', () => {
    progress = clamp(progress - SPACING, 0, maxProgress);
  });
  document.getElementById('navNext').addEventListener('click', () => {
    progress = clamp(progress + SPACING, 0, maxProgress);
  });

  // Teclado
  window.addEventListener('keydown', (e) => {
    if(document.getElementById('modalBg').style.display !== 'none'){
      if(e.key === 'Escape') closeModal();
      return;
    }
    if(e.key === 'ArrowDown' || e.key === 'ArrowRight' || e.key === ' '){
      e.preventDefault();
      progress = clamp(progress + SPACING, 0, maxProgress);
    }
    if(e.key === 'ArrowUp' || e.key === 'ArrowLeft'){
      e.preventDefault();
      progress = clamp(progress - SPACING, 0, maxProgress);
    }
  });

  // =========================================================
  // MODAL EXPANSIVO Y SISTEMA DE ME GUSTA
  // =========================================================
  let selectedHito = null;

  // Helper para convertir cualquier URL de YouTube (estándar, corta youtu.be, shorts o embed) a iframe embed
  function getYoutubeEmbedUrl(url){
    if(!url) return null;
    let videoId = null;

    // Patrones de YouTube: youtube.com/watch?v=ID, youtu.be/ID, youtube.com/embed/ID, youtube.com/shorts/ID
    const regExp = /(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?|shorts)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/;
    const match = url.match(regExp);

    if(match && match[1]){
      videoId = match[1];
    }

    return videoId ? `https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0` : null;
  }

  function openModal(h){
    selectedHito = h;
    const modalImg = document.getElementById('modalImg');
    const modalVideoContainer = document.getElementById('modalVideoContainer');
    const modalVideoIframe = document.getElementById('modalVideoIframe');

    // Manejar video de YouTube vs Imagen
    const embedUrl = getYoutubeEmbedUrl(h.video_url);
    if(embedUrl){
      modalVideoIframe.src = embedUrl;
      modalVideoContainer.style.display = 'flex';
      modalImg.style.display = 'none';
    } else {
      modalVideoIframe.src = '';
      modalVideoContainer.style.display = 'none';
      modalImg.src = h.image;
      modalImg.style.display = 'block';
    }

    document.getElementById('modalTag').textContent = h.category;
    document.getElementById('modalYear').textContent = h.year;
    document.getElementById('modalTitle').textContent = h.title;
    document.getElementById('modalDesc').textContent = h.description || h.summary || '';

    // Mensaje pastoral
    const msgBox = document.getElementById('modalMsgBox');
    if(h.mensaje_usuario){
      document.getElementById('modalMsg').textContent = h.mensaje_usuario;
      msgBox.style.display = 'block';
    } else {
      msgBox.style.display = 'none';
    }

    // Galería
    const galleryBox = document.getElementById('modalGalleryBox');
    const galleryGrid = document.getElementById('modalGalleryGrid');
    galleryGrid.innerHTML = '';
    if(h.fotos && h.fotos.length > 0){
      h.fotos.forEach(f => {
        const thumb = document.createElement('div');
        thumb.className = 'gallery-thumb';
        thumb.innerHTML = `<a href="${f.url}" target="_blank"><img src="${f.url}" alt="Foto"></a>`;
        galleryGrid.appendChild(thumb);
      });
      galleryBox.style.display = 'block';
    } else {
      galleryBox.style.display = 'none';
    }

    // Likes
    updateLikeButtonUI(h);

    document.getElementById('modalBg').style.display = 'grid';
  }

  function closeModal(){
    document.getElementById('modalBg').style.display = 'none';
    // Detener reproducción del video de YouTube al cerrar el modal
    const modalVideoIframe = document.getElementById('modalVideoIframe');
    if(modalVideoIframe){
      modalVideoIframe.src = '';
    }
    selectedHito = null;
  }

  function updateLikeButtonUI(h){
    const btn = document.getElementById('modalLikeBtn');
    const text = document.getElementById('modalLikeText');
    const count = document.getElementById('modalLikesCount');

    btn.classList.toggle('liked', !!h.liked);
    text.textContent = h.liked ? '♥ Te gusta' : '♡ Me gusta';
    count.textContent = h.likes || 0;
  }

  async function toggleModalLike(){
    if(!selectedHito) return;
    const h = selectedHito;
    const prevLiked = h.liked;
    const prevLikes = h.likes;

    // Actualización optimista
    h.liked = !prevLiked;
    h.likes += h.liked ? 1 : -1;
    updateLikeButtonUI(h);

    try {
      const res = await fetch(`/hitos/${h.id}/like`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json'
        }
      });
      if(!res.ok) throw new Error('Error al registrar like');
      const data = await res.json();
      h.liked = data.liked;
      h.likes = data.total_likes;
      updateLikeButtonUI(h);
    } catch(err){
      console.error(err);
      h.liked = prevLiked;
      h.likes = prevLikes;
      updateLikeButtonUI(h);
    }
  }
</script>
</body>
</html>
