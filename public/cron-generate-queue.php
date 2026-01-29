<?php
/**
 * CRON Wrapper - Generate Queue
 *
 * Tento soubor je wrapper pro CRON úlohu na shared hostingu.
 * V administraci hostingu zadejte cestu k tomuto souboru:
 * /www/doc/XXXXXX/jelenivzeleni.cz/www/cron-generate-queue.php
 */

// Načíst konfiguraci
define('ROOT_PATH', dirname(__DIR__));
$config = require ROOT_PATH . '/config/config.php';

// Kontrola tokenu (musí být v $_GET nebo $_SERVER)
$token = $_GET['token'] ?? $_SERVER['CRON_TOKEN'] ?? getenv('CRON_TOKEN') ?? '';
$expectedToken = $config['security']['cron_token'] ?? '';

if (empty($expectedToken)) {
    die("ERROR: CRON token is not configured in config.php\n");
}

if ($token !== $expectedToken) {
    http_response_code(403);
    die("ERROR: Invalid or missing CRON token\n");
}

// Spustit skutečnou CRON úlohu
header('Content-Type: text/plain; charset=utf-8');
require ROOT_PATH . '/cron/generate-call-list.php';
