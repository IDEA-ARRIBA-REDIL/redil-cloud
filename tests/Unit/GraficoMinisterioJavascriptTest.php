<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class GraficoMinisterioJavascriptTest extends TestCase
{
    private function ejecutarJavascript(string $prueba): array
    {
        $vista = dirname(__DIR__, 2).'/resources/views/contenido/paginas/grupos/grafico-del-ministerio.blade.php';
        $codigo = <<<'JS'
const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const source = fs.readFileSync(process.argv[1], 'utf8');
const layout = source.slice(source.indexOf('function distribuirNodos('), source.indexOf('// 2. Renderizado del Gráfico'));
function distribuir(nodos, aristas) {
    const context = { width: 1000, nodos, aristas };
    const conexiones = JSON.stringify(aristas);
    vm.runInNewContext(layout + '\ndistribuirNodos(nodos, aristas);', context, { timeout: 3000 });
    assert.equal(JSON.stringify(aristas), conexiones);
    assert(nodos.every(node => Number.isFinite(node.x) && Number.isFinite(node.y)));
    return nodos;
}
JS;
        $proceso = new Process(['node', '-e', $codigo."\n".$prueba, $vista]);
        $proceso->setTimeout(10);
        $proceso->run();
        $this->assertTrue($proceso->isSuccessful(), $proceso->getErrorOutput());

        return json_decode($proceso->getOutput(), true, flags: JSON_THROW_ON_ERROR);
    }

    public function test_encargado_que_pertenece_a_su_grupo_no_bloquea_el_dibujo(): void
    {
        $resultado = $this->ejecutarJavascript(<<<'JS'
const nodos = [{ id: 'A-1', level: 0 }, { id: 'G-450', level: 1 }, { id: 'A-2', level: 2 }];
const aristas = [{ from: 'A-1', to: 'G-450' }, { from: 'G-450', to: 'A-1' }, { from: 'G-450', to: 'A-2' }];
console.log(JSON.stringify(distribuir(nodos, aristas)));
JS);
        $this->assertSame(['A-1', 'G-450', 'A-2'], array_column($resultado, 'id'));
        $this->assertSame([80, 255, 430], array_column($resultado, 'y'));
    }

    public function test_ciclos_largos_autorreferencias_y_nodos_aislados_tienen_posiciones_validas(): void
    {
        $resultado = $this->ejecutarJavascript(<<<'JS'
const nodos = Array.from({ length: 8 }, (_, i) => ({ id: `N-${i}`, level: i }));
const aristas = [
    { from: 'N-0', to: 'N-1' }, { from: 'N-1', to: 'N-2' }, { from: 'N-2', to: 'N-0' },
    { from: 'N-2', to: 'N-2' }, { from: 'N-4', to: 'N-5' }, { from: 'N-5', to: 'N-4' },
    { from: 'N-2', to: 'faltante' }, { from: 'faltante', to: 'N-3' },
];
console.log(JSON.stringify(distribuir(nodos, aristas)));
JS);
        $this->assertCount(8, $resultado);
        $this->assertSame(8, count(array_unique(array_column($resultado, 'id'))));
    }

    public function test_hojas_compartidas_no_se_reubican_ni_se_superponen(): void
    {
        $resultado = $this->ejecutarJavascript(<<<'JS'
const crearNodos = () => [
    { id: 'G-1', level: 0 }, { id: 'G-2', level: 0 },
    ...Array.from({ length: 14 }, (_, i) => ({ id: `A-${i}`, level: 1 })),
];
const aristas = Array.from({ length: 14 }, (_, i) => ({ from: i < 7 ? 'G-1' : 'G-2', to: `A-${i}` }));
const nodos = distribuir(crearNodos(), [...aristas, { from: 'G-2', to: 'A-0' }, { from: 'G-1', to: 'A-0' }]);
for (const a of nodos) {
    for (const b of nodos) {
        if (a.id !== b.id && a.y === b.y) assert(Math.abs(a.x - b.x) >= 140);
    }
}
assert.equal(nodos.find(n => n.id === 'A-0').y, 255);
assert.equal(nodos.find(n => n.id === 'A-1').y, 315);
console.log(JSON.stringify(nodos));
JS);
        $this->assertCount(16, $resultado);
    }

    public function test_miles_de_personas_y_grafico_vacio_se_resuelven_sin_bloqueo(): void
    {
        $resultado = $this->ejecutarJavascript(<<<'JS'
assert.deepEqual(distribuir([], []), []);
const nodos = [{ id: 'G-1', level: 0 }, ...Array.from({ length: 2000 }, (_, i) => ({ id: `A-${i}`, level: 1 }))];
const aristas = nodos.slice(1).map(node => ({ from: 'G-1', to: node.id }));
distribuir(nodos, aristas);
console.log(JSON.stringify({ cantidad: nodos.length, posiciones: new Set(nodos.map(n => `${n.x},${n.y}`)).size }));
JS);
        $this->assertSame(['cantidad' => 2001, 'posiciones' => 2001], $resultado);
    }

    public function test_fallo_de_totales_permite_reintentar_sin_afectar_el_grafico(): void
    {
        $resultado = $this->ejecutarJavascript(<<<'JS'
const elements = {};
for (const id of ['totales-ministerio', 'total-personas-ministerio', 'total-grupos-ministerio', 'reintentar-totales-ministerio']) {
    const classes = new Set(['d-none']);
    elements[id] = {
        textContent: '', attributes: { 'aria-busy': 'false' }, dataset: { url: '/grupo/grafico-del-ministerio/G-450/2?solo_totales=1' },
        getAttribute(key) { return this.attributes[key]; }, setAttribute(key, value) { this.attributes[key] = value; },
        classList: { add(name) { classes.add(name); }, remove(name) { classes.delete(name); }, contains(name) { return classes.has(name); } },
    };
}
const codigoTotales = source.slice(source.indexOf('async function cargarTotalesMinisterio('), source.indexOf('document.addEventListener("DOMContentLoaded"'));
let requests = 0;
let mode = 'error';
const context = { document: { getElementById: id => elements[id] }, fetch: async (url, options) => {
    requests++;
    assert.equal(url, elements['totales-ministerio'].dataset.url);
    assert.equal(options.credentials, 'same-origin');
    return { ok: mode !== 'error', json: async () => mode === 'invalid' ? {} : { totalPersonas: 0, totalGrupos: 0 } };
} };
vm.runInNewContext(codigoTotales, context);
(async () => {
    await context.cargarTotalesMinisterio();
    assert.equal(elements['total-personas-ministerio'].textContent, 'No disponible');
    assert.equal(elements['reintentar-totales-ministerio'].classList.contains('d-none'), false);
    mode = 'invalid';
    await context.cargarTotalesMinisterio();
    assert.equal(elements['total-grupos-ministerio'].textContent, 'No disponible');
    mode = 'ok';
    await Promise.all([context.cargarTotalesMinisterio(), context.cargarTotalesMinisterio()]);
    assert.equal(requests, 3);
    assert.equal(elements['total-personas-ministerio'].textContent, '0');
    assert.equal(elements['total-grupos-ministerio'].textContent, '0');
    assert.equal(elements['reintentar-totales-ministerio'].classList.contains('d-none'), true);
    assert.equal(elements['totales-ministerio'].getAttribute('aria-busy'), 'false');
    console.log(JSON.stringify({ requests }));
})().catch(error => { console.error(error); process.exitCode = 1; });
JS);
        $this->assertSame(['requests' => 3], $resultado);
    }
}
