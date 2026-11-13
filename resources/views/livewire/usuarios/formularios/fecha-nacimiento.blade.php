<!-- fecha nacimiento -->
<div 
  class="mb-3 {{ $class }}"
  wire:ignore
  x-data="{
    abierto: false,
    modoManual: false,
    fecha: '',
    textoMostrar: '',
    dia: 1,
    mes: 1,
    ano: 2005,
    diasDelMes: [],
    meses: [
      { id: 1, nombre: 'Enero' },
      { id: 2, nombre: 'Febrero' },
      { id: 3, nombre: 'Marzo' },
      { id: 4, nombre: 'Abril' },
      { id: 5, nombre: 'Mayo' },
      { id: 6, nombre: 'Junio' },
      { id: 7, nombre: 'Julio' },
      { id: 8, nombre: 'Agosto' },
      { id: 9, nombre: 'Septiembre' },
      { id: 10, nombre: 'Octubre' },
      { id: 11, nombre: 'Noviembre' },
      { id: 12, nombre: 'Diciembre' }
    ],
    anos: [],
    scrollTimers: { dia: null, mes: null, ano: null },

    init() {
      const actualYear = new Date().getFullYear();
      const minYear = actualYear - 105;
      const anosArr = [];
      for (let y = actualYear; y >= minYear; y--) {
        anosArr.push(y);
      }
      this.anos = anosArr;

      // Calcular año de referencia inicial
      const edadMin = {{ $formulario && $formulario->edad_minima ? $formulario->edad_minima : 'null' }};
      const anoDefault = edadMin ? (actualYear - edadMin) : (actualYear - 18);
      this.ano = anoDefault;

      this.ajustarDias();

      // Cargar fecha inicial si existe
      const fechaInicial = '{{ old($nameId, $fecha ? $fecha : $fechaDefault) }}';
      if (fechaInicial) {
        this.setFechaDesdeString(fechaInicial);
      }

      // Escuchar cuando Livewire limpia o actualiza la fecha
      this.$watch('$wire.fecha', (val) => {
        if (!val) {
          this.fecha = '';
          this.textoMostrar = '';
        } else if (val !== this.fecha) {
          this.setFechaDesdeString(val);
        }
      });
    },

    setFechaDesdeString(str) {
      if (!str) return;
      const parts = str.split('-');
      if (parts.length === 3) {
        this.ano = parseInt(parts[0], 10);
        this.mes = parseInt(parts[1], 10);
        this.dia = parseInt(parts[2], 10);
        this.fecha = str;
        this.ajustarDias();

        const mesObj = this.meses[this.mes - 1];
        const mesNombre = mesObj ? mesObj.nombre : '';
        this.textoMostrar = `${String(this.dia).padStart(2, '0')} de ${mesNombre} de ${this.ano}`;
      }
    },

    get previewTexto() {
      if (!this.dia || !this.mes || !this.ano) return 'Selecciona una fecha';
      const mesObj = this.meses[this.mes - 1];
      const mesNombre = mesObj ? mesObj.nombre : '';
      return `${String(this.dia).padStart(2, '0')} de ${mesNombre} de ${this.ano}`;
    },

    ajustarDias() {
      const maxDias = new Date(this.ano, this.mes, 0).getDate();
      const arr = [];
      for (let i = 1; i <= maxDias; i++) {
        arr.push(i);
      }
      this.diasDelMes = arr;
      if (this.dia > maxDias) {
        this.dia = maxDias;
      }
    },

    abrirModal() {
      $wire.bloquearBtnGuardar();
      this.abierto = true;
      document.body.style.overflow = 'hidden';
      
      // Asegurar que el modal ya esté visible y renderizado al posicionar
      setTimeout(() => {
        this.posicionarRuedas();
      }, 50);
      setTimeout(() => {
        this.posicionarRuedas();
      }, 150);
    },

    cerrar() {
      this.abierto = false;
      document.body.style.overflow = '';
    },

    confirmar() {
      document.body.style.overflow = '';
      const diaStr = String(this.dia).padStart(2, '0');
      const mesStr = String(this.mes).padStart(2, '0');
      const fechaStr = `${this.ano}-${mesStr}-${diaStr}`;

      this.fecha = fechaStr;
      const mesObj = this.meses[this.mes - 1];
      const mesNombre = mesObj ? mesObj.nombre : '';
      this.textoMostrar = `${diaStr} de ${mesNombre} de ${this.ano}`;
      this.abierto = false;

      $wire.set('fecha', fechaStr);
      $wire.validarFecha();
    },

    posicionarRuedas() {
      if (this.$refs.wheelDia) {
        this.$refs.wheelDia.scrollTop = (this.dia - 1) * 40;
      }
      if (this.$refs.wheelMes) {
        this.$refs.wheelMes.scrollTop = (this.mes - 1) * 40;
      }
      if (this.$refs.wheelAno) {
        const idx = this.anos.indexOf(this.ano);
        this.$refs.wheelAno.scrollTop = (idx >= 0 ? idx : 0) * 40;
      }
    },

    onScroll(col) {
      clearTimeout(this.scrollTimers[col]);
      this.scrollTimers[col] = setTimeout(() => {
        this.syncFromScroll(col);
      }, 40);
    },

    syncFromScroll(col) {
      if (col === 'dia' && this.$refs.wheelDia) {
        const idx = Math.max(0, Math.min(this.diasDelMes.length - 1, Math.round(this.$refs.wheelDia.scrollTop / 40)));
        this.dia = this.diasDelMes[idx];
      } else if (col === 'mes' && this.$refs.wheelMes) {
        const idx = Math.max(0, Math.min(11, Math.round(this.$refs.wheelMes.scrollTop / 40)));
        const nuevoMes = idx + 1;
        if (this.mes !== nuevoMes) {
          this.mes = nuevoMes;
          this.ajustarDias();
        }
      } else if (col === 'ano' && this.$refs.wheelAno) {
        const idx = Math.max(0, Math.min(this.anos.length - 1, Math.round(this.$refs.wheelAno.scrollTop / 40)));
        const nuevoAno = this.anos[idx];
        if (this.ano !== nuevoAno) {
          this.ano = nuevoAno;
          this.ajustarDias();
        }
      }
    },

    scrollToItem(col, index) {
      let ref;
      if (col === 'dia') {
        ref = this.$refs.wheelDia;
        this.dia = this.diasDelMes[index];
      } else if (col === 'mes') {
        ref = this.$refs.wheelMes;
        this.mes = index + 1;
        this.ajustarDias();
      } else if (col === 'ano') {
        ref = this.$refs.wheelAno;
        this.ano = this.anos[index];
        this.ajustarDias();
      }
      if (ref) {
        ref.scrollTo({ top: index * 40, behavior: 'smooth' });
      }
    },

    step(col, direction) {
      if (col === 'dia') {
        let nuevoDia = this.dia + direction;
        if (nuevoDia < 1) nuevoDia = 1;
        if (nuevoDia > this.diasDelMes.length) nuevoDia = this.diasDelMes.length;
        this.scrollToItem('dia', nuevoDia - 1);
      } else if (col === 'mes') {
        let nuevoMes = this.mes + direction;
        if (nuevoMes < 1) nuevoMes = 1;
        if (nuevoMes > 12) nuevoMes = 12;
        this.scrollToItem('mes', nuevoMes - 1);
      } else if (col === 'ano') {
        const idx = this.anos.indexOf(this.ano);
        let nuevoIdx = idx - direction;
        if (nuevoIdx < 0) nuevoIdx = 0;
        if (nuevoIdx >= this.anos.length) nuevoIdx = this.anos.length - 1;
        this.scrollToItem('ano', nuevoIdx);
      }
    }
  }"
>
  <label for="fecha_nacimiento" class="form-label">
    {{ $label }}
  </label>

  <!-- Input oculto para envío tradicional de formularios POST -->
  <input 
    type="hidden" 
    name="{{ $nameId }}" 
    :value="fecha" 
  />

  <!-- Campo visual interactivo -->
  <div 
    class="input-group input-group-merge" 
    style="cursor: pointer;" 
    @click="abrirModal()"
  >
    <span class="input-group-text" style="cursor: pointer; background-color: #fff;"><i class="ti ti-calendar" style="color: #138848;"></i></span>
    <input 
      type="text" 
      id="fecha_nacimiento" 
      class="form-control" 
      style="cursor: pointer; background-color: #fff;" 
      readonly 
      :value="textoMostrar" 
      placeholder="Selecciona tu fecha de nacimiento" 
    />
  </div>

  @if($errors->has($nameId) || $mostrarError == true)
  <div class="text-danger ti-12px mt-2">
    <i class="ti ti-circle-x"></i> {{ $errors->first($nameId) ? $errors->first($nameId) : $msnError }}
  </div>
  @endif

  <!-- Modal Selector de Ruedas (Drum Roll) - Teleportado a body para centrado absoluto -->
  <template x-teleport="body">
    <div 
      x-show="abierto" 
      x-cloak
      class="wheel-picker-backdrop"
      style="position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; width: 100vw !important; height: 100vh !important; z-index: 99999 !important; background-color: rgba(15, 23, 42, 0.65) !important; backdrop-filter: blur(4px); display: flex !important; align-items: center !important; justify-content: center !important; margin: 0 !important; padding: 1rem !important;"
      @click.self="cerrar()"
      @keydown.escape.window="cerrar()"
    >
      <div 
        class="wheel-picker-card"
        style="margin: auto !important; background: #ffffff !important; border-radius: 24px !important; width: 100% !important; max-width: 410px !important; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35) !important; padding: 24px !important; position: relative !important;"
        @click.stop
      >
        <!-- Cabecera -->
        <div class="d-flex align-items-center justify-content-between mb-2">
          <div>
            <h5 class="fw-bold mb-0 text-black" style="font-size: 1.15rem; letter-spacing: -0.3px;">
              Fecha de nacimiento
            </h5>
            <small class="text-black" x-text="modoManual ? 'Ingresa los valores numéricos' : 'Desliza las columnas o usa las flechas'"></small>
          </div>
          <button 
            type="button" 
            @click="cerrar()" 
            class="btn btn-sm btn-icon rounded-circle"
            style="background: #f1f5f9; color: #64748b; width: 34px; height: 34px; border: none;"
          >
            <i class="ti ti-x" style="font-size: 1.1rem;"></i>
          </button>
        </div>

        <!-- Botón para alternar a ingreso manual -->
        <div class="d-flex justify-content-end mb-2">
          <button 
            type="button" 
            @click="modoManual = !modoManual; if(!modoManual) { setTimeout(() => posicionarRuedas(), 60); }" 
            class="btn btn-sm rounded-pill px-3 py-1" 
            style="background: #f8fafc; border: 1px solid #e2e8f0; font-size: 0.82rem; color: #475569; font-weight: 500;"
          >
            <i :class="modoManual ? 'ti ti-rotate me-1' : 'ti ti-keyboard me-1'"></i>
            <span x-text="modoManual ? 'Usar selector de ruedas' : 'Escribir con teclado'"></span>
          </button>
        </div>

        <!-- Badge de Previsualización en Tiempo Real -->
        <div 
          class="d-flex align-items-center justify-content-center gap-2 py-2 px-3 mb-3 rounded-pill" 
          style="background-color: #f0fdf4; border: 1px solid #bbf7d0;"
        >
          <i class="ti ti-calendar" style="color: #138848; font-size: 1.15rem;"></i>
          <span class="fw-bold" style="color: #138848; font-size: 1.02rem;" x-text="previewTexto"></span>
        </div>

        <!-- MODO A: Selector de Ruedas con Botones Stepper (Flechas ▲ y ▼) -->
        <div x-show="!modoManual">
          <!-- Botones de incremento superior (▲) -->
          <div class="d-flex justify-content-between mb-1 text-center">
            <button type="button" @click="step('dia', -1)" class="btn btn-sm btn-light py-0 px-2 rounded-2" style="width: 28%; font-size: 0.75rem; color: #64748b;" title="Día anterior">
              <i class="ti ti-chevron-up"></i>
            </button>
            <button type="button" @click="step('mes', -1)" class="btn btn-sm btn-light py-0 px-2 rounded-2" style="width: 42%; font-size: 0.75rem; color: #64748b;" title="Mes anterior">
              <i class="ti ti-chevron-up"></i>
            </button>
            <button type="button" @click="step('ano', 1)" class="btn btn-sm btn-light py-0 px-2 rounded-2" style="width: 28%; font-size: 0.75rem; color: #64748b;" title="Año posterior">
              <i class="ti ti-chevron-up"></i>
            </button>
          </div>

          <!-- Contenedor de las 3 Ruedas (exactamente 200px de altura = 5 filas de 40px) -->
          <div 
            class="position-relative d-flex justify-content-between mb-1" 
            style="height: 200px; background: #f8fafc; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0;"
          >
            <!-- Franja selectora central (fija a top 80px, altura 40px = fila 3 de 5) -->
            <div style="position: absolute; top: 80px; left: 6px; right: 6px; height: 40px; background: rgba(19, 136, 72, 0.1); border-top: 2px solid rgba(19, 136, 72, 0.45); border-bottom: 2px solid rgba(19, 136, 72, 0.45); border-radius: 10px; pointer-events: none; z-index: 1;"></div>

            <!-- Degradados de sombra 3D -->
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 75px; background: linear-gradient(to bottom, #f8fafc 20%, rgba(248, 250, 252, 0) 100%); pointer-events: none; z-index: 2;"></div>
            <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 75px; background: linear-gradient(to top, #f8fafc 20%, rgba(248, 250, 252, 0) 100%); pointer-events: none; z-index: 2;"></div>

            <!-- Columna Día -->
            <div 
              x-ref="wheelDia" 
              @scroll.passive="onScroll('dia')" 
              class="wheel-col-scroll" 
              style="width: 28%; height: 200px; z-index: 3;"
            >
              <template x-for="d in diasDelMes" :key="'dia-' + d">
                <div 
                  @click="scrollToItem('dia', d - 1)" 
                  class="wheel-row-item" 
                  :class="{ 'is-selected': dia === d }"
                  x-text="String(d).padStart(2, '0')"
                ></div>
              </template>
            </div>

            <!-- Columna Mes -->
            <div 
              x-ref="wheelMes" 
              @scroll.passive="onScroll('mes')" 
              class="wheel-col-scroll" 
              style="width: 44%; height: 200px; z-index: 3;"
            >
              <template x-for="(m, idx) in meses" :key="'mes-' + m.id">
                <div 
                  @click="scrollToItem('mes', idx)" 
                  class="wheel-row-item text-truncate px-1" 
                  :class="{ 'is-selected': mes === m.id }"
                  x-text="m.nombre"
                ></div>
              </template>
            </div>

            <!-- Columna Año -->
            <div 
              x-ref="wheelAno" 
              @scroll.passive="onScroll('ano')" 
              class="wheel-col-scroll" 
              style="width: 28%; height: 200px; z-index: 3;"
            >
              <template x-for="(a, idx) in anos" :key="'ano-' + a">
                <div 
                  @click="scrollToItem('ano', idx)" 
                  class="wheel-row-item" 
                  :class="{ 'is-selected': ano === a }"
                  x-text="a"
                ></div>
              </template>
            </div>
          </div>

          <!-- Botones de decremento inferior (▼) -->
          <div class="d-flex justify-content-between mb-4 text-center">
            <button type="button" @click="step('dia', 1)" class="btn btn-sm btn-light py-0 px-2 rounded-2" style="width: 28%; font-size: 0.75rem; color: #64748b;" title="Día siguiente">
              <i class="ti ti-chevron-down"></i>
            </button>
            <button type="button" @click="step('mes', 1)" class="btn btn-sm btn-light py-0 px-2 rounded-2" style="width: 42%; font-size: 0.75rem; color: #64748b;" title="Mes siguiente">
              <i class="ti ti-chevron-down"></i>
            </button>
            <button type="button" @click="step('ano', -1)" class="btn btn-sm btn-light py-0 px-2 rounded-2" style="width: 28%; font-size: 0.75rem; color: #64748b;" title="Año anterior">
              <i class="ti ti-chevron-down"></i>
            </button>
          </div>
        </div>

        <!-- MODO B: Ingreso Manual con Teclado -->
        <div x-show="modoManual" class="mb-4 pt-2">
          <div class="row g-2">
            <!-- Input Día -->
            <div class="col-3">
              <label class="form-label small text-black mb-1 text-center d-block">Día (DD)</label>
              <input 
                type="number" 
                min="1" 
                :max="diasDelMes.length" 
                x-model.number="dia" 
                @input="ajustarDias()" 
                class="form-control text-center fw-bold text-black" 
                style="font-size: 1.15rem; border-radius: 12px;" 
                placeholder="01"
              />
            </div>
            <!-- Select Mes -->
            <div class="col-5">
              <label class="form-label small text-black mb-1 text-center d-block">Mes</label>
              <select 
                x-model.number="mes" 
                @change="ajustarDias()" 
                class="form-select text-center fw-bold text-black px-1" 
                style="font-size: 0.95rem; border-radius: 12px; height: 43px;"
              >
                <template x-for="m in meses" :key="'opt-m-' + m.id">
                  <option :value="m.id" x-text="m.nombre"></option>
                </template>
              </select>
            </div>
            <!-- Input Año -->
            <div class="col-4">
              <label class="form-label small text-black mb-1 text-center d-block">Año (AAAA)</label>
              <input 
                type="number" 
                min="1920" 
                :max="new Date().getFullYear()" 
                x-model.number="ano" 
                @input="ajustarDias()" 
                class="form-control text-center fw-bold text-black" 
                style="font-size: 1.15rem; border-radius: 12px;" 
                placeholder="1995"
              />
            </div>
          </div>
        </div>

        <!-- Botones de Acción -->
        <div class="d-flex align-items-center justify-content-end gap-2 pt-1 border-top">
          <button 
            type="button" 
            @click="cerrar()" 
            class="btn rounded-pill px-4 py-2" 
            style="background-color: #f1f5f9; color: #475569; border: none; font-weight: 600; font-size: 0.9rem;"
          >
            Cancelar
          </button>
          <button 
            type="button" 
            @click="confirmar()" 
            class="btn rounded-pill px-4 py-2 text-white d-inline-flex align-items-center gap-2" 
            style="background-color: #138848; border: none; font-weight: 600; font-size: 0.9rem; box-shadow: 0 4px 12px rgba(19, 136, 72, 0.25);"
          >
            Confirmar <i class="ti ti-check ti-xs"></i>
          </button>
        </div>
      </div>
    </div>
  </template>

  <style>
    [x-cloak] {
      display: none !important;
    }
    .wheel-col-scroll {
      overflow-y: scroll !important;
      scroll-snap-type: y mandatory !important;
      -webkit-overflow-scrolling: touch !important;
      scrollbar-width: none !important;
      -ms-overflow-style: none !important;
      padding-top: 80px !important;
      padding-bottom: 80px !important;
      box-sizing: border-box !important;
    }
    .wheel-col-scroll::-webkit-scrollbar {
      display: none !important;
      width: 0 !important;
      height: 0 !important;
    }
    .wheel-row-item {
      height: 40px !important;
      min-height: 40px !important;
      max-height: 40px !important;
      line-height: 40px !important;
      font-size: 1.05rem !important;
      scroll-snap-align: center !important;
      text-align: center !important;
      cursor: pointer !important;
      user-select: none !important;
      box-sizing: border-box !important;
      margin: 0 !important;
      padding: 0 !important;
      color: #000000ff;
      font-weight: 400;
      opacity: 0.55;
      transition: color 0.15s ease, font-weight 0.15s ease, opacity 0.15s ease;
    }
    .wheel-row-item.is-selected {
      color: #138848 !important;
      font-weight: 700 !important;
      opacity: 1 !important;
    }
  </style>
</div>
<!-- fecha nacimiento -->
