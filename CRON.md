# CRON konfigurace — Připomněnka

Systém Připomněnka vyžaduje pravidelné spouštění CRON úloh pro automatizaci klíčových procesů.

## Přehled CRON úloh

| Úloha | Endpoint | Čas | Popis |
|-------|----------|-----|-------|
| Generování fronty | `/cron/generate-queue` | 6:00 denně | Vytváří seznam zákazníků k provolání |
| Emaily zákazníkům | `/cron/send-emails` | 6:00 denně | Posílá připomínky blížících se událostí |
| Připomínky expirace | `/cron/expiration-reminders` | 8:00 denně | Posílá QR kódy pro prodloužení (30 a 14 dní předem) |
| Souhrnný email | `/cron/admin-summary` | 7:00 denně | Volitelný denní přehled pro obsluhu |
| Zpracování plateb | `/cron/process-bank` | každých 15 min | Čte bankovní notifikace a páruje platby |

---

## Konfigurace na Webglobe (cPanel) — bez SSH

Na shared hostingu bez SSH se CRON nastavuje přes HTTP volání pomocí `curl` nebo `wget`.

### 1. Nastavte CRON token

V souboru `config/config.php` nastavte bezpečný token:

```php
'security' => [
    'cron_token' => 'VYGENERUJTE_NAHODNY_RETEZEC_32_ZNAKU',
    // ...
],
```

Pro vygenerování tokenu můžete použít: https://randomkeygen.com/ (256-bit WEP Key)

### 2. Přihlaste se do cPanelu

Webglobe administrace → cPanel → Cron Jobs (Naplánované úlohy)

### 3. Přidejte CRON úlohy

Nahraďte `vasedomena.cz` vaší doménou a `VAS_CRON_TOKEN` tokenem z config.php:

**Generování fronty k provolání (denně v 6:00):**
```
0 6 * * * curl -s "https://vasedomena.cz/cron/generate-queue?token=VAS_CRON_TOKEN" > /dev/null
```

**Emaily zákazníkům (denně v 6:05):**
```
5 6 * * * curl -s "https://vasedomena.cz/cron/send-emails?token=VAS_CRON_TOKEN" > /dev/null
```

**Souhrnný email pro obsluhu (denně v 7:00, volitelné):**
```
0 7 * * * curl -s "https://vasedomena.cz/cron/admin-summary?token=VAS_CRON_TOKEN" > /dev/null
```

**Připomínky expirace předplatného (denně v 8:00):**
```
0 8 * * * curl -s "https://vasedomena.cz/cron/expiration-reminders?token=VAS_CRON_TOKEN" > /dev/null
```

**Zpracování bankovních plateb (každých 15 minut):**
```
*/15 * * * * curl -s "https://vasedomena.cz/cron/process-bank?token=VAS_CRON_TOKEN" > /dev/null
```

### Alternativa s wget (pokud curl nefunguje)

```
0 6 * * * wget -q -O /dev/null "https://vasedomena.cz/cron/generate-queue?token=VAS_CRON_TOKEN"
```

---

## Testování

### Ruční spuštění přes prohlížeč

Otevřete v prohlížeči (nebo použijte Postman):
```
https://vasedomena.cz/cron/generate-queue?token=VAS_CRON_TOKEN
```

Měli byste vidět textový výstup typu:
```
[2025-01-26 06:00:00] Starting call list generation...
[2025-01-26 06:00:00] Found 5 active reminders
[2025-01-26 06:00:00] Added 2 new items to call queue, skipped 3
[2025-01-26 06:00:00] Call list generation completed
```

### Ověření v cPanelu

V cPanelu → Cron Jobs uvidíte seznam naplánovaných úloh a případné chyby.

---

## Řešení problémů

### Dashboard neukazuje připomínky na tento týden
1. Spusťte ručně URL: `https://vasedomena.cz/cron/generate-queue?token=...`
2. Připomínky se zobrazují až po přidání do `call_queue` tabulky
3. Zkontrolujte, že zákazník má aktivní předplatné

### CRON vrací chybu 403 (Forbidden)
- Zkontrolujte, že token v URL odpovídá tokenu v `config/config.php`
- Token nesmí obsahovat speciální znaky (pouze písmena a čísla)

### CRON vrací chybu 500
- Zkontrolujte PHP error log v cPanelu → Errors
- Ověřte připojení k databázi

### Emaily nechodí
1. Zkontrolujte nastavení emailu v `config/config.php`
2. Na shared hostingu použijte `mail()` funkci (bez SMTP)
3. Zkontrolujte složku spam u příjemce
4. Ověřte SPF záznam domény

---

## Doporučené pořadí úloh

| Čas | Úloha | Důvod |
|-----|-------|-------|
| 6:00 | generate-queue | Nejdříve vygenerovat frontu |
| 6:05 | send-emails | Pak poslat emaily (5 min odstup) |
| 7:00 | admin-summary | Souhrn pro obsluhu před otevřením |
| 8:00 | expiration-reminders | Expirace předplatného |
| */15 | process-bank | Průběžně celý den |

---

## Příklad kompletní konfigurace

Pro doménu `pripomnenka.jelenivzeleni.cz` s tokenem `abc123xyz`:

```
0 6 * * * curl -s "https://pripomnenka.jelenivzeleni.cz/cron/generate-queue?token=abc123xyz" > /dev/null
5 6 * * * curl -s "https://pripomnenka.jelenivzeleni.cz/cron/send-emails?token=abc123xyz" > /dev/null
0 7 * * * curl -s "https://pripomnenka.jelenivzeleni.cz/cron/admin-summary?token=abc123xyz" > /dev/null
0 8 * * * curl -s "https://pripomnenka.jelenivzeleni.cz/cron/expiration-reminders?token=abc123xyz" > /dev/null
*/15 * * * * curl -s "https://pripomnenka.jelenivzeleni.cz/cron/process-bank?token=abc123xyz" > /dev/null
```
