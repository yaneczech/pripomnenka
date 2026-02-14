<?php
/**
 * Připomněnka - Definice rout
 *
 * Formát: 'METHOD /cesta' => ['Controller', 'metoda', 'middleware']
 * Middleware: 'auth' = vyžaduje přihlášení zákazníka
 *             'admin' = vyžaduje přihlášení admina
 *             null = veřejně přístupné
 */

return [
    // ==========================================
    // Veřejná část
    // ==========================================
    'GET /' => ['HomeController', 'index', null],
    'GET /registrace' => ['AuthController', 'showRegister', null],
    'POST /registrace' => ['AuthController', 'register', null],
    'GET /prihlaseni' => ['AuthController', 'showLogin', null],
    'POST /prihlaseni' => ['AuthController', 'login', null],
    'POST /odhlaseni' => ['AuthController', 'logout', null],
    'GET /overeni/{token}' => ['AuthController', 'verifyOtp', null],
    'POST /overeni' => ['AuthController', 'submitOtp', null],

    // Aktivace účtu (z emailu)
    'GET /aktivace/{token}' => ['ActivationController', 'showActivation', null],
    'POST /aktivace/{token}' => ['ActivationController', 'processActivation', null],

    // GDPR
    'GET /ochrana-udaju' => ['CustomerController', 'gdprInfo', null],

    // ==========================================
    // Zákaznická sekce (vyžaduje přihlášení)
    // ==========================================
    'GET /moje-pripominky' => ['ReminderController', 'index', 'auth'],
    'GET /nova-pripominka' => ['ReminderController', 'create', 'auth'],
    'POST /nova-pripominka' => ['ReminderController', 'store', 'auth'],
    'GET /pripominka/{id}' => ['ReminderController', 'edit', 'auth'],
    'POST /pripominka/{id}' => ['ReminderController', 'update', 'auth'],
    'POST /pripominka/{id}/smazat' => ['ReminderController', 'delete', 'auth'],
    'GET /profil' => ['CustomerController', 'profile', 'auth'],
    'POST /profil' => ['CustomerController', 'updateProfile', 'auth'],
    'GET /export-dat' => ['CustomerController', 'exportData', 'auth'],
    'POST /smazat-ucet' => ['CustomerController', 'deleteAccount', 'auth'],

    // ==========================================
    // Administrace (vyžaduje admin přihlášení)
    // ==========================================
    'GET /admin' => ['AdminController', 'dashboard', 'admin'],
    'GET /admin/prihlaseni' => ['AdminAuthController', 'showLogin', null],
    'POST /admin/prihlaseni' => ['AdminAuthController', 'login', null],
    'POST /admin/odhlaseni' => ['AdminAuthController', 'logout', 'admin'],
    'GET /admin/zapomnene-heslo' => ['AdminAuthController', 'showForgotPassword', null],
    'POST /admin/zapomnene-heslo' => ['AdminAuthController', 'forgotPassword', null],
    'GET /admin/reset-hesla/{token}' => ['AdminAuthController', 'showResetPassword', null],
    'POST /admin/reset-hesla/{token}' => ['AdminAuthController', 'resetPassword', null],

    // Seznam k provolání
    'GET /admin/dnes' => ['CallListController', 'today', 'admin'],
    'GET /admin/tyden' => ['CallListController', 'week', 'admin'],
    'POST /admin/volani/{id}' => ['CallListController', 'logCall', 'admin'],

    // Správa zákazníků
    'GET /admin/zakaznici' => ['AdminCustomerController', 'index', 'admin'],
    'GET /admin/zakaznik/{id}' => ['AdminCustomerController', 'show', 'admin'],
    'POST /admin/zakaznik/{id}' => ['AdminCustomerController', 'update', 'admin'],
    'GET /admin/novy-zakaznik' => ['AdminCustomerController', 'create', 'admin'],
    'POST /admin/novy-zakaznik' => ['AdminCustomerController', 'store', 'admin'],
    'POST /admin/zakaznik/{id}/smazat' => ['AdminCustomerController', 'delete', 'admin'],
    'POST /admin/zakaznik/{id}/toggle-active' => ['AdminCustomerController', 'toggleActive', 'admin'],
    'POST /admin/zakaznik/{id}/email-aktivace' => ['AdminCustomerController', 'resendActivation', 'admin'],
    'POST /admin/zakaznik/{id}/email-qr' => ['AdminCustomerController', 'resendPaymentQr', 'admin'],

    // Správa předplatného
    'GET /admin/predplatne' => ['SubscriptionController', 'index', 'admin'],
    'POST /admin/predplatne/{id}/potvrdit' => ['SubscriptionController', 'confirmPayment', 'admin'],
    'GET /admin/platby' => ['PaymentController', 'unmatched', 'admin'],
    'POST /admin/platby/{id}/priradit' => ['PaymentController', 'match', 'admin'],

    // Nastavení
    'GET /admin/nastaveni' => ['SettingsController', 'index', 'admin'],
    'POST /admin/nastaveni' => ['SettingsController', 'update', 'admin'],
    'GET /admin/nastaveni/plany' => ['SettingsController', 'plans', 'admin'],
    'POST /admin/nastaveni/plany' => ['SettingsController', 'updatePlans', 'admin'],
    'GET /admin/nastaveni/emaily' => ['SettingsController', 'emailPreviews', 'admin'],
    'GET /admin/nastaveni/emaily/nahled/{type}' => ['SettingsController', 'emailPreview', 'admin'],

    // Debug
    'GET /admin/debug/reminders' => ['DebugController', 'reminders', 'admin'],

    // ==========================================
    // CRON endpointy (chráněné tokenem)
    // ==========================================
    'GET /cron/generate-queue' => ['CronController', 'generateQueue', 'cron'],
    'GET /cron/send-emails' => ['CronController', 'sendEmails', 'cron'],
    'GET /cron/admin-summary' => ['CronController', 'adminSummary', 'cron'],
    'GET /cron/process-payments' => ['CronController', 'processPayments', 'cron'],
    'GET /cron/expiration-reminders' => ['CronController', 'expirationReminders', 'cron'],
    'GET /cron/cleanup' => ['CronController', 'cleanup', 'cron'],
];
