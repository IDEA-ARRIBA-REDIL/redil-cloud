<?php

require __DIR__ . '/vendor/autoload.php';

use Carbon\Carbon;

// Mocking logic
function testLogic($diaGrupo, $diaCorte, $currentDateStr, $diasPlazo = null) {
    // Determine Report Date
    $mockNow = Carbon::parse($currentDateStr);
    Carbon::setTestNow($mockNow);

    echo "Testing Now: $currentDateStr (" . $mockNow->englishDayOfWeek . ")\n";
    echo "Grupo Dia: $diaGrupo, Corte: " . ($diaCorte ?? 'NULL') . ", Plazo: " . ($diasPlazo ?? 'NULL') . "\n";

    // 1. Calculate Fecha Reporte (from Modal/Grupo Update)
    $diaGrupoCarbon = $diaGrupo - 1;
    $daysToAdd = ($diaGrupoCarbon + 6) % 7;
    $fechaReporte = Carbon::now()->startOfWeek(Carbon::MONDAY)->addDays($daysToAdd)->format('Y-m-d');
    
    echo "Fecha Reporte Calculated: $fechaReporte (" . Carbon::parse($fechaReporte)->englishDayOfWeek . ")\n";

    // 2. Validate Range
    $valid = true;
    $fechaReporteObj = Carbon::parse($fechaReporte);
    
    if ($diasPlazo) {
         $fechaLimite = $fechaReporteObj->copy()->addDays($diasPlazo);
         if (Carbon::now()->format('Y-m-d') > $fechaLimite->format('Y-m-d')) {
            $valid = false;
            echo "Failed Plazo. Limit: " . $fechaLimite->format('Y-m-d') . "\n";
         }
    } elseif ($diaCorte) {
         $diaCorteCarbon = $diaCorte - 1;
         $daysToAddCorte = ($diaCorteCarbon + 6) % 7;
         $inicioSemana = $fechaReporteObj->copy()->startOfWeek(Carbon::MONDAY);
         $fechaCorteSemana = $inicioSemana->copy()->addDays($daysToAddCorte);
         
         if (Carbon::now()->format('Y-m-d') > $fechaCorteSemana->format('Y-m-d')) {
            $valid = false;
            echo "Failed Corte. Limit: " . $fechaCorteSemana->format('Y-m-d') . "\n";
         }
    }

    echo "Result: " . ($valid ? "ALLOWED" : "BLOCKED") . "\n";
    echo "------------------------------------------------\n";
}

echo "------------------------------------------------\n";

// Scenario 1: Basic Monday Group, Cutoff Saturday
// Today: Monday Dec 11 2023. Group: 2 (Mon). Cutoff: 7 (Sat).
testLogic(2, 7, '2023-12-11'); 

// Scenario 2: Late Report
// Today: Sunday Dec 17 2023. Group: 2 (Mon). Cutoff: 7 (Sat of CURRENT week Dec 11-17).
// Expectation: Blocked.
testLogic(2, 7, '2023-12-17');

// Scenario 3: Sunday Group (1). Cutoff Sunday (1).
// Today: Sunday Dec 17 2023. Group: 1 (Sun). Cutoff: 1 (Sun).
// Expectation: Allowed. (Today is cutoff).
testLogic(1, 1, '2023-12-17');

// Scenario 4: Plazo Logic
// Group: 5 (Thu). Plazo: 2.
// Meeting Date: Thu Dec 14. Limit: Thu+2 = Sat Dec 16.
// Today: Fri Dec 15. -> Allowed.
testLogic(5, null, '2023-12-15', 2);

// Today: Sun Dec 17. -> Blocked.
testLogic(5, null, '2023-12-17', 2);
