-- ============================================
-- Připomněnka - Databázové schéma
-- Květinářství Jeleni v zeleni
-- ============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------
-- Tabulka: admins (Administrátoři)
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------
-- Tabulka: subscription_plans (Varianty předplatného)
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS subscription_plans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    price DECIMAL(10,2) NOT NULL,
    reminder_limit TINYINT UNSIGNED NOT NULL,
    discount_percent TINYINT UNSIGNED DEFAULT 10,
    is_available BOOLEAN DEFAULT TRUE,
    is_default BOOLEAN DEFAULT FALSE,
    sort_order TINYINT UNSIGNED DEFAULT 0,
    description TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_available (is_available),
    INDEX idx_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------
-- Tabulka: customers (Zákazníci)
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS customers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL UNIQUE,
    phone_hash VARCHAR(64) NOT NULL,
    email VARCHAR(255) NOT NULL,
    email_hash VARCHAR(64) NOT NULL,
    name VARCHAR(100) DEFAULT NULL,
    password_hash VARCHAR(255) DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    gdpr_consent_at DATETIME DEFAULT NULL,
    gdpr_consent_text TEXT DEFAULT NULL,
    last_login_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_phone_hash (phone_hash),
    INDEX idx_email_hash (email_hash),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------
-- Tabulka: subscriptions (Předplatné)
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS subscriptions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    plan_id INT UNSIGNED NOT NULL,
    reminder_limit TINYINT UNSIGNED NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    price_paid DECIMAL(10,2) DEFAULT NULL,
    variable_symbol VARCHAR(10) NOT NULL UNIQUE,
    starts_at DATE DEFAULT NULL,
    expires_at DATE DEFAULT NULL,
    payment_method ENUM('cash', 'card', 'bank_transfer') NOT NULL,
    payment_status ENUM('pending', 'paid', 'mismatched') DEFAULT 'pending',
    payment_confirmed_at DATETIME DEFAULT NULL,
    payment_confirmed_by INT UNSIGNED DEFAULT NULL,
    payment_note VARCHAR(255) DEFAULT NULL,
    activation_token VARCHAR(64) DEFAULT NULL,
    activation_token_expires_at DATETIME DEFAULT NULL,
    activated_at DATETIME DEFAULT NULL,
    status ENUM('awaiting_payment', 'awaiting_activation', 'active', 'expired', 'cancelled') DEFAULT 'awaiting_payment',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES subscription_plans(id),
    FOREIGN KEY (payment_confirmed_by) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_customer (customer_id),
    INDEX idx_status (status),
    INDEX idx_expires (expires_at),
    INDEX idx_vs (variable_symbol),
    INDEX idx_payment_status (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------
-- Tabulka: vs_counter (Čítač variabilních symbolů)
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS vs_counter (
    year SMALLINT UNSIGNED PRIMARY KEY,
    last_number INT UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------
-- Tabulka: unmatched_payments (Nespárované platby)
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS unmatched_payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    amount DECIMAL(10,2) NOT NULL,
    variable_symbol VARCHAR(20) DEFAULT NULL,
    sender_name VARCHAR(255) DEFAULT NULL,
    received_at DATETIME NOT NULL,
    raw_email_data TEXT DEFAULT NULL,
    matched_to_subscription_id INT UNSIGNED DEFAULT NULL,
    matched_at DATETIME DEFAULT NULL,
    matched_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (matched_to_subscription_id) REFERENCES subscriptions(id) ON DELETE SET NULL,
    FOREIGN KEY (matched_by) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_vs (variable_symbol),
    INDEX idx_unmatched (matched_to_subscription_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------
-- Tabulka: reminders (Připomínky)
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS reminders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    event_type ENUM('birthday', 'nameday', 'wedding_anniversary', 'relationship_anniversary', 'mothers_day', 'fathers_day', 'valentines', 'womens_day', 'school_year_end', 'other') NOT NULL,
    recipient_relation ENUM('wife', 'husband', 'mother', 'father', 'daughter', 'son', 'grandmother', 'grandfather', 'sister', 'brother', 'mother_in_law', 'father_in_law', 'partner', 'friend', 'colleague', 'other') NOT NULL,
    event_day TINYINT UNSIGNED NOT NULL,
    event_month TINYINT UNSIGNED NOT NULL,
    advance_days TINYINT UNSIGNED DEFAULT 5,
    price_range ENUM('under_500', '500_800', '800_1200', '1200_2000', 'over_2000', 'to_discuss') DEFAULT 'to_discuss',
    customer_note TEXT DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_customer (customer_id),
    INDEX idx_date (event_month, event_day),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------
-- Tabulka: call_logs (Historie provolání)
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS call_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reminder_id INT UNSIGNED NOT NULL,
    call_date DATE NOT NULL,
    status ENUM('completed', 'no_answer', 'declined', 'postponed') NOT NULL,
    order_amount DECIMAL(10,2) DEFAULT NULL,
    admin_note TEXT DEFAULT NULL,
    postponed_to DATE DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reminder_id) REFERENCES reminders(id) ON DELETE CASCADE,
    INDEX idx_reminder (reminder_id),
    INDEX idx_date (call_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------
-- Tabulka: customer_notes (Interní poznámky)
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS customer_notes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    preferred_flowers TEXT DEFAULT NULL,
    typical_budget VARCHAR(50) DEFAULT NULL,
    preferred_call_time ENUM('morning', 'afternoon', 'evening', 'anytime') DEFAULT 'anytime',
    general_note TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------
-- Tabulka: settings (Nastavení systému)
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------
-- Tabulka: login_attempts (Rate limiting)
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_identifier (identifier),
    INDEX idx_ip (ip_address),
    INDEX idx_time (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------
-- Tabulka: otp_codes (Jednorázové kódy)
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS otp_codes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    code VARCHAR(6) NOT NULL,
    attempts TINYINT UNSIGNED DEFAULT 0,
    expires_at DATETIME NOT NULL,
    used_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_customer (customer_id),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------
-- Tabulka: call_queue (Fronta k provolání)
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS call_queue (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reminder_id INT UNSIGNED NOT NULL,
    scheduled_date DATE NOT NULL,
    attempt_count TINYINT UNSIGNED DEFAULT 1,
    priority TINYINT UNSIGNED DEFAULT 0,
    status ENUM('pending', 'completed', 'no_answer', 'declined', 'postponed', 'gave_up') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reminder_id) REFERENCES reminders(id) ON DELETE CASCADE,
    UNIQUE KEY unique_reminder_date (reminder_id, scheduled_date),
    INDEX idx_date (scheduled_date),
    INDEX idx_status (status),
    INDEX idx_priority (priority DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- Výchozí data
-- ============================================

-- Varianty předplatného
INSERT INTO subscription_plans (name, slug, price, reminder_limit, discount_percent, is_available, is_default, sort_order, description) VALUES
('Early bird', 'early_bird', 75.00, 5, 10, TRUE, FALSE, 1, 'Zvýhodněná cena pro první zákazníky. 5 připomínek, 10% sleva na kytice.'),
('Standard', 'standard', 150.00, 10, 10, TRUE, TRUE, 2, 'Plná verze služby. 10 připomínek, 10% sleva na kytice.');

-- Výchozí nastavení
INSERT INTO settings (setting_key, setting_value) VALUES
('default_advance_days', '5'),
('workdays', '1,2,3,4,5'),
('email_customer_reminder_subject', 'Blíží se důležité datum! 💐'),
('email_customer_reminder_template', 'Dobrý den{{#name}}, {{name}}{{/name}}!\n\nBlíží se {{event_type}} ({{recipient}}) dne {{date}}.\n\nBrzy vám zavoláme z květinářství Jeleni v zeleni.\n\nPokud nechcete čekat: {{shop_phone}}'),
('email_activation_subject', 'Vítejte v Připomněnce! Nastavte si své připomínky 💐'),
('email_payment_qr_subject', 'QR kód pro platbu předplatného Připomněnka'),
('email_expiration_subject', 'Vaše předplatné Připomněnka brzy vyprší'),
('shop_phone', '123456789'),
('shop_email', 'info@jelenivzeleni.cz'),
('shop_name', 'Jeleni v zeleni'),
('bank_account', '123456789/0100'),
('bank_iban', 'CZ1234567890123456789012'),
('bank_imap_host', 'imap.airbank.cz'),
('bank_imap_email', ''),
('bank_imap_password', ''),
('activation_link_validity_days', '30');

-- Inicializace čítače VS pro aktuální rok
INSERT INTO vs_counter (year, last_number) VALUES (YEAR(CURRENT_DATE), 0);
