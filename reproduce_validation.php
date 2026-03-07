<?php

use App\Models\Actividad;
use App\Models\ActividadCategoria;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

// 1. Create a dummy user
$user = new User();
$user->id = 99999;
$user->genero = 1; // Male
$user->fecha_nacimiento = '2000-01-01'; // 25 years old
$user->tipo_usuario_id = 1;
$user->sede_id = 1;
$user->estado_civil_id = 1;
// Mock relations
$user->setRelation('pasosCrecimiento', collect());

// 2. Create a dummy activity
$actividad = new Actividad();
$actividad->id = 88888;
$actividad->nombre = "Test Activity";
$actividad->restriccion_por_categoria = true;

// 3. Create categories
// Cat 1: Valid (Matches User)
$cat1 = new ActividadCategoria();
$cat1->id = 101;
$cat1->nombre = "Category Valid";
$cat1->genero = 1; // Male (Matches)
$cat1->actividad_id = $actividad->id;
$cat1->setRelation('tipoUsuarios', collect());
$cat1->setRelation('rangosEdad', collect());
$cat1->setRelation('sedes', collect());
$cat1->setRelation('procesosRequisito', collect());

// Cat 2: Invalid (Gender Mismatch)
$cat2 = new ActividadCategoria();
$cat2->id = 102;
$cat2->nombre = "Category Invalid";
$cat2->genero = 2; // Female (Mismatch)
$cat2->actividad_id = $actividad->id;
$cat2->setRelation('tipoUsuarios', collect());
$cat2->setRelation('rangosEdad', collect());
$cat2->setRelation('sedes', collect());
$cat2->setRelation('procesosRequisito', collect());

// Mock the relation on activity
$actividad->setRelation('categorias', collect([$cat1, $cat2]));

// 4. Mock the method behavior (since we can't easily mock the internal calls to relations in a script without full DB)
// Actually, we can just call the method if we mock the relations correctly.
// But `verificarDisponibilidadCategorias` calls `$this->categorias()->with(...)`.
// We can't mock the Eloquent builder easily here.

// ALTERNATIVE: We will just inspect the code logic mentally or create a unit test.
// But since I can't run unit tests easily, I'll try to use the existing DB data if possible.
// Let's try to find an existing activity and user to test with.

$actividadReal = Actividad::where('restriccion_por_categoria', true)->first();
if ($actividadReal) {
    echo "Found Activity: " . $actividadReal->nombre . "\n";
    $userReal = User::first();
    echo "Testing with User: " . $userReal->nombre . "\n";
    
    $result = $actividadReal->verificarDisponibilidadCategorias($userReal->id);
    echo "Result Success: " . ($result['success'] ? 'YES' : 'NO') . "\n";
    echo "Message: " . $result['message'] . "\n";
    echo "Available Categories: " . ($result['categorias'] ? $result['categorias']->count() : 0) . "\n";
} else {
    echo "No activity with restriction found.\n";
}
