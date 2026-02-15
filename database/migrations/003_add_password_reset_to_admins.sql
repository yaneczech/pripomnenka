-- Migration: Add password reset token fields to admins table
-- Run this on existing databases

ALTER TABLE admins ADD COLUMN password_reset_token VARCHAR(64) DEFAULT NULL AFTER name;
ALTER TABLE admins ADD COLUMN password_reset_expires_at DATETIME DEFAULT NULL AFTER password_reset_token;
