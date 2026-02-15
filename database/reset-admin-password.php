<?php

/**
 * Emergency Admin Password Reset
 *
 * Resets an admin password directly via command line.
 * Use when email-based reset is not available.
 *
 * Usage: php database/reset-admin-password.php [email]
 */

// Prevent web access
if (php_sapi_name() !== 'cli') {
    die('This script must be run from command line');
}

// Load configuration
$config = require __DIR__ . '/../config/config.php';

// Connect to database
try {
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        $config['db']['host'],
        $config['db']['name'],
        $config['db']['charset']
    );

    $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

// Get email from argument or prompt
$email = $argv[1] ?? null;

if (!$email) {
    // List available admins
    $stmt = $pdo->query('SELECT id, email, name FROM admins ORDER BY id');
    $admins = $stmt->fetchAll();

    if (empty($admins)) {
        die("No admin accounts found. Run seed.php first.\n");
    }

    echo "Available admin accounts:\n";
    foreach ($admins as $admin) {
        echo "  [{$admin['id']}] {$admin['email']} ({$admin['name']})\n";
    }

    echo "\nEnter admin email: ";
    $email = trim(fgets(STDIN));
}

// Find admin
$stmt = $pdo->prepare('SELECT id, email, name FROM admins WHERE email = ?');
$stmt->execute([strtolower(trim($email))]);
$admin = $stmt->fetch();

if (!$admin) {
    die("Admin with email '{$email}' not found.\n");
}

echo "Resetting password for: {$admin['name']} ({$admin['email']})\n";

// Generate new password
$password = bin2hex(random_bytes(8)); // 16 characters
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Update password and clear any reset tokens
$stmt = $pdo->prepare('UPDATE admins SET password_hash = ?, password_reset_token = NULL, password_reset_expires_at = NULL WHERE id = ?');
$stmt->execute([$passwordHash, $admin['id']]);

echo "\nNew password: {$password}\n";
echo "\nSAVE THIS PASSWORD! It won't be shown again.\n";
echo "Change it after logging in.\n";
