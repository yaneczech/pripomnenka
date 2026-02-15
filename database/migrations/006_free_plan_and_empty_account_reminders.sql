-- Migrace 006: Bezplatný tarif + sledování připomínek prázdného účtu
-- Služba bude zdarma, bez slevy na kytice

-- Aktualizovat výchozí tarif na bezplatný
UPDATE subscription_plans SET
    name = 'Zdarma',
    slug = 'free',
    price = 0.00,
    reminder_limit = 5,
    discount_percent = 0,
    is_available = TRUE,
    is_default = TRUE,
    sort_order = 1,
    description = 'Bezplatná služba. 5 připomínek, osobní telefonát před každou událostí.'
WHERE slug = 'standard' OR is_default = TRUE
LIMIT 1;

-- Deaktivovat Early bird (není potřeba při bezplatném tarifu)
UPDATE subscription_plans SET is_available = FALSE, show_on_landing = FALSE WHERE slug = 'early_bird';

-- Přidat sloupce pro sledování připomínek prázdného účtu
ALTER TABLE customers
    ADD COLUMN empty_reminder_count TINYINT UNSIGNED DEFAULT 0 AFTER last_login_at,
    ADD COLUMN empty_reminder_last_sent_at DATETIME DEFAULT NULL AFTER empty_reminder_count;

-- Nastavení pro e-mail připomínky prázdného účtu
INSERT INTO settings (setting_key, setting_value) VALUES
    ('email_empty_account_subject', 'Zatím nemáte nastavené žádné připomínky 📅'),
    ('empty_reminder_delay_days', '3'),
    ('empty_reminder_max_count', '2')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
