<?php

/**
 * Email Service
 *
 * Handles sending all types of emails in the system
 */

declare(strict_types=1);

namespace Services;

class EmailService
{
    private array $config;
    private string $fromAddress;
    private string $fromName;
    private \Models\Setting $setting;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/config.php';
        $this->fromAddress = $this->config['email']['from_address'] ?? 'pripomnenka@jelenivzeleni.cz';
        $this->fromName = $this->config['email']['from_name'] ?? 'Jeleni v zeleni';
        $this->setting = new \Models\Setting();
    }

    /**
     * Send activation email to customer
     */
    public function sendActivationEmail(array $customer, string $activationUrl): bool
    {
        $subject = $this->setting->get('email_activation_subject', 'Vítejte v Připomněnce! 🌷');

        $body = $this->renderTemplate('activation', [
            'customer' => $customer,
            'activation_url' => $activationUrl,
            'shop_phone' => $this->setting->get('shop_phone', '123 456 789'),
        ]);

        return $this->send($customer['email'], $subject, $body);
    }

    /**
     * Send payment QR code email
     */
    public function sendPaymentQrEmail(array $customer, array $subscription): bool
    {
        $subject = $this->setting->get('email_payment_qr_subject', 'QR kód pro platbu Připomněnka 💳');

        $qrCodeUrl = $this->generateQrCodeUrl($subscription);

        $body = $this->renderTemplate('payment_qr', [
            'customer' => $customer,
            'subscription' => $subscription,
            'qr_code_url' => $qrCodeUrl,
            'bank_account' => $this->setting->get('bank_account', '123456789/0100'),
            'shop_phone' => $this->setting->get('shop_phone', '123 456 789'),
        ]);

        return $this->send($customer['email'], $subject, $body);
    }

    /**
     * Send event reminder email to customer
     */
    public function sendEventReminderEmail(array $customer, array $reminder): bool
    {
        $subject = $this->parseSubject(
            $this->setting->get('email_customer_reminder_subject', 'Blíží se důležité datum! 💐'),
            $reminder
        );

        $body = $this->renderTemplate('event_reminder', [
            'customer' => $customer,
            'reminder' => $reminder,
            'event_type' => translate_event_type($reminder['event_type']),
            'recipient' => translate_relation($reminder['recipient_relation']),
            'date' => format_date_long($reminder['event_day'], $reminder['event_month']),
            'shop_phone' => $this->setting->get('shop_phone', '123 456 789'),
        ]);

        return $this->send($customer['email'], $subject, $body);
    }

    /**
     * Send subscription expiration reminder
     */
    public function sendExpirationReminderEmail(array $customer, array $subscription, int $daysLeft): bool
    {
        $subject = $this->setting->get('email_expiration_subject', 'Vaše předplatné Připomněnka brzy vyprší ⏰');

        $qrCodeUrl = $this->generateQrCodeUrl($subscription);

        $body = $this->renderTemplate('expiration_reminder', [
            'customer' => $customer,
            'subscription' => $subscription,
            'days_left' => $daysLeft,
            'qr_code_url' => $qrCodeUrl,
            'bank_account' => $this->setting->get('bank_account', '123456789/0100'),
            'shop_phone' => $this->setting->get('shop_phone', '123 456 789'),
        ]);

        return $this->send($customer['email'], $subject, $body);
    }

    /**
     * Send OTP code email
     */
    public function sendOtpEmail(array $customer, string $code): bool
    {
        $subject = 'Váš přihlašovací kód do Připomněnky';

        $body = $this->renderTemplate('otp', [
            'customer' => $customer,
            'code' => $code,
            'shop_phone' => $this->setting->get('shop_phone', '123 456 789'),
        ]);

        return $this->send($customer['email'], $subject, $body);
    }

    /**
     * Send admin password reset email
     */
    public function sendAdminPasswordResetEmail(string $adminEmail, string $adminName, string $resetUrl): bool
    {
        $subject = 'Obnova hesla do administrace Připomněnky';

        $body = $this->getAdminPasswordResetTemplate([
            'admin_name' => $adminName,
            'reset_url' => $resetUrl,
        ]);

        return $this->send($adminEmail, $subject, $body);
    }

    /**
     * Send admin daily summary email
     */
    public function sendAdminSummaryEmail(string $adminEmail, array $stats): bool
    {
        $subject = 'Připomněnka: Denní přehled - ' . date('j.n.Y');

        $body = $this->renderTemplate('admin_summary', [
            'stats' => $stats,
            'date' => date('j. n. Y'),
        ]);

        return $this->send($adminEmail, $subject, $body);
    }

    /**
     * Generate QR code URL for payment
     */
    private function generateQrCodeUrl(array $subscription): string
    {
        $iban = $this->setting->get('bank_iban', 'CZ1234567890123456789012');
        $amount = $subscription['price'];
        $vs = $subscription['variable_symbol'];
        $message = 'Pripomnenka ' . $vs;

        // Generate SPAYD (Short Payment Descriptor) format
        $spayd = sprintf(
            'SPD*1.0*ACC:%s*AM:%.2f*CC:CZK*X-VS:%s*MSG:%s',
            $iban,
            $amount,
            $vs,
            $message
        );

        // Use external QR code generator API
        // In production, consider using a local library
        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($spayd);
    }

    /**
     * Parse subject with variables
     */
    private function parseSubject(string $template, array $reminder): string
    {
        $replacements = [
            '{{event_type}}' => translate_event_type($reminder['event_type']),
            '{{recipient}}' => translate_relation($reminder['recipient_relation']),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Render email template
     */
    private function renderTemplate(string $template, array $data): string
    {
        $templatePath = __DIR__ . '/../Views/emails/' . $template . '.php';

        if (!file_exists($templatePath)) {
            // Fallback to simple text template
            return $this->renderFallbackTemplate($template, $data);
        }

        extract($data);
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }

    /**
     * Render fallback template when file doesn't exist
     */
    private function renderFallbackTemplate(string $template, array $data): string
    {
        switch ($template) {
            case 'activation':
                return $this->getActivationTemplate($data);
            case 'payment_qr':
                return $this->getPaymentQrTemplate($data);
            case 'event_reminder':
                return $this->getEventReminderTemplate($data);
            case 'expiration_reminder':
                return $this->getExpirationReminderTemplate($data);
            case 'otp':
                return $this->getOtpTemplate($data);
            case 'admin_summary':
                return $this->getAdminSummaryTemplate($data);
            default:
                return '';
        }
    }

    /**
     * Get activation email template
     */
    private function getActivationTemplate(array $data): string
    {
        $name = $data['customer']['name'] ? ", {$data['customer']['name']}" : '';

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Vítejte v Připomněnce!</title>
</head>
<body style="font-family: Georgia, serif; line-height: 1.6; color: #544a26; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #3e6ea1; margin: 0;">Vítejte v Připomněnce! 🦌</h1>
    </div>

    <p>Dobrý den{$name}!</p>

    <p>Děkujeme, že jste se přidal/a k Připomněnce od Jelenů v zeleni.</p>

    <p>Teď si nastavte, jaká data vám máme hlídat:</p>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{$data['activation_url']}" style="background-color: #b87333; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">NASTAVIT PŘIPOMÍNKY →</a>
    </div>

    <p style="color: #888; font-size: 14px;">Odkaz platí 30 dní. Pokud vyprší, ozvěte se nám a pošleme nový.</p>

    <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">

    <p style="text-align: center; color: #666;">
        S pozdravem,<br>
        <strong>Vaše květinářství Jeleni v zeleni 🌷</strong><br>
        Tel: {$data['shop_phone']}
    </p>
</body>
</html>
HTML;
    }

    /**
     * Get payment QR email template
     */
    private function getPaymentQrTemplate(array $data): string
    {
        $amount = number_format($data['subscription']['price'], 0, ',', ' ');

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>QR kód pro platbu</title>
</head>
<body style="font-family: Georgia, serif; line-height: 1.6; color: #544a26; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #3e6ea1; margin: 0;">QR kód pro platbu 💳</h1>
    </div>

    <p>Dobrý den!</p>

    <p>Pro aktivaci služby Připomněnka prosím uhraďte:</p>

    <div style="text-align: center; margin: 30px 0; padding: 20px; background: #f9f9f9; border-radius: 10px;">
        <img src="{$data['qr_code_url']}" alt="QR kód pro platbu" style="max-width: 200px;">

        <p style="margin-top: 20px;">
            <strong>Částka:</strong> {$amount} Kč<br>
            <strong>Účet:</strong> {$data['bank_account']}<br>
            <strong>VS:</strong> {$data['subscription']['variable_symbol']}
        </p>
    </div>

    <p>Po připsání platby vám automaticky pošleme aktivační odkaz (obvykle do 24 hodin).</p>

    <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">

    <p style="text-align: center; color: #666;">
        S pozdravem,<br>
        <strong>Jeleni v zeleni 🦌</strong><br>
        Tel: {$data['shop_phone']}
    </p>
</body>
</html>
HTML;
    }

    /**
     * Get event reminder email template
     */
    private function getEventReminderTemplate(array $data): string
    {
        $name = $data['customer']['name'] ? ", {$data['customer']['name']}" : '';

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Blíží se důležité datum!</title>
</head>
<body style="font-family: Georgia, serif; line-height: 1.6; color: #544a26; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #3e6ea1; margin: 0;">Blíží se {$data['event_type']}! 🎂</h1>
    </div>

    <p>Dobrý den{$name}!</p>

    <p>Za několik dní, <strong>{$data['date']}</strong>, má {$data['recipient']} {$data['event_type']}.</p>

    <p>Brzy vám zavoláme, abychom společně vybrali tu pravou kytici.</p>

    <p>Nechcete čekat? Ozvěte se nám:<br>
    📞 <a href="tel:+420{$data['shop_phone']}" style="color: #3e6ea1;">{$data['shop_phone']}</a></p>

    <div style="background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <strong>🎁 Nezapomeňte:</strong> máte <strong>10% slevu</strong> na všechny kytice!
    </div>

    <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">

    <p style="text-align: center; color: #666;">
        <strong>Vaši Jeleni v zeleni 🦌</strong>
    </p>
</body>
</html>
HTML;
    }

    /**
     * Get expiration reminder email template
     */
    private function getExpirationReminderTemplate(array $data): string
    {
        $amount = number_format($data['subscription']['price'], 0, ',', ' ');
        $daysText = $data['days_left'] == 1 ? 'den' : ($data['days_left'] < 5 ? 'dny' : 'dní');

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Předplatné brzy vyprší</title>
</head>
<body style="font-family: Georgia, serif; line-height: 1.6; color: #544a26; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #d4853a; margin: 0;">Vaše Připomněnka brzy vyprší ⏰</h1>
    </div>

    <p>Dobrý den!</p>

    <p>Vaše předplatné Připomněnky vyprší za <strong>{$data['days_left']} {$daysText}</strong>.</p>

    <p>Chcete pokračovat? Stačí zaplatit:</p>

    <div style="text-align: center; margin: 30px 0; padding: 20px; background: #f9f9f9; border-radius: 10px;">
        <img src="{$data['qr_code_url']}" alt="QR kód pro platbu" style="max-width: 200px;">

        <p style="margin-top: 20px;">
            <strong>Částka:</strong> {$amount} Kč<br>
            <strong>VS:</strong> {$data['subscription']['variable_symbol']}
        </p>
    </div>

    <p>Po zaplacení se předplatné automaticky prodlouží o další rok.</p>

    <p style="color: #888; font-size: 14px;">Pokud nechcete pokračovat, nemusíte nic dělat. Vaše data zůstanou uložená pro případ, že si to rozmyslíte.</p>

    <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">

    <p style="text-align: center; color: #666;">
        Díky, že jste s námi! 🦌<br>
        <strong>Jeleni v zeleni</strong>
    </p>
</body>
</html>
HTML;
    }

    /**
     * Get OTP email template
     */
    private function getOtpTemplate(array $data): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Váš přihlašovací kód</title>
</head>
<body style="font-family: Georgia, serif; line-height: 1.6; color: #544a26; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #3e6ea1; margin: 0;">Váš přihlašovací kód 🔐</h1>
    </div>

    <p>Dobrý den!</p>

    <p>Pro přihlášení do Připomněnky použijte tento kód:</p>

    <div style="text-align: center; margin: 30px 0;">
        <div style="font-size: 36px; font-weight: bold; letter-spacing: 4px; background: #f0f0f0; padding: 20px; border-radius: 10px; font-family: monospace; user-select: all;">
            {$data['code']}
        </div>
    </div>

    <p style="color: #888; font-size: 14px;">Kód platí 10 minut. Pokud jste o přihlášení nežádali, tento e-mail ignorujte.</p>

    <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">

    <p style="text-align: center; color: #666;">
        <strong>Jeleni v zeleni 🦌</strong><br>
        Tel: {$data['shop_phone']}
    </p>
</body>
</html>
HTML;
    }

    /**
     * Get admin summary email template
     */
    private function getAdminSummaryTemplate(array $data): string
    {
        $stats = $data['stats'];

        $callsToday = $stats['calls_today'] ?? 0;
        $awaitingActivation = $stats['awaiting_activation'] ?? 0;
        $unmatchedPayments = $stats['unmatched_payments'] ?? 0;
        $expiringThisWeek = $stats['expiring_this_week'] ?? 0;

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Denní přehled</title>
</head>
<body style="font-family: Georgia, serif; line-height: 1.6; color: #544a26; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #3e6ea1; margin: 0;">Denní přehled 📊</h1>
        <p style="color: #888;">{$data['date']}</p>
    </div>

    <div style="background: #f9f9f9; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
        <h2 style="margin-top: 0; color: #3e6ea1;">📞 Dnes volat</h2>
        <p style="font-size: 24px; font-weight: bold; margin: 0;">{$callsToday} zákazníků</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
        <div style="background: #e3f2fd; padding: 15px; border-radius: 10px;">
            <p style="margin: 0; color: #666;">Čeká na aktivaci</p>
            <p style="font-size: 20px; font-weight: bold; margin: 5px 0 0;">{$awaitingActivation}</p>
        </div>

        <div style="background: #ffebee; padding: 15px; border-radius: 10px;">
            <p style="margin: 0; color: #666;">Nespárované platby</p>
            <p style="font-size: 20px; font-weight: bold; margin: 5px 0 0; color: #c0392b;">{$unmatchedPayments}</p>
        </div>

        <div style="background: #fff3e0; padding: 15px; border-radius: 10px;">
            <p style="margin: 0; color: #666;">Expiruje tento týden</p>
            <p style="font-size: 20px; font-weight: bold; margin: 5px 0 0;">{$expiringThisWeek}</p>
        </div>
    </div>

    <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">

    <p style="text-align: center;">
        <a href="{$this->config['app']['url']}/admin" style="color: #3e6ea1;">Otevřít administraci →</a>
    </p>
</body>
</html>
HTML;
    }

    /**
     * Get admin password reset email template
     */
    private function getAdminPasswordResetTemplate(array $data): string
    {
        $name = $data['admin_name'] ? ", {$data['admin_name']}" : '';

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Obnova hesla</title>
</head>
<body style="font-family: Georgia, serif; line-height: 1.6; color: #544a26; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #3e6ea1; margin: 0;">Obnova hesla</h1>
    </div>

    <p>Dobrý den{$name}!</p>

    <p>Obdrželi jsme žádost o obnovu hesla do administrace Připomněnky.</p>

    <p>Pro nastavení nového hesla klikněte na tlačítko níže:</p>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{$data['reset_url']}" style="background-color: #b87333; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">NASTAVIT NOVÉ HESLO</a>
    </div>

    <p style="color: #888; font-size: 14px;">Odkaz platí 1 hodinu. Pokud jste o obnovu hesla nežádali, tento email ignorujte.</p>

    <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">

    <p style="text-align: center; color: #666;">
        <strong>Připomněnka — Jeleni v zeleni</strong>
    </p>
</body>
</html>
HTML;
    }

    /**
     * Send email
     */
    private function send(string $to, string $subject, string $body): bool
    {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $this->formatAddress($this->fromAddress, $this->fromName),
            'Reply-To: ' . $this->fromAddress,
            'X-Mailer: PHP/' . phpversion(),
        ];

        // Check if we should use SMTP
        if (!empty($this->config['email']['smtp']['host'])) {
            return $this->sendViaSMTP($to, $subject, $body, $headers);
        }

        // Use PHP mail() function (recommended for shared hosting)
        return mail($to, $subject, $body, implode("\r\n", $headers));
    }

    /**
     * Send email via SMTP (if configured)
     */
    private function sendViaSMTP(string $to, string $subject, string $body, array $headers): bool
    {
        // This is a simplified SMTP implementation
        // For production, consider using PHPMailer or similar library

        $smtp = $this->config['email']['smtp'];

        try {
            $socket = @fsockopen($smtp['host'], $smtp['port'], $errno, $errstr, 30);

            if (!$socket) {
                error_log("SMTP connection failed: $errstr ($errno)");
                return false;
            }

            // Read greeting
            fgets($socket, 515);

            // EHLO
            fputs($socket, "EHLO " . gethostname() . "\r\n");
            while ($line = fgets($socket, 515)) {
                if (substr($line, 3, 1) == ' ') break;
            }

            // STARTTLS if port 587
            if ($smtp['port'] == 587) {
                fputs($socket, "STARTTLS\r\n");
                fgets($socket, 515);
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

                fputs($socket, "EHLO " . gethostname() . "\r\n");
                while ($line = fgets($socket, 515)) {
                    if (substr($line, 3, 1) == ' ') break;
                }
            }

            // AUTH LOGIN
            fputs($socket, "AUTH LOGIN\r\n");
            fgets($socket, 515);
            fputs($socket, base64_encode($smtp['user']) . "\r\n");
            fgets($socket, 515);
            fputs($socket, base64_encode($smtp['pass']) . "\r\n");
            $response = fgets($socket, 515);

            if (substr($response, 0, 3) != '235') {
                error_log("SMTP auth failed: $response");
                fclose($socket);
                return false;
            }

            // MAIL FROM
            fputs($socket, "MAIL FROM:<{$this->fromAddress}>\r\n");
            fgets($socket, 515);

            // RCPT TO
            fputs($socket, "RCPT TO:<$to>\r\n");
            fgets($socket, 515);

            // DATA
            fputs($socket, "DATA\r\n");
            fgets($socket, 515);

            // Headers and body
            $message = implode("\r\n", $headers) . "\r\n";
            $message .= "To: $to\r\n";
            $message .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
            $message .= "\r\n";
            $message .= $body;
            $message .= "\r\n.\r\n";

            fputs($socket, $message);
            fgets($socket, 515);

            // QUIT
            fputs($socket, "QUIT\r\n");
            fclose($socket);

            return true;

        } catch (Exception $e) {
            error_log("SMTP error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Format email address with name
     */
    private function formatAddress(string $email, string $name = ''): string
    {
        if (empty($name)) {
            return $email;
        }

        // Encode name if it contains special characters
        if (preg_match('/[^\x20-\x7E]/', $name)) {
            $name = '=?UTF-8?B?' . base64_encode($name) . '?=';
        }

        return "$name <$email>";
    }
}
