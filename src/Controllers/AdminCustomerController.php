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
use Services\EmailService;

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
        $plans = $this->plan->findAll();

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
            'plans' => $plans,
            'reminderCount' => count($reminders),
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

        // Najít plán (potřebujeme cenu pro validaci platby)
        $plan = $this->plan->find($data['plan_id']);
        $isFree = $plan && (float) $plan['price'] <= 0;

        // Pro bezplatné tarify není potřeba způsob platby
        if ($isFree) {
            $data['payment_method'] = 'cash'; // výchozí pro bezplatné
        }

        // Validace
        $validator = $this->validate($data);
        $validator
            ->required('phone', 'Zadejte telefon.')
            ->phone('phone', 'Neplatný formát telefonu.')
            ->required('email', 'Zadejte email.')
            ->email('email', 'Neplatný formát emailu.')
            ->required('plan_id', 'Vyberte variantu předplatného.');

        if (!$isFree) {
            $validator->in('payment_method', ['cash', 'card', 'bank_transfer'], 'Neplatný způsob platby.');
        }

        // Kontrola duplicit
        if ($this->customer->exists($data['email'], $data['phone'])) {
            $validator->addError('phone', 'Zákazník s tímto telefonem nebo emailem již existuje.');
        }

        if ($validator->fails()) {
            $this->withErrors($validator->errors());
            $this->withOldInput();
            $this->redirect('/admin/novy-zakaznik');
        }
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

        // Zpracovat podle typu tarifu a způsobu platby
        $emailService = new EmailService();
        $customer = $this->customer->find($customerId);

        if ((float) $plan['price'] <= 0) {
            // Bezplatný tarif — platba se nepožaduje, rovnou aktivační email
            $subscription = $this->subscription->find($subscriptionId);
            $activationUrl = $this->config['app']['url'] . '/aktivace/' . $subscription['activation_token'];
            $emailService->sendActivationEmail($customer, $activationUrl);
            flash('success', 'Hotovo! Bezplatný tarif — zákazníkovi jsme poslali email s aktivačním odkazem.');
        } elseif ($data['payment_method'] === 'bank_transfer') {
            $subscription = $this->subscription->find($subscriptionId);
            $emailService->sendPaymentQrEmail($customer, $subscription);
            flash('success', 'Zákazníkovi jsme poslali QR kód pro platbu. VS: ' . $subscription['variable_symbol']);
        } else {
            // Hotově/kartou - potvrdit platbu
            $this->subscription->confirmPayment($subscriptionId, \Session::getAdminId(), $plan['price']);

            $subscription = $this->subscription->find($subscriptionId);
            $activationUrl = $this->config['app']['url'] . '/aktivace/' . $subscription['activation_token'];
            $emailService->sendActivationEmail($customer, $activationUrl);
            flash('success', 'Hotovo! Zákazníkovi jsme poslali email s aktivačním odkazem.');
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
     * Změnit tarif zákazníka (upgrade/downgrade)
     */
    public function changePlan(array $params): void
    {
        $this->validateCsrf();

        $id = (int) $params['id'];
        $customer = $this->customer->find($id);

        if (!$customer) {
            $this->notFound();
        }

        $subscription = $this->subscription->findLatestByCustomer($id);
        if (!$subscription) {
            flash('error', 'Zákazník nemá žádné předplatné.');
            $this->redirect('/admin/zakaznik/' . $id);
        }

        $planId = (int) $this->input('plan_id', 0);
        $plan = $this->plan->find($planId);

        if (!$plan) {
            flash('error', 'Neplatný tarif.');
            $this->redirect('/admin/zakaznik/' . $id);
        }

        $activeReminders = $this->reminder->countByCustomer($id);
        if ($plan['reminder_limit'] < $activeReminders) {
            flash('error', 'Nelze snížit tarif pod aktuální počet připomínek (' . $activeReminders . ').');
            $this->redirect('/admin/zakaznik/' . $id);
        }

        $this->subscription->changePlan($subscription['id'], $plan);

        flash('success', 'Tarif byl změněn.');
        $this->redirect('/admin/zakaznik/' . $id);
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
            $customer = $this->customer->find($id);
            $activationUrl = $this->config['app']['url'] . '/aktivace/' . $token;
            $emailService = new EmailService();
            $emailService->sendActivationEmail($customer, $activationUrl);
            flash('success', 'Aktivační email byl odeslán.');
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

        $customer = $this->customer->find($id);
        $emailService = new EmailService();
        $emailService->sendPaymentQrEmail($customer, $subscription);
        flash('success', 'Email s QR kódem byl odeslán. VS: ' . $subscription['variable_symbol']);

        $this->redirect('/admin/zakaznik/' . $id);
    }

    /**
     * Přidat připomínku za zákazníka
     */
    public function storeReminder(array $params): void
    {
        $this->validateCsrf();

        $id = (int) $params['id'];
        $customer = $this->customer->find($id);

        if (!$customer) {
            $this->notFound();
        }

        $subscription = $this->subscription->findLatestByCustomer($id);

        if (!$subscription || !in_array($subscription['status'], ['active', 'awaiting_activation'])) {
            flash('error', 'Zákazník nemá aktivní předplatné.');
            $this->redirect('/admin/zakaznik/' . $id);
        }

        $reminderCount = $this->reminder->countByCustomer($id);

        if ($reminderCount >= $subscription['reminder_limit']) {
            flash('error', 'Zákazník dosáhl limitu připomínek (' . $subscription['reminder_limit'] . ').');
            $this->redirect('/admin/zakaznik/' . $id);
        }

        $data = [
            'event_type' => $this->input('event_type'),
            'recipient_relation' => $this->input('recipient_relation'),
            'event_day' => (int) $this->input('event_day'),
            'event_month' => (int) $this->input('event_month'),
            'advance_days' => (int) $this->input('advance_days', 5),
            'price_range' => $this->input('price_range', 'to_discuss'),
            'customer_note' => trim($this->input('customer_note', '')),
        ];

        // Pro svátky s fixním datem přepsat datum
        if ($data['event_type'] && has_automatic_date($data['event_type'])) {
            $holidayDate = get_holiday_date($data['event_type']);
            if ($holidayDate) {
                $data['event_day'] = $holidayDate['day'];
                $data['event_month'] = $holidayDate['month'];
            }
        }

        // Validace
        $validator = $this->validate($data);
        $validator
            ->required('event_type', 'Vyberte typ události.')
            ->in('event_type', array_keys(\Models\Reminder::getEventTypes()), 'Neplatný typ události.')
            ->required('recipient_relation', 'Vyberte koho slavíte.')
            ->in('recipient_relation', array_keys(\Models\Reminder::getRelations()), 'Neplatný vztah.')
            ->required('event_day', 'Vyberte den.')
            ->between('event_day', 1, 31, 'Neplatný den.')
            ->required('event_month', 'Vyberte měsíc.')
            ->between('event_month', 1, 12, 'Neplatný měsíc.');

        if ($validator->fails()) {
            $this->withErrors($validator->errors());
            flash('error', 'Opravte chyby ve formuláři.');
            $this->redirect('/admin/zakaznik/' . $id);
        }

        $data['customer_id'] = $id;
        $reminderId = $this->reminder->create($data);

        // Aktualizovat call queue
        $callQueue = new \Models\CallQueue();
        $callQueue->regenerateForReminder($reminderId);

        flash('success', 'Připomínka přidána.');
        $this->redirect('/admin/zakaznik/' . $id);
    }

    /**
     * Ručně prodloužit předplatné
     */
    public function extendSubscription(array $params): void
    {
        $this->validateCsrf();

        $id = (int) $params['id'];
        $customer = $this->customer->find($id);

        if (!$customer) {
            $this->notFound();
        }

        $subscription = $this->subscription->findLatestByCustomer($id);

        if (!$subscription) {
            flash('error', 'Zákazník nemá žádné předplatné.');
            $this->redirect('/admin/zakaznik/' . $id);
        }

        // Vypočítat nové datum expirace
        $now = date('Y-m-d');
        if ($subscription['expires_at'] && strtotime($subscription['expires_at']) > time()) {
            // Pokud ještě nevypršelo, přidat rok k aktuální expiraci
            $newExpires = date('Y-m-d', strtotime($subscription['expires_at'] . ' +1 year'));
        } else {
            // Pokud už vypršelo, počítat od dnes
            $newExpires = date('Y-m-d', strtotime('+1 year'));
        }

        $startsAt = $subscription['starts_at'] ?? $now;

        $this->subscription->update($subscription['id'], [
            'starts_at' => $startsAt,
            'expires_at' => $newExpires,
            'status' => 'active',
            'payment_status' => 'paid',
            'payment_confirmed_at' => date('Y-m-d H:i:s'),
            'payment_confirmed_by' => \Session::getAdminId(),
        ]);

        flash('success', 'Předplatné prodlouženo do ' . format_date($newExpires) . '.');
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
