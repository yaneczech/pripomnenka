<?php

/**
 * Send Customer Emails CRON Job
 *
 * Sends reminder emails to customers about upcoming events
 * Run daily at 6:00 AM
 *
 * Usage: php cron/send-customer-emails.php
 * Or via HTTP: /cron/send-emails?token=XXX
 */

// Load bootstrap
require_once __DIR__ . '/bootstrap.php';

$log = function($message) {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
};

$log('Starting customer email sending...');

$db = Database::getInstance();
$emailService = new EmailService();

$today = date('Y-m-d');

// Get all calls scheduled for today that haven't had email sent yet
// We'll add a simple tracking by checking if a reminder email was sent today
$calls = $db->fetchAll("
    SELECT
        cq.id as queue_id,
        cq.reminder_id,
        r.event_type,
        r.recipient_relation,
        r.event_day,
        r.event_month,
        r.price_range,
        r.customer_note,
        c.id as customer_id,
        c.phone,
        c.email,
        c.name as customer_name
    FROM call_queue cq
    JOIN reminders r ON cq.reminder_id = r.id
    JOIN customers c ON r.customer_id = c.id
    JOIN subscriptions s ON s.customer_id = c.id AND s.status = 'active' AND s.expires_at >= CURDATE()
    WHERE cq.scheduled_date = ?
      AND cq.status = 'pending'
      AND cq.attempt_count = 1
", [$today]);

$log('Found ' . count($calls) . ' calls to send reminder emails for');

$sent = 0;
$failed = 0;

foreach ($calls as $call) {
    // Prepare customer data
    $customer = [
        'id' => $call['customer_id'],
        'name' => $call['customer_name'],
        'email' => $call['email'],
        'phone' => $call['phone'],
    ];

    // Prepare reminder data
    $reminder = [
        'event_type' => $call['event_type'],
        'recipient_relation' => $call['recipient_relation'],
        'event_day' => $call['event_day'],
        'event_month' => $call['event_month'],
        'price_range' => $call['price_range'],
        'customer_note' => $call['customer_note'],
    ];

    // Send email
    $result = $emailService->sendEventReminderEmail($customer, $reminder);

    if ($result) {
        $sent++;
        $log("Sent reminder to: {$customer['email']}");
    } else {
        $failed++;
        $log("FAILED to send to: {$customer['email']}");
    }

    // Small delay to avoid overwhelming mail server
    usleep(100000); // 100ms
}

$log("Event email sending completed: $sent sent, $failed failed");

// =====================================================
// Připomínky prázdného účtu
// Zákazníci, kteří aktivovali účet ale nemají žádné připomínky
// =====================================================

$log('Checking for empty accounts to remind...');

$setting = new Setting();
$delayDays = (int) $setting->get('empty_reminder_delay_days', '3');
$maxCount = (int) $setting->get('empty_reminder_max_count', '2');

// Najít zákazníky s aktivovaným účtem, bez připomínek, kteří ještě nedostali max počet upozornění
$emptyAccounts = $db->fetchAll("
    SELECT
        c.id as customer_id,
        c.email,
        c.name,
        c.phone,
        c.empty_reminder_count,
        s.activated_at
    FROM customers c
    JOIN subscriptions s ON s.customer_id = c.id AND s.status = 'active'
    LEFT JOIN reminders r ON r.customer_id = c.id AND r.is_active = TRUE
    WHERE s.activated_at IS NOT NULL
      AND s.activated_at <= DATE_SUB(NOW(), INTERVAL ? DAY)
      AND c.empty_reminder_count < ?
      AND (c.empty_reminder_last_sent_at IS NULL
           OR c.empty_reminder_last_sent_at <= DATE_SUB(NOW(), INTERVAL ? DAY))
      AND c.is_active = TRUE
      AND r.id IS NULL
    GROUP BY c.id
", [$delayDays, $maxCount, $delayDays]);

$log('Found ' . count($emptyAccounts) . ' empty accounts to remind');

$emptySent = 0;
$emptyFailed = 0;

foreach ($emptyAccounts as $account) {
    $customer = [
        'id' => $account['customer_id'],
        'name' => $account['name'],
        'email' => $account['email'],
        'phone' => $account['phone'],
    ];

    $nextCount = $account['empty_reminder_count'] + 1;
    $result = $emailService->sendEmptyAccountReminderEmail($customer, $nextCount);

    if ($result) {
        // Aktualizovat počítadlo
        $db->query(
            "UPDATE customers SET empty_reminder_count = ?, empty_reminder_last_sent_at = NOW() WHERE id = ?",
            [$nextCount, $account['customer_id']]
        );
        $emptySent++;
        $log("Sent empty account reminder #{$nextCount} to: {$customer['email']}");
    } else {
        $emptyFailed++;
        $log("FAILED to send empty account reminder to: {$customer['email']}");
    }

    usleep(100000); // 100ms
}

$log("Empty account reminders completed: $emptySent sent, $emptyFailed failed");
