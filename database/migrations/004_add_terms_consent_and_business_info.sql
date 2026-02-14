-- Migration: Add terms consent field and business info settings
-- Run this on existing databases

-- Add terms consent timestamp to customers
ALTER TABLE customers ADD COLUMN terms_consent_at DATETIME DEFAULT NULL AFTER gdpr_consent_text;

-- Add business info settings (for terms page and GDPR page)
INSERT INTO settings (setting_key, setting_value) VALUES
('shop_name_full', 'Jeleni v zeleni'),
('shop_address', 'Palackého 1308/32, 586 01 Jihlava'),
('shop_ico', '14111250'),
('shop_owner', 'Sofie Janečková'),
('terms_effective_date', '2026-02-14')
ON DUPLICATE KEY UPDATE setting_value = setting_value;
