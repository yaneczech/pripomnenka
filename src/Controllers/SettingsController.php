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
            'title' => 'Nastaveni',
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

        flash('success', 'Nastaveni bylo ulozeno.');
        $this->redirect('/admin/nastaveni');
    }

    /**
     * Správa tarifů předplatného
     */
    public function plans(array $params): void
    {
        $plans = $this->plan->findAll();

        $this->view('admin/settings/plans', [
            'title' => 'Tarify predplatneho',
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
            flash('error', 'Vyplnte nazev a cenu tarifu.');
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

        flash('success', 'Tarif byl pridan.');
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
            flash('error', 'Neplatna data.');
            return;
        }

        $this->plan->update($id, [
            'name' => $name,
            'price' => $price,
            'reminder_limit' => $reminderLimit,
            'discount_percent' => $discountPercent,
            'description' => $description,
        ]);

        flash('success', 'Tarif byl aktualizovan.');
    }

    /**
     * Aktivovat/deaktivovat tarif
     */
    private function togglePlan(): void
    {
        $id = (int) $this->input('plan_id', 0);

        if ($id <= 0) {
            flash('error', 'Neplatny tarif.');
            return;
        }

        $plan = $this->plan->find($id);
        if ($plan) {
            $this->plan->update($id, [
                'is_available' => !$plan['is_available'],
            ]);
            flash('success', $plan['is_available'] ? 'Tarif byl deaktivovan.' : 'Tarif byl aktivovan.');
        }
    }

    /**
     * Nastavit výchozí tarif
     */
    private function setDefaultPlan(): void
    {
        $id = (int) $this->input('plan_id', 0);

        if ($id <= 0) {
            flash('error', 'Neplatny tarif.');
            return;
        }

        // Zrušit výchozí u všech
        $this->db->query("UPDATE subscription_plans SET is_default = FALSE");

        // Nastavit nový výchozí
        $this->plan->update($id, ['is_default' => true]);

        flash('success', 'Vychozi tarif byl nastaven.');
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
}
