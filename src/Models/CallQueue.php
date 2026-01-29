<?php
/**
 * Připomněnka - CallQueue Model
 *
 * Správa fronty k provolání
 */

declare(strict_types=1);

namespace Models;

use Models\Setting;

class CallQueue
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Přegenerovat call queue pro konkrétní připomínku
     * Volá se při vytvoření/úpravě připomínky zákazníkem
     */
    public function regenerateForReminder(int $reminderId): void
    {
        // Načíst připomínku s informací o zákazníkovi
        $reminder = $this->db->fetchOne(
            "SELECT r.*, c.id as customer_id, c.is_active as customer_is_active
             FROM reminders r
             JOIN customers c ON r.customer_id = c.id
             WHERE r.id = ?",
            [$reminderId]
        );

        if (!$reminder || !$reminder['is_active'] || !$reminder['customer_is_active']) {
            return;
        }

        // Zkontrolovat, že zákazník má aktivní předplatné
        $hasActiveSubscription = $this->db->fetchOne(
            "SELECT id FROM subscriptions
             WHERE customer_id = ?
               AND status = 'active'
               AND expires_at >= CURDATE()",
            [$reminder['customer_id']]
        );

        if (!$hasActiveSubscription) {
            return;
        }

        // Nejprve smazat všechny budoucí/nevyřízené záznamy pro tuto připomínku
        $this->db->query(
            "DELETE FROM call_queue
             WHERE reminder_id = ?
               AND status IN ('pending', 'no_answer')
               AND scheduled_date >= CURDATE()",
            [$reminderId]
        );

        // Vypočítat nové call_date
        $callDate = $this->calculateCallDate($reminder);

        if (!$callDate) {
            return;
        }

        $today = date('Y-m-d');

        // Přidat do call_queue pouze pokud call_date je dnes nebo v budoucnu
        if ($callDate >= $today) {
            $this->db->query(
                "INSERT INTO call_queue (reminder_id, scheduled_date, attempt_count, priority, status)
                 VALUES (?, ?, 1, 0, 'pending')",
                [$reminderId, $callDate]
            );
        }
    }

    /**
     * Vypočítat datum volání na základě připomínky
     */
    private function calculateCallDate(array $reminder): ?string
    {
        $year = (int) date('Y');

        // Pro svátky s automatickým datem použít vypočítané datum
        $eventDay = $reminder['event_day'];
        $eventMonth = $reminder['event_month'];

        if (has_automatic_date($reminder['event_type'])) {
            $holidayDate = get_holiday_date($reminder['event_type'], $year);
            if ($holidayDate) {
                $eventDay = $holidayDate['day'];
                $eventMonth = $holidayDate['month'];
            }
        }

        $eventDate = mktime(0, 0, 0, $eventMonth, $eventDay, $year);

        // Pokud událost už letos proběhla, použít příští rok
        if ($eventDate < time()) {
            $year++;

            // Pro pohyblivé svátky přepočítat datum pro nový rok
            if (has_automatic_date($reminder['event_type'])) {
                $holidayDate = get_holiday_date($reminder['event_type'], $year);
                if ($holidayDate) {
                    $eventDay = $holidayDate['day'];
                    $eventMonth = $holidayDate['month'];
                }
            }

            $eventDate = mktime(0, 0, 0, $eventMonth, $eventDay, $year);
        }

        // Načíst nastavení pracovních dní
        $workdaysStr = Setting::get('workdays', '1,2,3,4,5');
        $workdays = array_map('intval', explode(',', $workdaysStr));

        $advanceDays = $reminder['advance_days'] ?: (int) Setting::get('default_advance_days', 5);

        // Jít zpět X pracovních dní
        $callDate = $eventDate;
        $daysBack = 0;

        while ($daysBack < $advanceDays) {
            $callDate = strtotime('-1 day', $callDate);
            $dayOfWeek = (int) date('N', $callDate); // 1=Monday, 7=Sunday

            if (in_array($dayOfWeek, $workdays)) {
                $daysBack++;
            }
        }

        // Pokud vypočtené datum padne na víkend/nepracovní den, posunout zpět
        while (!in_array((int) date('N', $callDate), $workdays)) {
            $callDate = strtotime('-1 day', $callDate);
        }

        return date('Y-m-d', $callDate);
    }

    /**
     * Smazat call queue záznamy pro připomínku
     * Volá se při smazání připomínky
     */
    public function deleteForReminder(int $reminderId): void
    {
        $this->db->query(
            "DELETE FROM call_queue WHERE reminder_id = ?",
            [$reminderId]
        );
    }
}
