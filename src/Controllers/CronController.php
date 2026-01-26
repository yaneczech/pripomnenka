<?php
/**
 * Připomněnka - CronController
 *
 * Public endpoints for CRON jobs (protected by token in middleware)
 */

declare(strict_types=1);

namespace Controllers;

class CronController extends BaseController
{
    /**
     * Generate daily call queue
     * URL: /cron/generate-queue?token=XXX
     */
    public function generateQueue(array $params): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        require ROOT_PATH . '/cron/generate-call-list.php';
    }

    /**
     * Send customer reminder emails
     * URL: /cron/send-emails?token=XXX
     */
    public function sendEmails(array $params): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        require ROOT_PATH . '/cron/send-customer-emails.php';
    }

    /**
     * Send admin daily summary
     * URL: /cron/admin-summary?token=XXX
     */
    public function adminSummary(array $params): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        require ROOT_PATH . '/cron/send-admin-summary.php';
    }

    /**
     * Send subscription expiration reminders
     * URL: /cron/expiration-reminders?token=XXX
     */
    public function expirationReminders(array $params): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        require ROOT_PATH . '/cron/send-expiration-reminders.php';
    }

    /**
     * Process bank payment emails
     * URL: /cron/process-payments?token=XXX
     */
    public function processPayments(array $params): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        require ROOT_PATH . '/cron/process-bank-emails.php';
    }
}
