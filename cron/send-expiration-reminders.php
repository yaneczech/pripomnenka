<?php

/**
 * Send Expiration Reminders CRON Job
 *
 * Sends emails to customers whose subscriptions are about to expire
 * Run daily at 8:00 AM
 *
 * Usage: php cron/send-expiration-reminders.php
 * Or via HTTP: /cron/expiration-reminders?token=XXX
 */

// Load bootstrap
require_once __DIR__ . '/bootstrap.php';

$log = function($message) {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
};

$log('Starting expiration reminder emails...');

$db = Database::getInstance();
$emailService = new EmailService();

$today = date('Y-m-d');

// Calculate reminder dates
$in30Days = date('Y-m-d', strtotime('+30 days'));
$in14Days = date('Y-m-d', strtotime('+14 days'));

// Get subscriptions expiring in exactly 30 days (first reminder)
// Bezplatné tarify (price=0) se neposílají — automaticky se prodlouží
$expiring30 = $db->fetchAll("
    SELECT
        s.*,
        c.id as customer_id,
        c.phone,
        c.email,
        c.name as customer_name,
        sp.name as plan_name
    FROM subscriptions s
    JOIN customers c ON s.customer_id = c.id
    JOIN subscription_plans sp ON s.plan_id = sp.id
    WHERE s.status = 'active'
      AND s.expires_at = ?
      AND s.price > 0
", [$in30Days]);

$log('Found ' . count($expiring30) . ' subscriptions expiring in 30 days');

// Get subscriptions expiring in exactly 14 days (second reminder)
$expiring14 = $db->fetchAll("
    SELECT
        s.*,
        c.id as customer_id,
        c.phone,
        c.email,
        c.name as customer_name,
        sp.name as plan_name
    FROM subscriptions s
    JOIN customers c ON s.customer_id = c.id
    JOIN subscription_plans sp ON s.plan_id = sp.id
    WHERE s.status = 'active'
      AND s.expires_at = ?
      AND s.price > 0
", [$in14Days]);

$log('Found ' . count($expiring14) . ' subscriptions expiring in 14 days');

$sent = 0;
$failed = 0;

// Send 30-day reminders
foreach ($expiring30 as $sub) {
    $customer = [
        'id' => $sub['customer_id'],
        'name' => $sub['customer_name'],
        'email' => $sub['email'],
        'phone' => $sub['phone'],
    ];

    $subscription = [
        'id' => $sub['id'],
        'plan_name' => $sub['plan_name'],
        'price' => $sub['price'],
        'variable_symbol' => $sub['variable_symbol'],
        'expires_at' => $sub['expires_at'],
    ];

    $result = $emailService->sendExpirationReminderEmail($customer, $subscription, 30);

    if ($result) {
        $sent++;
        $log("Sent 30-day reminder to: {$customer['email']}");
    } else {
        $failed++;
        $log("FAILED 30-day reminder to: {$customer['email']}");
    }

    usleep(100000);
}

// Send 14-day reminders
foreach ($expiring14 as $sub) {
    $customer = [
        'id' => $sub['customer_id'],
        'name' => $sub['customer_name'],
        'email' => $sub['email'],
        'phone' => $sub['phone'],
    ];

    $subscription = [
        'id' => $sub['id'],
        'plan_name' => $sub['plan_name'],
        'price' => $sub['price'],
        'variable_symbol' => $sub['variable_symbol'],
        'expires_at' => $sub['expires_at'],
    ];

    $result = $emailService->sendExpirationReminderEmail($customer, $subscription, 14);

    if ($result) {
        $sent++;
        $log("Sent 14-day reminder to: {$customer['email']}");
    } else {
        $failed++;
        $log("FAILED 14-day reminder to: {$customer['email']}");
    }

    usleep(100000);
}

// Auto-renew free subscriptions (price=0)
$autoRenewed = $db->query("
    UPDATE subscriptions
    SET expires_at = DATE_ADD(expires_at, INTERVAL 1 YEAR)
    WHERE status = 'active'
      AND expires_at <= ?
      AND price = 0
", [$today]);

$renewedCount = $autoRenewed->rowCount();
if ($renewedCount > 0) {
    $log("Auto-renewed $renewedCount free subscriptions");
}

// Mark expired subscriptions (only paid plans)
$stmt = $db->query("
    UPDATE subscriptions
    SET status = 'expired'
    WHERE status = 'active'
      AND expires_at < ?
      AND price > 0
", [$today]);

$expired = $stmt->rowCount();
if ($expired > 0) {
    $log("Marked $expired subscriptions as expired");
}

$log("Expiration reminders completed: $sent sent, $failed failed");
