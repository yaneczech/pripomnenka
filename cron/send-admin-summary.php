<?php

/**
 * Send Admin Summary CRON Job
 *
 * Sends daily summary email to admin (Sofie)
 * Run daily at 7:00 AM
 *
 * Usage: php cron/send-admin-summary.php
 * Or via HTTP: /cron/admin-summary?token=XXX
 */

// Load bootstrap
require_once __DIR__ . '/bootstrap.php';

$log = function($message) {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
};

$log('Starting admin summary email...');

$db = Database::getInstance();
$emailService = new EmailService();

$today = date('Y-m-d');
$weekFromNow = date('Y-m-d', strtotime('+7 days'));

// Gather statistics
$stats = [];

// Calls for today
$stats['calls_today'] = (int) $db->fetchOne("
    SELECT COUNT(*) as count
    FROM call_queue
    WHERE scheduled_date = ?
      AND status = 'pending'
", [$today])['count'];

// Awaiting activation
$stats['awaiting_activation'] = (int) $db->fetchOne("
    SELECT COUNT(*) as count
    FROM subscriptions
    WHERE status = 'awaiting_activation'
")['count'];

// Awaiting payment
$stats['awaiting_payment'] = (int) $db->fetchOne("
    SELECT COUNT(*) as count
    FROM subscriptions
    WHERE status = 'awaiting_payment'
")['count'];

// Unmatched payments
$stats['unmatched_payments'] = (int) $db->fetchOne("
    SELECT COUNT(*) as count
    FROM unmatched_payments
    WHERE matched_to_subscription_id IS NULL
")['count'];

// Expiring this week
$stats['expiring_this_week'] = (int) $db->fetchOne("
    SELECT COUNT(*) as count
    FROM subscriptions
    WHERE status = 'active'
      AND expires_at BETWEEN ? AND ?
", [$today, $weekFromNow])['count'];

// Calls this week
$stats['calls_this_week'] = (int) $db->fetchOne("
    SELECT COUNT(*) as count
    FROM call_queue
    WHERE scheduled_date BETWEEN ? AND ?
      AND status = 'pending'
", [$today, $weekFromNow])['count'];

// Total active customers
$stats['active_customers'] = (int) $db->fetchOne("
    SELECT COUNT(*) as count
    FROM subscriptions
    WHERE status = 'active'
")['count'];

// Total reminders
$stats['total_reminders'] = (int) $db->fetchOne("
    SELECT COUNT(*) as count
    FROM reminders
    WHERE is_active = 1
")['count'];

// This month revenue
$monthStart = date('Y-m-01');
$monthEnd = date('Y-m-t');
$stats['revenue_this_month'] = (float) $db->fetchOne("
    SELECT COALESCE(SUM(price_paid), 0) as total
    FROM subscriptions
    WHERE payment_confirmed_at BETWEEN ? AND ?
", [$monthStart . ' 00:00:00', $monthEnd . ' 23:59:59'])['total'];

$log('Stats gathered:');
$log("  - Calls today: {$stats['calls_today']}");
$log("  - Awaiting activation: {$stats['awaiting_activation']}");
$log("  - Unmatched payments: {$stats['unmatched_payments']}");
$log("  - Expiring this week: {$stats['expiring_this_week']}");

// Sestavit seznam příjemců: všichni admini + další e-maily z nastavení
$admins = $db->fetchAll("SELECT email FROM admins");
$recipients = array_column($admins, 'email');

$setting = new Setting();
$extraEmails = trim($setting->get('notification_emails', ''));
if (!empty($extraEmails)) {
    $parsed = array_map('trim', explode(',', $extraEmails));
    $parsed = array_filter($parsed, function ($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    });
    $recipients = array_unique(array_merge($recipients, $parsed));
}

if (empty($recipients)) {
    $log('No recipients found, skipping email');
    exit;
}

$log('Sending summary to ' . count($recipients) . ' recipient(s): ' . implode(', ', $recipients));

// Send email to each recipient
$sentCount = 0;
$failCount = 0;

foreach ($recipients as $email) {
    $result = $emailService->sendAdminSummaryEmail($email, $stats);

    if ($result) {
        $sentCount++;
        $log("Summary email sent to: {$email}");
    } else {
        $failCount++;
        $log("FAILED to send summary to: {$email}");
    }

    usleep(100000); // 100ms
}

$log("Admin summary completed: {$sentCount} sent, {$failCount} failed");
