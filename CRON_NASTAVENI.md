# CRON Úlohy - Kompletní přehled a nastavení

## 📋 Přehled všech CRON úloh

Systém Připomněnka používá 5 automatických úloh, které se starají o generování připomínek, odesílání emailů a údržbu databáze.

---

## 1️⃣ Generování fronty volání

**Účel:** Vytváří seznam zákazníků k provolání na aktuální den

**URL pro Webglobe CRON:**
```
https://pripomnenka.jelenivzeleni.cz/cron-generate-queue.php?token=f8A3kN7vQ1mZx9T2cR5pL0hYw6JdS4uB
```

**Doporučená frekvence:** Denně v **6:00**

**Co dělá:**
- Projde všechny aktivní připomínky
- Vypočítá, které připadají na dnešek (podle předstihu v pracovních dnech)
- Přidá je do fronty k provolání
- Přesune nezvednutá volání z včerejška na dnešek
- Po 5 neúspěšných pokusech označí jako "vzdáno"

**Výstup:**
```
[2026-01-29 06:10:49] Starting call list generation...
[2026-01-29 06:10:49] Found 3 active reminders
[2026-01-29 06:10:49] Added 0 new items to call queue, skipped 2
[2026-01-29 06:10:49] Moved 0 'no answer' calls to today
[2026-01-29 06:10:49] Call list generation completed
```

---

## 2️⃣ Zpracování bankovních plateb

**Účel:** Čte emailové notifikace z banky a automaticky páruje platby

**URL pro Webglobe CRON:**
```
https://pripomnenka.jelenivzeleni.cz/cron-process-payments.php?token=f8A3kN7vQ1mZx9T2cR5pL0hYw6JdS4uB
```

**Doporučená frekvence:** Každých **15 minut** (nebo každou hodinu)

**Co dělá:**
- Připojí se k emailu přes IMAP
- Přečte nové notifikace o platbách od AirBank
- Spáruje platby podle variabilního symbolu
- Zkontroluje částku (musí sedět přesně)
- Automaticky aktivuje předplatné nebo označí jako problematickou platbu
- Pošle aktivační email zákazníkovi

**Poznámka:**
- Pokud nemáte nakonfigurované IMAP údaje v nastavení, script se přeskočí
- Pro plnou automatizaci je potřeba vyplnit v Nastavení → Banka: IMAP host, email a heslo

**Výstup (když je IMAP nakonfigurováno):**
```
[2026-01-29 06:15:00] Starting bank email processing...
[2026-01-29 06:15:02] Connected successfully
[2026-01-29 06:15:02] Found 2 new emails
[2026-01-29 06:15:02] Parsed payment: 150.00 CZK, VS: 26001
[2026-01-29 06:15:03] Matched and activated: VS 26001, sent activation email
[2026-01-29 06:15:03] Processing completed: 2 processed, 1 matched, 1 unmatched
```

**Výstup (když IMAP není nakonfigurováno):**
```
[2026-01-29 06:15:00] Starting bank email processing...
[2026-01-29 06:15:00] IMAP not configured, skipping
```

---

## 3️⃣ Připomínky expirujícího předplatného

**Účel:** Odesílá emailové upozornění zákazníkům, kterým brzy vyprší předplatné

**URL pro Webglobe CRON:**
```
https://pripomnenka.jelenivzeleni.cz/cron-expiration-reminders.php?token=f8A3kN7vQ1mZx9T2cR5pL0hYw6JdS4uB
```

**Doporučená frekvence:** Denně v **8:00**

**Co dělá:**
- Najde předplatné expirující za 30 dní → pošle email s QR kódem
- Najde předplatné expirující za 14 dní → pošle druhou připomínku
- Označí již vypršelá předplatné jako "expired"

**Výstup:**
```
[2026-01-29 08:00:00] Starting expiration reminder emails...
[2026-01-29 08:00:01] Found 2 subscriptions expiring in 30 days
[2026-01-29 08:00:02] Sent email to jan.novak@email.cz (expires: 2026-02-28)
[2026-01-29 08:00:03] Sent email to petra.svobodova@email.cz (expires: 2026-03-01)
[2026-01-29 08:00:03] Found 0 subscriptions expiring in 14 days
[2026-01-29 08:00:04] Marked 1 subscriptions as expired
[2026-01-29 08:00:04] Expiration reminders completed: 2 sent, 0 failed
```

---

## 4️⃣ Souhrnný email pro Sofii

**Účel:** Pošle denní přehled Sofii o tom, co ji čeká dnes

**URL pro Webglobe CRON:**
```
https://pripomnenka.jelenivzeleni.cz/cron-admin-summary.php?token=f8A3kN7vQ1mZx9T2cR5pL0hYw6JdS4uB
```

**Doporučená frekvence:** Denně v **7:00**

**Co dělá:**
- Spočítá počet volání na dnes
- Spočítá nespárované platby
- Spočítá zákazníky čekající na aktivaci
- Pošle přehledný email na admin email

**Obsah emailu:**
```
Dobrý den, Sofie!

Přehled na dnešek (29. 1. 2026):

📞 K provolání dnes: 5 zákazníků
💳 Nespárované platby: 0
⏳ Čeká na aktivaci: 2 zákazníci
📅 Tento týden volat: 12 připomínek

──────────────────────────────
Přejeme hezký den!
Připomněnka | Jeleni v zeleni
```

**Poznámka:** Email se odesílá pouze pokud je něco k řešení (volání > 0 nebo nespárované platby > 0)

---

## 5️⃣ Úklid databáze

**Účel:** Automaticky maže staré záznamy a uvolňuje místo

**URL pro Webglobe CRON:**
```
https://pripomnenka.jelenivzeleni.cz/cron-cleanup.php?token=f8A3kN7vQ1mZx9T2cR5pL0hYw6JdS4uB
```

**Doporučená frekvence:** Denně v **3:00** (v noci)

**Co dělá:**
- Smaže staré OTP kódy (starší než 24 hodin)
- Smaže staré login attempts (starší než 24 hodin)
- Smaže dokončené položky z call_queue (starší než 90 dní)
- Smaže historii volání (starší než 2 roky)
- Označí vypršelá předplatné jako "expired"

**Výstup:**
```
[2026-01-29 03:00:00] Starting cleanup...
[2026-01-29 03:00:01] Deleted 15 old OTP codes (older than 24 hours)
[2026-01-29 03:00:01] Deleted 47 old login attempts (older than 24 hours)
[2026-01-29 03:00:01] Deleted 23 old call queue records (completed/declined older than 90 days)
[2026-01-29 03:00:01] Marked 1 subscriptions as expired
[2026-01-29 03:00:02] Deleted 0 old call logs (older than 2 years)
[2026-01-29 03:00:02] Cleanup completed
```

---

## 🔧 Nastavení v administraci Webglobe

### Krok 1: Přihlášení do Webglobe
1. Přihlaste se na https://admin.webglobe.com/
2. Vyberte doménu jelenivzeleni.cz
3. Přejděte do sekce **CRON**

### Krok 2: Přidání jednotlivých úloh

Pro každou úlohu:
1. Klikněte na **Přidat nový CRON**
2. Vyplňte URL (viz výše)
3. Nastavte frekvenci
4. Uložte

### Příklad nastavení pro "Generování fronty"
```
Název: Připomněnka - Generování fronty volání
URL: https://pripomnenka.jelenivzeleni.cz/cron-generate-queue.php?token=f8A3kN7vQ1mZx9T2cR5pL0hYw6JdS4uB
Frekvence: Denně
Čas: 06:00
Aktivní: ✓
```

---

## 📊 Doporučený plán úloh

| Čas | Úloha | Popis |
|-----|-------|-------|
| **03:00** | Cleanup | Úklid databáze (v noci, když nikdo nepracuje) |
| **06:00** | Generate Queue | Příprava seznamu k provolání (před začátkem pracovní doby) |
| **07:00** | Admin Summary | Email pro Sofii (aby věděla co ji čeká) |
| **08:00** | Expiration Reminders | Upozornění na vypršení předplatného |
| **09:00, 12:00, 15:00** | Process Payments | Kontrola plateb (3× denně, nebo každou hodinu) |

---

## ✅ Testování CRON úloh

### Ruční test (přes prohlížeč)
Každou úlohu můžete spustit ručně zadáním URL do prohlížeče:
```
https://pripomnenka.jelenivzeleni.cz/cron-generate-queue.php?token=f8A3kN7vQ1mZx9T2cR5pL0hYw6JdS4uB
```

Měli byste vidět výstup podobný výše uvedeným příkladům.

### Automatické testování
Po nastavení v Webglobe počkejte na první spuštění a zkontrolujte:
1. V administraci Webglobe → CRON → Historie běhu
2. V aplikaci → Admin → Dashboard (měly by se objevovat nové položky k volání)

---

## 🔐 Bezpečnost

### CRON Token
Token `f8A3kN7vQ1mZx9T2cR5pL0hYw6JdS4uB` je uložen v `config/config.php`:

```php
'security' => [
    'cron_token' => 'f8A3kN7vQ1mZx9T2cR5pL0hYw6JdS4uB',
    // ...
],
```

**⚠️ DŮLEŽITÉ:**
- Token nikdy nezveřejňujte
- Pokud dojde k úniku, vygenerujte nový: `bin2hex(random_bytes(16))`
- Změňte ho v config.php a aktualizujte všechny CRON úlohy ve Webglobe

### Co chrání token
- Zabraňuje neoprávněnému spouštění CRON úloh
- Každý wrapper script (`public/cron-*.php`) kontroluje token před spuštěním
- Bez správného tokenu dostanete: `ERROR: Invalid or missing CRON token`

---

## 🐛 Řešení problémů

### "ERROR: CRON token is not configured in config.php"
**Řešení:** Zkontrolujte `config/config.php`, že obsahuje:
```php
'security' => [
    'cron_token' => 'f8A3kN7vQ1mZx9T2cR5pL0hYw6JdS4uB',
],
```

### "Fatal error: Class 'Database' not found"
**Řešení:** Nahrajte aktualizovaný `cron/bootstrap.php` na server

### "Non-static method Setting::get() cannot be called statically"
**Řešení:** Nahrajte aktualizované CRON scripty (`cron/*.php`) na server

### "The script for cron job doesn't exists"
**Řešení:** Použijte URL formát (https://...) místo fyzické cesty k souboru

### CRON úloha se nespouští
**Řešení:**
1. Zkontrolujte, že je úloha aktivní ve Webglobe administraci
2. Otestujte URL ručně v prohlížeči
3. Zkontrolujte historii běhu ve Webglobe → CRON → Historie

### IMAP "Could not connect"
**Řešení:**
1. Zkontrolujte, že máte vyplněné IMAP údaje v Admin → Nastavení → Banka
2. Ověřte správnost údajů (host: imap.airbank.cz, port: 993)
3. Ujistěte se, že hosting má povoleno IMAP rozšíření

---

## 📝 Poznámky k běhu

### Překrývající se běhy
Webglobe zabraňuje překrývání - pokud úloha ještě běží, další spuštění se přeskočí.

### Timeout
Webglobe má standardní timeout 60 sekund pro CRON úlohy. Všechny naše úlohy běží do 5 sekund, takže je to v pořádku.

### Logování
Všechny úlohy vypisují svůj průběh. Výstup je viditelný:
- V historii běhu ve Webglobe
- Při ručním spuštění přes prohlížeč

---

## 📞 Kontakt při problémech

Pokud něco nefunguje:
1. Zkuste úlohu spustit ručně přes prohlížeč
2. Zkopírujte celý výstup (včetně chybových hlášek)
3. Kontaktujte vývojáře s těmito informacemi

---

**Poslední aktualizace:** 29. ledna 2026
**Verze:** 1.0
