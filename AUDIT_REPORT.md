# Audit Systému Připomněnka

Datum: 2026-01-30
Status: ⚠️ **Nalezeny kritické a vysoké problémy**

## Rozsah a metoda
- Proveden statický audit zdrojového kódu (bez spouštění aplikace).
- Prohlédnuty klíčové části: `config/`, `public/`, `src/Controllers`, `src/Models`, `src/Services`, `cron/`, `database/schema.sql`.

---

## 🔴 Kritické problémy

### 1) Citlivé údaje uloženy přímo v repozitáři
**Evidence:** `config/config.php:11-35`

**Problém:** `public/` je document root, takže `config/config.php` není přímo web‑přístupný. Riziko ale zůstává: konfigurační soubor obsahuje reálné přihlašovací údaje k DB a CRON token přímo v repozitáři. Pokud je repozitář sdílený (nebo unikne), umožňuje přímý přístup k produkční databázi i CRON endpointům.

**Dopad:** Únik DB dat, kompromitace celé služby.

**Doporučení:**
- Přesunout tajné údaje do `config/config.local.php` nebo `.env` (již v `.gitignore`).
- Rotovat DB hesla a `cron_token` okamžitě.
- Nasadit kontrolu, která při chybějících tajných hodnot zastaví běh aplikace.

---

## 🔶 Vysoké priority

### 2) CSRF kontrola je v `CustomerController` rozbitá + chybí správné odhlášení
**Evidence:** `src/Controllers/CustomerController.php:50-53, 180-212`

**Problém:** Volání `\CSRF::verify()` bez parametru vyvolá v PHP 8 TypeError (500). Zároveň je volána neexistující metoda `\Session::logout()`. To blokuje aktualizaci profilu a GDPR mazání účtu.

**Dopad:** Profil nejde aktualizovat, GDPR smazání selže; navíc CSRF ochrana není správně vynucena.

**Doporučení:**
- Nahradit za `$this->validateCsrf()`.
- Použít `\Session::logoutCustomer()`.

---

### 3) Automatické párování plateb resetuje expiraci na „dnes + 1 rok“
**Evidence:** `cron/process-bank-emails.php:117-125`

**Problém:** Při automatickém párování bankovní platby se `expires_at` vždy nastavuje na `CURDATE() + 1 YEAR`. Pokud zákazník obnoví dříve, ztratí zbývající období.

**Dopad:** Zákazníci přijdou o část zaplacené služby, riziko reklamací.

**Doporučení:**
- Použít stejnou logiku jako v `Subscription::confirmBankPayment()` (přičíst rok k existujícímu `expires_at`, pokud je v budoucnu).
- Ideálně vyvolat modelovou metodu místo přímého SQL.

---

### 4) Neexistující routy a metody → 500 chyby
**Evidence:** `config/routes.php:16-22, 72-73` + `src/Views/auth/login.php:58-116`

**Problém:** Routy odkazují na neexistující metody (`AuthController::register/verifyOtp/submitOtp`) a neexistující `PaymentController`. Login UI zároveň odkazuje na `/prihlaseni/otp` a `/prihlaseni/znovu-poslat`, které nejsou definované.

**Dopad:** 500 chyby v loginu a admin části, rozbitý OTP flow a správa plateb.

**Doporučení:**
- Implementovat chybějící controller/metody, nebo odstranit routy a odkazy.
- U loginu sjednotit cesty s reálnými routami.

---

## ⚠️ Střední priority

### 5) IMAP heslo ukládané v plaintextu
**Evidence:** `src/Controllers/SettingsController.php:74-83`

**Problém:** Heslo k bankovnímu IMAP účtu se ukládá přímo do DB bez šifrování.

**Dopad:** Při úniku DB lze číst bankovní notifikace.

**Doporučení:**
- Šifrovat heslo (např. libsodium/openssl) nebo přesunout do bezpečného secrets store.
- V adminu zobrazovat pouze maskovanou hodnotu.

---

### 6) Externí generátor QR kódu sdílí platební data
**Evidence:** `src/Services/EmailService.php:141-159`

**Problém:** QR kód se generuje přes externí API (`api.qrserver.com`). Do URL odchází IBAN, částka, VS.

**Dopad:** Únik platebních údajů třetí straně, závislost na externí službě.

**Doporučení:**
- Generovat QR lokálně (např. PHP knihovna) nebo přes interní službu.

---

### 7) Veřejný testovací endpoint zveřejňuje cestu na serveru
**Evidence:** `public/test.php:6-9`

**Problém:** Soubor zobrazuje absolutní cesty a serverový čas.

**Dopad:** Informační únik, usnadňuje útoky.

**Doporučení:**
- Odstranit z `public/` nebo omezit přístup (např. pouze v dev režimu).

---

### 8) Deaktivovaní zákazníci se stále mohou přihlásit
**Evidence:** `src/Controllers/AuthController.php:80-112`

**Problém:** `is_active` se neověřuje při loginu ani během session middleware.

**Dopad:** Deaktivace zákazníka je neúčinná (může dál měnit data).

**Doporučení:**
- Při loginu blokovat `is_active = 0`.
- Při každém requestu (middleware) kontrolovat stav zákazníka.

---

## ℹ️ Nízké priority / Doporučení

### 9) Chybí HSTS hlavička
**Evidence:** `public/index.php` (security headers)

**Doporučení:** Přidat `Strict-Transport-Security` pro posílení HTTPS.

### 10) CRON token v URL (GET) může unikat do logů
**Evidence:** `public/cron-*.php`, `public/index.php` (cron middleware)

**Doporučení:** Přidat IP allowlist, Basic Auth, nebo posílit ochranu (např. POST + header token).

---

## ✅ Pozitivní nálezy
- CSRF ochrana je implementovaná napříč většinou POST endpointů.
- Cleanup CRON existuje (OTP/login attempts/call queue/logs) – udržuje DB čistou.
- Prepared statements s PDO jsou používané konzistentně.
- `call_queue` má unikátní index proti duplicitám.
- Session cookies mají `HttpOnly` a `SameSite=Strict`.

---

## Doporučené priority oprav
1. **Okamžitě:** Rotace tajných údajů + přesun secrets z repozitáře.
2. **Do 1–2 dnů:** Oprava CSRF v `CustomerController`, chybějící logout, expirace v auto‑párování plateb.
3. **Krátkodobě:** Opravit/odstranit neexistující routy a linky v loginu.
4. **Střednědobě:** Šifrování IMAP hesla, lokální QR generátor, odstranění `public/test.php`.

---

## ✅ Opraveno v této iteraci
- Potvrzení platby v administraci: opravené parametry, zápis poznámky a reálné odeslání aktivačního emailu.
- Přidána možnost upgrade/downgrade tarifu u existujících zákazníků (UI + backend, kontrola limitu připomínek).

---

Pokud chceš, mohu rovnou připravit patch s opravami kritických a vysokých problémů.
