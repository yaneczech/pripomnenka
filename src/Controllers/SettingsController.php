<?php
/**
 * Připomněnka - SettingsController
 *
 * Správa nastavení systému
 */

declare(strict_types=1);

namespace Controllers;

use Models\Setting;
use Models\SubscriptionPlan;
use Services\EmailService;

class SettingsController extends BaseController
{
    private Setting $setting;
    private SubscriptionPlan $plan;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->setting = new Setting();
        $this->plan = new SubscriptionPlan();
    }

    /**
     * Zobrazit nastavení
     */
    public function index(array $params): void
    {
        $settings = $this->setting->getAll();
        $plans = $this->plan->findAll();

        $this->view('admin/settings/index', [
            'title' => 'Nastavení',
            'settings' => $settings,
            'plans' => $plans,
        ], 'admin');
    }

    /**
     * Uložit nastavení
     */
    public function update(array $params): void
    {
        $this->validateCsrf();

        $settings = [
            'shop_phone' => trim($this->input('shop_phone', '')),
            'shop_email' => trim($this->input('shop_email', '')),
            'bank_account' => trim($this->input('bank_account', '')),
            'bank_iban' => trim($this->input('bank_iban', '')),
            'default_advance_days' => (int) $this->input('default_advance_days', 5),
            'activation_link_validity_days' => (int) $this->input('activation_link_validity_days', 30),
        ];

        // Emailové šablony
        $emailSettings = [
            'email_activation_subject',
            'email_payment_qr_subject',
            'email_customer_reminder_subject',
            'email_customer_reminder_template',
            'email_expiration_subject',
        ];

        foreach ($emailSettings as $key) {
            if (isset($_POST[$key])) {
                $settings[$key] = trim($_POST[$key]);
            }
        }

        // IMAP nastavení (pokud vyplněno)
        if ($this->input('bank_imap_email')) {
            $settings['bank_imap_host'] = trim($this->input('bank_imap_host', ''));
            $settings['bank_imap_email'] = trim($this->input('bank_imap_email', ''));

            // Heslo pouze pokud bylo změněno
            $password = $this->input('bank_imap_password', '');
            if (!empty($password)) {
                $settings['bank_imap_password'] = $password; // TODO: Šifrovat
            }
        }

        // Uložit nastavení
        foreach ($settings as $key => $value) {
            $this->setting->set($key, (string) $value);
        }

        flash('success', 'Nastavení bylo uloženo.');
        $this->redirect('/admin/nastaveni');
    }

    /**
     * Správa tarifů předplatného
     */
    public function plans(array $params): void
    {
        $plans = $this->plan->findAll();

        $this->view('admin/settings/plans', [
            'title' => 'Tarify předplatného',
            'plans' => $plans,
        ], 'admin');
    }

    /**
     * Aktualizace tarifů
     */
    public function updatePlans(array $params): void
    {
        $this->validateCsrf();

        $action = $this->input('action', '');

        switch ($action) {
            case 'add':
                $this->addPlan();
                break;

            case 'update':
                $this->updatePlan();
                break;

            case 'toggle':
                $this->togglePlan();
                break;

            case 'set_default':
                $this->setDefaultPlan();
                break;
        }

        $this->redirect('/admin/nastaveni/plany');
    }

    /**
     * Přidat nový tarif
     */
    private function addPlan(): void
    {
        $name = trim($this->input('name', ''));
        $slug = $this->createSlug($name);
        $price = (float) $this->input('price', 0);
        $reminderLimit = (int) $this->input('reminder_limit', 5);
        $discountPercent = (int) $this->input('discount_percent', 10);
        $description = trim($this->input('description', ''));

        if (empty($name) || $price <= 0) {
            flash('error', 'Vyplňte název a cenu tarifu.');
            return;
        }

        $this->plan->create([
            'name' => $name,
            'slug' => $slug,
            'price' => $price,
            'reminder_limit' => $reminderLimit,
            'discount_percent' => $discountPercent,
            'description' => $description,
            'is_available' => true,
            'is_default' => false,
            'sort_order' => 99,
        ]);

        flash('success', 'Tarif byl přidán.');
    }

    /**
     * Aktualizovat tarif
     */
    private function updatePlan(): void
    {
        $id = (int) $this->input('plan_id', 0);
        $name = trim($this->input('name', ''));
        $price = (float) $this->input('price', 0);
        $reminderLimit = (int) $this->input('reminder_limit', 5);
        $discountPercent = (int) $this->input('discount_percent', 10);
        $description = trim($this->input('description', ''));

        if ($id <= 0 || empty($name) || $price <= 0) {
            flash('error', 'Neplatná data.');
            return;
        }

        $this->plan->update($id, [
            'name' => $name,
            'price' => $price,
            'reminder_limit' => $reminderLimit,
            'discount_percent' => $discountPercent,
            'description' => $description,
        ]);

        flash('success', 'Tarif byl aktualizován.');
    }

    /**
     * Aktivovat/deaktivovat tarif
     */
    private function togglePlan(): void
    {
        $id = (int) $this->input('plan_id', 0);

        if ($id <= 0) {
            flash('error', 'Neplatný tarif.');
            return;
        }

        $plan = $this->plan->find($id);
        if ($plan) {
            $this->plan->toggleAvailability($id);
            flash('success', $plan['is_available'] ? 'Tarif byl deaktivován.' : 'Tarif byl aktivován.');
        }
    }

    /**
     * Nastavit výchozí tarif
     */
    private function setDefaultPlan(): void
    {
        $id = (int) $this->input('plan_id', 0);

        if ($id <= 0) {
            flash('error', 'Neplatný tarif.');
            return;
        }

        // Nastavit jako výchozí (automaticky zruší ostatní)
        $this->plan->setDefault($id);

        flash('success', 'Výchozí tarif byl nastaven.');
    }

    /**
     * Vytvořit slug z názvu
     */
    private function createSlug(string $name): string
    {
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9]+/', '_', $slug);
        $slug = trim($slug, '_');
        return $slug ?: 'plan_' . time();
    }

    /**
     * Náhled zasílaných emailů
     */
    public function emailPreviews(array $params): void
    {
        $this->view('admin/settings/email-previews', [
            'title' => 'Náhled emailů',
        ], 'admin');
    }

    /**
     * Zobrazit konkrétní email náhled
     */
    public function emailPreview(array $params): void
    {
        $type = $params['type'] ?? 'activation';

        // Vytvořit EmailService
        $emailService = new EmailService();

        // Testovací data pro jednotlivé typy emailů
        $testData = $this->getTestDataForEmailType($type);

        if ($testData === null) {
            http_response_code(404);
            echo 'Neznámý typ emailu';
            return;
        }

        // Použít reflexi pro přístup k private metodě renderTemplate
        $reflection = new \ReflectionClass($emailService);
        $method = $reflection->getMethod('renderFallbackTemplate');
        $method->setAccessible(true);

        $html = $method->invoke($emailService, $type, $testData);

        echo $html;
    }

    /**
     * Získat testovací data pro daný typ emailu
     */
    private function getTestDataForEmailType(string $type): ?array
    {
        switch ($type) {
            case 'activation':
                return [
                    'customer' => [
                        'name' => 'Jan Novák',
                        'email' => 'jan.novak@example.com',
                    ],
                    'activation_url' => $this->config['app']['url'] . '/aktivace/demo-token',
                    'shop_phone' => $this->setting->get('shop_phone', '123 456 789'),
                ];

            case 'payment_qr':
                return [
                    'customer' => [
                        'name' => 'Jan Novák',
                        'email' => 'jan.novak@example.com',
                    ],
                    'subscription' => [
                        'price' => 150,
                        'variable_symbol' => '26001',
                    ],
                    'qr_code_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=SPD*1.0*ACC:CZ1234567890*AM:150.00',
                    'bank_account' => $this->setting->get('bank_account', '123456789/0100'),
                    'shop_phone' => $this->setting->get('shop_phone', '123 456 789'),
                ];

            case 'event_reminder':
                return [
                    'customer' => [
                        'name' => 'Jan Novák',
                        'email' => 'jan.novak@example.com',
                    ],
                    'reminder' => [
                        'event_type' => 'birthday',
                        'recipient_relation' => 'wife',
                        'event_day' => 15,
                        'event_month' => 3,
                    ],
                    'event_type' => 'narozeniny',
                    'recipient' => 'vaše manželka',
                    'date' => '15. března',
                    'shop_phone' => $this->setting->get('shop_phone', '123 456 789'),
                ];

            case 'expiration_reminder':
                return [
                    'customer' => [
                        'name' => 'Jan Novák',
                        'email' => 'jan.novak@example.com',
                    ],
                    'subscription' => [
                        'price' => 150,
                        'variable_symbol' => '26001',
                    ],
                    'days_left' => 14,
                    'qr_code_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=SPD*1.0*ACC:CZ1234567890*AM:150.00',
                    'bank_account' => $this->setting->get('bank_account', '123456789/0100'),
                    'shop_phone' => $this->setting->get('shop_phone', '123 456 789'),
                ];

            case 'otp':
                return [
                    'customer' => [
                        'name' => 'Jan Novák',
                        'email' => 'jan.novak@example.com',
                    ],
                    'code' => '123456',
                    'shop_phone' => $this->setting->get('shop_phone', '123 456 789'),
                ];

            case 'admin_summary':
                return [
                    'stats' => [
                        'calls_today' => 5,
                        'awaiting_activation' => 2,
                        'unmatched_payments' => 1,
                        'expiring_this_week' => 3,
                    ],
                    'date' => date('j. n. Y'),
                ];

            default:
                return null;
        }
    }
}
