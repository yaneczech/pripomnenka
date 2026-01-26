-- Migration: Add is_active column to customers table
-- Run this on existing databases

ALTER TABLE customers ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER password_hash;
ALTER TABLE customers ADD INDEX idx_is_active (is_active);
