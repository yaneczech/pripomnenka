<?php
/**
 * Připomněnka - Base Controller
 *
 * Základní třída pro všechny controllery
 */

declare(strict_types=1);

namespace Controllers;

use Models\Database;

abstract class BaseController
{
    protected array $config;
    protected Database $db;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->db = Database::getInstance($config['db']);
    }

    /**
     * Render view s layoutem
     */
    protected function view(string $name, array $data = [], string $layout = 'public'): void
    {
        // Přidat společná data
        $data['config'] = $this->config;
        $data['flash'] = get_flash();

        view($name, $data, $layout);
    }

    /**
     * Render view bez layoutu
     */
    protected function partial(string $name, array $data = []): void
    {
        view($name, $data);
    }

    /**
     * JSON odpověď
     */
    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Přesměrování
     */
    protected function redirect(string $url, int $code = 302): never
    {
        redirect($url, $code);
    }

    /**
     * Přesměrování zpět (referer)
     */
    protected function back(): never
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        redirect($referer);
    }

    /**
     * Získání POST dat
     */
    protected function input(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $_POST;
        }

        return $_POST[$key] ?? $default;
    }

    /**
     * Získání GET parametru
     */
    protected function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $_GET;
        }

        return $_GET[$key] ?? $default;
    }

    /**
     * Kontrola CSRF tokenu
     */
    protected function validateCsrf(): void
    {
        require_once ROOT_PATH . '/src/Helpers/CSRF.php';
        \CSRF::check();
    }

    /**
     * Validace vstupních dat
     */
    protected function validate(array $data): \Validator
    {
        require_once ROOT_PATH . '/src/Helpers/Validator.php';
        return \Validator::make($data);
    }

    /**
     * Uložení chyb validace do session
     */
    protected function withErrors(array $errors): void
    {
        $_SESSION['validation_errors'] = $errors;
    }

    /**
     * Získání chyb validace
     */
    protected function getErrors(): array
    {
        $errors = $_SESSION['validation_errors'] ?? [];
        unset($_SESSION['validation_errors']);
        return $errors;
    }

    /**
     * Uložení starých vstupů
     */
    protected function withOldInput(): void
    {
        set_old_input($_POST);
    }

    /**
     * Kontrola, zda je požadavek AJAX
     */
    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * 404 odpověď
     */
    protected function notFound(): never
    {
        http_response_code(404);
        require ROOT_PATH . '/src/Views/errors/404.php';
        exit;
    }

    /**
     * 403 odpověď
     */
    protected function forbidden(): never
    {
        http_response_code(403);
        require ROOT_PATH . '/src/Views/errors/403.php';
        exit;
    }
}
