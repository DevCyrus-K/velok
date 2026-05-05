<?php
require __DIR__ . '/bootstrap/app.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

// Delete all users except admin@kwikshift.com
$deleted = User::where('email', '<>', 'admin@kwikshift.com')->delete();
echo "Deleted $deleted user(s)\n";

// Update the remaining user's password to "password"
$updated = User::where('email', 'admin@kwikshift.com')->update([
    'password' => bcrypt('password')
]);
echo "Updated $updated user(s) with password\n";

echo "\nRemaining users:\n";
User::all()->each(function($u) {
    echo "ID: {$u->id}, Name: {$u->name}, Email: {$u->email}\n";
});
