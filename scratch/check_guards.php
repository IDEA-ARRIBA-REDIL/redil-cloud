<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;
echo "ROLES:\n";
$roles = DB::table('roles')->get();
foreach ($roles as $role) {
    echo "- Name: {$role->name}, Guard: {$role->guard_name}\n";
}
echo "\nPERMISSIONS (first 20):\n";
$permissions = DB::table('permissions')->limit(20)->get();
foreach ($permissions as $p) {
    echo "- Name: {$p->name}, Guard: {$p->guard_name}\n";
}
echo "\nGuard names summary:\n";
$guards = DB::table('permissions')->select('guard_name', DB::raw('count(*) as total'))->groupBy('guard_name')->get();
foreach ($guards as $g) {
    echo '- Guard: '.($g->guard_name ?? 'NULL').", Total: {$g->total}\n";
}
