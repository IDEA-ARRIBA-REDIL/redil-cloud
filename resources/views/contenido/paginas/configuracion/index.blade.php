@extends('layouts/contentNavbarLayout')

@section('title', 'Configuración')

@section('content')
<div 
  x-data="{
    search: '',
    items: {{ Js::from($items) }},
    normalizar(texto) {
      return (texto || '')
        .toString()
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim();
    },
    coincide(item) {
      const termino = this.normalizar(this.search);
      if (!termino) return true;
      
      const palabras = termino.split(/\s+/).filter(Boolean);
      const textoCompleto = this.normalizar((item.title || '') + ' ' + (item.keywords || ''));
      
      return palabras.every(palabra => textoCompleto.includes(palabra));
    },
    get itemsFiltrados() {
      return this.items.filter(item => this.coincide(item));
    },
    limpiarBusqueda() {
      this.search = '';
      this.$refs.searchInput.focus();
    }
  }"
  @keydown.window.prevent.slash="$event.target.tagName !== 'INPUT' && $event.target.tagName !== 'TEXTAREA' ? $refs.searchInput.focus() : null"
>
  <!-- Encabezado con título y barra de búsqueda -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
      <h4 class="mb-1 fw-semibold text-primary">Configuración</h4>
    </div>

    <!-- Buscador Reactivo -->
    <div class="w-100" style="max-width: 380px;">
      <div class="input-group input-group-merge shadow-sm bg-white rounded-3 border border-primary">
        <span class="input-group-text bg-white  text-muted ps-3">
          <i class="ti ti-search"></i>
        </span>
        <input 
          type="text" 
          class="form-control border-start-0  ps-1" 
          placeholder="Buscar configuración (ej. roles, plantilla, pagos)..." 
          x-model="search"
          x-ref="searchInput"
          @keydown.escape="search = ''"
          autocomplete="off"
        >
        <button 
          class="input-group-text bg-white border-start-0 text-muted pe-3 btn btn-link text-decoration-none" 
          type="button" 
          x-show="search.length > 0" 
          @click="limpiarBusqueda()"
          title="Limpiar búsqueda (Esc)"
          style="display: none;"
        >
          <i class="ti ti-x ti-xs"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- Cuadrícula de Tarjetas de Configuración -->
  <div class="row g-4 mt-2">
    @foreach($items as $index => $item)
    <div 
      class="col-6 col-sm-4 col-md-3 col-lg-2 config-card-item"
      x-show="coincide(items[{{ $index }}])"
      x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 transform scale-95"
      x-transition:enter-end="opacity-100 transform scale-100"
    >
      <a href="{{ route($item['route']) }}" class="text-body text-decoration-none">
        <div class="card h-100 text-center border-0 shadow-sm card-hover">
          <div class="card-body d-flex flex-column align-items-center justify-content-center p-4">
            <div class="avatar avatar-lg mb-3">
              <span class="avatar-initial rounded-circle {{ $item['color'] }}">
                <i class="ti {{ $item['icon'] }} ti-md"></i>
              </span>
            </div>
            <p class="mb-0 fw-semibold text-black text-wrap">{{ $item['title'] }}</p>
          </div>
        </div>
      </a>
    </div>
    @endforeach
  </div>

  <!-- Estado Vacío cuando no hay coincidencias -->
  <div 
    class="text-center py-5 my-4" 
    x-show="itemsFiltrados.length === 0" 
    style="display: none;"
    x-transition
  >
    <div class="avatar avatar-xl bg-label-secondary mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center">
      <i class="ti ti-search-off ti-lg text-white"></i>
    </div>
    <h5 class="fw-semibold mb-1 text-black">No se encontraron opciones</h5>
    <p class="text-black mb-3">
      No hay ninguna configuración que coincida con "<span class="fw-bold text-primary" x-text="search"></span>"
    </p>
    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" @click="limpiarBusqueda()">
      <i class="ti ti-arrow-back-up me-1"></i> Mostrar todas las opciones
    </button>
  </div>
</div>

<style>
.card-hover {
  transition: all 0.25s ease-in-out;
  cursor: pointer;
}
.card-hover:hover {
  transform: translateY(-5px);
  box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.1) !important;
}
.avatar-initial {
  display: flex;
  align-items: center;
  justify-content: center;
}
/* Asegurar que el texto no se corte */
.text-wrap {
  white-space: normal !important;
  word-wrap: break-word;
}
</style>
@endsection
