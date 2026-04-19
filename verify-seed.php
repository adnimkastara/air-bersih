<?php
require_once 'bootstrap/app.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

// Initialize eloquent
$app->make('db');

echo "=== DATABASE VERIFICATION ===\n\n";

echo "ROLES:\n";
$roles = \App\Models\Role::all();
echo "Total: " . count($roles) . "\n";
foreach ($roles as $role) {
    echo "  - {$role->id}: {$role->name}\n";
}

echo "\nUSERS:\n";
$users = \App\Models\User::with('role')->get();
echo "Total: " . count($users) . "\n";
foreach ($users as $user) {
    $roleName = $user->role ? $user->role->name : 'NO ROLE';
    echo "  - {$user->id}: {$user->email} (Role: {$roleName})\n";
}

echo "\nKECAMATANS:\n";
$kecamatans = \App\Models\Kecamatan::all();
echo "Total: " . count($kecamatans) . "\n";
foreach ($kecamatans as $kec) {
    echo "  - {$kec->id}: {$kec->name}\n";
}

echo "\nDESAS:\n";
$desas = \App\Models\Desa::all();
echo "Total: " . count($desas) . "\n";
foreach ($desas as $desa) {
    echo "  - {$desa->id}: {$desa->name} (Kecamatan ID: {$desa->kecamatan_id})\n";
}

echo "\nPELANGGANS:\n";
$pelanggans = \App\Models\Pelanggan::all();
echo "Total: " . count($pelanggans) . "\n";
foreach ($pelanggans as $pelanggan) {
    echo "  - {$pelanggan->id}: {$pelanggan->name} (Status: {$pelanggan->status})\n";
}

echo "\nMETER RECORDS:\n";
$meters = \App\Models\MeterRecord::all();
echo "Total: " . count($meters) . "\n";
foreach ($meters as $meter) {
    $consumption = $meter->meter_current_month - $meter->meter_previous_month;
    echo "  - {$meter->id}: Pelanggan {$meter->pelanggan_id} | Previous: {$meter->meter_previous_month} | Current: {$meter->meter_current_month} | Consumption: {$consumption}\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";
