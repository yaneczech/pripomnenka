<?php
/**
 * Připomněnka - Admin Model
 */

declare(strict_types=1);

namespace Models;

class Admin
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Najít admina podle ID
     */
    public function find(int $id): ?array
    {
        $admin = $this->db->fetchOne(
            "SELECT * FROM admins WHERE id = ?",
            [$id]
        );

        if ($admin) {
            unset($admin['password_hash']);
        }

        return $admin;
    }

    /**
     * Najít admina podle emailu
     */
    public function findByEmail(string $email): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM admins WHERE email = ?",
            [strtolower(trim($email))]
        );
    }

    /**
     * Ověřit přihlašovací údaje
     */
    public function authenticate(string $email, string $password): ?array
    {
        $admin = $this->findByEmail($email);

        if (!$admin) {
            return null;
        }

        if (!password_verify($password, $admin['password_hash'])) {
            return null;
        }

        unset($admin['password_hash']);
        return $admin;
    }

    /**
     * Vytvořit nového admina
     */
    public function create(string $email, string $password, string $name): int
    {
        return $this->db->insert('admins', [
            'email' => strtolower(trim($email)),
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'name' => $name,
        ]);
    }

    /**
     * Aktualizovat heslo
     */
    public function updatePassword(int $id, string $password): bool
    {
        return $this->db->update(
            'admins',
            ['password_hash' => password_hash($password, PASSWORD_DEFAULT)],
            'id = ?',
            [$id]
        ) > 0;
    }

    /**
     * Získat všechny adminy
     */
    public function getAll(): array
    {
        $admins = $this->db->fetchAll("SELECT * FROM admins ORDER BY name");

        foreach ($admins as &$admin) {
            unset($admin['password_hash']);
        }

        return $admins;
    }

    /**
     * Kontrola existence emailu
     */
    public function emailExists(string $email, int $excludeId = null): bool
    {
        $sql = "SELECT 1 FROM admins WHERE email = ?";
        $params = [strtolower(trim($email))];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        return (bool) $this->db->fetchColumn($sql, $params);
    }
}
