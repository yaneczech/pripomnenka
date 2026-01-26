<?php

/**
 * Database Seeder
 *
 * Creates initial admin account and seed data
 *
 * Usage: php database/seed.php
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

    echo "✓ Connected to database\n";

} catch (PDOException $e) {
    die("✗ Database connection failed: " . $e->getMessage() . "\n");
}

// ============================================================
// Create Admin Account
// ============================================================

echo "\n== Creating Admin Account ==\n";

$adminEmail = 'sofie@jelenivzeleni.cz';
$adminName = 'Sofie';

// Check if admin already exists
$stmt = $pdo->prepare('SELECT id FROM admins WHERE email = ?');
$stmt->execute([$adminEmail]);

if ($stmt->fetch()) {
    echo "→ Admin account already exists: {$adminEmail}\n";
} else {
    // Generate random password
    $password = bin2hex(random_bytes(8)); // 16 characters

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('INSERT INTO admins (email, password_hash, name) VALUES (?, ?, ?)');
    $stmt->execute([$adminEmail, $passwordHash, $adminName]);

    echo "✓ Admin account created:\n";
    echo "  Email: {$adminEmail}\n";
    echo "  Password: {$password}\n";
    echo "\n  ⚠️  SAVE THIS PASSWORD! It won't be shown again.\n";
}

// ============================================================
// Verify Subscription Plans
// ============================================================

echo "\n== Verifying Subscription Plans ==\n";

$stmt = $pdo->query('SELECT COUNT(*) FROM subscription_plans');
$planCount = $stmt->fetchColumn();

if ($planCount > 0) {
    echo "→ {$planCount} subscription plan(s) already exist\n";
} else {
    // Insert default plans
    $plans = [
        ['Early bird', 'early_bird', 75.00, 5, 10, 1, 0, 1, 'Zvýhodněná cena pro první zákazníky. 5 připomínek, 10% sleva na kytice.'],
        ['Standard', 'standard', 150.00, 10, 10, 1, 1, 2, 'Plná verze služby. 10 připomínek, 10% sleva na kytice.'],
    ];

    $stmt = $pdo->prepare('
        INSERT INTO subscription_plans
        (name, slug, price, reminder_limit, discount_percent, is_available, is_default, sort_order, description)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    foreach ($plans as $plan) {
        $stmt->execute($plan);
    }

    echo "✓ Created default subscription plans\n";
}

// List current plans
$stmt = $pdo->query('SELECT name, price, reminder_limit, is_default FROM subscription_plans ORDER BY sort_order');
$plans = $stmt->fetchAll();

foreach ($plans as $plan) {
    $default = $plan['is_default'] ? ' (default)' : '';
    echo "  - {$plan['name']}: {$plan['price']} Kč, {$plan['reminder_limit']} reminders{$default}\n";
}

// ============================================================
// Verify Settings
// ============================================================

echo "\n== Verifying Settings ==\n";

$stmt = $pdo->query('SELECT COUNT(*) FROM settings');
$settingsCount = $stmt->fetchColumn();

if ($settingsCount > 0) {
    echo "→ {$settingsCount} setting(s) already exist\n";
} else {
    // Insert default settings
    $settings = [
        ['default_advance_days', '5'],
        ['workdays', '1,2,3,4,5'],
        ['email_customer_reminder_subject', 'Blíží se důležité datum! 💐'],
        ['email_customer_reminder_template', 'Dobrý den{{#name}}, {{name}}{{/name}}!\n\nBlíží se {{event_type}} ({{recipient}}) dne {{date}}.\n\nBrzy vám zavoláme z květinářství Jeleni v zeleni.\n\nPokud nechcete čekat: {{shop_phone}}'],
        ['email_activation_subject', 'Vítejte v Připomněnce! Nastavte si své připomínky 💐'],
        ['email_payment_qr_subject', 'QR kód pro platbu předplatného Připomněnka'],
        ['email_expiration_subject', 'Vaše předplatné Připomněnka brzy vyprší'],
        ['shop_phone', '123456789'],
        ['shop_email', 'info@jelenivzeleni.cz'],
        ['bank_account', '123456789/0100'],
        ['bank_iban', 'CZ1234567890123456789012'],
        ['bank_imap_host', 'imap.airbank.cz'],
        ['bank_imap_email', ''],
        ['bank_imap_password', ''],
        ['activation_link_validity_days', '30'],
    ];

    $stmt = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)');

    foreach ($settings as $setting) {
        $stmt->execute($setting);
    }

    echo "✓ Created default settings\n";
}

// ============================================================
// Initialize VS Counter
// ============================================================

echo "\n== Verifying VS Counter ==\n";

$year = date('Y');
$shortYear = date('y');

$stmt = $pdo->prepare('SELECT last_number FROM vs_counter WHERE year = ?');
$stmt->execute([$shortYear]);
$counter = $stmt->fetch();

if ($counter) {
    echo "→ VS counter for {$year}: last number is {$counter['last_number']}\n";
} else {
    $stmt = $pdo->prepare('INSERT INTO vs_counter (year, last_number) VALUES (?, 0)');
    $stmt->execute([$shortYear]);
    echo "✓ Initialized VS counter for {$year}\n";
}

// ============================================================
// Create Test Customer (optional)
// ============================================================

echo "\n== Test Data ==\n";

// Ask if user wants to create test data
echo "Create test customer? [y/N]: ";
$input = trim(fgets(STDIN));

if (strtolower($input) === 'y') {
    $testPhone = '+420777888999';
    $testEmail = 'test@example.com';

    // Check if exists
    $stmt = $pdo->prepare('SELECT id FROM customers WHERE phone = ? OR email = ?');
    $stmt->execute([$testPhone, $testEmail]);

    if ($stmt->fetch()) {
        echo "→ Test customer already exists\n";
    } else {
        // Create customer
        $stmt = $pdo->prepare('
            INSERT INTO customers (phone, phone_hash, email, email_hash, name, gdpr_consent_at, gdpr_consent_text)
            VALUES (?, ?, ?, ?, ?, NOW(), ?)
        ');
        $stmt->execute([
            $testPhone,
            hash('sha256', $testPhone),
            $testEmail,
            hash('sha256', $testEmail),
            'Jan Testovací',
            'Souhlasím se zpracováním osobních údajů dle GDPR.',
        ]);

        $customerId = $pdo->lastInsertId();

        // Get default plan
        $stmt = $pdo->query('SELECT id, price, reminder_limit FROM subscription_plans WHERE is_default = 1 LIMIT 1');
        $plan = $stmt->fetch();

        // Generate VS
        $stmt = $pdo->prepare('UPDATE vs_counter SET last_number = last_number + 1 WHERE year = ?');
        $stmt->execute([$shortYear]);

        $stmt = $pdo->prepare('SELECT last_number FROM vs_counter WHERE year = ?');
        $stmt->execute([$shortYear]);
        $lastNumber = $stmt->fetchColumn();

        $vs = $shortYear . str_pad($lastNumber, 3, '0', STR_PAD_LEFT);

        // Create subscription
        $stmt = $pdo->prepare('
            INSERT INTO subscriptions
            (customer_id, plan_id, reminder_limit, price, variable_symbol, starts_at, expires_at, payment_method, payment_status, status)
            VALUES (?, ?, ?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), ?, ?, ?)
        ');
        $stmt->execute([
            $customerId,
            $plan['id'],
            $plan['reminder_limit'],
            $plan['price'],
            $vs,
            'cash',
            'paid',
            'active',
        ]);

        // Create sample reminders
        $reminders = [
            ['birthday', 'wife', 15, 3, '800_1200', 'Má ráda tulipány'],
            ['wedding_anniversary', 'wife', 8, 6, '1200_2000', null],
            ['nameday', 'mother', 18, 11, '500_800', 'Preferuje pastelové barvy'],
        ];

        $stmt = $pdo->prepare('
            INSERT INTO reminders
            (customer_id, event_type, recipient_relation, event_day, event_month, price_range, customer_note)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');

        foreach ($reminders as $reminder) {
            $stmt->execute(array_merge([$customerId], $reminder));
        }

        echo "✓ Created test customer:\n";
        echo "  Phone: {$testPhone}\n";
        echo "  Email: {$testEmail}\n";
        echo "  Name: Jan Testovací\n";
        echo "  Subscription: Active (Standard)\n";
        echo "  Reminders: 3\n";
    }
} else {
    echo "→ Skipping test data\n";
}

// ============================================================
// Summary
// ============================================================

echo "\n== Summary ==\n";
echo "✓ Database seeding completed!\n\n";

echo "Next steps:\n";
echo "1. Update config/config.php with your database credentials\n";
echo "2. Configure email settings in admin panel\n";
echo "3. Set up CRON jobs for automated tasks\n";
echo "4. Change admin password after first login!\n\n";
