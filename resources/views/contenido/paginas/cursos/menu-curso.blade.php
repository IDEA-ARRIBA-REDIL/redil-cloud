<div class="row mb-4">
    <div class="col-12">
        <ul class="nav nav-pills flex-column flex-md-row mb-3 shadow-sm bg-white rounded-3 p-2">
            
            <li class="nav-item">
                <a class="nav-link py-2 px-3 {{ Route::currentRouteName() == 'cursos.editar' ? 'active shadow-sm' : '' }}" 
                   href="{{ route('cursos.editar', $curso) }}">
                    <i class="ti ti-settings me-1"></i> Información General
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link py-2 px-3 {{ Route::currentRouteName() == 'cursos.restricciones' ? 'active shadow-sm' : '' }}" 
                   href="{{ route('cursos.restricciones', $curso) }}">
                    <i class="ti ti-shield-lock me-1"></i> Restricciones
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link py-2 px-3 {{ Route::currentRouteName() == 'cursos.detalle' ? 'active shadow-sm' : '' }}" 
                   href="{{ route('cursos.detalle', $curso) }}">
                    <i class="ti ti-info-circle me-1"></i> Detalles y Syllabus
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link py-2 px-3 {{ Route::currentRouteName() == 'cursos.contenido' ? 'active shadow-sm' : '' }}" 
                   href="{{ route('cursos.contenido', $curso) }}">
                    <i class="ti ti-book me-1"></i> Lecciones (Contenido)
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link py-2 px-3 {{ Route::currentRouteName() == 'cursos.estudiantes' ? 'active shadow-sm' : '' }}" 
                   href="{{ route('cursos.estudiantes', $curso) }}">
                    <i class="ti ti-users me-1"></i> Estudiantes
                    <span class="badge rounded-pill bg-danger ms-1">{{ $curso->usuarios->count() }}</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link py-2 px-3 {{ Route::currentRouteName() == 'cursos.equipo' ? 'active shadow-sm' : '' }}" 
                   href="{{ route('cursos.equipo', $curso) }}">
                    <i class="ti ti-users-group me-1"></i> Equipo
                </a>
            </li>

            <li class="nav-item ms-md-auto">
                <a class="nav-link py-2 px-3 btn-outline-primary" target="_blank"
                   href="{{ route('cursos.previsualizar', $curso->slug) }}">
                    <i class="ti ti-external-link me-1"></i> Vista Previa
                </a>
            </li>
            
        </ul>
    </div>
</div>
