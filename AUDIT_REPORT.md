# Audit Systému Připomněnka

Datum: 2026-01-29
Status: ✅ **VŠECHNY KRITICKÉ PROBLÉMY OPRAVENY**

## 🔴 Kritické problémy (OPRAVENO)

### 1. CRON generate-call-list.php - Nekontroluje is_active zákazníka
**Soubor:** `/cron/generate-call-list.php:70-78`
**Problém:** SQL dotaz nenačítá sloupec `c.is_active` a nekontroluje ho, takže deaktivovaní zákazníci mohou být stále přidáváni do call queue.

**Aktuální kód:**
```sql
SELECT r.*, c.phone, c.email, c.name as customer_name
FROM reminders r
JOIN customers c ON r.customer_id = c.id
JOIN subscriptions s ON c.id = s.customer_id
WHERE r.is_active = 1
  AND s.status = 'active'
  AND s.expires_at >= CURDATE()
```

**Mělo by být:**
```sql
SELECT r.*, c.phone, c.email, c.name as customer_name
FROM reminders r
JOIN customers c ON r.customer_id = c.id
JOIN subscriptions s ON c.id = s.customer_id
WHERE r.is_active = 1
  AND c.is_active = 1  -- PŘIDAT TUTO PODMÍNKU
  AND s.status = 'active'
  AND s.expires_at >= CURDATE()
```

---

### 2. CallQueue Model - Nekontroluje is_active zákazníka
**Soubor:** `/src/Models/CallQueue.php:23-45`
**Problém:** Metoda `regenerateForReminder` nekontroluje, zda je zákazník aktivní.

**Řešení:** Přidat kontrolu `c.is_active = 1` do SQL dotazu.

---

### 3. Subscription Model - Špatný výpočet expirace při obnově
**Soubor:** `/src/Models/Subscription.php:146-172, 177-220`
**Problém:** Při obnově předplatného se nastavuje `expires_at` na `+1 year` od dnešního data, místo aby se přičetl rok k existujícímu datu expirace.

**Scénář:**
- Předplatné vyprší 15. dubna
- Zákazník obnoví 1. dubna (14 dní před expirací)
- **AKTUÁLNĚ:** Nové expires_at = 1. dubna příštího roku (ztratil 14 dní!)
- **MĚLO BY BÝT:** Nové expires_at = 15. dubna příštího roku

**Řešení:** Kontrolovat existující `expires_at` a pokud je v budoucnu, přidat rok k němu.

---

## ⚠️ Střední priority

### 4. OTP kódy - Chybí cleanup starých kódů
**Problém:** Tabulka `otp_codes` se nikdy nečistí od starých/vypršených kódů.

**Řešení:** Přidat CRON úlohu nebo automatický cleanup při vytváření nového kódu.

---

### 5. Login attempts - Chybí cleanup starých pokusů
**Problém:** Tabulka `login_attempts` se nikdy nečistí.

**Řešení:** Přidat CRON úlohu pro mazání záznamů starších než 24 hodin.

---

### 6. Call queue - Možné duplicity
**Problém:** Pokud zákazník rychle upraví připomínku 2x po sobě, mohly by vzniknout duplicity v `call_queue`.

**Řešení:** Je tam `UNIQUE KEY unique_reminder_date (reminder_id, scheduled_date)`, takže databáze by měla zabránit duplicitám, ale mělo by se to ošetřit i v kódu.

---

## ℹ️ Doporučení

### 7. GDPR - Retention policy
**Problém:** Systém nemá automatické mazání neaktivních účtů po 2 letech (jak je ve specifikaci).

**Řešení:** Přidat CRON úlohu pro kontrolu a upozornění/mazání neaktivních účtů.

---

### 8. Email templates - Tvrdě zakódované texty
**Problém:** V `EmailService.php` jsou email šablony přímo v kódu, ne v databázi.

**Status:** Toto je OK pro MVP, ale do budoucna by měly být editovatelné v administraci.

---

### 9. Security headers - Chybí implementace
**Problém:** V CLAUDE.md jsou specifikované security headers, ale nejsou nikde implementované.

**Řešení:** Přidat do hlavního index.php nebo do .htaccess.

---

## ✅ Co funguje správně

- Foreign keys a CASCADE DELETE jsou správně nastaveny
- Indexy jsou na správných sloupcích
- CSRF ochrana je implementována
- Prepared statements (PDO) všude použity správně
- Password hashing s bcrypt
- Token generování pomocí `random_bytes()`
- Unikátní constrainty na správných místech

---

## Priority oprav

1. **Vysoká priorita:**
   - Opravit CRON generate-call-list (is_active check)
   - Opravit CallQueue Model (is_active check)
   - Opravit výpočet expirace při obnově předplatného

2. **Střední priorita:**
   - Přidat cleanup pro OTP kódy
   - Přidat cleanup pro login attempts
   - Ošetřit duplicity v call queue

3. **Nízká priorita:**
   - Implementovat GDPR retention policy
   - Přidat security headers
