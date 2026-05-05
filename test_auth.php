<?php
require __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = User::where('email', 'admin@kwikshift.com')->first();

echo "User found: " . ($user ? "Yes" : "No") . "\n";
echo "Email: " . $user->email . "\n";
echo "Password hash: " . $user->password . "\n";

// Test if password matches
$passwordMatch = Hash::check('password', $user->password);
echo "Password 'password' matches: " . ($passwordMatch ? "YES" : "NO") . "\n";

// Test authentication
$authenticated = Auth::attempt(['email' => 'admin@kwikshift.com', 'password' => 'password']);
echo "Authentication result: " . ($authenticated ? "SUCCESS" : "FAILED") . "\n";
