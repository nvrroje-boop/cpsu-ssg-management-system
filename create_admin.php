<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

try {
    // Delete existing admin if exists
    $existing = User::where('email', 'admin@ssg.local')->first();
    if ($existing) {
        $existing->delete();
        echo "Deleted existing admin user.\n";
    }

    // Ensure Admin role exists
    $adminRole = Role::firstOrCreate(['role_name' => 'Admin']);
    echo "Admin role ID: {$adminRole->id}\n";

    // Ensure BSIT department exists
    $department = Department::firstOrCreate(['department_name' => 'BSIT']);
    echo "Department ID: {$department->id}\n";

    // Create new admin user
    $admin = User::create([
        'email' => 'admin@ssg.local',
        'name' => 'System Administrator',
        'password' => Hash::make('admin12345'),
        'role_id' => $adminRole->id,
        'department_id' => $department->id,
        'qr_token' => 'ADMIN-QR-000001',
    ]);

    echo "\n✓ Admin user created successfully!\n";
    echo "Email: admin@ssg.local\n";
    echo "Password: admin12345\n";
    echo "User ID: {$admin->id}\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
