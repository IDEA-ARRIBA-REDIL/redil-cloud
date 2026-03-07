import os

path = 'd:/Programacion/redil-laravel-11/resources/views/livewire/cursos/gestionar-contenido-curso.blade.php'

with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

str1 = """                                                            <button @click="tipoContenido = 'archivo'; modo = 'editar'" class="btn btn-outline-primary d-flex flex-column align-items-center p-3 border-2" style="width: 120px;">
                                                                <i class="ti ti-file-download mb-2" style="font-size: 1.5rem;"></i>
                                                                <span>Recurso</span>
                                                            </button>"""

repl1 = str1 + """
                                                            <button @click="tipoContenido = 'iframe'; modo = 'editar'" class="btn btn-outline-primary d-flex flex-column align-items-center p-3 border-2" style="width: 120px;">
                                                                <i class="ti ti-code mb-2" style="font-size: 1.5rem;"></i>
                                                                <span>Iframe</span>
                                                            </button>"""

str2 = """                                                             <button @click="{{ $item->itemable->archivo_path ? 'modo = \\'visualizar\\'' : 'tipoContenido = \\'ninguno\\'' }}" class="btn btn-sm btn-link text-muted mt-2 p-0">
                                                                 <i class="ti ti-arrow-left me-1"></i> Cancelar
                                                             </button>
                                                         </div>
                                                     </div>
                                                 @else"""

repl2 = """                                                             <button @click="{{ $item->itemable->archivo_path ? 'modo = \\'visualizar\\'' : 'tipoContenido = \\'ninguno\\'' }}" class="btn btn-sm btn-link text-muted mt-2 p-0">
                                                                 <i class="ti ti-arrow-left me-1"></i> Cancelar
                                                             </button>
                                                         </div>
                                                     </div>

                                                     <!-- Iframe -->
                                                     <div x-show="tipoContenido === 'iframe'" x-cloak>
                                                         <!-- Vista Previa Iframe -->
                                                         <div x-show="modo === 'visualizar'" class="mb-3">
                                                             <div class="ratio ratio-16x9 mb-3 rounded overflow-hidden shadow-sm p-3 border" style="background-color: #f8f9fa;">
                                                                 {!! $item->itemable->iframe_codigo !!}
                                                             </div>
                                                             <div class="d-flex justify-content-between">
                                                                 <button @click="modo = 'editar'" class="btn btn-sm btn-outline-primary">
                                                                     <i class="ti ti-edit me-1"></i> Editar Iframe
                                                                 </button>
                                                             </div>
                                                         </div>

                                                         <!-- Edición Iframe -->
                                                         <div x-show="modo === 'editar'">
                                                             <div class="mb-3">
                                                                 <label class="form-label">Código del Iframe</label>
                                                                 <textarea class="form-control text-monospace" rows="5" placeholder="<iframe src=&quot;...&quot;></iframe>" x-ref="iframeCodigo{{ $item->id }}" style="font-family: monospace;">{{ $item->itemable->iframe_codigo }}</textarea>
                                                                 <button class="btn btn-primary mt-2" @click="$wire.guardarIframeLeccion({{ $item->itemable->id }}, $refs['iframeCodigo' + $item->id].value).then(() => { modo = 'visualizar' })">
                                                                     Guardar Código
                                                                 </button>
                                                             </div>
                                                             <button @click="{{ $item->itemable->iframe_codigo ? 'modo = \\'visualizar\\'' : 'tipoContenido = \\'ninguno\\'' }}" class="btn btn-sm btn-link text-muted mt-2 ps-0">
                                                                 <i class="ti ti-arrow-left me-1"></i> Cancelar
                                                             </button>
                                                         </div>
                                                     </div>
                                                 @else"""


if str1 in content:
    content = content.replace(str1, repl1)
    print("Patch 1 applied.")
else:
    print("Patch 1 failed to match.")

if str2 in content:
    content = content.replace(str2, repl2)
    print("Patch 2 applied.")
else:
    print("Patch 2 failed to match.")

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)
