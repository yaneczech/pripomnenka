<?php
/**
 * Připomněnka - ActivationController
 *
 * Aktivace účtu zákazníka
 */

declare(strict_types=1);

namespace Controllers;

use Models\Customer;
use Models\Subscription;
use Models\Reminder;

class ActivationController extends BaseController
{
    private Customer $customer;
    private Subscription $subscription;
    private Reminder $reminder;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->customer = new Customer();
        $this->subscription = new Subscription();
        $this->reminder = new Reminder();
    }

    /**
     * Zobrazit aktivační wizard
     */
    public function showActivation(array $params): void
    {
        $token = $params['token'] ?? '';

        // Najít předplatné podle tokenu
        $subscription = $this->subscription->findByActivationToken($token);

        if (!$subscription) {
            // Token neplatný nebo expirovaný
            $this->view('activation/expired', [
                'title' => 'Odkaz vypršel',
            ], 'public');
            return;
        }

        // Kontrola, zda už není aktivováno
        if ($subscription['status'] === 'active') {
            flash('info', 'Váš účet je již aktivní. Můžete se přihlásit.');
            $this->redirect('/prihlaseni');
        }

        $customer = $this->customer->find($subscription['customer_id']);
        $step = \Session::get('activation_step', 1);

        $this->view('activation/wizard', [
            'title' => 'Aktivace účtu',
            'token' => $token,
            'subscription' => $subscription,
            'customer' => $customer,
            'step' => $step,
            'errors' => $this->getErrors(),
        ], 'public');
    }

    /**
     * Zpracovat aktivaci
     */
    public function processActivation(array $params): void
    {
        $this->validateCsrf();

        $token = $params['token'] ?? '';
        $step = (int) $this->input('step', 1);

        $subscription = $this->subscription->findByActivationToken($token);

        if (!$subscription) {
            flash('error', 'Neplatný aktivační odkaz.');
            $this->redirect('/');
        }

        switch ($step) {
            case 1:
                $this->processStep1($token, $subscription);
                break;
            case 2:
                $this->processStep2($token, $subscription);
                break;
            case 3:
                $this->processStep3($token, $subscription);
                break;
            default:
                $this->redirect('/aktivace/' . $token);
        }
    }

    /**
     * Krok 1: Představení (jméno, heslo, souhlas s podmínkami)
     */
    private function processStep1(string $token, array $subscription): void
    {
        $name = trim($this->input('name', ''));
        $password = $this->input('password', '');
        $gdprConsent = $this->input('gdpr_consent');

        // Validace souhlasu s podmínkami a GDPR
        if (!$gdprConsent) {
            $this->withErrors(['gdpr_consent' => 'Pro pokračování musíte souhlasit s obchodními podmínkami a zpracováním osobních údajů.']);
            $this->withOldInput();
            $this->redirect('/aktivace/' . $token);
        }

        // Validace hesla (pokud bylo zadáno)
        if ($password && strlen($password) < 8) {
            $this->withErrors(['password' => 'Heslo musí mít alespoň 8 znaků.']);
            $this->withOldInput();
            $this->redirect('/aktivace/' . $token);
        }

        // Aktualizovat zákazníka — souhlas s GDPR i obchodními podmínkami
        $now = date('Y-m-d H:i:s');
        $updateData = [
            'gdpr_consent_at' => $now,
            'gdpr_consent_text' => 'Souhlas s obchodními podmínkami a zpracováním osobních údajů pro službu Připomněnka. Verze ' . date('Y-m-d'),
            'terms_consent_at' => $now,
        ];

        if ($name) {
            $updateData['name'] = $name;
        }

        if ($password) {
            $updateData['password'] = $password;
        }

        $this->customer->update($subscription['customer_id'], $updateData);

        // Přejít na krok 2
        \Session::set('activation_step', 2);
        $this->redirect('/aktivace/' . $token);
    }

    /**
     * Krok 2: Přidání připomínek
     */
    private function processStep2(string $token, array $subscription): void
    {
        $action = $this->input('action');

        if ($action === 'add_reminder') {
            // Přidat připomínku
            $data = [
                'customer_id' => $subscription['customer_id'],
                'event_type' => $this->input('event_type'),
                'recipient_relation' => $this->input('recipient_relation'),
                'event_day' => (int) $this->input('event_day'),
                'event_month' => (int) $this->input('event_month'),
                'advance_days' => (int) $this->input('advance_days', 5),
                'price_range' => $this->input('price_range', 'to_discuss'),
                'customer_note' => trim($this->input('customer_note', '')),
            ];

            // Kontrola limitu
            $currentCount = $this->reminder->countByCustomer($subscription['customer_id']);
            if ($currentCount >= $subscription['reminder_limit']) {
                flash('warning', 'Dosáhli jste limitu připomínek.');
                $this->redirect('/aktivace/' . $token);
            }

            // Validace
            $validator = $this->validate($data);
            $validator
                ->required('event_type')
                ->required('recipient_relation')
                ->required('event_day')
                ->between('event_day', 1, 31)
                ->required('event_month')
                ->between('event_month', 1, 12);

            if ($validator->fails()) {
                $this->withErrors($validator->errors());
                $this->withOldInput();
                $this->redirect('/aktivace/' . $token);
            }

            $this->reminder->create($data);
            flash('success', 'Připomínka přidána.');

        } elseif ($action === 'continue' || $action === 'skip') {
            // Přejít na krok 3
            \Session::set('activation_step', 3);
        }

        $this->redirect('/aktivace/' . $token);
    }

    /**
     * Krok 3: Dokončení
     */
    private function processStep3(string $token, array $subscription): void
    {
        // Aktivovat předplatné
        $this->subscription->activate($subscription['id']);

        // Přihlásit zákazníka
        $customer = $this->customer->find($subscription['customer_id']);
        \Session::loginCustomer($customer['id'], $customer['name']);

        // Vyčistit activation step
        \Session::remove('activation_step');

        flash('success', 'Váš účet byl aktivován! Vítejte v Připomněnce.');
        $this->redirect('/moje-pripominky');
    }
}
