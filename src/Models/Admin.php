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
     * Vygenerovat token pro obnovu hesla
     */
    public function generatePasswordResetToken(int $id): ?string
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $updated = $this->db->update(
            'admins',
            [
                'password_reset_token' => hash('sha256', $token),
                'password_reset_expires_at' => $expiresAt,
            ],
            'id = ?',
            [$id]
        );

        return $updated > 0 ? $token : null;
    }

    /**
     * Najít admina podle reset tokenu (platného)
     */
    public function findByResetToken(string $token): ?array
    {
        $tokenHash = hash('sha256', $token);

        $admin = $this->db->fetchOne(
            "SELECT * FROM admins
             WHERE password_reset_token = ?
             AND password_reset_expires_at > NOW()",
            [$tokenHash]
        );

        if ($admin) {
            unset($admin['password_hash']);
        }

        return $admin;
    }

    /**
     * Resetovat heslo pomocí tokenu
     */
    public function resetPasswordWithToken(string $token, string $newPassword): bool
    {
        $admin = $this->findByResetToken($token);

        if (!$admin) {
            return false;
        }

        $this->db->update(
            'admins',
            [
                'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                'password_reset_token' => null,
                'password_reset_expires_at' => null,
            ],
            'id = ?',
            [$admin['id']]
        );

        return true;
    }

    /**
     * Vymazat reset token
     */
    public function clearResetToken(int $id): void
    {
        $this->db->update(
            'admins',
            [
                'password_reset_token' => null,
                'password_reset_expires_at' => null,
            ],
            'id = ?',
            [$id]
        );
    }

    /**
     * Kontrola existence emailu
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
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
