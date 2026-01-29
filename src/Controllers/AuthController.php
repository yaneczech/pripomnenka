<?php
/**
 * Připomněnka - AuthController
 *
 * Přihlašování a registrace zákazníků
 */

declare(strict_types=1);

namespace Controllers;

use Models\Customer;
use Models\OtpCode;
use Services\EmailService;

class AuthController extends BaseController
{
    private Customer $customer;
    private OtpCode $otpCode;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->customer = new Customer();
        $this->otpCode = new OtpCode();
    }

    /**
     * Zobrazit přihlašovací formulář
     */
    public function showLogin(array $params): void
    {
        if (\Session::isLoggedIn()) {
            $this->redirect('/moje-pripominky');
        }

        $this->view('auth/login', [
            'title' => 'Přihlášení',
            'step' => 'identifier', // identifier, password, otp
            'errors' => $this->getErrors(),
        ], 'public');
    }

    /**
     * Zpracovat přihlášení
     */
    public function login(array $params): void
    {
        $this->validateCsrf();

        $step = $this->input('step', 'identifier');

        switch ($step) {
            case 'identifier':
                $this->handleIdentifier();
                break;
            case 'password':
                $this->handlePassword();
                break;
            case 'otp':
                $this->handleOtp();
                break;
            default:
                $this->redirect('/prihlaseni');
        }
    }

    /**
     * Krok 1: Identifikace zákazníka
     */
    private function handleIdentifier(): void
    {
        $identifier = trim($this->input('identifier', ''));

        if (empty($identifier)) {
            flash('error', 'Zadejte telefon nebo email.');
            $this->redirect('/prihlaseni');
        }

        // Najít zákazníka
        $customer = $this->customer->findByIdentifier($identifier);

        if (!$customer) {
            // Ochrana proti enumeration - stejná zpráva
            \Session::set('login_identifier', $identifier);
            \Session::set('login_step', 'otp_fake');

            $this->view('auth/login', [
                'title' => 'Přihlášení',
                'step' => 'otp',
                'identifier' => $this->maskIdentifier($identifier),
                'errors' => [],
            ], 'public');
            return;
        }

        \Session::set('login_customer_id', $customer['id']);
        \Session::set('login_identifier', $identifier);

        // Má heslo?
        if ($this->customer->hasPassword($customer['id'])) {
            $this->view('auth/login', [
                'title' => 'Přihlášení',
                'step' => 'password',
                'identifier' => $this->maskIdentifier($identifier),
                'customerName' => $customer['name'],
                'errors' => [],
            ], 'public');
        } else {
            // Poslat OTP
            $this->sendOtp($customer);
        }
    }

    /**
     * Krok 2a: Ověření hesla
     */
    private function handlePassword(): void
    {
        $customerId = \Session::get('login_customer_id');
        $password = $this->input('password', '');

        if (!$customerId) {
            $this->redirect('/prihlaseni');
        }

        $customer = $this->customer->find($customerId);

        if (!$customer) {
            $this->redirect('/prihlaseni');
        }

        // Rate limiting
        if ($this->isRateLimited($customer['email'])) {
            flash('error', 'Příliš mnoho pokusů. Zkuste to za 15 minut.');
            $this->redirect('/prihlaseni');
        }

        if (!$this->customer->verifyPassword($customerId, $password)) {
            $this->logLoginAttempt($customer['email']);

            $this->view('auth/login', [
                'title' => 'Přihlášení',
                'step' => 'password',
                'identifier' => $this->maskIdentifier(\Session::get('login_identifier')),
                'customerName' => $customer['name'],
                'errors' => ['password' => 'Nesprávné heslo. Zkuste to znovu nebo použijte přihlášení kódem.'],
            ], 'public');
            return;
        }

        // Úspěšné přihlášení
        $this->loginSuccess($customer);
    }

    /**
     * Krok 2b: Ověření OTP
     */
    private function handleOtp(): void
    {
        $customerId = \Session::get('login_customer_id');
        $code = $this->input('code', '');

        // Fake OTP pro neexistující účty
        if (\Session::get('login_step') === 'otp_fake') {
            sleep(1); // Simulace ověření
            $this->view('auth/login', [
                'title' => 'Přihlášení',
                'step' => 'otp',
                'identifier' => $this->maskIdentifier(\Session::get('login_identifier')),
                'errors' => ['code' => 'Nesprávný kód. Zkontrolujte email a zkuste to znovu.'],
            ], 'public');
            return;
        }

        if (!$customerId) {
            $this->redirect('/prihlaseni');
        }

        $customer = $this->customer->find($customerId);

        if (!$customer) {
            $this->redirect('/prihlaseni');
        }

        // Ověřit OTP
        if (!$this->otpCode->verify($customerId, $code)) {
            $remaining = $this->otpCode->getRemainingAttempts($customerId);

            $this->view('auth/login', [
                'title' => 'Přihlášení',
                'step' => 'otp',
                'identifier' => $this->maskIdentifier(\Session::get('login_identifier')),
                'errors' => ['code' => 'Nesprávný kód. ' . ($remaining > 0 ? "Zbývá {$remaining} pokusů." : 'Vyžádejte si nový kód.')],
                'canResend' => $remaining === 0,
            ], 'public');
            return;
        }

        // Úspěšné přihlášení
        $this->loginSuccess($customer);
    }

    /**
     * Úspěšné přihlášení
     */
    private function loginSuccess(array $customer): void
    {
        \Session::loginCustomer($customer['id'], $customer['name']);
        $this->customer->updateLastLogin($customer['id']);

        // Vyčistit login session data
        \Session::remove('login_customer_id');
        \Session::remove('login_identifier');
        \Session::remove('login_step');

        flash('success', 'Vítejte' . ($customer['name'] ? ', ' . $customer['name'] : '') . '!');

        // Přesměrovat na původní stránku nebo na připomínky
        $redirect = \Session::get('redirect_after_login', '/moje-pripominky');
        \Session::remove('redirect_after_login');

        $this->redirect($redirect);
    }

    /**
     * Poslat OTP kód
     */
    private function sendOtp(array $customer): void
    {
        $code = $this->otpCode->create($customer['id']);

        // Odeslat email s kódem
        $emailService = new EmailService();
        $emailSent = $emailService->sendOtpEmail($customer, $code);

        // Pro debug režim ukázat kód přímo na stránce
        $debugOtp = null;
        if (isset($this->config['app']['debug']) && $this->config['app']['debug']) {
            $debugOtp = $code;
        }

        \Session::set('login_step', 'otp');

        $this->view('auth/login', [
            'title' => 'Přihlášení',
            'step' => 'otp',
            'identifier' => $customer['email'], // Zobrazit email (ne maskovaný)
            'customerName' => $customer['name'],
            'emailSent' => $emailSent,
            'debugOtp' => $debugOtp,
            'errors' => [],
        ], 'public');
    }

    /**
     * Znovu poslat OTP
     */
    public function resendOtp(array $params): void
    {
        $customerId = \Session::get('login_customer_id');

        if (!$customerId) {
            $this->redirect('/prihlaseni');
        }

        $customer = $this->customer->find($customerId);

        if ($customer) {
            $this->sendOtp($customer);
        } else {
            $this->redirect('/prihlaseni');
        }
    }

    /**
     * Odhlášení
     */
    public function logout(array $params): void
    {
        $this->validateCsrf();

        \Session::logoutCustomer();
        flash('success', 'Byli jste odhlášeni.');

        $this->redirect('/');
    }

    /**
     * Přepnout na OTP přihlášení
     */
    public function switchToOtp(array $params): void
    {
        $customerId = \Session::get('login_customer_id');

        if (!$customerId) {
            $this->redirect('/prihlaseni');
        }

        $customer = $this->customer->find($customerId);

        if ($customer) {
            $this->sendOtp($customer);
        } else {
            $this->redirect('/prihlaseni');
        }
    }

    /**
     * Maskovat identifikátor (email/telefon)
     */
    private function maskIdentifier(string $identifier): string
    {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            [$local, $domain] = explode('@', $identifier);
            $masked = substr($local, 0, 2) . str_repeat('*', max(0, strlen($local) - 2));
            return $masked . '@' . $domain;
        }

        // Telefon
        $phone = preg_replace('/[^\d+]/', '', $identifier);
        if (strlen($phone) > 4) {
            return substr($phone, 0, 4) . str_repeat('*', strlen($phone) - 7) . substr($phone, -3);
        }

        return $identifier;
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
            [$identifier, $_SERVER['REMOTE_ADDR'] ?? '']
        );

        return $count >= $this->config['security']['max_login_attempts'];
    }

    /**
     * Zalogovat pokus o přihlášení
     */
    private function logLoginAttempt(string $identifier): void
    {
        $this->db->insert('login_attempts', [
            'identifier' => $identifier,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);
    }

    /**
     * Zobrazit registrační formulář (pro zákazníky bez aktivačního odkazu)
     */
    public function showRegister(array $params): void
    {
        // Registrace je pouze přes aktivační odkaz z admina
        flash('info', 'Pro registraci kontaktujte květinářství Jeleni v zeleni.');
        $this->redirect('/');
    }
}
