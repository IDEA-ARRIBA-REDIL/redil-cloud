<!-- Restricciones de permisos por rol -->
<div class="col-md-12">
  <div class="card mb-4">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center pb-2">
      <div class="mb-2 mb-md-0">
        <h5 class="card-title text-black fw-semibold mb-1">
          <i class="ti ti-shield-lock me-2 text-danger"></i>Restricciones de permisos por rol
        </h5>
        <p class="text-muted small mb-0">
          Por defecto <strong>todos los roles tienen acceso total</strong> para vincular y desvincular. Agrega aquí únicamente los roles a los que desees <strong>denegar</strong> alguna acción.
        </p>
      </div>
      <div>
        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill shadow-sm" id="btnAgregarRestriccion">
          <i class="ti ti-plus me-1"></i> Agregar restricción a un rol
        </button>
      </div>
    </div>
    <div class="card-body">
      <input type="hidden" name="gestionar_privilegios_roles" value="1">

      {{-- Estado vacío: Sin restricciones --}}
      <div id="alertaSinRestricciones" class="text-center py-4 border rounded bg-light {{ $rolesRestringidos->isNotEmpty() ? 'd-none' : '' }}">
        <div class="avatar avatar-md bg-label-success rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center">
          <i class="ti ti-shield-check fs-3"></i>
        </div>
        <h6 class="fw-semibold mb-1">Sin restricciones configuradas</h6>
        <p class="text-muted small mb-0">
          Todos los roles tienen permiso para vincular y desvincular encargados e integrantes en este tipo de grupo.
        </p>
      </div>

      {{-- Tabla de roles con restricciones --}}
      <div id="contenedorTablaRestricciones" class="table-responsive {{ $rolesRestringidos->isEmpty() ? 'd-none' : '' }}">
        <table class="table table-hover align-middle border-top mb-0" id="tablaRestriccionesRoles">
          <thead class="table-light">
            <tr>
              <th style="min-width: 220px;">Rol con restricción</th>
              <th class="text-center" style="min-width: 140px;">
                <div class="d-flex align-items-center justify-content-center gap-1 text-danger">
                  <i class="ti ti-user-x"></i>
                  <span class="small fw-semibold">Denegar asignar encargado</span>
                </div>
              </th>
              <th class="text-center" style="min-width: 140px;">
                <div class="d-flex align-items-center justify-content-center gap-1 text-danger">
                  <i class="ti ti-user-minus"></i>
                  <span class="small fw-semibold">Denegar desvincular encargado</span>
                </div>
              </th>
              <th class="text-center" style="min-width: 140px;">
                <div class="d-flex align-items-center justify-content-center gap-1 text-danger">
                  <i class="ti ti-users"></i>
                  <span class="small fw-semibold">Denegar asignar integrante</span>
                </div>
              </th>
              <th class="text-center" style="min-width: 140px;">
                <div class="d-flex align-items-center justify-content-center gap-1 text-danger">
                  <i class="ti ti-user-off"></i>
                  <span class="small fw-semibold">Denegar desvincular integrante</span>
                </div>
              </th>
              <th class="text-center" style="width: 70px;">Quitar</th>
            </tr>
          </thead>
          <tbody id="cuerpoTablaRestricciones">
            @foreach ($rolesRestringidos as $index => $rolRestringido)
              @php
                $p = $rolRestringido->pivot;
                $denegarAsignarEncargado = !(bool)$p->asignar_encargado;
                $denegarDesvincularEncargado = !(bool)$p->desvincular_encargado;
                $denegarAsignarAsistente = !(bool)$p->asignar_asistente;
                $denegarDesvincularAsistente = !(bool)$p->desvincular_asistente;
              @endphp
              <tr class="fila-restriccion">
                <td>
                  <select name="restricciones_roles[{{ $index }}][rol_id]" class="form-select select-rol" required>
                    <option value="">Selecciona un rol...</option>
                    @foreach ($roles as $r)
                      <option value="{{ $r->id }}" {{ $r->id == $rolRestringido->id ? 'selected' : '' }}>
                        {{ $r->name }} {{ $r->dependiente ? '(Dependiente)' : '' }}
                      </option>
                    @endforeach
                  </select>
                </td>
                <td class="text-center">
                  <div class="form-check form-switch d-inline-block">
                    <input class="form-check-input" type="checkbox"
                      name="restricciones_roles[{{ $index }}][denegar_asignar_encargado]"
                      value="1" {{ $denegarAsignarEncargado ? 'checked' : '' }}
                      title="Marcar para denegar">
                  </div>
                </td>
                <td class="text-center">
                  <div class="form-check form-switch d-inline-block">
                    <input class="form-check-input" type="checkbox"
                      name="restricciones_roles[{{ $index }}][denegar_desvincular_encargado]"
                      value="1" {{ $denegarDesvincularEncargado ? 'checked' : '' }}
                      title="Marcar para denegar">
                  </div>
                </td>
                <td class="text-center">
                  <div class="form-check form-switch d-inline-block">
                    <input class="form-check-input" type="checkbox"
                      name="restricciones_roles[{{ $index }}][denegar_asignar_asistente]"
                      value="1" {{ $denegarAsignarAsistente ? 'checked' : '' }}
                      title="Marcar para denegar">
                  </div>
                </td>
                <td class="text-center">
                  <div class="form-check form-switch d-inline-block">
                    <input class="form-check-input" type="checkbox"
                      name="restricciones_roles[{{ $index }}][denegar_desvincular_asistente]"
                      value="1" {{ $denegarDesvincularAsistente ? 'checked' : '' }}
                      title="Marcar para denegar">
                  </div>
                </td>
                <td class="text-center">
                  <button type="button" class="btn btn-sm btn-icon btn-outline-danger btn-eliminar-fila" title="Quitar restricción y permitir todo a este rol">
                    <i class="ti ti-trash"></i>
                  </button>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<!-- /Restricciones de permisos por rol -->

{{-- Template para nuevas filas dinámicas --}}
<template id="templateFilaRestriccion">
  <tr class="fila-restriccion">
    <td>
      <select name="restricciones_roles[INDEX][rol_id]" class="form-select select-rol" required>
        <option value="">Selecciona un rol...</option>
        @foreach ($roles as $r)
          <option value="{{ $r->id }}">
            {{ $r->name }} {{ $r->dependiente ? '(Dependiente)' : '' }}
          </option>
        @endforeach
      </select>
    </td>
    <td class="text-center">
      <div class="form-check form-switch d-inline-block">
        <input class="form-check-input" type="checkbox"
          name="restricciones_roles[INDEX][denegar_asignar_encargado]"
          value="1" checked
          title="Marcar para denegar">
      </div>
    </td>
    <td class="text-center">
      <div class="form-check form-switch d-inline-block">
        <input class="form-check-input" type="checkbox"
          name="restricciones_roles[INDEX][denegar_desvincular_encargado]"
          value="1" checked
          title="Marcar para denegar">
      </div>
    </td>
    <td class="text-center">
      <div class="form-check form-switch d-inline-block">
        <input class="form-check-input" type="checkbox"
          name="restricciones_roles[INDEX][denegar_asignar_asistente]"
          value="1"
          title="Marcar para denegar">
      </div>
    </td>
    <td class="text-center">
      <div class="form-check form-switch d-inline-block">
        <input class="form-check-input" type="checkbox"
          name="restricciones_roles[INDEX][denegar_desvincular_asistente]"
          value="1"
          title="Marcar para denegar">
      </div>
    </td>
    <td class="text-center">
      <button type="button" class="btn btn-sm btn-icon btn-outline-danger btn-eliminar-fila" title="Quitar restricción y permitir todo a este rol">
        <i class="ti ti-trash"></i>
      </button>
    </td>
  </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let contadorRestricciones = {{ count($rolesRestringidos ?? []) }};
    const btnAgregar = document.getElementById('btnAgregarRestriccion');
    const cuerpoTabla = document.getElementById('cuerpoTablaRestricciones');
    const alertaSinRestricciones = document.getElementById('alertaSinRestricciones');
    const contenedorTabla = document.getElementById('contenedorTablaRestricciones');
    const template = document.getElementById('templateFilaRestriccion');

    function actualizarVisibilidadTabla() {
        if (!cuerpoTabla || !alertaSinRestricciones || !contenedorTabla) return;
        const filas = cuerpoTabla.querySelectorAll('.fila-restriccion');
        if (filas.length > 0) {
            alertaSinRestricciones.classList.add('d-none');
            contenedorTabla.classList.remove('d-none');
        } else {
            alertaSinRestricciones.classList.remove('d-none');
            contenedorTabla.classList.add('d-none');
        }
    }

    if (btnAgregar && template && cuerpoTabla) {
        btnAgregar.addEventListener('click', function () {
            contadorRestricciones++;
            const nuevoHtml = template.innerHTML.replaceAll('INDEX', contadorRestricciones);
            cuerpoTabla.insertAdjacentHTML('beforeend', nuevoHtml);
            actualizarVisibilidadTabla();
        });
    }

    if (cuerpoTabla) {
        cuerpoTabla.addEventListener('click', function (e) {
            const btnEliminar = e.target.closest('.btn-eliminar-fila');
            if (btnEliminar) {
                const fila = btnEliminar.closest('.fila-restriccion');
                if (fila) {
                    fila.remove();
                    actualizarVisibilidadTabla();
                }
            }
        });
    }
});
</script>
