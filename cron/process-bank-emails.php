<?php

/**
 * Process Bank Emails CRON Job
 *
 * Reads payment notifications from AirBank via IMAP
 * and automatically matches payments to subscriptions
 * Run every 15 minutes
 *
 * Usage: php cron/process-bank-emails.php
 * Or via HTTP: /cron/process-payments?token=XXX
 */

// Load bootstrap
require_once __DIR__ . '/bootstrap.php';

$log = function($message) {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
};

$log('Starting bank email processing...');

// Check if IMAP is configured
$imapHost = Setting::get('bank_imap_host');
$imapEmail = Setting::get('bank_imap_email');
$imapPassword = Setting::get('bank_imap_password');

if (empty($imapHost) || empty($imapEmail) || empty($imapPassword)) {
    $log('IMAP not configured, skipping');
    exit;
}

// Check if IMAP extension is available
if (!function_exists('imap_open')) {
    $log('ERROR: IMAP extension not installed');
    exit;
}

$db = Database::getInstance();
$emailService = new EmailService();

// Connect to IMAP
$mailbox = '{' . $imapHost . ':993/imap/ssl}INBOX';

$log("Connecting to: $mailbox");

$connection = @imap_open($mailbox, $imapEmail, $imapPassword);

if (!$connection) {
    $log('ERROR: Could not connect to IMAP: ' . imap_last_error());
    exit;
}

$log('Connected successfully');

// Search for unread messages from AirBank
$emails = imap_search($connection, 'UNSEEN FROM "airbank"');

if ($emails === false) {
    $log('No new payment emails found');
    imap_close($connection);
    exit;
}

$log('Found ' . count($emails) . ' new emails');

$processed = 0;
$matched = 0;
$unmatched = 0;

foreach ($emails as $emailId) {
    // Get email content
    $header = imap_headerinfo($connection, $emailId);
    $body = imap_fetchbody($connection, $emailId, 1);

    // Decode body if needed
    $encoding = imap_fetchstructure($connection, $emailId)->encoding;
    if ($encoding == 3) { // BASE64
        $body = base64_decode($body);
    } elseif ($encoding == 4) { // QUOTED-PRINTABLE
        $body = quoted_printable_decode($body);
    }

    // Try to parse payment info from email
    // AirBank format varies, this is a common pattern
    $payment = parsePaymentEmail($body);

    if (!$payment) {
        $log("Could not parse email: " . $header->subject);
        continue;
    }

    $processed++;
    $log("Parsed payment: {$payment['amount']} CZK, VS: {$payment['vs']}");

    // Try to match with subscription
    $subscription = null;

    if (!empty($payment['vs'])) {
        $subscription = $db->fetchOne("
            SELECT s.*, c.email, c.name as customer_name
            FROM subscriptions s
            JOIN customers c ON s.customer_id = c.id
            WHERE s.variable_symbol = ?
              AND s.status IN ('awaiting_payment', 'expired')
        ", [$payment['vs']]);
    }

    if ($subscription) {
        // Check amount
        $expectedAmount = (float) $subscription['price'];
        $paidAmount = (float) $payment['amount'];

        if (abs($expectedAmount - $paidAmount) < 0.01) {
            // Exact match - auto approve
            $db->query("
                UPDATE subscriptions
                SET payment_status = 'paid',
                    price_paid = ?,
                    payment_confirmed_at = NOW(),
                    status = 'awaiting_activation',
                    starts_at = CURDATE(),
                    expires_at = DATE_ADD(CURDATE(), INTERVAL 1 YEAR)
                WHERE id = ?
            ", [$paidAmount, $subscription['id']]);

            // Generate activation token
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

            $db->query("
                UPDATE subscriptions
                SET activation_token = ?,
                    activation_token_expires_at = ?
                WHERE id = ?
            ", [$token, $expiresAt, $subscription['id']]);

            // Send activation email
            $config = require __DIR__ . '/../config/config.php';
            $activationUrl = $config['app']['url'] . '/aktivace/' . $token;

            $customer = [
                'email' => $subscription['email'],
                'name' => $subscription['customer_name'],
            ];

            $emailService->sendActivationEmail($customer, $activationUrl);

            $matched++;
            $log("Matched and activated: VS {$payment['vs']}, sent activation email");

        } else {
            // Amount mismatch - save as unmatched
            $db->query("
                INSERT INTO unmatched_payments
                (amount, variable_symbol, sender_name, received_at, raw_email_data)
                VALUES (?, ?, ?, NOW(), ?)
            ", [
                $paidAmount,
                $payment['vs'],
                $payment['sender'] ?? null,
                $body,
            ]);

            // Update subscription with note
            $note = $paidAmount > $expectedAmount
                ? "Přeplatek: zaplaceno {$paidAmount} Kč místo {$expectedAmount} Kč"
                : "Nedoplatek: zaplaceno {$paidAmount} Kč místo {$expectedAmount} Kč";

            $db->query("
                UPDATE subscriptions
                SET payment_note = ?,
                    price_paid = ?
                WHERE id = ?
            ", [$note, $paidAmount, $subscription['id']]);

            $unmatched++;
            $log("Amount mismatch for VS {$payment['vs']}: paid {$paidAmount}, expected {$expectedAmount}");
        }

    } else {
        // No matching subscription - save as unmatched
        $db->query("
            INSERT INTO unmatched_payments
            (amount, variable_symbol, sender_name, received_at, raw_email_data)
            VALUES (?, ?, ?, NOW(), ?)
        ", [
            $payment['amount'],
            $payment['vs'] ?? null,
            $payment['sender'] ?? null,
            $body,
        ]);

        $unmatched++;
        $log("No subscription found for VS: {$payment['vs']}");
    }

    // Mark email as read
    imap_setflag_full($connection, (string)$emailId, '\\Seen');
}

imap_close($connection);

$log("Processing completed: $processed processed, $matched matched, $unmatched unmatched");

/**
 * Parse payment info from AirBank notification email
 */
function parsePaymentEmail(string $body): ?array
{
    $payment = [
        'amount' => null,
        'vs' => null,
        'sender' => null,
    ];

    // Try to extract amount (patterns vary)
    // Common patterns: "1 500,00 CZK", "150.00 Kč", "Částka: 150 Kč"
    if (preg_match('/(?:částka|amount|připsáno)[:\s]*([0-9\s]+[,\.][0-9]{2})\s*(?:CZK|Kč|KČ)/ui', $body, $matches)) {
        $payment['amount'] = (float) str_replace([' ', ','], ['', '.'], $matches[1]);
    } elseif (preg_match('/([0-9\s]+[,\.][0-9]{2})\s*(?:CZK|Kč|KČ)/ui', $body, $matches)) {
        $payment['amount'] = (float) str_replace([' ', ','], ['', '.'], $matches[1]);
    }

    // Try to extract VS
    // Common patterns: "VS: 25001", "variabilní symbol: 25001", "/VS25001"
    if (preg_match('/(?:VS|variabilní\s*symbol)[:\s]*([0-9]+)/ui', $body, $matches)) {
        $payment['vs'] = $matches[1];
    } elseif (preg_match('/\/VS([0-9]+)/i', $body, $matches)) {
        $payment['vs'] = $matches[1];
    }

    // Try to extract sender name
    if (preg_match('/(?:od|from|odesílatel|plátce)[:\s]*([^\n\r]+)/ui', $body, $matches)) {
        $payment['sender'] = trim($matches[1]);
    }

    // Must have at least amount to be valid
    if ($payment['amount'] === null) {
        return null;
    }

    return $payment;
}
