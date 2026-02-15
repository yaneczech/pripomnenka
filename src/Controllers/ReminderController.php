<?php
/**
 * Připomněnka - ReminderController
 *
 * Správa připomínek zákazníka
 */

declare(strict_types=1);

namespace Controllers;

use Models\Reminder;
use Models\Subscription;
use Models\CallQueue;

class ReminderController extends BaseController
{
    private Reminder $reminder;
    private Subscription $subscription;
    private CallQueue $callQueue;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->reminder = new Reminder();
        $this->subscription = new Subscription();
        $this->callQueue = new CallQueue();
    }

    /**
     * Seznam připomínek
     */
    public function index(array $params): void
    {
        $customerId = \Session::getCustomerId();
        $reminders = $this->reminder->getByCustomerSorted($customerId);
        $subscription = $this->subscription->findActiveByCustomer($customerId);

        $reminderCount = count($reminders);
        $reminderLimit = $subscription ? $subscription['reminder_limit'] : 0;

        $this->view('customer/reminders/index', [
            'title' => 'Moje připomínky',
            'reminders' => $reminders,
            'reminderCount' => $reminderCount,
            'reminderLimit' => $reminderLimit,
            'canAdd' => $reminderCount < $reminderLimit,
            'subscription' => $subscription,
        ], 'public');
    }

    /**
     * Formulář pro novou připomínku
     */
    public function create(array $params): void
    {
        $customerId = \Session::getCustomerId();
        $subscription = $this->subscription->findActiveByCustomer($customerId);

        if (!$subscription) {
            flash('error', 'Nemáte aktivní předplatné.');
            $this->redirect('/moje-pripominky');
        }

        $reminderCount = $this->reminder->countByCustomer($customerId);

        if ($reminderCount >= $subscription['reminder_limit']) {
            flash('error', 'Dosáhli jste limitu připomínek (' . $subscription['reminder_limit'] . ').');
            $this->redirect('/moje-pripominky');
        }

        $this->view('customer/reminders/create', [
            'title' => 'Nová připomínka',
            'eventTypes' => Reminder::getEventTypes(),
            'relations' => Reminder::getRelations(),
            'priceRanges' => Reminder::getPriceRanges(),
            'advanceDays' => Reminder::getAdvanceDays(),
            'errors' => $this->getErrors(),
            'remainingCount' => $subscription['reminder_limit'] - $reminderCount,
        ], 'public');
    }

    /**
     * Uložit novou připomínku
     */
    public function store(array $params): void
    {
        $this->validateCsrf();

        $customerId = \Session::getCustomerId();
        $subscription = $this->subscription->findActiveByCustomer($customerId);

        if (!$subscription) {
            flash('error', 'Nemáte aktivní předplatné.');
            $this->redirect('/moje-pripominky');        }

        $reminderCount = $this->reminder->countByCustomer($customerId);

        if ($reminderCount >= $subscription['reminder_limit']) {
            flash('error', 'Dosáhli jste limitu připomínek.');
            $this->redirect('/moje-pripominky');
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

        // Pro svátky s fixním datem přepsat datum na správné
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
            ->in('event_type', array_keys(Reminder::getEventTypes()), 'Neplatný typ události.')
            ->required('recipient_relation', 'Vyberte koho slavíte.')
            ->in('recipient_relation', array_keys(Reminder::getRelations()), 'Neplatný vztah.')
            ->required('event_day', 'Vyberte den.')
            ->between('event_day', 1, 31, 'Neplatný den.')
            ->required('event_month', 'Vyberte měsíc.')
            ->between('event_month', 1, 12, 'Neplatný měsíc.')
            ->validDate('event_day', 'event_month', 'Neplatné datum.')
            ->in('advance_days', array_keys(Reminder::getAdvanceDays()), 'Neplatný předstih.')
            ->in('price_range', array_keys(Reminder::getPriceRanges()), 'Neplatný rozpočet.')
            ->maxLength('customer_note', 500, 'Poznámka může mít maximálně 500 znaků.');

        if ($validator->fails()) {
            $this->withErrors($validator->errors());
            $this->withOldInput();
            $this->redirect('/nova-pripominka');
        }

        $data['customer_id'] = $customerId;
        $reminderId = $this->reminder->create($data);

        // Aktualizovat call queue pro tuto připomínku
        $this->callQueue->regenerateForReminder($reminderId);

        flash('success', 'Připomínka uložena! Ozveme se vám včas.');
        $this->redirect('/moje-pripominky');
    }

    /**
     * Editace připomínky
     */
    public function edit(array $params): void
    {
        $id = (int) $params['id'];
        $customerId = \Session::getCustomerId();

        $reminder = $this->reminder->find($id);

        if (!$reminder || !$this->reminder->belongsToCustomer($id, $customerId)) {
            $this->notFound();
        }

        $this->view('customer/reminders/edit', [
            'title' => 'Upravit připomínku',
            'reminder' => $reminder,
            'eventTypes' => Reminder::getEventTypes(),
            'relations' => Reminder::getRelations(),
            'priceRanges' => Reminder::getPriceRanges(),
            'advanceDays' => Reminder::getAdvanceDays(),
            'errors' => $this->getErrors(),
        ], 'public');
    }

    /**
     * Aktualizovat připomínku
     */
    public function update(array $params): void
    {
        $this->validateCsrf();

        $id = (int) $params['id'];
        $customerId = \Session::getCustomerId();

        if (!$this->reminder->belongsToCustomer($id, $customerId)) {
            $this->notFound();
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

        // Pro svátky s fixním datem přepsat datum na správné
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
            ->in('event_type', array_keys(Reminder::getEventTypes()), 'Neplatný typ události.')
            ->required('recipient_relation', 'Vyberte koho slavíte.')
            ->in('recipient_relation', array_keys(Reminder::getRelations()), 'Neplatný vztah.')
            ->required('event_day', 'Vyberte den.')
            ->between('event_day', 1, 31, 'Neplatný den.')
            ->required('event_month', 'Vyberte měsíc.')
            ->between('event_month', 1, 12, 'Neplatný měsíc.')
            ->validDate('event_day', 'event_month', 'Neplatné datum.')
            ->in('advance_days', array_keys(Reminder::getAdvanceDays()), 'Neplatný předstih.')
            ->in('price_range', array_keys(Reminder::getPriceRanges()), 'Neplatný rozpočet.')
            ->maxLength('customer_note', 500, 'Poznámka může mít maximálně 500 znaků.');

        if ($validator->fails()) {
            $this->withErrors($validator->errors());
            $this->withOldInput();
            $this->redirect('/pripominka/' . $id);
        }

        $this->reminder->update($id, $data);

        // Aktualizovat call queue pro tuto připomínku
        $this->callQueue->regenerateForReminder($id);

        flash('success', 'Připomínka aktualizována.');
        $this->redirect('/moje-pripominky');
    }

    /**
     * Smazat připomínku
     */
    public function delete(array $params): void
    {
        $this->validateCsrf();

        $id = (int) $params['id'];
        $customerId = \Session::getCustomerId();

        if (!$this->reminder->belongsToCustomer($id, $customerId)) {
            $this->notFound();
        }

        // Nejprve smazat z call queue
        $this->callQueue->deleteForReminder($id);

        // Pak smazat připomínku
        $this->reminder->delete($id);

        flash('success', 'Připomínka smazána.');
        $this->redirect('/moje-pripominky');
    }
}
