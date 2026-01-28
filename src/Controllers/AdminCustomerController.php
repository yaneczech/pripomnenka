<?php
/**
 * Připomněnka - AdminCustomerController
 *
 * Správa zákazníků v administraci
 */

declare(strict_types=1);

namespace Controllers;

use Models\Customer;
use Models\Subscription;
use Models\SubscriptionPlan;
use Models\Reminder;
use Models\Setting;

class AdminCustomerController extends BaseController
{
    private Customer $customer;
    private Subscription $subscription;
    private SubscriptionPlan $plan;
    private Reminder $reminder;
    private Setting $setting;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->customer = new Customer();
        $this->subscription = new Subscription();
        $this->plan = new SubscriptionPlan();
        $this->reminder = new Reminder();
        $this->setting = new Setting();
    }

    /**
     * Seznam zákazníků
     */
    public function index(array $params): void
    {
        $filter = $this->query('filter', 'all');
        $search = $this->query('q', '');

        if ($search) {
            $customers = $this->customer->search($search);
        } else {
            $customers = $this->customer->getAll($filter);
        }

        $this->view('admin/customers/index', [
            'title' => 'Zákazníci',
            'customers' => $customers,
            'filter' => $filter,
            'search' => $search,
            'counts' => [
                'all' => $this->customer->count('all'),
                'active' => $this->customer->count('active'),
                'awaiting_activation' => $this->customer->count('awaiting_activation'),
                'awaiting_payment' => $this->customer->count('awaiting_payment'),
                'expired' => $this->customer->count('expired'),
            ],
        ], 'admin');
    }

    /**
     * Detail zákazníka
     */
    public function show(array $params): void
    {
        $id = (int) $params['id'];
        $customer = $this->customer->find($id);

        if (!$customer) {
            $this->notFound();
        }

        $subscription = $this->subscription->findLatestByCustomer($id);
        $reminders = $this->reminder->getByCustomerSorted($id);

        // Historie volání
        $callHistory = $this->db->fetchAll(
            "SELECT cl.*, r.event_type, r.recipient_relation, r.event_day, r.event_month
             FROM call_logs cl
             JOIN reminders r ON r.id = cl.reminder_id
             WHERE r.customer_id = ?
             ORDER BY cl.call_date DESC
             LIMIT 20",
            [$id]
        );

        // Interní poznámky
        $notes = $this->db->fetchOne(
            "SELECT * FROM customer_notes WHERE customer_id = ?",
            [$id]
        );

        $this->view('admin/customers/show', [
            'title' => $customer['name'] ?: $customer['phone'],
            'customer' => $customer,
            'subscription' => $subscription,
            'reminders' => $reminders,
            'callHistory' => $callHistory,
            'notes' => $notes,
            'reminderLimit' => $subscription ? $subscription['reminder_limit'] : 0,
        ], 'admin');
    }

    /**
     * Formulář pro nového zákazníka
     */
    public function create(array $params): void
    {
        $plans = $this->plan->getAvailable();
        $defaultPlan = $this->plan->getDefault();

        $this->view('admin/customers/create', [
            'title' => 'Nový zákazník',
            'plans' => $plans,
            'defaultPlanId' => $defaultPlan ? $defaultPlan['id'] : null,
            'errors' => $this->getErrors(),
        ], 'admin');
    }

    /**
     * Uložit nového zákazníka
     */
    public function store(array $params): void
    {
        $this->validateCsrf();

        $data = [
            'phone' => trim($this->input('phone', '')),
            'email' => trim($this->input('email', '')),
            'plan_id' => (int) $this->input('plan_id'),
            'payment_method' => $this->input('payment_method', 'cash'),
        ];

        // Validace
        $validator = $this->validate($data);
        $validator
            ->required('phone', 'Zadejte telefon.')
            ->phone('phone', 'Neplatný formát telefonu.')
            ->required('email', 'Zadejte email.')
            ->email('email', 'Neplatný formát emailu.')
            ->required('plan_id', 'Vyberte variantu předplatného.')
            ->in('payment_method', ['cash', 'card', 'bank_transfer'], 'Neplatný způsob platby.');

        // Kontrola duplicit
        if ($this->customer->exists($data['email'], $data['phone'])) {
            $validator->addError('phone', 'Zákazník s tímto telefonem nebo emailem již existuje.');
        }

        if ($validator->fails()) {
            $this->withErrors($validator->errors());
            $this->withOldInput();
            $this->redirect('/admin/novy-zakaznik');
        }

        // Najít plán
        $plan = $this->plan->find($data['plan_id']);
        if (!$plan) {
            flash('error', 'Neplatná varianta předplatného.');
            $this->redirect('/admin/novy-zakaznik');
        }

        // Vytvořit zákazníka
        $customerId = $this->customer->create([
            'phone' => $data['phone'],
            'email' => $data['email'],
        ]);

        // Vytvořit předplatné
        $subscriptionId = $this->subscription->create([
            'customer_id' => $customerId,
            'plan_id' => $plan['id'],
            'reminder_limit' => $plan['reminder_limit'],
            'price' => $plan['price'],
            'payment_method' => $data['payment_method'],
        ]);

        $subscription = $this->subscription->find($subscriptionId);

        // Zpracovat podle způsobu platby
        if ($data['payment_method'] === 'bank_transfer') {
            // TODO: Odeslat email s QR kódem
            flash('success', 'Zákazníkovi jsme poslali QR kód pro platbu. VS: ' . $subscription['variable_symbol']);
        } else {
            // Hotově/kartou - potvrdit platbu
            $this->subscription->confirmPayment($subscriptionId, \Session::getAdminId(), $plan['price']);

            // TODO: Odeslat aktivační e-mail
            flash('success', 'Hotovo! Zákazníkovi jsme poslali e-mail s aktivačním odkazem.');
        }

        $this->redirect('/admin/zakaznik/' . $customerId);
    }

    /**
     * Aktualizovat zákazníka
     */
    public function update(array $params): void
    {
        $this->validateCsrf();

        $id = (int) $params['id'];
        $customer = $this->customer->find($id);

        if (!$customer) {
            $this->notFound();
        }

        $data = [
            'name' => trim($this->input('name', '')),
            'phone' => trim($this->input('phone', '')),
            'email' => trim($this->input('email', '')),
        ];

        // Validace
        $validator = $this->validate($data);
        $validator
            ->required('phone', 'Zadejte telefon.')
            ->phone('phone', 'Neplatný formát telefonu.')
            ->required('email', 'Zadejte email.')
            ->email('email', 'Neplatný formát emailu.');

        // Kontrola duplicit (mimo tohoto zákazníka)
        if ($this->customer->exists($data['email'], $data['phone'], $id)) {
            $validator->addError('phone', 'Zákazník s tímto telefonem nebo emailem již existuje.');
        }

        if ($validator->fails()) {
            $this->withErrors($validator->errors());
            $this->withOldInput();
            $this->redirect('/admin/zakaznik/' . $id);
        }

        $this->customer->update($id, $data);

        // Aktualizovat interní poznámky
        $notes = [
            'preferred_flowers' => $this->input('preferred_flowers'),
            'typical_budget' => $this->input('typical_budget'),
            'preferred_call_time' => $this->input('preferred_call_time'),
            'general_note' => $this->input('general_note'),
        ];

        $this->db->query(
            "INSERT INTO customer_notes (customer_id, preferred_flowers, typical_budget, preferred_call_time, general_note)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                preferred_flowers = VALUES(preferred_flowers),
                typical_budget = VALUES(typical_budget),
                preferred_call_time = VALUES(preferred_call_time),
                general_note = VALUES(general_note)",
            [$id, $notes['preferred_flowers'], $notes['typical_budget'], $notes['preferred_call_time'], $notes['general_note']]
        );

        flash('success', 'Změny uloženy.');
        $this->redirect('/admin/zakaznik/' . $id);
    }

    /**
     * Smazat zákazníka
     */
    public function delete(array $params): void
    {
        $this->validateCsrf();

        $id = (int) $params['id'];
        $customer = $this->customer->find($id);

        if (!$customer) {
            $this->notFound();
        }

        $this->customer->delete($id);

        flash('success', 'Zákazník byl smazán.');
        $this->redirect('/admin/zakaznici');
    }

    /**
     * Znovu odeslat aktivační email
     */
    public function resendActivation(array $params): void
    {
        $this->validateCsrf();

        $id = (int) $params['id'];
        $subscription = $this->subscription->findLatestByCustomer($id);

        if (!$subscription || $subscription['status'] !== 'awaiting_activation') {
            flash('error', 'Zákazník nemá předplatné čekající na aktivaci.');
            $this->redirect('/admin/zakaznik/' . $id);
        }

        // Vygenerovat nový token
        $token = $this->subscription->regenerateActivationToken($subscription['id']);

        if ($token) {
            // TODO: Odeslat e-mail
            flash('success', 'Aktivační e-mail byl odeslán.');
        } else {
            flash('error', 'Nepodařilo se vygenerovat aktivační odkaz.');
        }

        $this->redirect('/admin/zakaznik/' . $id);
    }

    /**
     * Znovu odeslat QR kód pro platbu
     */
    public function resendPaymentQr(array $params): void
    {
        $this->validateCsrf();

        $id = (int) $params['id'];
        $subscription = $this->subscription->findLatestByCustomer($id);

        if (!$subscription || $subscription['status'] !== 'awaiting_payment') {
            flash('error', 'Zákazník nemá předplatné čekající na platbu.');
            $this->redirect('/admin/zakaznik/' . $id);
        }

        // TODO: Odeslat e-mail s QR kódem
        flash('success', 'E-mail s QR kódem byl odeslán. VS: ' . $subscription['variable_symbol']);

        $this->redirect('/admin/zakaznik/' . $id);
    }

    /**
     * Přepnout aktivní stav zákazníka
     */
    public function toggleActive(array $params): void
    {
        $this->validateCsrf();

        $id = (int) $params['id'];
        $customer = $this->customer->find($id);

        if (!$customer) {
            $this->notFound();
        }

        $this->customer->toggleActive($id);

        $isActive = !($customer['is_active'] ?? true);
        $message = $isActive ? 'Zákazník byl aktivován.' : 'Zákazník byl deaktivován.';

        flash('success', $message);
        $this->redirect('/admin/zakaznik/' . $id);
    }
}
