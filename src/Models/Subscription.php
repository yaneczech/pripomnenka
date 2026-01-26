<?php
/**
 * Připomněnka - Subscription Model
 */

declare(strict_types=1);

namespace Models;

class Subscription
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Najít předplatné podle ID
     */
    public function find(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT s.*, sp.name as plan_name, sp.discount_percent
             FROM subscriptions s
             JOIN subscription_plans sp ON sp.id = s.plan_id
             WHERE s.id = ?",
            [$id]
        );
    }

    /**
     * Najít aktivní předplatné zákazníka
     */
    public function findActiveByCustomer(int $customerId): ?array
    {
        return $this->db->fetchOne(
            "SELECT s.*, sp.name as plan_name, sp.discount_percent
             FROM subscriptions s
             JOIN subscription_plans sp ON sp.id = s.plan_id
             WHERE s.customer_id = ? AND s.status = 'active'
             ORDER BY s.id DESC LIMIT 1",
            [$customerId]
        );
    }

    /**
     * Najít poslední předplatné zákazníka
     */
    public function findLatestByCustomer(int $customerId): ?array
    {
        return $this->db->fetchOne(
            "SELECT s.*, sp.name as plan_name, sp.discount_percent
             FROM subscriptions s
             JOIN subscription_plans sp ON sp.id = s.plan_id
             WHERE s.customer_id = ?
             ORDER BY s.id DESC LIMIT 1",
            [$customerId]
        );
    }

    /**
     * Najít předplatné podle variabilního symbolu
     */
    public function findByVariableSymbol(string $vs): ?array
    {
        return $this->db->fetchOne(
            "SELECT s.*, sp.name as plan_name, c.email, c.phone, c.name as customer_name
             FROM subscriptions s
             JOIN subscription_plans sp ON sp.id = s.plan_id
             JOIN customers c ON c.id = s.customer_id
             WHERE s.variable_symbol = ?",
            [$vs]
        );
    }

    /**
     * Najít předplatné podle aktivačního tokenu
     */
    public function findByActivationToken(string $token): ?array
    {
        return $this->db->fetchOne(
            "SELECT s.*, sp.name as plan_name, sp.discount_percent, c.email, c.phone, c.name as customer_name
             FROM subscriptions s
             JOIN subscription_plans sp ON sp.id = s.plan_id
             JOIN customers c ON c.id = s.customer_id
             WHERE s.activation_token = ? AND s.activation_token_expires_at > NOW()",
            [$token]
        );
    }

    /**
     * Vytvořit nové předplatné
     */
    public function create(array $data): int
    {
        $vs = $this->generateVariableSymbol();

        return $this->db->insert('subscriptions', [
            'customer_id' => $data['customer_id'],
            'plan_id' => $data['plan_id'],
            'reminder_limit' => $data['reminder_limit'],
            'price' => $data['price'],
            'variable_symbol' => $vs,
            'payment_method' => $data['payment_method'],
            'payment_status' => 'pending',
            'status' => $data['payment_method'] === 'bank_transfer' ? 'awaiting_payment' : 'awaiting_activation',
        ]);
    }

    /**
     * Aktualizovat předplatné
     */
    public function update(int $id, array $data): bool
    {
        $updateData = [];
        $allowed = ['payment_status', 'payment_confirmed_at', 'payment_confirmed_by',
                    'payment_note', 'price_paid', 'starts_at', 'expires_at',
                    'activation_token', 'activation_token_expires_at', 'activated_at', 'status'];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        if (empty($updateData)) {
            return true;
        }

        return $this->db->update('subscriptions', $updateData, 'id = ?', [$id]) > 0;
    }

    /**
     * Potvrdit platbu (hotově/kartou)
     */
    public function confirmPayment(int $id, int $adminId = null, float $amount = null): bool
    {
        $subscription = $this->find($id);
        if (!$subscription) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $startsAt = date('Y-m-d');
        $expiresAt = date('Y-m-d', strtotime('+1 year'));

        // Vygenerovat aktivační token
        $token = generate_token(32);
        $tokenExpires = date('Y-m-d H:i:s', strtotime('+30 days'));

        return $this->update($id, [
            'payment_status' => 'paid',
            'payment_confirmed_at' => $now,
            'payment_confirmed_by' => $adminId,
            'price_paid' => $amount ?? $subscription['price'],
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'activation_token' => $token,
            'activation_token_expires_at' => $tokenExpires,
            'status' => 'awaiting_activation',
        ]);
    }

    /**
     * Potvrdit platbu převodem
     */
    public function confirmBankPayment(int $id, float $amount, int $adminId = null): bool
    {
        $subscription = $this->find($id);
        if (!$subscription) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $startsAt = date('Y-m-d');
        $expiresAt = date('Y-m-d', strtotime('+1 year'));

        // Kontrola částky
        $paymentStatus = abs($amount - $subscription['price']) < 0.01 ? 'paid' : 'mismatched';
        $paymentNote = null;

        if ($paymentStatus === 'mismatched') {
            $diff = $amount - $subscription['price'];
            $paymentNote = $diff > 0
                ? "Přeplatek " . number_format($diff, 2, ',', ' ') . " Kč"
                : "Nedoplatek " . number_format(abs($diff), 2, ',', ' ') . " Kč";
        }

        // Vygenerovat aktivační token
        $token = generate_token(32);
        $tokenExpires = date('Y-m-d H:i:s', strtotime('+30 days'));

        return $this->update($id, [
            'payment_status' => $paymentStatus,
            'payment_confirmed_at' => $now,
            'payment_confirmed_by' => $adminId,
            'price_paid' => $amount,
            'payment_note' => $paymentNote,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'activation_token' => $token,
            'activation_token_expires_at' => $tokenExpires,
            'status' => 'awaiting_activation',
        ]);
    }

    /**
     * Aktivovat předplatné
     */
    public function activate(int $id): bool
    {
        return $this->update($id, [
            'activated_at' => date('Y-m-d H:i:s'),
            'activation_token' => null,
            'activation_token_expires_at' => null,
            'status' => 'active',
        ]);
    }

    /**
     * Vygenerovat nový aktivační token
     */
    public function regenerateActivationToken(int $id): ?string
    {
        $token = generate_token(32);
        $tokenExpires = date('Y-m-d H:i:s', strtotime('+30 days'));

        $updated = $this->update($id, [
            'activation_token' => $token,
            'activation_token_expires_at' => $tokenExpires,
        ]);

        return $updated ? $token : null;
    }

    /**
     * Vygenerovat variabilní symbol
     * Formát: RRCCC (rok + pořadové číslo)
     */
    private function generateVariableSymbol(): string
    {
        $year = (int) date('Y');
        $yearShort = $year % 100; // 2025 -> 25

        // Atomická aktualizace čítače
        $this->db->query(
            "INSERT INTO vs_counter (year, last_number) VALUES (?, 1)
             ON DUPLICATE KEY UPDATE last_number = last_number + 1",
            [$year]
        );

        $number = (int) $this->db->fetchColumn(
            "SELECT last_number FROM vs_counter WHERE year = ?",
            [$year]
        );

        // Formát: RRCCC (např. 25001, 25002, ...)
        return sprintf('%02d%03d', $yearShort, $number);
    }

    /**
     * Získat předplatné čekající na platbu
     */
    public function getAwaitingPayment(): array
    {
        return $this->db->fetchAll(
            "SELECT s.*, sp.name as plan_name, c.name as customer_name, c.email, c.phone
             FROM subscriptions s
             JOIN subscription_plans sp ON sp.id = s.plan_id
             JOIN customers c ON c.id = s.customer_id
             WHERE s.status = 'awaiting_payment'
             ORDER BY s.created_at DESC"
        );
    }

    /**
     * Získat předplatné čekající na aktivaci
     */
    public function getAwaitingActivation(): array
    {
        return $this->db->fetchAll(
            "SELECT s.*, sp.name as plan_name, c.name as customer_name, c.email, c.phone
             FROM subscriptions s
             JOIN subscription_plans sp ON sp.id = s.plan_id
             JOIN customers c ON c.id = s.customer_id
             WHERE s.status = 'awaiting_activation'
             ORDER BY s.created_at DESC"
        );
    }

    /**
     * Získat předplatné expirující brzy
     */
    public function getExpiringWithin(int $days): array
    {
        $date = date('Y-m-d', strtotime("+{$days} days"));

        return $this->db->fetchAll(
            "SELECT s.*, sp.name as plan_name, c.name as customer_name, c.email, c.phone
             FROM subscriptions s
             JOIN subscription_plans sp ON sp.id = s.plan_id
             JOIN customers c ON c.id = s.customer_id
             WHERE s.status = 'active' AND s.expires_at <= ? AND s.expires_at >= CURDATE()
             ORDER BY s.expires_at ASC",
            [$date]
        );
    }

    /**
     * Získat vypršelá předplatné
     */
    public function getExpired(): array
    {
        return $this->db->fetchAll(
            "SELECT s.*, sp.name as plan_name, c.name as customer_name, c.email, c.phone
             FROM subscriptions s
             JOIN subscription_plans sp ON sp.id = s.plan_id
             JOIN customers c ON c.id = s.customer_id
             WHERE s.status = 'active' AND s.expires_at < CURDATE()
             ORDER BY s.expires_at DESC"
        );
    }

    /**
     * Označit vypršelá předplatné
     */
    public function markExpired(): int
    {
        return $this->db->query(
            "UPDATE subscriptions SET status = 'expired' WHERE status = 'active' AND expires_at < CURDATE()"
        )->rowCount();
    }

    /**
     * Získat limit připomínek pro zákazníka
     */
    public function getReminderLimit(int $customerId): int
    {
        $subscription = $this->findActiveByCustomer($customerId);
        return $subscription ? (int) $subscription['reminder_limit'] : 0;
    }

    /**
     * Statistiky předplatného
     */
    public function getStats(): array
    {
        return [
            'active' => (int) $this->db->fetchColumn("SELECT COUNT(*) FROM subscriptions WHERE status = 'active'"),
            'awaiting_payment' => (int) $this->db->fetchColumn("SELECT COUNT(*) FROM subscriptions WHERE status = 'awaiting_payment'"),
            'awaiting_activation' => (int) $this->db->fetchColumn("SELECT COUNT(*) FROM subscriptions WHERE status = 'awaiting_activation'"),
            'expired' => (int) $this->db->fetchColumn("SELECT COUNT(*) FROM subscriptions WHERE status = 'expired'"),
            'revenue_total' => (float) $this->db->fetchColumn("SELECT COALESCE(SUM(price_paid), 0) FROM subscriptions WHERE payment_status = 'paid'"),
            'revenue_this_month' => (float) $this->db->fetchColumn(
                "SELECT COALESCE(SUM(price_paid), 0) FROM subscriptions
                 WHERE payment_status = 'paid' AND MONTH(payment_confirmed_at) = MONTH(CURDATE()) AND YEAR(payment_confirmed_at) = YEAR(CURDATE())"
            ),
        ];
    }
}
