<?php

/**
 * Cleanup CRON Job
 *
 * Maže staré záznamy z databáze
 * Run daily at 3:00 AM
 *
 * Usage: php cron/cleanup.php
 * Or via HTTP: /cron/cleanup?token=XXX
 */

// Load bootstrap
require_once __DIR__ . '/bootstrap.php';

$log = function($message) {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
};

$log('Starting cleanup...');

$db = Database::getInstance();

// 1. Smazat staré OTP kódy (starší než 24 hodin)
$deleted = $db->query("
    DELETE FROM otp_codes
    WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
")->rowCount();

$log("Deleted $deleted old OTP codes (older than 24 hours)");

// 2. Smazat staré login attempts (starší než 24 hodin)
$deleted = $db->query("
    DELETE FROM login_attempts
    WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
")->rowCount();

$log("Deleted $deleted old login attempts (older than 24 hours)");

// 3. Smazat staré call_queue záznamy (dokončené/odmítnuté starší než 90 dní)
$deleted = $db->query("
    DELETE FROM call_queue
    WHERE status IN ('completed', 'declined', 'gave_up')
      AND created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)
")->rowCount();

$log("Deleted $deleted old call queue records (completed/declined older than 90 days)");

// 4. Označit vypršelá předplatné jako expired
$expired = $db->query("
    UPDATE subscriptions
    SET status = 'expired'
    WHERE status = 'active' AND expires_at < CURDATE()
")->rowCount();

if ($expired > 0) {
    $log("Marked $expired subscriptions as expired");
}

// 5. Smazat staré call_logs (starší než 2 roky)
$deleted = $db->query("
    DELETE FROM call_logs
    WHERE created_at < DATE_SUB(NOW(), INTERVAL 2 YEAR)
")->rowCount();

$log("Deleted $deleted old call logs (older than 2 years)");

$log('Cleanup completed');
