<?php

/**
 * CRON Bootstrap
 *
 * Shared initialization for all CRON scripts
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Set timezone
date_default_timezone_set('Europe/Prague');

// Verify CRON token if running via HTTP
if (php_sapi_name() !== 'cli') {
    $config = require __DIR__ . '/../config/config.php';
    $providedToken = $_GET['token'] ?? '';

    if (empty($config['security']['cron_token']) || $providedToken !== $config['security']['cron_token']) {
        http_response_code(403);
        die('Invalid CRON token');
    }

    // Set content type for HTTP output
    header('Content-Type: text/plain; charset=utf-8');
}

// Autoloader
spl_autoload_register(function ($class) {
    // Odstranit namespace prefix pokud existuje
    $class = ltrim($class, '\\');

    // Pokud třída obsahuje namespace (např. Models\Database)
    // získat jen název třídy (Database)
    $parts = explode('\\', $class);
    $className = end($parts);

    // Možné namespace/složky
    $namespaceToDir = [
        'Models' => 'Models',
        'Services' => 'Services',
        'Helpers' => 'Helpers',
        'Controllers' => 'Controllers',
    ];

    // Zkusit najít podle namespace
    if (count($parts) > 1) {
        $namespace = $parts[0];
        if (isset($namespaceToDir[$namespace])) {
            $path = __DIR__ . '/../src/' . $namespaceToDir[$namespace] . '/' . $className . '.php';
            if (file_exists($path)) {
                require_once $path;
                return;
            }
        }
    }

    // Zkusit najít v každé složce (fallback pro třídy bez namespace)
    $paths = [
        __DIR__ . '/../src/Models/' . $className . '.php',
        __DIR__ . '/../src/Services/' . $className . '.php',
        __DIR__ . '/../src/Helpers/' . $className . '.php',
        __DIR__ . '/../src/Controllers/' . $className . '.php',
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Load helpers
require_once __DIR__ . '/../src/Helpers/functions.php';

// Load config
$config = require __DIR__ . '/../config/config.php';

// Create class aliases for backward compatibility (CRON scripts use classes without namespace)
class_alias('Models\\Database', 'Database');
class_alias('Models\\Setting', 'Setting');
class_alias('Models\\Customer', 'Customer');
class_alias('Models\\Subscription', 'Subscription');
class_alias('Models\\Reminder', 'Reminder');
