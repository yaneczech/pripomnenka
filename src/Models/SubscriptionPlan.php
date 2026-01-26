<?php
/**
 * Připomněnka - SubscriptionPlan Model
 */

declare(strict_types=1);

namespace Models;

class SubscriptionPlan
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Najít plán podle ID
     */
    public function find(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM subscription_plans WHERE id = ?",
            [$id]
        );
    }

    /**
     * Najít plán podle slugu
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM subscription_plans WHERE slug = ?",
            [$slug]
        );
    }

    /**
     * Získat všechny dostupné plány
     */
    public function getAvailable(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM subscription_plans WHERE is_available = 1 ORDER BY sort_order ASC"
        );
    }

    /**
     * Ziskat vsechny plany (vcetne nedostupnych)
     */
    public function getAll(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM subscription_plans ORDER BY sort_order ASC"
        );
    }

    /**
     * Alias pro getAll
     */
    public function findAll(): array
    {
        return $this->getAll();
    }

    /**
     * Získat výchozí plán
     */
    public function getDefault(): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM subscription_plans WHERE is_default = 1 AND is_available = 1 LIMIT 1"
        );
    }

    /**
     * Vytvořit nový plán
     */
    public function create(array $data): int
    {
        return $this->db->insert('subscription_plans', [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'price' => $data['price'],
            'reminder_limit' => $data['reminder_limit'],
            'discount_percent' => $data['discount_percent'] ?? 10,
            'is_available' => $data['is_available'] ?? 1,
            'is_default' => $data['is_default'] ?? 0,
            'sort_order' => $data['sort_order'] ?? 0,
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * Aktualizovat plán
     */
    public function update(int $id, array $data): bool
    {
        $updateData = [];
        $allowed = ['name', 'slug', 'price', 'reminder_limit', 'discount_percent',
                    'is_available', 'is_default', 'sort_order', 'description'];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        if (empty($updateData)) {
            return true;
        }

        // Pokud nastavujeme tento plán jako výchozí, zrušit ostatní
        if (isset($updateData['is_default']) && $updateData['is_default']) {
            $this->db->query("UPDATE subscription_plans SET is_default = 0 WHERE id != ?", [$id]);
        }

        return $this->db->update('subscription_plans', $updateData, 'id = ?', [$id]) > 0;
    }

    /**
     * Smazat plán (pokud není použit)
     */
    public function delete(int $id): bool
    {
        // Kontrola, zda není plán použit
        $inUse = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM subscriptions WHERE plan_id = ?",
            [$id]
        );

        if ($inUse > 0) {
            return false;
        }

        return $this->db->delete('subscription_plans', 'id = ?', [$id]) > 0;
    }

    /**
     * Nastavit jako výchozí
     */
    public function setDefault(int $id): bool
    {
        $this->db->query("UPDATE subscription_plans SET is_default = 0");
        return $this->db->update('subscription_plans', ['is_default' => 1], 'id = ?', [$id]) > 0;
    }

    /**
     * Přepnout dostupnost
     */
    public function toggleAvailability(int $id): bool
    {
        return $this->db->query(
            "UPDATE subscription_plans SET is_available = NOT is_available WHERE id = ?",
            [$id]
        )->rowCount() > 0;
    }
}
