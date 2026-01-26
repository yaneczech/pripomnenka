<?php
/**
 * Připomněnka - Setting Model
 */

declare(strict_types=1);

namespace Models;

class Setting
{
    private Database $db;
    private static array $cache = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Získat hodnotu nastavení
     */
    public function get(string $key, mixed $default = null): mixed
    {
        // Kontrola cache
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $value = $this->db->fetchColumn(
            "SELECT setting_value FROM settings WHERE setting_key = ?",
            [$key]
        );

        if ($value === false) {
            return $default;
        }

        self::$cache[$key] = $value;
        return $value;
    }

    /**
     * Nastavit hodnotu
     */
    public function set(string $key, string $value): bool
    {
        $result = $this->db->query(
            "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
            [$key, $value]
        );

        // Invalidovat cache
        self::$cache[$key] = $value;

        return $result->rowCount() > 0;
    }

    /**
     * Získat více nastavení najednou
     */
    public function getMultiple(array $keys): array
    {
        $placeholders = implode(',', array_fill(0, count($keys), '?'));

        $rows = $this->db->fetchAll(
            "SELECT setting_key, setting_value FROM settings WHERE setting_key IN ({$placeholders})",
            $keys
        );

        $result = [];
        foreach ($rows as $row) {
            $result[$row['setting_key']] = $row['setting_value'];
            self::$cache[$row['setting_key']] = $row['setting_value'];
        }

        return $result;
    }

    /**
     * Nastavit více hodnot najednou
     */
    public function setMultiple(array $settings): bool
    {
        $this->db->beginTransaction();

        try {
            foreach ($settings as $key => $value) {
                $this->set($key, $value);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Získat všechna nastavení
     */
    public function getAll(): array
    {
        $rows = $this->db->fetchAll("SELECT setting_key, setting_value FROM settings");

        $result = [];
        foreach ($rows as $row) {
            $result[$row['setting_key']] = $row['setting_value'];
            self::$cache[$row['setting_key']] = $row['setting_value'];
        }

        return $result;
    }

    /**
     * Smazat nastavení
     */
    public function delete(string $key): bool
    {
        unset(self::$cache[$key]);
        return $this->db->delete('settings', 'setting_key = ?', [$key]) > 0;
    }

    /**
     * Vymazat cache
     */
    public function clearCache(): void
    {
        self::$cache = [];
    }

    // Pomocné metody pro konkrétní nastavení

    public function getShopPhone(): string
    {
        return $this->get('shop_phone', '');
    }

    public function getShopEmail(): string
    {
        return $this->get('shop_email', '');
    }

    public function getShopName(): string
    {
        return $this->get('shop_name', 'Jeleni v zeleni');
    }

    public function getBankAccount(): string
    {
        return $this->get('bank_account', '');
    }

    public function getBankIban(): string
    {
        return $this->get('bank_iban', '');
    }

    public function getDefaultAdvanceDays(): int
    {
        return (int) $this->get('default_advance_days', 5);
    }

    public function getWorkdays(): array
    {
        $workdays = $this->get('workdays', '1,2,3,4,5');
        return array_map('intval', explode(',', $workdays));
    }

    public function getActivationLinkValidityDays(): int
    {
        return (int) $this->get('activation_link_validity_days', 30);
    }

    public function getEmailTemplate(string $type): string
    {
        return $this->get('email_' . $type . '_template', '');
    }

    public function getEmailSubject(string $type): string
    {
        return $this->get('email_' . $type . '_subject', '');
    }
}
