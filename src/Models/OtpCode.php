<?php
/**
 * Připomněnka - OtpCode Model
 */

declare(strict_types=1);

namespace Models;

class OtpCode
{
    private Database $db;
    private int $lifetime;
    private int $maxAttempts;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $config = require ROOT_PATH . '/config/config.php';
        $this->lifetime = $config['security']['otp_lifetime'] ?? 600;
        $this->maxAttempts = $config['security']['otp_max_attempts'] ?? 3;
    }

    /**
     * Vytvořit nový OTP kód pro zákazníka
     */
    public function create(int $customerId): string
    {
        // Zneplatnit předchozí kódy
        $this->invalidateForCustomer($customerId);

        // Vygenerovat nový kód
        $code = generate_otp();
        $expiresAt = date('Y-m-d H:i:s', time() + $this->lifetime);

        $this->db->insert('otp_codes', [
            'customer_id' => $customerId,
            'code' => $code,
            'expires_at' => $expiresAt,
            'attempts' => 0,
        ]);

        return $code;
    }

    /**
     * Ověřit OTP kód
     */
    public function verify(int $customerId, string $code): bool
    {
        $otpRecord = $this->db->fetchOne(
            "SELECT * FROM otp_codes
             WHERE customer_id = ? AND used_at IS NULL AND expires_at > NOW()
             ORDER BY id DESC LIMIT 1",
            [$customerId]
        );

        if (!$otpRecord) {
            return false;
        }

        // Kontrola počtu pokusů
        if ($otpRecord['attempts'] >= $this->maxAttempts) {
            return false;
        }

        // Inkrementovat počet pokusů
        $this->db->query(
            "UPDATE otp_codes SET attempts = attempts + 1 WHERE id = ?",
            [$otpRecord['id']]
        );

        // Ověření kódu (timing-safe)
        if (!hash_equals($otpRecord['code'], $code)) {
            return false;
        }

        // Označit jako použitý
        $this->db->query(
            "UPDATE otp_codes SET used_at = NOW() WHERE id = ?",
            [$otpRecord['id']]
        );

        return true;
    }

    /**
     * Zneplatnit všechny OTP kódy zákazníka
     */
    public function invalidateForCustomer(int $customerId): void
    {
        $this->db->query(
            "UPDATE otp_codes SET used_at = NOW() WHERE customer_id = ? AND used_at IS NULL",
            [$customerId]
        );
    }

    /**
     * Zkontrolovat, zda má zákazník platný OTP kód
     */
    public function hasValidCode(int $customerId): bool
    {
        return (bool) $this->db->fetchColumn(
            "SELECT 1 FROM otp_codes
             WHERE customer_id = ? AND used_at IS NULL AND expires_at > NOW() AND attempts < ?
             LIMIT 1",
            [$customerId, $this->maxAttempts]
        );
    }

    /**
     * Získat zbývající pokusy
     */
    public function getRemainingAttempts(int $customerId): int
    {
        $attempts = $this->db->fetchColumn(
            "SELECT attempts FROM otp_codes
             WHERE customer_id = ? AND used_at IS NULL AND expires_at > NOW()
             ORDER BY id DESC LIMIT 1",
            [$customerId]
        );

        if ($attempts === false) {
            return 0;
        }

        return max(0, $this->maxAttempts - (int) $attempts);
    }

    /**
     * Vyčistit staré OTP kódy
     */
    public function cleanup(): int
    {
        return $this->db->query(
            "DELETE FROM otp_codes WHERE expires_at < DATE_SUB(NOW(), INTERVAL 1 DAY)"
        )->rowCount();
    }
}
