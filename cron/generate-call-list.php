<?php

/**
 * Generate Call List CRON Job
 *
 * Generates the daily call queue based on reminders
 * Run daily at 6:00 AM
 *
 * Usage: php cron/generate-call-list.php
 * Or via HTTP: /cron/generate-queue?token=XXX
 */

// Load bootstrap
require_once __DIR__ . '/bootstrap.php';

$log = function($message) {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
};

$log('Starting call list generation...');

$db = Database::getInstance();
$setting = new Setting();
$today = date('Y-m-d');
$todayDay = (int) date('j');
$todayMonth = (int) date('n');

// Get default advance days from settings
$defaultAdvanceDays = (int) $setting->get('default_advance_days', 5);

// Get workdays setting (1=Monday, 7=Sunday)
$workdaysStr = $setting->get('workdays', '1,2,3,4,5');
$workdays = array_map('intval', explode(',', $workdaysStr));

// WorkdayCalculator s podporou českých státních svátků
$calculator = new \Services\WorkdayCalculator($workdays);

/**
 * Calculate the date X workdays before the event
 */
function getCallDate(int $eventDay, int $eventMonth, int $advanceDays, array $workdays): ?string
{
    global $calculator;

    $year = (int) date('Y');
    $eventDate = mktime(0, 0, 0, $eventMonth, $eventDay, $year);

    // If the event already passed this year, use next year
    if ($eventDate < time()) {
        $year++;
        $eventDate = mktime(0, 0, 0, $eventMonth, $eventDay, $year);
    }

    return $calculator->subtractWorkdays(date('Y-m-d', $eventDate), $advanceDays);
}

// Get all active reminders with active subscriptions
$reminders = $db->fetchAll("
    SELECT r.*, c.phone, c.email, c.name as customer_name
    FROM reminders r
    JOIN customers c ON r.customer_id = c.id
    JOIN subscriptions s ON c.id = s.customer_id
    WHERE r.is_active = 1
      AND c.is_active = 1
      AND s.status = 'active'
      AND s.expires_at >= CURDATE()
");

$log('Found ' . count($reminders) . ' active reminders');

$added = 0;
$skipped = 0;

foreach ($reminders as $reminder) {
    $advanceDays = $reminder['advance_days'] ?: $defaultAdvanceDays;

    // Pro svátky s automatickým datem vypočítat aktuální datum
    $eventDay = $reminder['event_day'];
    $eventMonth = $reminder['event_month'];

    if (has_automatic_date($reminder['event_type'])) {
        $holidayDate = get_holiday_date($reminder['event_type']);
        if ($holidayDate) {
            $eventDay = $holidayDate['day'];
            $eventMonth = $holidayDate['month'];
        }
    }

    $callDate = getCallDate(
        $eventDay,
        $eventMonth,
        $advanceDays,
        $workdays
    );

    if (!$callDate) {
        $skipped++;
        continue;
    }

    // Only add if call date is today or in the future
    if ($callDate < $today) {
        continue;
    }

    // Check if already in queue
    $existing = $db->fetchOne("
        SELECT id FROM call_queue
        WHERE reminder_id = ? AND scheduled_date = ?
    ", [$reminder['id'], $callDate]);

    if ($existing) {
        $skipped++;
        continue;
    }

    // Add to queue
    $db->query("
        INSERT INTO call_queue (reminder_id, scheduled_date, attempt_count, priority, status)
        VALUES (?, ?, 1, 0, 'pending')
    ", [$reminder['id'], $callDate]);

    $added++;
}

$log("Added $added new items to call queue, skipped $skipped");

// Handle postponed calls - move "no_answer" from yesterday to today
$yesterday = date('Y-m-d', strtotime('-1 day'));

$noAnswerCalls = $db->fetchAll("
    SELECT cq.*, r.event_day, r.event_month
    FROM call_queue cq
    JOIN reminders r ON cq.reminder_id = r.id
    WHERE cq.scheduled_date = ?
      AND cq.status = 'no_answer'
      AND cq.attempt_count < 5
", [$yesterday]);

$moved = 0;
foreach ($noAnswerCalls as $call) {
    // Check if already has entry for today
    $todayExists = $db->fetchOne("
        SELECT id FROM call_queue
        WHERE reminder_id = ? AND scheduled_date = ?
    ", [$call['reminder_id'], $today]);

    if ($todayExists) {
        continue;
    }

    // Create new entry for today with incremented attempt count
    $db->query("
        INSERT INTO call_queue (reminder_id, scheduled_date, attempt_count, priority, status)
        VALUES (?, ?, ?, ?, 'pending')
    ", [
        $call['reminder_id'],
        $today,
        $call['attempt_count'] + 1,
        $call['attempt_count'] + 1, // Higher priority for repeat calls
    ]);

    $moved++;
}

$log("Moved $moved 'no answer' calls to today");

// Mark old entries as "gave_up" after 5 attempts
$stmt = $db->query("
    UPDATE call_queue
    SET status = 'gave_up'
    WHERE status = 'no_answer'
      AND attempt_count >= 5
      AND scheduled_date < ?
", [$today]);

$gaveUp = $stmt->rowCount();
if ($gaveUp > 0) {
    $log("Marked $gaveUp calls as 'gave up' (after 5 attempts)");
}

$log('Call list generation completed');
