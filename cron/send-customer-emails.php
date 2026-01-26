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

$log("Email sending completed: $sent sent, $failed failed");
