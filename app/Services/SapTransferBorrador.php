<?php

namespace App\Services;

use Illuminate\Support\Collection;

class SapTransferBorrador
{
    /** @return array{encabezado: string, detalle: string, faltantes: array<string, int>} */
    public function generar(Collection $movimientos): array
    {
        $cabecera = ['DocNum', 'DocType', 'DocDate', 'U_HBT_Tercero', 'DocCurrency', 'TaxDate', 'Series', 'TransferAccount', 'TransferDate', 'TransferSum', 'U_MAN_CONVENIO_REC', 'U_MAN_FECHA_REC', 'Reference2', 'JournalRemarks', 'Remarks'];
        $cabeceraTecnica = ['DocNum', 'DocType', 'DocDate', 'U_HBT_Tercero', 'DocCurr', 'TaxDate', 'Series', 'TransferAccount', 'TransferDate', 'TransferSum', 'U_MAN_CONVENIO_REC', 'U_MAN_FECHA_REC', 'Ref2', 'JrnlMemo', 'Remarks'];
        $detalle = ['ParentKey', 'LineNum', 'AccountCode', 'SumPaid', 'SumPaidFC', 'Decription', 'U_HBT_Tercero', 'U_MAN_LISTADO', 'ProfitCenter', 'ProjectCode', 'ProfitCenter2', 'U_MAN_TRANSACCION', 'U_CODIGO_BARRAS', 'JournalRemarks', 'Comments'];
        $detalleTecnico = ['docnum', 'linenum', 'acctcode', 'assumapplied', 'appliedfc', 'descrip', 'u_hbt_tercero', 'u_man_listado', 'ocrcode', 'project', 'ocrcode2', 'u_man_transaccion', 'u_codigo_barras', 'JrnlMemo', 'Comments'];
        $documentos = [];
        $lineas = [];
        $faltantes = [];

        foreach ($movimientos->sortBy(fn (object $fila): string => sprintf('%s-%s-%012d', $fila->fecha, $fila->origen, $fila->id)) as $fila) {
            $canal = match ((int) $fila->int_id_forma_pago) {
                29 => 'PSE',
                32, 51 => 'Tarjeta',
                default => $fila->origen === 'ofrenda' ? 'PSE' : ($fila->punto_pago_id !== null ? 'PDP' : 'Sin definir'),
            };
            $fecha = str_replace('-', '', substr((string) $fila->fecha, 0, 10));
            $clave = implode('|', [$fecha, $fila->origen, $canal, $fila->moneda_id]);
            if (! isset($documentos[$clave])) {
                [$cuenta, $convenio] = match ($canal) {
                    'PSE', 'Tarjeta' => ['11050513', 'DAV1'],
                    'PDP' => ['11050512', 'PDP1'],
                    default => ['', ''],
                };
                $numero = count($documentos) + 1;
                $comentario = $fila->origen === 'ofrenda' ? 'Pago en Linea '.$canal : 'Pago Eventos - '.$canal;
                $fechaTransferencia = $canal === 'Tarjeta' ? $fecha : date('Ymd', strtotime(substr((string) $fila->fecha, 0, 10).' +1 day'));
                $documentos[$clave] = ['numero' => $numero, 'suma' => 0, 'lineas' => 0, 'fila' => [$numero, 'rAccount', $fecha, 'P800116748', (int) $fila->moneda_id === 1 ? '$' : '', $fecha, '78', $cuenta, $fechaTransferencia, 0, $convenio, $fecha, $fila->origen === 'ofrenda' ? 'Donacion' : 'Eventos', $comentario, $comentario]];
            }
            $documentos[$clave]['lineas']++;
            $documentos[$clave]['suma'] += (float) $fila->valor;
            $identificacion = trim((string) $fila->identificacion);
            $referencia = trim((string) $fila->referencia);
            if ($fila->origen === 'actividad' && stripos((string) ($fila->metodo_pago ?? ''), 'efectivo') !== false) {
                $referencia = (string) $fila->id;
            }
            $campos = ['AccountCode' => trim((string) $fila->cuenta_sap), 'ProfitCenter' => trim((string) $fila->sucursal_sap), 'ProfitCenter2' => trim((string) $fila->centro_costo_sap), 'U_HBT_Tercero' => $identificacion, 'U_MAN_TRANSACCION' => $referencia];
            foreach ($campos as $campo => $valor) {
                if ($valor === '') {
                    $faltantes[$campo] = ($faltantes[$campo] ?? 0) + 1;
                }
            }
            if ($campos['AccountCode'] !== '' && preg_match('/^[0-9]{8}$/', $campos['AccountCode']) !== 1) {
                $faltantes['AccountCode con formato distinto a Visión'] = ($faltantes['AccountCode con formato distinto a Visión'] ?? 0) + 1;
            }
            foreach (['canal sin equivalencia confirmada' => $canal === 'Sin definir', 'moneda sin equivalencia confirmada' => (int) $fila->moneda_id !== 1] as $campo => $falta) {
                if ($falta) {
                    $faltantes[$campo] = ($faltantes[$campo] ?? 0) + 1;
                }
            }
            $lineas[] = [$documentos[$clave]['numero'], $documentos[$clave]['lineas'], $campos['AccountCode'], $fila->valor, '', $fila->concepto, $identificacion === '' ? '' : 'C'.$identificacion, '', $campos['ProfitCenter'], '', $campos['ProfitCenter2'], $campos['U_MAN_TRANSACCION'], '', $documentos[$clave]['fila'][13], $documentos[$clave]['fila'][13]];
        }

        $encabezados = [];
        foreach ($documentos as $documento) {
            $fila = $documento['fila'];
            $fila[9] = number_format($documento['suma'], 2, '.', '');
            $encabezados[] = $fila;
        }

        return ['encabezado' => $this->tsv($cabecera, $cabeceraTecnica, $encabezados), 'detalle' => $this->tsv($detalle, $detalleTecnico, $lineas), 'faltantes' => $faltantes];
    }

    private function tsv(array $titulos, array $tecnicos, array $filas): string
    {
        $archivo = fopen('php://temp', 'w+');
        foreach ([$titulos, $tecnicos, ...$filas] as $fila) {
            fputcsv($archivo, $fila, "\t", '"', '');
        }
        rewind($archivo);
        $contenido = stream_get_contents($archivo);
        fclose($archivo);

        if (! mb_check_encoding($contenido, 'UTF-8')) {
            throw new \RuntimeException('El borrador SAP contiene texto que no está codificado en UTF-8.');
        }

        return $contenido;
    }
}
