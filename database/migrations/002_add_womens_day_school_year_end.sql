-- Migrace: Přidání MDŽ a Konce školního roku do event_type
-- Datum: 29. 1. 2026

ALTER TABLE reminders
MODIFY COLUMN event_type ENUM(
    'birthday',
    'nameday',
    'wedding_anniversary',
    'relationship_anniversary',
    'mothers_day',
    'fathers_day',
    'valentines',
    'womens_day',
    'school_year_end',
    'other'
) NOT NULL;
