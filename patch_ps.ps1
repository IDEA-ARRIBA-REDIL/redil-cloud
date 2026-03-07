$path = "d:\Programacion\redil-laravel-11\resources\views\livewire\cursos\gestionar-contenido-curso.blade.php"
$enc = New-Object -TypeName System.Text.UTF8Encoding -ArgumentList $false
$content = [System.IO.File]::ReadAllText($path, $enc)

$search = "                                                 @else`r`n                                                     <!-- Configuración de Evaluación -->"

$strToInsert = @'
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
                                                             <button @click="{{ $item->itemable->iframe_codigo ? 'modo = \'visualizar\'' : 'tipoContenido = \'ninguno\'' }}" class="btn btn-sm btn-link text-muted mt-2 ps-0">
                                                                 <i class="ti ti-arrow-left me-1"></i> Cancelar
                                                             </button>
                                                         </div>
                                                     </div>
'@

$replace = $strToInsert + "`r`n                                                 @else`r`n                                                     <!-- Configuración de Evaluación -->"

if ($content.Contains($search)) {
    $content = $content.Replace($search, $replace)
    [System.IO.File]::WriteAllText($path, $content, $enc)
    Write-Host "Success chunk 2 (CRLF)"
} else {
    $search = "                                                 @else`n                                                     <!-- Configuración de Evaluación -->"
    $replace = $strToInsert + "`n                                                 @else`n                                                     <!-- Configuración de Evaluación -->"
    if ($content.Contains($search)) {
        $content = $content.Replace($search, $replace)
        [System.IO.File]::WriteAllText($path, $content, $enc)
        Write-Host "Success chunk 2 (LF)"
    } else {
        Write-Host "Search string not found!"
    }
}
