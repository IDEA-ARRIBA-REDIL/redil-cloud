const fs = require('fs');
const path = 'd:/Programacion/redil-laravel-11/resources/views/livewire/cursos/gestionar-contenido-curso.blade.php';

let content = fs.readFileSync(path, 'utf8');

// Parche 1: Botón Iframe
const btnRegex =
  /(<button @click="tipoContenido = 'archivo'; modo = 'editar'"[^>]+>\s*<i class="ti ti-file-download mb-2"[^>]+><\/i>\s*<span>Recurso<\/span>\s*<\/button>\s*)<\/div>\s*<\/div>/;
const btnReplacement = `$1<button @click="tipoContenido = 'iframe'; modo = 'editar'" class="btn btn-outline-primary d-flex flex-column align-items-center p-3 border-2" style="width: 120px;">
                                                                <i class="ti ti-code mb-2" style="font-size: 1.5rem;"></i>
                                                                <span>Iframe</span>
                                                            </button>
                                                        </div>
                                                    </div>`;
content = content.replace(btnRegex, btnReplacement);

// Parche 2: Sección Iframe
const sectionRegex =
  /(<button @click="{{ \$item->itemable->archivo_path \? 'modo = \\'visualizar\\'' : 'tipoContenido = \\'ninguno\\'' }}" class="btn btn-sm btn-link text-muted mt-2 p-0">\s*<i class="ti ti-arrow-left me-1"><\/i> Cancelar\s*<\/button>\s*<\/div>\s*<\/div>)\s*@else/;

const sectionReplacement = `$1
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
                                                @else`;
content = content.replace(sectionRegex, sectionReplacement);

fs.writeFileSync(path, content, 'utf8');
console.log('Reemplazo hecho');
