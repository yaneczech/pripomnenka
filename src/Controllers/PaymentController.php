<?php
/**
 * Připomněnka - PaymentController
 *
 * Správa nespárovaných plateb v admin sekci
 */

declare(strict_types=1);

namespace Controllers;

use Models\Subscription;
use Models\Customer;

class PaymentController extends BaseController
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
     * Seznam nespárovaných plateb
     */
    public function unmatched(array $params): void
    {
        $payments = $this->db->fetchAll(
            "SELECT * FROM unmatched_payments WHERE matched_to_subscription_id IS NULL ORDER BY received_at DESC"
        );

        // Načíst čekající předplatné pro ruční přiřazení
        $pendingSubscriptions = $this->subscription->findByStatus('awaiting_payment');

        $this->view('admin/payments/unmatched', [
            'title' => 'Nespárované platby',
            'payments' => $payments,
            'pendingSubscriptions' => $pendingSubscriptions,
        ], 'admin');
    }

    /**
     * Ruční přiřazení nespárované platby k předplatnému
     */
    public function match(array $params): void
    {
        $this->validateCsrf();

        $paymentId = (int) $params['id'];
        $subscriptionId = (int) $this->input('subscription_id');

        $payment = $this->db->fetchOne(
            "SELECT * FROM unmatched_payments WHERE id = ? AND matched_to_subscription_id IS NULL",
            [$paymentId]
        );

        if (!$payment) {
            flash('error', 'Platba nenalezena.');
            $this->redirect('/admin/platby');
        }

        $subscription = $this->subscription->find($subscriptionId);
        if (!$subscription) {
            flash('error', 'Předplatné nenalezeno.');
            $this->redirect('/admin/platby');
        }

        // Přiřadit platbu
        $this->db->update('unmatched_payments', [
            'matched_to_subscription_id' => $subscriptionId,
            'matched_at' => date('Y-m-d H:i:s'),
            'matched_by' => \Session::getAdminId(),
        ], 'id = ?', [$paymentId]);

        // Potvrdit platbu na předplatném
        $this->subscription->confirmBankPayment(
            $subscriptionId,
            (float) $payment['amount'],
            \Session::getAdminId()
        );

        flash('success', 'Platba byla přiřazena a předplatné aktivováno.');
        $this->redirect('/admin/platby');
    }
}
