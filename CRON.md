# CRON konfigurace — Připomněnka

Systém Připomněnka vyžaduje pravidelné spouštění CRON úloh pro automatizaci klíčových procesů.

## Přehled CRON úloh

| Úloha | Soubor | Čas | Popis |
|-------|--------|-----|-------|
| Generování fronty | `generate-call-list.php` | 6:00 denně | Vytváří seznam zákazníků k provolání |
| Emaily zákazníkům | `send-customer-emails.php` | 6:00 denně | Posílá připomínky blížících se událostí |
| Připomínky expirace | `send-expiration-reminders.php` | 8:00 denně | Posílá QR kódy pro prodloužení (30 a 14 dní předem) |
| Souhrnný email | `send-admin-summary.php` | 7:00 denně | Volitelný denní přehled pro obsluhu |
| Zpracování plateb | `process-bank-emails.php` | každých 15 min | Čte bankovní notifikace a páruje platby |

## Konfigurace na Webglobe (cPanel)

### 1. Přihlaste se do cPanelu
Obvykle na adrese: `https://vasedomena.cz:2083` nebo přes administraci Webglobe.

### 2. Otevřete "Cron Jobs" (Naplánované úlohy)

### 3. Přidejte následující úlohy:

**Generování fronty k provolání (denně v 6:00):**
```
0 6 * * * /usr/bin/php /cesta/k/pripomnenka/cron/generate-call-list.php >> /cesta/k/pripomnenka/storage/logs/cron.log 2>&1
```

**Emaily zákazníkům (denně v 6:00):**
```
0 6 * * * /usr/bin/php /cesta/k/pripomnenka/cron/send-customer-emails.php >> /cesta/k/pripomnenka/storage/logs/cron.log 2>&1
```

**Připomínky expirace předplatného (denně v 8:00):**
```
0 8 * * * /usr/bin/php /cesta/k/pripomnenka/cron/send-expiration-reminders.php >> /cesta/k/pripomnenka/storage/logs/cron.log 2>&1
```

**Souhrnný email pro obsluhu (denně v 7:00, volitelné):**
```
0 7 * * * /usr/bin/php /cesta/k/pripomnenka/cron/send-admin-summary.php >> /cesta/k/pripomnenka/storage/logs/cron.log 2>&1
```

**Zpracování bankovních plateb (každých 15 minut):**
```
*/15 * * * * /usr/bin/php /cesta/k/pripomnenka/cron/process-bank-emails.php >> /cesta/k/pripomnenka/storage/logs/cron.log 2>&1
```

## Alternativa: Spouštění přes HTTP

Pokud nelze spouštět PHP přímo, použijte HTTP endpoint s tokenem:

```
0 6 * * * curl -s "https://vasedomena.cz/cron/generate-queue?token=VAS_CRON_TOKEN" > /dev/null
0 6 * * * curl -s "https://vasedomena.cz/cron/send-emails?token=VAS_CRON_TOKEN" > /dev/null
0 8 * * * curl -s "https://vasedomena.cz/cron/expiration-reminders?token=VAS_CRON_TOKEN" > /dev/null
*/15 * * * * curl -s "https://vasedomena.cz/cron/process-bank?token=VAS_CRON_TOKEN" > /dev/null
```

**Poznámka:** Token nastavte v `config/config.php` pod `security.cron_token`.

## Zjištění cesty k PHP

Na Webglobe obvykle:
- PHP 8.x: `/usr/bin/php` nebo `/usr/local/bin/php8.2`

Pro zjištění správné cesty spusťte v SSH (pokud je dostupné):
```bash
which php
```

## Zjištění absolutní cesty k projektu

V cPanelu → File Manager najděte složku `pripomnenka` a poznamenejte si celou cestu, např.:
```
/home/username/public_html/pripomnenka
```

## Testování

Pro ruční otestování CRON úlohy:

```bash
php /cesta/k/pripomnenka/cron/generate-call-list.php
```

Výstup by měl ukázat:
```
[2025-01-26 06:00:00] Starting call list generation...
[2025-01-26 06:00:00] Found X active reminders
[2025-01-26 06:00:00] Added X new items to call queue, skipped X
[2025-01-26 06:00:00] Call list generation completed
```

## Řešení problémů

### Úloha neběží
1. Zkontrolujte cestu k PHP (`which php`)
2. Zkontrolujte absolutní cestu k souborům
3. Zkontrolujte oprávnění souborů (`chmod +x cron/*.php`)
4. Zkontrolujte log soubor: `storage/logs/cron.log`

### Dashboard neukazuje připomínky
- Spusťte ručně: `php cron/generate-call-list.php`
- Připomínky se zobrazují až po přidání do `call_queue` tabulky

### Emaily nechodí
1. Zkontrolujte nastavení SMTP v `config/config.php`
2. Zkontrolujte SPF/DKIM záznamy domény
3. Zkontrolujte log emailů

## Doporučené pořadí úloh

1. **6:00** — `generate-call-list.php` (nejdříve vygenerovat frontu)
2. **6:00** — `send-customer-emails.php` (pak poslat emaily)
3. **7:00** — `send-admin-summary.php` (souhrn pro obsluhu)
4. **8:00** — `send-expiration-reminders.php` (expirace předplatného)
5. ***/15** — `process-bank-emails.php` (průběžně celý den)
