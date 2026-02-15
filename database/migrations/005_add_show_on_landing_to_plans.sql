-- Migration 005: Add show_on_landing column to subscription_plans
-- Allows hiding specific plans from the landing page pricing section

ALTER TABLE subscription_plans
ADD COLUMN show_on_landing BOOLEAN DEFAULT TRUE AFTER is_default;
