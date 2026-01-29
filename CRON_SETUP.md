# Nastavení CRON úloh pro Webglobe Shared Hosting

## 📍 Kde nastavit CRON

V administraci Webglobe hostingu:
1. Přihlaste se do **Zákaznického centra Webglobe**
2. Vyberte doménu **jelenivzeleni.cz**
3. Přejděte do sekce **CRON** nebo **Plánované úlohy**

## 🔧 Konfigurace jednotlivých úloh

### Důležité informace:
- **Token:** `f8A3kN7vQ1mZx9T2cR5pL0hYw6JdS4uB` (z config.php)
- **Absolutní cesta k public:** `/www/doc/[ČÍSLO_ÚČTU]/jelenivzeleni.cz/www/`

  ⚠️ Číslo účtu zjistíte v administraci nebo pomocí: `pwd` v SSH/FTP

---

## 📋 CRON Úlohy

### 1. Generování fronty k provolání
**Kdy:** Denně v 6:00
**Popis:** Vytváří seznam zákazníků k provolání na daný den

**Nastavení v administraci:**
```
Název: Generate Call Queue
Čas: 0 6 * * *
Typ: PHP skript
Cesta: /www/doc/[ČÍSLO]/jelenivzeleni.cz/www/cron-generate-queue.php?token=f8A3kN7vQ1mZx9T2cR5pL0hYw6JdS4uB
```

---

### 2. Odesílání emailů zákazníkům
**Kdy:** Denně v 6:00
**Popis:** Posílá automatické připomínky zákazníkům

**Nastavení v administraci:**
```
Název: Send Customer Emails
Čas: 0 6 * * *
Typ: PHP skript
Cesta: /www/doc/[ČÍSLO]/jelenivzeleni.cz/www/cron-send-emails.php?token=f8A3kN7vQ1mZx9T2cR5pL0hYw6JdS4uB
```

---

### 3. Čištění databáze
**Kdy:** Denně ve 3:00
**Popis:** Maže staré OTP kódy, login attempts, apod.

**Nastavení v administraci:**
```
Název: Database Cleanup
Čas: 0 3 * * *
Typ: PHP skript
Cesta: /www/doc/[ČÍSLO]/jelenivzeleni.cz/www/cron-cleanup.php?token=f8A3kN7vQ1mZx9T2cR5pL0hYw6JdS4uB
```

---

### 4. Zpracování bankovních plateb
**Kdy:** Každých 15 minut
**Popis:** Kontroluje emaily z banky a páruje platby

**Nastavení v administraci:**
```
Název: Process Bank Payments
Čas: */15 * * * *
Typ: PHP skript
Cesta: /www/doc/[ČÍSLO]/jelenivzeleni.cz/www/cron-process-payments.php?token=f8A3kN7vQ1mZx9T2cR5pL0hYw6JdS4uB
```

---

### 5. Upozornění na expiraci předplatného
**Kdy:** Denně v 8:00
**Popis:** Posílá upozornění 30 a 14 dní před expirací

**Nastavení v administraci:**
```
Název: Expiration Reminders
Čas: 0 8 * * *
Typ: PHP skript
Cesta: /www/doc/[ČÍSLO]/jelenivzeleni.cz/www/cron-expiration-reminders.php?token=f8A3kN7vQ1mZx9T2cR5pL0hYw6JdS4uB
```

---

### 6. Denní přehled pro administrátora (VOLITELNÉ)
**Kdy:** Denně v 7:00
**Popis:** Posílá souhrnný email Sofii

**Nastavení v administraci:**
```
Název: Admin Daily Summary
Čas: 0 7 * * *
Typ: PHP skript
Cesta: /www/doc/[ČÍSLO]/jelenivzeleni.cz/www/cron-admin-summary.php?token=f8A3kN7vQ1mZx9T2cR5pL0hYw6JdS4uB
```

---

## 🔍 Jak zjistit absolutní cestu?

### Možnost 1: Z FTP
1. Připojte se přes FTP
2. Cesta je obvykle: `/www/doc/[ČÍSLO_ÚČTU]/[doména]/www/`

### Možnost 2: Vytvořit testovací skript
Vytvořte soubor `test-path.php` v public/:
```php
<?php
echo "Absolutní cesta: " . __DIR__;
```

Otevřete v prohlížeči: `https://jelenivzeleni.cz/test-path.php`

---

## ✅ Testování CRON úloh

Po nastavení můžete otestovat ručním spuštěním v prohlížeči:

```
https://jelenivzeleni.cz/cron-generate-queue.php?token=f8A3kN7vQ1mZx9T2cR5pL0hYw6JdS4uB
https://jelenivzeleni.cz/cron-send-emails.php?token=f8A3kN7vQ1mZx9T2cR5pL0hYw6JdS4uB
https://jelenivzeleni.cz/cron-cleanup.php?token=f8A3kN7vQ1mZx9T2cR5pL0hYw6JdS4uB
```

Měli byste vidět výstup typu:
```
[2026-01-29 15:30:00] Starting call list generation...
[2026-01-29 15:30:01] Found 5 active reminders
[2026-01-29 15:30:01] Added 5 new items to call queue, skipped 0
[2026-01-29 15:30:01] Call list generation completed
```

---

## 🔒 Bezpečnost

✅ Skripty jsou chráněné tokenem
✅ Bez tokenu nelze CRON spustit
✅ Token je uložený v `config/config.php` (mimo public/)
✅ Skutečné CRON skripty jsou v `/cron/` (mimo veřejný přístup)

---

## ⚠️ Poznámky

1. **První spuštění může trvat déle** - generuje se fronta poprvé
2. **Kontrolujte logy** - pokud něco nefunguje, kontaktujte Webglobe podporu
3. **Minimální interval** - Webglobe obvykle umožňuje nejmenší interval 15 minut
4. **Časové pásmo** - CRON běží v časovém pásmu serveru (UTC+1)

---

## 📞 Kontakt na podporu Webglobe

Pokud máte problém s nastavením:
- **Email:** podpora@webglobe.cz
- **Telefon:** +420 234 700 900
- **Live chat:** dostupný v zákaznickém centru
