<?php
/**
 * Připomněnka - CustomerController
 *
 * Handles customer profile, settings and GDPR functions
 */

declare(strict_types=1);

namespace Controllers;

use Models\Customer;
use Models\Subscription;

class CustomerController extends BaseController
{
    private Customer $customer;
    private Subscription $subscription;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->customer = new Customer();
        $this->subscription = new Subscription();
    }

    /**
     * Show customer profile
     */
    public function profile(array $params): void
    {
        $customerId = \Session::getCustomerId();
        $customer = $this->customer->find($customerId);

        if (!$customer) {
            redirect('/odhlaseni');
        }

        $subscription = $this->subscription->findByCustomerId($customerId);

        $this->view('customer/profile', [
            'customer' => $customer,
            'subscription' => $subscription,
        ]);
    }

    /**
     * Update customer profile
     */
    public function updateProfile(array $params): void
    {
        \CSRF::verify();

        $customerId = \Session::getCustomerId();
        $customer = $this->customer->find($customerId);

        if (!$customer) {
            redirect('/odhlaseni');
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
        ];

        // Update customer
        $this->customer->update($customerId, $data);

        // Handle password change
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!empty($newPassword)) {
            // If customer has password, verify current one
            if ($customer['password_hash'] && !password_verify($currentPassword, $customer['password_hash'])) {
                \Session::flash('error', 'Aktuální heslo není správné.');
                redirect('/profil');
            }

            // Validate new password
            if (strlen($newPassword) < 8) {
                \Session::flash('error', 'Nové heslo musí mít alespoň 8 znaků.');
                redirect('/profil');
            }

            if ($newPassword !== $confirmPassword) {
                \Session::flash('error', 'Nová hesla se neshodují.');
                redirect('/profil');
            }

            // Update password
            $this->customer->update($customerId, [
                'password' => $newPassword,
            ]);

            \Session::flash('success', 'Profil a heslo byly aktualizovány.');
        } else {
            \Session::flash('success', 'Profil byl aktualizován.');
        }

        redirect('/profil');
    }

    /**
     * Export all customer data (GDPR)
     */
    public function exportData(array $params): void
    {
        $customerId = \Session::getCustomerId();
        $customer = $this->customer->find($customerId);

        if (!$customer) {
            redirect('/odhlaseni');
        }

        // Get all customer data
        $data = $this->customer->exportData($customerId);

        // Determine format
        $format = $_GET['format'] ?? 'json';

        if ($format === 'pdf') {
            $this->exportPdf($data);
        } else {
            $this->exportJson($data);
        }
    }

    /**
     * Export data as JSON
     */
    private function exportJson(array $data): void
    {
        $filename = 'moje-data-' . date('Y-m-d') . '.json';

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Export data as PDF (simple HTML-based)
     */
    private function exportPdf(array $data): void
    {
        // For shared hosting without PDF libraries, generate HTML that can be printed
        $settings = new \Models\Setting();

        $this->view('customer/export-pdf', [
            'data' => $data,
            'exportDate' => date('j. n. Y H:i'),
            'shopEmail' => $settings->getShopEmail(),
            'shopPhone' => $settings->getShopPhone(),
        ], 'print');
    }

    /**
     * Show delete account confirmation
     */
    public function deleteAccountForm(array $params): void
    {
        $customerId = \Session::getCustomerId();
        $customer = $this->customer->find($customerId);

        if (!$customer) {
            redirect('/odhlaseni');
        }

        $this->view('customer/delete-account', [
            'customer' => $customer,
        ]);
    }

    /**
     * Delete customer account (GDPR)
     */
    public function deleteAccount(array $params): void
    {
        \CSRF::verify();

        $customerId = \Session::getCustomerId();
        $customer = $this->customer->find($customerId);

        if (!$customer) {
            redirect('/odhlaseni');
        }

        // Verify password or use confirmation phrase
        $confirmation = $_POST['confirmation'] ?? '';

        if ($confirmation !== 'SMAZAT ÚČET') {
            \Session::flash('error', 'Pro potvrzení smazání napište "SMAZAT ÚČET".');
            redirect('/smazat-ucet');
        }

        // Log the deletion request
        error_log(sprintf(
            '[GDPR] Account deletion requested: customer_id=%d, email=%s, ip=%s',
            $customerId,
            $customer['email'],
            $_SERVER['REMOTE_ADDR']
        ));

        // Delete customer (cascades to all related data)
        $this->customer->delete($customerId);

        // Clear session
        \Session::logout();

        // Show confirmation
        \Session::flash('success', 'Váš účet byl smazán. Děkujeme, že jste byli s námi.');
        redirect('/');
    }

    /**
     * Show GDPR information
     */
    public function gdprInfo(array $params): void
    {
        $this->view('customer/gdpr-info');
    }
}
