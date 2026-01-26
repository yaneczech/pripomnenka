<?php
/**
 * Připomněnka - Reminder Model
 */

declare(strict_types=1);

namespace Models;

class Reminder
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Najít připomínku podle ID
     */
    public function find(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM reminders WHERE id = ?",
            [$id]
        );
    }

    /**
     * Najít připomínku s detaily zákazníka
     */
    public function findWithCustomer(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT r.*, c.name as customer_name, c.phone, c.email
             FROM reminders r
             JOIN customers c ON c.id = r.customer_id
             WHERE r.id = ?",
            [$id]
        );
    }

    /**
     * Získat připomínky zákazníka
     */
    public function getByCustomer(int $customerId, bool $activeOnly = true): array
    {
        $where = $activeOnly ? 'AND r.is_active = 1' : '';

        return $this->db->fetchAll(
            "SELECT r.*
             FROM reminders r
             WHERE r.customer_id = ? {$where}
             ORDER BY r.event_month ASC, r.event_day ASC",
            [$customerId]
        );
    }

    /**
     * Získat připomínky zákazníka seřazené podle blízkosti
     */
    public function getByCustomerSorted(int $customerId): array
    {
        $reminders = $this->getByCustomer($customerId);

        // Seřadit podle počtu dní do události
        usort($reminders, function ($a, $b) {
            $daysA = days_until($a['event_day'], $a['event_month']);
            $daysB = days_until($b['event_day'], $b['event_month']);
            return $daysA <=> $daysB;
        });

        return $reminders;
    }

    /**
     * Počet připomínek zákazníka
     */
    public function countByCustomer(int $customerId): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM reminders WHERE customer_id = ? AND is_active = 1",
            [$customerId]
        );
    }

    /**
     * Vytvořit novou připomínku
     */
    public function create(array $data): int
    {
        return $this->db->insert('reminders', [
            'customer_id' => $data['customer_id'],
            'event_type' => $data['event_type'],
            'recipient_relation' => $data['recipient_relation'],
            'event_day' => $data['event_day'],
            'event_month' => $data['event_month'],
            'advance_days' => $data['advance_days'] ?? 5,
            'price_range' => $data['price_range'] ?? 'to_discuss',
            'customer_note' => $data['customer_note'] ?? null,
            'is_active' => 1,
        ]);
    }

    /**
     * Aktualizovat připomínku
     */
    public function update(int $id, array $data): bool
    {
        $updateData = [];
        $allowed = ['event_type', 'recipient_relation', 'event_day', 'event_month',
                    'advance_days', 'price_range', 'customer_note', 'is_active'];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        if (empty($updateData)) {
            return true;
        }

        return $this->db->update('reminders', $updateData, 'id = ?', [$id]) > 0;
    }

    /**
     * Smazat připomínku
     */
    public function delete(int $id): bool
    {
        return $this->db->delete('reminders', 'id = ?', [$id]) > 0;
    }

    /**
     * Deaktivovat připomínku (soft delete)
     */
    public function deactivate(int $id): bool
    {
        return $this->update($id, ['is_active' => 0]);
    }

    /**
     * Ověřit vlastnictví připomínky
     */
    public function belongsToCustomer(int $id, int $customerId): bool
    {
        return $this->db->exists('reminders', 'id = ? AND customer_id = ?', [$id, $customerId]);
    }

    /**
     * Získat připomínky k provolání na daný den
     */
    public function getForCallDate(\DateTime $date): array
    {
        $dateStr = $date->format('Y-m-d');

        return $this->db->fetchAll(
            "SELECT r.*, c.name as customer_name, c.phone, c.email,
                    cn.preferred_flowers, cn.typical_budget, cn.preferred_call_time, cn.general_note as internal_note,
                    cq.attempt_count, cq.id as queue_id,
                    (SELECT MAX(order_amount) FROM call_logs WHERE reminder_id = r.id AND status = 'completed') as last_order_amount,
                    (SELECT MAX(call_date) FROM call_logs WHERE reminder_id = r.id AND status = 'completed') as last_order_date
             FROM call_queue cq
             JOIN reminders r ON r.id = cq.reminder_id
             JOIN customers c ON c.id = r.customer_id
             LEFT JOIN customer_notes cn ON cn.customer_id = c.id
             WHERE cq.scheduled_date = ? AND cq.status = 'pending'
             ORDER BY cq.priority DESC, cq.attempt_count ASC",
            [$dateStr]
        );
    }

    /**
     * Získat připomínky pro generování fronty k provolání
     * Vrátí připomínky, které mají být provolány v daný den (podle advance_days)
     */
    public function getForQueueGeneration(\DateTime $eventDate): array
    {
        $day = (int) $eventDate->format('j');
        $month = (int) $eventDate->format('n');

        return $this->db->fetchAll(
            "SELECT r.*, c.id as customer_id
             FROM reminders r
             JOIN customers c ON c.id = r.customer_id
             JOIN subscriptions s ON s.customer_id = c.id AND s.status = 'active'
             WHERE r.event_day = ? AND r.event_month = ? AND r.is_active = 1",
            [$day, $month]
        );
    }

    /**
     * Získat dostupné typy událostí
     */
    public static function getEventTypes(): array
    {
        return [
            'birthday' => 'Narozeniny',
            'nameday' => 'Svátek',
            'wedding_anniversary' => 'Výročí svatby',
            'relationship_anniversary' => 'Výročí vztahu',
            'mothers_day' => 'Den matek',
            'fathers_day' => 'Den otců',
            'valentines' => 'Valentýn',
            'other' => 'Jiné',
        ];
    }

    /**
     * Získat dostupné vztahy
     */
    public static function getRelations(): array
    {
        return [
            'wife' => 'Manželka',
            'husband' => 'Manžel',
            'mother' => 'Matka',
            'father' => 'Otec',
            'daughter' => 'Dcera',
            'son' => 'Syn',
            'grandmother' => 'Babička',
            'grandfather' => 'Dědeček',
            'sister' => 'Sestra',
            'brother' => 'Bratr',
            'mother_in_law' => 'Tchyně',
            'father_in_law' => 'Tchán',
            'partner' => 'Partner/ka',
            'friend' => 'Kamarád/ka',
            'colleague' => 'Kolega/yně',
            'other' => 'Jiné',
        ];
    }

    /**
     * Získat dostupné cenové rozsahy
     */
    public static function getPriceRanges(): array
    {
        return [
            'under_500' => 'Do 500 Kč',
            '500_800' => '500–800 Kč',
            '800_1200' => '800–1200 Kč',
            '1200_2000' => '1200–2000 Kč',
            'over_2000' => 'Nad 2000 Kč',
            'to_discuss' => 'Poradíme při hovoru',
        ];
    }

    /**
     * Získat dostupné předstihy
     */
    public static function getAdvanceDays(): array
    {
        return [
            3 => '3 pracovní dny',
            5 => '5 pracovních dnů',
            7 => '7 pracovních dnů',
            10 => '10 pracovních dnů',
            14 => '14 pracovních dnů',
        ];
    }
}
