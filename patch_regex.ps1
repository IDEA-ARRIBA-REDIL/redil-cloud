$path = "d:\Programacion\redil-laravel-11\resources\views\livewire\cursos\gestionar-contenido-curso.blade.php"
$enc = New-Object -TypeName System.Text.UTF8Encoding -ArgumentList $false
$content = [System.IO.File]::ReadAllText($path, $enc)

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
                                                                 <button class="btn btn-primary mt-2" @click="$wire.guardarIframeLeccion({{ $item->itemable->id }}, $refs.iframeCodigo{{ $item->id }}.value).then(() => { modo = 'visualizar' })">
                                                                     Guardar Código
                                                                 </button>
                                                             </div>
                                                             <button @click="{{ $item->itemable->iframe_codigo ? 'modo = \'visualizar\'' : 'tipoContenido = \'ninguno\'' }}" class="btn btn-sm btn-link text-muted mt-2 ps-0">
                                                                 <i class="ti ti-arrow-left me-1"></i> Cancelar
                                                             </button>
                                                         </div>
                                                     </div>
'@

$pattern = "(\r?\n)(\s*)@else(\r?\n)(\s*<!-- Configuración de Evaluación -->)"
$evaluator = [System.Text.RegularExpressions.MatchEvaluator] {
    param([System.Text.RegularExpressions.Match]$match)
    $m1 = $match.Groups[1].Value
    $m2 = $match.Groups[2].Value
    $m3 = $match.Groups[3].Value
    $m4 = $match.Groups[4].Value
    
    return $m1 + $m2 + $strToInsert + $m1 + $m2 + "@else" + $m3 + $m4
}

$newContent = [regex]::Replace($content, $pattern, $evaluator)

if ($newContent -ne $content) {
    [System.IO.File]::WriteAllText($path, $newContent, $enc)
    Write-Host "Success regex replacement"
} else {
    Write-Host "No match found with regex!"
}
