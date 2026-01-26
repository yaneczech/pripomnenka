<?php
/**
 * Připomněnka - AdminController
 *
 * Dashboard a hlavní admin funkce
 */

declare(strict_types=1);

namespace Controllers;

use Models\Customer;
use Models\Subscription;
use Models\Reminder;

class AdminController extends BaseController
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
     * Dashboard
     */
    public function dashboard(array $params): void
    {
        // Dnes volat
        $today = new \DateTime();
        $callList = $this->reminder->getForCallDate($today);
        $todayCallCount = count($callList);

        // Opakované pokusy (3+)
        $repeatedAttempts = array_filter($callList, fn($c) => $c['attempt_count'] >= 3);

        // Čekající na aktivaci
        $awaitingActivation = $this->subscription->getAwaitingActivation();

        // Nespárované platby
        $unmatchedPayments = $this->db->fetchAll(
            "SELECT * FROM unmatched_payments WHERE matched_to_subscription_id IS NULL ORDER BY received_at DESC"
        );

        // Tento týden
        $weekEnd = (new \DateTime())->modify('+7 days');
        $thisWeekCount = 0;
        for ($i = 0; $i <= 7; $i++) {
            $date = (new \DateTime())->modify("+{$i} days");
            $thisWeekCount += count($this->reminder->getForCallDate($date));
        }

        // Expiruje brzy (30 dní)
        $expiringSoon = $this->subscription->getExpiringWithin(30);

        // Statistiky
        $stats = $this->subscription->getStats();
        $stats['customers_active'] = $this->customer->count('active');
        $stats['reminders_total'] = (int) $this->db->fetchColumn("SELECT COUNT(*) FROM reminders WHERE is_active = 1");

        $this->view('admin/dashboard', [
            'title' => 'Dashboard',
            'todayCallCount' => $todayCallCount,
            'hasRepeatedAttempts' => count($repeatedAttempts) > 0,
            'awaitingActivationCount' => count($awaitingActivation),
            'unmatchedPaymentsCount' => count($unmatchedPayments),
            'thisWeekCount' => $thisWeekCount,
            'expiringSoonCount' => count($expiringSoon),
            'stats' => $stats,
        ], 'admin');
    }
}
