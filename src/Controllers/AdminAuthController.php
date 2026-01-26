<?php
/**
 * Připomněnka - AdminAuthController
 *
 * Přihlašování administrátorů
 */

declare(strict_types=1);

namespace Controllers;

use Models\Admin;

class AdminAuthController extends BaseController
{
    private Admin $admin;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->admin = new Admin();
    }

    /**
     * Zobrazit přihlašovací formulář
     */
    public function showLogin(array $params): void
    {
        if (\Session::isAdmin()) {
            $this->redirect('/admin');
        }

        $this->view('admin/auth/login', [
            'title' => 'Přihlášení do administrace',
            'errors' => $this->getErrors(),
        ], 'admin-login');
    }

    /**
     * Zpracovat přihlášení
     */
    public function login(array $params): void
    {
        $this->validateCsrf();

        $email = trim($this->input('email', ''));
        $password = $this->input('password', '');

        // Validace
        $validator = $this->validate([
            'email' => $email,
            'password' => $password,
        ]);

        $validator
            ->required('email', 'Zadejte email.')
            ->email('email', 'Neplatný formát emailu.')
            ->required('password', 'Zadejte heslo.');

        if ($validator->fails()) {
            $this->withErrors($validator->errors());
            $this->withOldInput();
            $this->redirect('/admin/prihlaseni');
        }

        // Rate limiting
        if ($this->isRateLimited($email)) {
            flash('error', 'Příliš mnoho pokusů. Zkuste to za 15 minut.');
            $this->redirect('/admin/prihlaseni');
        }

        // Autentizace
        $admin = $this->admin->authenticate($email, $password);

        if (!$admin) {
            $this->logLoginAttempt($email);
            $this->withErrors(['password' => 'Nesprávný email nebo heslo.']);
            $this->withOldInput();
            $this->redirect('/admin/prihlaseni');
        }

        // Úspěšné přihlášení
        \Session::loginAdmin($admin['id'], $admin['name']);

        flash('success', 'Vítejte, ' . $admin['name'] . '!');
        $this->redirect('/admin');
    }

    /**
     * Odhlášení
     */
    public function logout(array $params): void
    {
        $this->validateCsrf();

        \Session::logoutAdmin();
        flash('success', 'Byli jste odhlášeni.');

        $this->redirect('/admin/prihlaseni');
    }

    /**
     * Kontrola rate limiting
     */
    private function isRateLimited(string $identifier): bool
    {
        $count = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM login_attempts
             WHERE (identifier = ? OR ip_address = ?)
             AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)",
            ['admin:' . $identifier, $_SERVER['REMOTE_ADDR'] ?? '']
        );

        return $count >= $this->config['security']['max_login_attempts'];
    }

    /**
     * Zalogovat pokus o přihlášení
     */
    private function logLoginAttempt(string $identifier): void
    {
        $this->db->insert('login_attempts', [
            'identifier' => 'admin:' . $identifier,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);
    }
}
