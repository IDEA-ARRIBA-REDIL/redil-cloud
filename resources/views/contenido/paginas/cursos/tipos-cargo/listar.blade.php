@extends('layouts/layoutMaster')
@section('title', 'Gestionar Tipos de Cargo de Cursos')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0 fw-bold text-primary">Gestionar Tipos de Cargo</h4>
                <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalCrear">
                    <i class="ti ti-plus me-1"></i> Nuevo Cargo
                </button>
            </div>
            @if (session('success'))
                <div class="alert alert-success mt-2">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger mt-2">{{ session('error') }}</div>
            @endif
        </div>
    </div>

    <div class="row g-4 mb-4">
        @forelse($cargos as $cargo)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title text-black mb-1 fw-bold">{{ $cargo->nombre }}</h5>
                            </div>
                            <div class="d-flex gap-2">
                                <button
                                    class="btn btn-icon btn-text-secondary p-0 rounded-circle bg-transparent border-0 shadow-none btn-editar"
                                    data-id="{{ $cargo->id }}" data-nombre="{{ $cargo->nombre }}"
                                    data-responder="{{ $cargo->puede_responder_preguntas }}"
                                    data-editar-curso="{{ $cargo->puede_editar_curso }}"
                                    data-editar-restricciones="{{ $cargo->puede_editar_restricciones }}"
                                    data-editar-contenido="{{ $cargo->puede_editar_contenido }}"
                                    data-gestionar-equipo="{{ $cargo->puede_gestionar_equipo }}"
                                    data-gestionar-estudiantes="{{ $cargo->puede_gestionar_estudiantes }}"
                                    data-limita-carreras="{{ $cargo->limita_carreras }}"
                                    data-ver-todos="{{ $cargo->puede_ver_todos_los_cursos }}"
                                    data-carreras="{{ json_encode($cargo->carreras_permitidas ?? []) }}"
                                    data-bs-toggle="modal" data-bs-target="#modalEditar" title="Editar">
                                    <i class="ti ti-edit ti-md text-black"></i>
                                </button>
                                <form action="{{ route('cursos.tipos-cargo.destroy', $cargo->id) }}" method="POST"
                                    class="m-0 form-eliminar">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="btn btn-icon btn-text-secondary p-0 rounded-circle bg-transparent border-0 shadow-none eliminar-btn"
                                        title="Eliminar">
                                        <i class="ti ti-trash ti-md text-black"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="mt-3">
                            <h6 class="fw-semibold">Permisos:</h6>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                @if ($cargo->puede_responder_preguntas)
                                    <span class="badge bg-label-info">Responder Foro</span>
                                @endif
                                @if ($cargo->puede_editar_curso)
                                    <span class="badge bg-label-info">Editar Curso</span>
                                @endif
                                @if ($cargo->puede_editar_restricciones)
                                    <span class="badge bg-label-info">Restricciones</span>
                                @endif
                                @if ($cargo->puede_editar_contenido)
                                    <span class="badge bg-label-info">Contenido LMS</span>
                                @endif
                                @if ($cargo->puede_gestionar_equipo)
                                    <span class="badge bg-label-info">Dpto. Creadores</span>
                                @endif
                                @if ($cargo->puede_gestionar_estudiantes)
                                    <span class="badge bg-label-info">Ver Estudiantes</span>
                                @endif
                                @if ($cargo->puede_ver_todos_los_cursos)
                                    <span class="badge bg-label-success">Ver Todos Cursos</span>
                                @endif
                               
                            </div>
                            <br>
                             @if ($cargo->limita_carreras)
                                    <span class="badge text-black fw-semibold">Limitar Carreras (Dashboard)</span><br>
                                    <ul class="mt-1">
                                        @php
                                            $carrerasCargo = $carreras->whereIn('id', $cargo->carreras_permitidas ?? []);
                                        @endphp
                                        @foreach ($carrerasCargo as $c)
                                            <li class="text-black">{{ $c->nombre }}</li><br>
                                        @endforeach
                                    </ul>
                                @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border">
                    <div class="card-body py-5 text-center">
                        <i class="ti ti-users ti-xl mb-3 text-muted"></i>
                        <h6>No hay tipos de cargo registrados.</h6>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Modal Crear --}}
    <div class="modal fade" id="modalCrear" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('cursos.tipos-cargo.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Nuevo Cargo de Curso</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Cargo</label>
                            <input type="text" name="nombre" class="form-control" required
                                placeholder="Ej. Tutor, Revisor, Asesor Junior...">
                        </div>

                        <h6 class="mt-4 fw-semibold text-primary">Permisos Activos</h6>
                        <hr class="mt-0">

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="puede_responder_preguntas" id="c_resp"
                                value="1">
                            <label class="form-check-label" for="c_resp">Moderador del Foro (Responder Dudas)</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="puede_editar_curso" id="c_ed_c"
                                value="1">
                            <label class="form-check-label" for="c_ed_c">Editar Información e Imágenes del Curso</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="puede_editar_restricciones" id="c_ed_r"
                                value="1">
                            <label class="form-check-label" for="c_ed_r">Gestionar Restricciones y Precios</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="puede_editar_contenido" id="c_ed_cont"
                                value="1">
                            <label class="form-check-label" for="c_ed_cont">Gestionar Módulos y Lecciones</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="puede_gestionar_equipo"
                                id="c_g_equipo" value="1">
                            <label class="form-check-label" for="c_g_equipo">Añadir/Remover equipo del curso</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="puede_gestionar_estudiantes"
                                id="c_g_est" value="1">
                            <label class="form-check-label" for="c_g_est">Pestaña "Estudiantes" (Ver inscritos)</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="puede_ver_todos_los_cursos"
                                id="c_ver_todos" value="1">
                            <label class="form-check-label" for="c_ver_todos">Ver todos los cursos del sistema (Sin
                                restricciones)</label>
                        </div>

                        <h6 class="mt-4 fw-semibold text-warning">Alcance Administrativo</h6>
                        <hr class="mt-0">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input toggle-carreras" type="checkbox" name="limita_carreras"
                                id="c_limita" value="1">
                            <label class="form-check-label" for="c_limita">Requiere asignar qué carreras puede ver en su
                                Dashboard</label>
                        </div>

                        <div class="mb-3 div-carreras d-none">
                            <label class="form-label">Carreras Permitidas</label>
                            <select name="carreras[]" class="form-select select2" multiple>
                                @foreach ($carreras as $carrera)
                                    <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary rounded-pill">Guardar Cargo</button>
                        <button type="button" class="btn btn-outline-secondary rounded-pill"
                            data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Editar --}}
    <div class="modal fade" id="modalEditar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formEditar" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Cargo de Curso</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Cargo</label>
                            <input type="text" name="nombre" id="editNombre" class="form-control" required>
                        </div>

                        <h6 class="mt-4 fw-semibold text-primary">Permisos Activos</h6>
                        <hr class="mt-0">

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input checkbox-permiso" type="checkbox"
                                name="puede_responder_preguntas" id="e_resp" value="1">
                            <label class="form-check-label" for="e_resp">Moderador del Foro (Responder Dudas)</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input checkbox-permiso" type="checkbox" name="puede_editar_curso"
                                id="e_ed_c" value="1">
                            <label class="form-check-label" for="e_ed_c">Editar Información e Imágenes del Curso</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input checkbox-permiso" type="checkbox"
                                name="puede_editar_restricciones" id="e_ed_r" value="1">
                            <label class="form-check-label" for="e_ed_r">Gestionar Restricciones y Precios</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input checkbox-permiso" type="checkbox"
                                name="puede_editar_contenido" id="e_ed_cont" value="1">
                            <label class="form-check-label" for="e_ed_cont">Gestionar Módulos y Lecciones</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input checkbox-permiso" type="checkbox"
                                name="puede_gestionar_equipo" id="e_g_equipo" value="1">
                            <label class="form-check-label" for="e_g_equipo">Añadir/Remover equipo del curso</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input checkbox-permiso" type="checkbox"
                                name="puede_gestionar_estudiantes" id="e_g_est" value="1">
                            <label class="form-check-label" for="e_g_est">Pestaña "Estudiantes" (Ver inscritos)</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input checkbox-permiso" type="checkbox"
                                name="puede_ver_todos_los_cursos" id="e_ver_todos" value="1">
                            <label class="form-check-label" for="e_ver_todos">Ver todos los cursos del sistema (Sin
                                restricciones)</label>
                        </div>

                        <h6 class="mt-4 fw-semibold text-warning">Alcance Administrativo</h6>
                        <hr class="mt-0">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input toggle-carreras" type="checkbox" name="limita_carreras"
                                id="e_limita" value="1">
                            <label class="form-check-label" for="e_limita">Requiere asignar qué carreras puede ver en su
                                Dashboard</label>
                        </div>

                        <div class="mb-3 div-carreras d-none">
                            <label class="form-label">Carreras Permitidas</label>
                            <select name="carreras[]" id="editCarreras" class="form-select select2" multiple>
                                @foreach ($carreras as $carrera)
                                    <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary rounded-pill waves-effect waves-light">Guardar
                            cambios</button>
                        <button type="button" class="btn btn-outline-secondary rounded-pill waves-effect waves-light"
                            data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Confirmación eliminar
            document.querySelectorAll('.eliminar-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    let form = this.closest('form');

                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: "Se borrará este rol. Los miembros que lo tengan asignado perderán los permisos. Esta acción no se puede deshacer de forma segura si ya está en uso.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        customClass: {
                            confirmButton: 'btn btn-danger me-3',
                            cancelButton: 'btn btn-secondary'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            // Inicializar Select2
            $('.select2').each(function() {
                var $this = $(this);
                $this.select2({
                    dropdownParent: $this.parent(),
                    placeholder: 'Seleccione carreras...'
                });
            });

            // Toggle visibilidad carreras
            document.querySelectorAll('.toggle-carreras').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const div = this.closest('.modal-body').querySelector('.div-carreras');
                    if (this.checked) {
                        div.classList.remove('d-none');
                    } else {
                        div.classList.add('d-none');
                    }
                });
            });

            // Editar dinámico
            document.querySelectorAll('.btn-editar').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.dataset.id;
                    document.getElementById('editNombre').value = this.dataset.nombre;
                    document.getElementById('e_resp').checked = this.dataset.responder == '1';
                    document.getElementById('e_ed_c').checked = this.dataset.editarCurso == '1';
                    document.getElementById('e_ed_r').checked = this.dataset.editarRestricciones ==
                        '1';
                    document.getElementById('e_ed_cont').checked = this.dataset.editarContenido ==
                        '1';
                    document.getElementById('e_g_equipo').checked = this.dataset.gestionarEquipo ==
                        '1';
                    document.getElementById('e_g_est').checked = this.dataset
                        .gestionarEstudiantes == '1';
                    document.getElementById('e_ver_todos').checked = this.dataset.verTodos == '1';

                    const limita = this.dataset.limitaCarreras == '1';
                    const cbLimita = document.getElementById('e_limita');
                    cbLimita.checked = limita;

                    // Mostrar/ocultar div carreras
                    const divEdit = document.querySelector('#modalEditar .div-carreras');
                    if (limita) {
                        divEdit.classList.remove('d-none');
                    } else {
                        divEdit.classList.add('d-none');
                    }

                    // Cargar carreras en Select2
                    const carreras = JSON.parse(this.dataset.carreras || '[]');
                    $('#editCarreras').val(carreras).trigger('change');

                    const form = document.getElementById('formEditar');
                    form.action = "{{ route('cursos.tipos-cargo.update', ':id') }}".replace(':id', id);
                });
            });
        });
    </script>
@endsection
