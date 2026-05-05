<?php
// Set up Laravel application
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

try {
    $kernel->bootstrap();
    
    // Now test authentication
    \DB::connection()->getPdo();
    echo "Database connection: OK\n";
    
    $user = \App\Models\User::where('email', 'admin@kwikshift.com')->first();
    if ($user) {
        echo "User found: " . $user->email . "\n";
        echo "Password hash stored: " . substr($user->password, 0, 20) . "...\n";
        
        // Test password verification
        $verified = \Illuminate\Support\Facades\Hash::check('password', $user->password);
        echo "Password verification: " . ($verified ? "PASSED" : "FAILED") . "\n";
    } else {
        echo "User not found\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
