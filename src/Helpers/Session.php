<?php
/**
 * Připomněnka - Session Helper
 *
 * Bezpečná správa sessions
 */

declare(strict_types=1);

class Session
{
    private static bool $started = false;

    /**
     * Spuštění session s bezpečným nastavením
     */
    public static function start(int $lifetime = 86400): void
    {
        if (self::$started) {
            return;
        }

        // Bezpečné nastavení session cookie
        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Strict',
        ]);

        session_name('pripomnenka_session');
        session_start();

        self::$started = true;

        // Regenerace session ID pokud je starší než 30 minut
        if (!isset($_SESSION['_last_regeneration'])) {
            $_SESSION['_last_regeneration'] = time();
        } elseif (time() - $_SESSION['_last_regeneration'] > 1800) {
            self::regenerate();
        }
    }

    /**
     * Regenerace session ID
     */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
        $_SESSION['_last_regeneration'] = time();
    }

    /**
     * Nastavení hodnoty
     */
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Získání hodnoty
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Kontrola existence klíče
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Odstranění hodnoty
     */
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Zničení celé session
     */
    public static function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        self::$started = false;
    }

    /**
     * Přihlášení zákazníka
     */
    public static function loginCustomer(int $customerId, string $name = null): void
    {
        self::regenerate();
        self::set('customer_id', $customerId);
        self::set('customer_name', $name);
        self::set('logged_in_at', time());
    }

    /**
     * Odhlášení zákazníka
     */
    public static function logoutCustomer(): void
    {
        self::remove('customer_id');
        self::remove('customer_name');
        self::remove('logged_in_at');
        self::regenerate();
    }

    /**
     * Kontrola přihlášení zákazníka
     */
    public static function isLoggedIn(): bool
    {
        return self::has('customer_id');
    }

    /**
     * Získání ID přihlášeného zákazníka
     */
    public static function getCustomerId(): ?int
    {
        return self::get('customer_id');
    }

    /**
     * Získání jména přihlášeného zákazníka
     */
    public static function getCustomerName(): ?string
    {
        return self::get('customer_name');
    }

    /**
     * Přihlášení admina
     */
    public static function loginAdmin(int $adminId, string $name): void
    {
        self::regenerate();
        self::set('admin_id', $adminId);
        self::set('admin_name', $name);
        self::set('admin_logged_in_at', time());
    }

    /**
     * Odhlášení admina
     */
    public static function logoutAdmin(): void
    {
        self::remove('admin_id');
        self::remove('admin_name');
        self::remove('admin_logged_in_at');
        self::regenerate();
    }

    /**
     * Kontrola přihlášení admina
     */
    public static function isAdmin(): bool
    {
        return self::has('admin_id');
    }

    /**
     * Získání ID přihlášeného admina
     */
    public static function getAdminId(): ?int
    {
        return self::get('admin_id');
    }

    /**
     * Získání jména přihlášeného admina
     */
    public static function getAdminName(): ?string
    {
        return self::get('admin_name');
    }
}
