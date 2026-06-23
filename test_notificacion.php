<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first(); // Or some user
echo 'Originator: '.$user->id."\n";
$lideres = $user->lideres('objeto')->get();
echo 'Lideres count: '.$lideres->count()."\n";
