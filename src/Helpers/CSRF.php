<?php
/**
 * Připomněnka - CSRF Helper
 *
 * Ochrana proti Cross-Site Request Forgery
 */

declare(strict_types=1);

class CSRF
{
    private const TOKEN_NAME = '_csrf_token';
    private const TOKEN_LIFETIME = 3600; // 1 hodina

    /**
     * Vygenerování nového CSRF tokenu
     */
    public static function generate(): string
    {
        $token = bin2hex(random_bytes(32));

        $_SESSION[self::TOKEN_NAME] = [
            'token' => $token,
            'expires' => time() + self::TOKEN_LIFETIME,
        ];

        return $token;
    }

    /**
     * Získání aktuálního tokenu (nebo vygenerování nového)
     */
    public static function token(): string
    {
        if (!isset($_SESSION[self::TOKEN_NAME]) ||
            $_SESSION[self::TOKEN_NAME]['expires'] < time()) {
            return self::generate();
        }

        return $_SESSION[self::TOKEN_NAME]['token'];
    }

    /**
     * Ověření CSRF tokenu
     */
    public static function verify(?string $token): bool
    {
        if ($token === null || !isset($_SESSION[self::TOKEN_NAME])) {
            return false;
        }

        $stored = $_SESSION[self::TOKEN_NAME];

        // Kontrola expirace
        if ($stored['expires'] < time()) {
            self::invalidate();
            return false;
        }

        // Porovnání tokenů (timing-safe)
        if (!hash_equals($stored['token'], $token)) {
            return false;
        }

        return true;
    }

    /**
     * Ověření a vygenerování nového tokenu
     */
    public static function verifyAndRefresh(?string $token): bool
    {
        $valid = self::verify($token);

        if ($valid) {
            self::generate();
        }

        return $valid;
    }

    /**
     * Zneplatnění tokenu
     */
    public static function invalidate(): void
    {
        unset($_SESSION[self::TOKEN_NAME]);
    }

    /**
     * HTML input pole s tokenem
     */
    public static function field(): string
    {
        $token = self::token();
        return '<input type="hidden" name="' . self::TOKEN_NAME . '" value="' . e($token) . '">';
    }

    /**
     * Kontrola CSRF z POST požadavku
     * Vyhodí výjimku při neplatném tokenu
     */
    public static function check(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $token = $_POST[self::TOKEN_NAME] ?? null;

        if (!self::verifyAndRefresh($token)) {
            http_response_code(403);
            throw new RuntimeException('Neplatný bezpečnostní token. Obnovte stránku a zkuste to znovu.');
        }
    }
}
