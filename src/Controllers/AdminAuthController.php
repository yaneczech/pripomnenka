<?php
/**
 * Připomněnka - AdminAuthController
 *
 * Přihlašování administrátorů
 */

declare(strict_types=1);

namespace Controllers;

use Models\Admin;
use Services\EmailService;

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
     * Zobrazit formulář pro zapomenuté heslo
     */
    public function showForgotPassword(array $params): void
    {
        if (\Session::isAdmin()) {
            $this->redirect('/admin');
        }

        $this->view('admin/auth/forgot-password', [
            'title' => 'Zapomenuté heslo',
            'errors' => $this->getErrors(),
        ], 'admin-login');
    }

    /**
     * Zpracovat žádost o obnovu hesla
     */
    public function forgotPassword(array $params): void
    {
        $this->validateCsrf();

        $email = trim($this->input('email', ''));

        $validator = $this->validate(['email' => $email]);
        $validator
            ->required('email', 'Zadejte email.')
            ->email('email', 'Neplatný formát emailu.');

        if ($validator->fails()) {
            $this->withErrors($validator->errors());
            $this->withOldInput();
            $this->redirect('/admin/zapomnene-heslo');
        }

        // Rate limiting
        if ($this->isRateLimited('reset:' . $email)) {
            flash('error', 'Příliš mnoho pokusů. Zkuste to za 15 minut.');
            $this->redirect('/admin/zapomnene-heslo');
        }

        // Vždy zobrazit stejnou zprávu (ochrana proti enumeration)
        $admin = $this->admin->findByEmail($email);

        if ($admin) {
            $token = $this->admin->generatePasswordResetToken($admin['id']);

            if ($token) {
                $resetUrl = $this->config['app']['url'] . '/admin/reset-hesla/' . $token;
                $emailService = new EmailService();
                $emailService->sendAdminPasswordResetEmail($admin['email'], $admin['name'], $resetUrl);
            }
        } else {
            // Log attempt for non-existent email
            $this->logLoginAttempt('reset:' . $email);
        }

        flash('success', 'Pokud účet s tímto emailem existuje, poslali jsme vám odkaz pro obnovu hesla.');
        $this->redirect('/admin/zapomnene-heslo');
    }

    /**
     * Zobrazit formulář pro nastavení nového hesla
     */
    public function showResetPassword(array $params): void
    {
        $token = $params['token'] ?? '';

        $admin = $this->admin->findByResetToken($token);

        if (!$admin) {
            flash('error', 'Odkaz pro obnovu hesla je neplatný nebo vypršel. Požádejte o nový.');
            $this->redirect('/admin/zapomnene-heslo');
        }

        $this->view('admin/auth/reset-password', [
            'title' => 'Nové heslo',
            'token' => $token,
            'errors' => $this->getErrors(),
        ], 'admin-login');
    }

    /**
     * Zpracovat nastavení nového hesla
     */
    public function resetPassword(array $params): void
    {
        $this->validateCsrf();

        $token = $params['token'] ?? '';
        $password = $this->input('password', '');
        $passwordConfirm = $this->input('password_confirm', '');

        // Validace
        $validator = $this->validate([
            'password' => $password,
            'password_confirm' => $passwordConfirm,
        ]);

        $validator
            ->required('password', 'Zadejte nové heslo.')
            ->minLength('password', 8, 'Heslo musí mít alespoň 8 znaků.');

        if ($validator->fails()) {
            $this->withErrors($validator->errors());
            $this->redirect('/admin/reset-hesla/' . $token);
        }

        if ($password !== $passwordConfirm) {
            $this->withErrors(['password_confirm' => 'Hesla se neshodují.']);
            $this->redirect('/admin/reset-hesla/' . $token);
        }

        // Ověřit token a nastavit heslo
        $success = $this->admin->resetPasswordWithToken($token, $password);

        if (!$success) {
            flash('error', 'Odkaz pro obnovu hesla je neplatný nebo vypršel. Požádejte o nový.');
            $this->redirect('/admin/zapomnene-heslo');
        }

        flash('success', 'Heslo bylo úspěšně změněno. Můžete se přihlásit.');
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
