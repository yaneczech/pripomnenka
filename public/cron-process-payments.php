<?php
/**
 * CRON Wrapper - Process Payments
 */

define('ROOT_PATH', dirname(__DIR__));
$config = require ROOT_PATH . '/config/config.php';

$token = $_GET['token'] ?? $_SERVER['CRON_TOKEN'] ?? getenv('CRON_TOKEN') ?? '';
$expectedToken = $config['security']['cron_token'] ?? '';

if (empty($expectedToken)) {
    die("ERROR: CRON token is not configured in config.php\n");
}

if ($token !== $expectedToken) {
    http_response_code(403);
    die("ERROR: Invalid or missing CRON token\n");
}

header('Content-Type: text/plain; charset=utf-8');
require ROOT_PATH . '/cron/process-bank-emails.php';
