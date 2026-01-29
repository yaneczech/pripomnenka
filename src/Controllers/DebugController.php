<?php
/**
 * Debug Controller - pro testování a ladění
 */

declare(strict_types=1);

namespace Controllers;

use Models\Database;
use Models\Reminder;
use Models\CallQueue;
use Models\Setting;

class DebugController extends Controller
{
    /**
     * Debug připomínek - ukáže proč se nezobrazují
     */
    public function reminders(array $params): void
    {
        $db = Database::getInstance();
        $setting = new Setting();

        // Získat všechny připomínky s detaily
        $reminders = $db->fetchAll("
            SELECT
                r.id as reminder_id,
                r.event_day,
                r.event_month,
                r.advance_days,
                r.event_type,
                r.recipient_relation,
                r.is_active as reminder_active,
                c.id as customer_id,
                c.name as customer_name,
                c.phone,
                c.email,
                c.is_active as customer_active,
                s.id as subscription_id,
                s.status as subscription_status,
                s.expires_at
            FROM reminders r
            JOIN customers c ON r.customer_id = c.id
            LEFT JOIN subscriptions s ON c.id = s.customer_id AND s.status = 'active'
            ORDER BY r.event_month, r.event_day
        ");

        $defaultAdvanceDays = (int) $setting->get('default_advance_days', 5);
        $workdaysStr = $setting->get('workdays', '1,2,3,4,5');
        $workdays = array_map('intval', explode(',', $workdaysStr));

        $today = date('Y-m-d');

        // Pro každou připomínku vypočítat call_date
        $debugData = [];
        foreach ($reminders as $reminder) {
            $advanceDays = $reminder['advance_days'] ?: $defaultAdvanceDays;
            $callDate = $this->calculateCallDate(
                (int)$reminder['event_day'],
                (int)$reminder['event_month'],
                $advanceDays,
                $workdays
            );

            // Zkontrolovat call_queue
            $queueEntry = $db->fetchOne("
                SELECT * FROM call_queue
                WHERE reminder_id = ?
                ORDER BY scheduled_date DESC
                LIMIT 1
            ", [$reminder['reminder_id']]);

            // Určit důvod, proč není v call_queue
            $reason = '';
            $shouldBeInQueue = false;

            if (!$reminder['reminder_active']) {
                $reason = '❌ Připomínka je neaktivní';
            } elseif (!$reminder['customer_active']) {
                $reason = '❌ Zákazník je neaktivní';
            } elseif (!$reminder['subscription_id']) {
                $reason = '❌ Zákazník nemá předplatné';
            } elseif ($reminder['subscription_status'] !== 'active') {
                $reason = '⚠️ Předplatné není aktivní (status: ' . $reminder['subscription_status'] . ')';
            } elseif ($reminder['expires_at'] && $reminder['expires_at'] < date('Y-m-d')) {
                $reason = '⚠️ Předplatné vypršelo (' . $reminder['expires_at'] . ')';
            } elseif ($callDate < $today) {
                $reason = '⏰ Datum volání už prošlo (mělo být: ' . $callDate . ')';
            } elseif ($queueEntry) {
                $reason = '✅ V call_queue (datum: ' . $queueEntry['scheduled_date'] . ', status: ' . $queueEntry['status'] . ')';
            } else {
                $reason = '✅ Mělo by být v call_queue na ' . $callDate;
                $shouldBeInQueue = true;
            }

            $debugData[] = [
                'reminder' => $reminder,
                'call_date' => $callDate,
                'advance_days' => $advanceDays,
                'queue_entry' => $queueEntry,
                'reason' => $reason,
                'should_be_in_queue' => $shouldBeInQueue,
            ];
        }

        $this->view('admin/debug/reminders', [
            'title' => 'Debug: Připomínky',
            'reminders' => $debugData,
            'today' => $today,
            'default_advance_days' => $defaultAdvanceDays,
            'workdays' => $workdays,
        ]);
    }

    /**
     * Vypočítat datum volání (kopie z CRON)
     */
    private function calculateCallDate(int $eventDay, int $eventMonth, int $advanceDays, array $workdays): string
    {
        $year = date('Y');
        $eventDate = mktime(0, 0, 0, $eventMonth, $eventDay, $year);

        // If the event already passed this year, use next year
        if ($eventDate < time()) {
            $year++;
            $eventDate = mktime(0, 0, 0, $eventMonth, $eventDay, $year);
        }

        // Go back X workdays
        $callDate = $eventDate;
        $daysBack = 0;

        while ($daysBack < $advanceDays) {
            $callDate = strtotime('-1 day', $callDate);
            $dayOfWeek = (int) date('N', $callDate);

            if (in_array($dayOfWeek, $workdays)) {
                $daysBack++;
            }
        }

        // Also skip backwards if the calculated date lands on a non-workday
        while (!in_array((int) date('N', $callDate), $workdays)) {
            $callDate = strtotime('-1 day', $callDate);
        }

        return date('Y-m-d', $callDate);
    }
}
