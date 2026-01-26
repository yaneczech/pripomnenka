<?php
/**
 * Připomněnka - Customer Model
 */

declare(strict_types=1);

namespace Models;

class Customer
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Najít zákazníka podle ID
     */
    public function find(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM customers WHERE id = ?",
            [$id]
        );
    }

    /**
     * Najít zákazníka podle emailu
     */
    public function findByEmail(string $email): ?array
    {
        $hash = create_search_hash($email);
        return $this->db->fetchOne(
            "SELECT * FROM customers WHERE email_hash = ?",
            [$hash]
        );
    }

    /**
     * Najít zákazníka podle telefonu
     */
    public function findByPhone(string $phone): ?array
    {
        $hash = create_search_hash(format_phone($phone));
        return $this->db->fetchOne(
            "SELECT * FROM customers WHERE phone_hash = ?",
            [$hash]
        );
    }

    /**
     * Najít zákazníka podle emailu nebo telefonu
     */
    public function findByIdentifier(string $identifier): ?array
    {
        // Zkusit nejdřív jako email
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return $this->findByEmail($identifier);
        }

        // Jinak jako telefon
        return $this->findByPhone($identifier);
    }

    /**
     * Vytvořit nového zákazníka
     */
    public function create(array $data): int
    {
        $phone = format_phone($data['phone']);
        $email = strtolower(trim($data['email']));

        return $this->db->insert('customers', [
            'phone' => $phone,
            'phone_hash' => create_search_hash($phone),
            'email' => $email,
            'email_hash' => create_search_hash($email),
            'name' => $data['name'] ?? null,
            'password_hash' => isset($data['password']) ? password_hash($data['password'], PASSWORD_DEFAULT) : null,
            'gdpr_consent_at' => $data['gdpr_consent_at'] ?? null,
            'gdpr_consent_text' => $data['gdpr_consent_text'] ?? null,
        ]);
    }

    /**
     * Aktualizovat zákazníka
     */
    public function update(int $id, array $data): bool
    {
        $updateData = [];

        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }

        if (isset($data['email'])) {
            $email = strtolower(trim($data['email']));
            $updateData['email'] = $email;
            $updateData['email_hash'] = create_search_hash($email);
        }

        if (isset($data['phone'])) {
            $phone = format_phone($data['phone']);
            $updateData['phone'] = $phone;
            $updateData['phone_hash'] = create_search_hash($phone);
        }

        if (isset($data['password'])) {
            $updateData['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (isset($data['gdpr_consent_at'])) {
            $updateData['gdpr_consent_at'] = $data['gdpr_consent_at'];
            $updateData['gdpr_consent_text'] = $data['gdpr_consent_text'] ?? null;
        }

        if (isset($data['last_login_at'])) {
            $updateData['last_login_at'] = $data['last_login_at'];
        }

        if (empty($updateData)) {
            return true;
        }

        return $this->db->update('customers', $updateData, 'id = ?', [$id]) > 0;
    }

    /**
     * Smazat zákazníka
     */
    public function delete(int $id): bool
    {
        return $this->db->delete('customers', 'id = ?', [$id]) > 0;
    }

    /**
     * Ověřit heslo
     */
    public function verifyPassword(int $id, string $password): bool
    {
        $customer = $this->find($id);

        if (!$customer || !$customer['password_hash']) {
            return false;
        }

        return password_verify($password, $customer['password_hash']);
    }

    /**
     * Má zákazník nastavené heslo?
     */
    public function hasPassword(int $id): bool
    {
        $customer = $this->find($id);
        return $customer && !empty($customer['password_hash']);
    }

    /**
     * Aktualizovat čas posledního přihlášení
     */
    public function updateLastLogin(int $id): void
    {
        $this->update($id, ['last_login_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Vyhledat zákazníky
     */
    public function search(string $query, int $limit = 50): array
    {
        $query = '%' . $query . '%';

        return $this->db->fetchAll(
            "SELECT c.*, s.status as subscription_status, s.expires_at
             FROM customers c
             LEFT JOIN subscriptions s ON s.customer_id = c.id
                AND s.id = (SELECT MAX(id) FROM subscriptions WHERE customer_id = c.id)
             WHERE c.name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?
             ORDER BY c.created_at DESC
             LIMIT ?",
            [$query, $query, $query, $limit]
        );
    }

    /**
     * Získat všechny zákazníky s filtrováním
     */
    public function getAll(string $filter = 'all', int $limit = 100, int $offset = 0): array
    {
        $where = '1=1';
        $params = [];

        switch ($filter) {
            case 'active':
                $where = "EXISTS (SELECT 1 FROM subscriptions s WHERE s.customer_id = c.id AND s.status = 'active')";
                break;
            case 'awaiting_activation':
                $where = "EXISTS (SELECT 1 FROM subscriptions s WHERE s.customer_id = c.id AND s.status = 'awaiting_activation')";
                break;
            case 'awaiting_payment':
                $where = "EXISTS (SELECT 1 FROM subscriptions s WHERE s.customer_id = c.id AND s.status = 'awaiting_payment')";
                break;
            case 'expired':
                $where = "EXISTS (SELECT 1 FROM subscriptions s WHERE s.customer_id = c.id AND s.status = 'expired')";
                break;
        }

        return $this->db->fetchAll(
            "SELECT c.*,
                    (SELECT status FROM subscriptions WHERE customer_id = c.id ORDER BY id DESC LIMIT 1) as subscription_status,
                    (SELECT expires_at FROM subscriptions WHERE customer_id = c.id ORDER BY id DESC LIMIT 1) as subscription_expires_at
             FROM customers c
             WHERE {$where}
             ORDER BY c.created_at DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$limit, $offset])
        );
    }

    /**
     * Počet zákazníků podle filtru
     */
    public function count(string $filter = 'all'): int
    {
        $where = '1=1';

        switch ($filter) {
            case 'active':
                $where = "EXISTS (SELECT 1 FROM subscriptions s WHERE s.customer_id = c.id AND s.status = 'active')";
                break;
            case 'awaiting_activation':
                $where = "EXISTS (SELECT 1 FROM subscriptions s WHERE s.customer_id = c.id AND s.status = 'awaiting_activation')";
                break;
            case 'awaiting_payment':
                $where = "EXISTS (SELECT 1 FROM subscriptions s WHERE s.customer_id = c.id AND s.status = 'awaiting_payment')";
                break;
            case 'expired':
                $where = "EXISTS (SELECT 1 FROM subscriptions s WHERE s.customer_id = c.id AND s.status = 'expired')";
                break;
        }

        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM customers c WHERE {$where}"
        );
    }

    /**
     * Kontrola existence emailu nebo telefonu
     */
    public function exists(?string $email = null, ?string $phone = null, ?int $excludeId = null): bool
    {
        if ($email) {
            $hash = create_search_hash($email);
            $sql = "SELECT 1 FROM customers WHERE email_hash = ?";
            $params = [$hash];

            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }

            if ($this->db->fetchColumn($sql, $params)) {
                return true;
            }
        }

        if ($phone) {
            $hash = create_search_hash(format_phone($phone));
            $sql = "SELECT 1 FROM customers WHERE phone_hash = ?";
            $params = [$hash];

            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }

            if ($this->db->fetchColumn($sql, $params)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Export dat zákazníka (GDPR)
     */
    public function exportData(int $id): array
    {
        $customer = $this->find($id);

        if (!$customer) {
            return [];
        }

        // Odstranit citlivé údaje
        unset($customer['password_hash'], $customer['phone_hash'], $customer['email_hash']);

        // Přidat připomínky
        $reminders = $this->db->fetchAll(
            "SELECT event_type, recipient_relation, event_day, event_month, advance_days, price_range, customer_note, created_at
             FROM reminders WHERE customer_id = ?",
            [$id]
        );

        // Přidat předplatné
        $subscriptions = $this->db->fetchAll(
            "SELECT sp.name as plan_name, s.price, s.starts_at, s.expires_at, s.status, s.created_at
             FROM subscriptions s
             JOIN subscription_plans sp ON sp.id = s.plan_id
             WHERE s.customer_id = ?",
            [$id]
        );

        return [
            'customer' => $customer,
            'reminders' => $reminders,
            'subscriptions' => $subscriptions,
            'exported_at' => date('Y-m-d H:i:s'),
        ];
    }
}
