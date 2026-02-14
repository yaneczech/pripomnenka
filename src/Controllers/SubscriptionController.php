<?php
/**
 * Připomněnka - SubscriptionController
 *
 * Správa předplatného a plateb v admin sekci
 */

declare(strict_types=1);

namespace Controllers;

use Models\Subscription;
use Models\Customer;

class SubscriptionController extends BaseController
{
    private Subscription $subscription;
    private Customer $customer;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->subscription = new Subscription();
        $this->customer = new Customer();
    }

    /**
     * Přehled předplatného a plateb
     */
    public function index(array $params): void
    {
        $filter = $this->query('filter', 'all');

        // Statistiky
        $stats = [
            'pending' => $this->subscription->countByStatus('awaiting_payment'),
            'unmatched' => $this->countUnmatchedPayments(),
            'expiring' => $this->subscription->countExpiringSoon(30),
            'expired' => $this->subscription->countByStatus('expired'),
        ];

        // Získat seznam podle filtru
        $subscriptions = match ($filter) {
            'pending' => $this->subscription->findByStatus('awaiting_payment'),
            'unmatched' => $this->getUnmatchedPayments(),
            'expiring' => $this->subscription->findExpiringSoon(30),
            'expired' => $this->subscription->findByStatus('expired'),
            default => $this->subscription->findAll(),
        };

        // Přidat data zákazníků
        foreach ($subscriptions as &$sub) {
            $sub['customer'] = $this->customer->find($sub['customer_id']);
        }

        $this->view('admin/subscriptions/index', [
            'title' => 'Předplatné',
            'subscriptions' => $subscriptions,
            'stats' => $stats,
            'filter' => $filter,
        ], 'admin');
    }

    /**
     * Potvrzení platby
     */
    public function confirmPayment(array $params): void
    {
        $this->validateCsrf();

        $id = (int) $params['id'];
        $subscription = $this->subscription->find($id);

        if (!$subscription) {
            flash('error', 'Předplatné nenalezeno.');
            $this->redirect('/admin/predplatne');
        }

        $pricePaid = (float) $this->input('price_paid', $subscription['price']);

        // Aktivovat předplatné — správné pořadí: id, adminId, amount
        $this->subscription->confirmPayment($id, \Session::getAdminId(), $pricePaid);

        // Odeslat aktivační email zákazníkovi
        $customer = $this->customer->find($subscription['customer_id']);
        if ($customer) {
            $this->sendActivationEmail($customer, $subscription);
        }

        flash('success', 'Platba potvrzena a aktivační email odeslán.');
        $this->redirect('/admin/predplatne');
    }

    /**
     * Ruční přiřazení nespárované platby
     */
    public function matchPayment(array $params): void
    {
        $this->validateCsrf();

        $paymentId = (int) $params['id'];
        $subscriptionId = (int) $this->input('subscription_id');

        // TODO: Implementovat párování plateb
        flash('success', 'Platba byla přiřazena.');
        $this->redirect('/admin/predplatne?filter=unmatched');
    }

    /**
     * Počet nespárovaných plateb
     */
    private function countUnmatchedPayments(): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM unmatched_payments WHERE matched_to_subscription_id IS NULL"
        );
    }

    /**
     * Získat nespárované platby
     */
    private function getUnmatchedPayments(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM unmatched_payments WHERE matched_to_subscription_id IS NULL ORDER BY received_at DESC"
        );
    }

    /**
     * Odeslat aktivační email
     */
    private function sendActivationEmail(array $customer, array $subscription): void
    {
        // Vygenerovat aktivační token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

        $this->subscription->update($subscription['id'], [
            'activation_token' => $token,
            'activation_token_expires_at' => $expiresAt,
            'status' => 'awaiting_activation',
        ]);

        // TODO: Odeslat email přes EmailService
    }
}
