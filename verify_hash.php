<?php
// Simple password hash verification test without loading Laravel

$hash = '$2y$12$M/PwKeJi8GKfRMtS.502neXqz841ULYR3XaU7g9h9WI8jENajHLtS';
$password = 'password';

// Use PHP's password_verify function directly
$result = password_verify($password, $hash);

echo "Password: $password\n";
echo "Hash: $hash\n";
echo "Verification Result: " . ($result ? "PASSED" : "FAILED") . "\n";

if (!$result) {
    echo "\nTrying to generate a new hash for comparison:\n";
    $new_hash = password_hash($password, PASSWORD_BCRYPT);
    echo "New hash: $new_hash\n";
    echo "Verification with new hash: " . (password_verify($password, $new_hash) ? "PASSED" : "FAILED") . "\n";
}
