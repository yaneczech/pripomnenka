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
    $paths = [
        __DIR__ . '/../src/Models/' . $class . '.php',
        __DIR__ . '/../src/Services/' . $class . '.php',
        __DIR__ . '/../src/Helpers/' . $class . '.php',
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
