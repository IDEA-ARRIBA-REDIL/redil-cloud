<?php
$path = 'd:/Programacion/redil-laravel-11/resources/views/livewire/cursos/gestionar-contenido-curso.blade.php';
$content = file_get_contents($path);

$str1 = "                                                            <button @click=\"tipoContenido = 'archivo'; modo = 'editar'\" class=\"btn btn-outline-primary d-flex flex-column align-items-center p-3 border-2\" style=\"width: 120px;\">\r
                                                                <i class=\"ti ti-file-download mb-2\" style=\"font-size: 1.5rem;\"></i>\r
                                                                <span>Recurso</span>\r
                                                            </button>";

$repl1 = $str1 . "\r\n                                                            <button @click=\"tipoContenido = 'iframe'; modo = 'editar'\" class=\"btn btn-outline-primary d-flex flex-column align-items-center p-3 border-2\" style=\"width: 120px;\">\r\n                                                                <i class=\"ti ti-code mb-2\" style=\"font-size: 1.5rem;\"></i>\r\n                                                                <span>Iframe</span>\r\n                                                            </button>";

$str2 = "                                                             <button @click=\"{{ \$item->itemable->archivo_path ? 'modo = \\'visualizar\\'' : 'tipoContenido = \\'ninguno\\'' }}\" class=\"btn btn-sm btn-link text-muted mt-2 p-0\">\r
                                                                 <i class=\"ti ti-arrow-left me-1\"></i> Cancelar\r
                                                             </button>\r
                                                         </div>\r
                                                     </div>\r
                                                 @else";

$repl2 = "                                                             <button @click=\"{{ \$item->itemable->archivo_path ? 'modo = \\'visualizar\\'' : 'tipoContenido = \\'ninguno\\'' }}\" class=\"btn btn-sm btn-link text-muted mt-2 p-0\">\r\n                                                                 <i class=\"ti ti-arrow-left me-1\"></i> Cancelar\r\n                                                             </button>\r\n                                                         </div>\r\n                                                     </div>\r\n\r\n                                                     <!-- Iframe -->\r\n                                                     <div x-show=\"tipoContenido === 'iframe'\" x-cloak>\r\n                                                         <!-- Vista Previa Iframe -->\r\n                                                         <div x-show=\"modo === 'visualizar'\" class=\"mb-3\">\r\n                                                             <div class=\"ratio ratio-16x9 mb-3 rounded overflow-hidden shadow-sm p-3 border\" style=\"background-color: #f8f9fa;\">\r\n                                                                 {!! \$item->itemable->iframe_codigo !!}\r\n                                                             </div>\r\n                                                             <div class=\"d-flex justify-content-between\">\r\n                                                                 <button @click=\"modo = 'editar'\" class=\"btn btn-sm btn-outline-primary\">\r\n                                                                     <i class=\"ti ti-edit me-1\"></i> Editar Iframe\r\n                                                                 </button>\r\n                                                             </div>\r\n                                                         </div>\r\n\r\n                                                         <!-- Edición Iframe -->\r\n                                                         <div x-show=\"modo === 'editar'\">\r\n                                                             <div class=\"mb-3\">\r\n                                                                 <label class=\"form-label\">Código del Iframe</label>\r\n                                                                 <textarea class=\"form-control text-monospace\" rows=\"5\" placeholder=\"&lt;iframe src=&quot;...&quot;&gt;&lt;/iframe&gt;\" x-ref=\"iframeCodigo{{ \$item->id }}\" style=\"font-family: monospace;\">{{ \$item->itemable->iframe_codigo }}</textarea>\r\n                                                                 <button class=\"btn btn-primary mt-2\" @click=\"\$wire.guardarIframeLeccion({{ \$item->itemable->id }}, \$refs['iframeCodigo' + \$item->id].value).then(() => { modo = 'visualizar' })\">\r\n                                                                     Guardar Código\r\n                                                                 </button>\r\n                                                             </div>\r\n                                                             <button @click=\"{{ \$item->itemable->iframe_codigo ? 'modo = \\'visualizar\\'' : 'tipoContenido = \\'ninguno\\'' }}\" class=\"btn btn-sm btn-link text-muted mt-2 ps-0\">\r\n                                                                 <i class=\"ti ti-arrow-left me-1\"></i> Cancelar\r\n                                                             </button>\r\n                                                         </div>\r\n                                                     </div>\r\n                                                 @else";


// Since Windows might have \n instead of \r\n, let's make it robust by replacing \r\n with \n in the search strings and replacing it dynamically.
$str1_n = str_replace("\r\n", "\n", $str1);
$str2_n = str_replace("\r\n", "\n", $str2);
$repl1_n = str_replace("\r\n", "\n", $repl1);
$repl2_n = str_replace("\r\n", "\n", $repl2);

$content_n = str_replace("\r\n", "\n", $content);

if (strpos($content_n, $str1_n) !== false) {
    $content_n = str_replace($str1_n, $repl1_n, $content_n);
    echo "Patch 1 applied.\n";
} else {
    echo "Patch 1 failed to match.\n";
}

if (strpos($content_n, $str2_n) !== false) {
    $content_n = str_replace($str2_n, $repl2_n, $content_n);
    echo "Patch 2 applied.\n";
} else {
    echo "Patch 2 failed to match.\n";
}

file_put_contents($path, $content_n);
echo "Done.\n";
