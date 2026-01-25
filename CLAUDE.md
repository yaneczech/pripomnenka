# CLAUDE.md — Systém Připomněnka

## Přehled projektu

**Název:** Připomněnka  
**Klient:** Květinářství Jeleni v zeleni (Jihlava)  
**Účel:** Systém pro správu připomínek výročí a svátků zákazníků  
**Technologie:** PHP 8.x, MySQL, vanilla JS (bez frameworku)  
**Hosting:** Webglobe shared hosting (bez SSH, s CRON)

---

## Byznys kontext

Zákazníci (typicky muži) si registrují důležitá data — narozeniny manželky, výročí svatby apod. Systém 5 pracovních dní předem upozorní obsluhu (Sofie), která zákazníkovi zavolá a domluví individuální objednávku. Sekundárně jde zákazníkovi automatický email.

**Hlavní hodnota:** Zákazník nikdy nezapomene → spokojená partnerka → loajální zákazník.

---

## Funkční požadavky

### 1. Zákaznická část

#### 1.1 Aktivace účtu (z emailu)
Zákazník dostane od Sofie email s unikátním aktivačním odkazem. Po kliknutí:

**Progress bar nahoře:** Krok 1/3 → 2/3 → 3/3

**Krok 1: "Nejdřív se představte"**
- Telefon a email (předvyplněné, needitovatelné)
- Jméno (volitelné) — placeholder: "Jak vám máme říkat?"
- Heslo (volitelné) — s vysvětlením: "Pokud nenastavíte, pošleme vám při každém přihlášení kód na email"
- GDPR souhlas (povinný checkbox)
- Tlačítko: "Pokračovat" nebo "Přeskočit a nastavit později"

**Krok 2: "Jaká data vám máme hlídat?"**
- Formulář pro přidání připomínek (viz 1.2)
- Zobrazení zbývajícího limitu: "Můžete přidat ještě 3 připomínky" (s progress barem)
- Možnost přidat více připomínek najednou (tlačítko "+ Další připomínka")
- Tlačítko: "Pokračovat" nebo "Přidat připomínky později"

**Krok 3: "Hotovo! 🎉"**
- Rekapitulace: "Připomeneme vám:"
  - 15. března — Narozeniny manželky (za 47 dní)
  - 8. června — Výročí svatby (za 132 dní)
- Info o slevě: "Nezapomeňte: máte 10% slevu na všechny kytice!"
- Tlačítko: "Přejít do profilu"

**Chybové stavy:**
- Expirovaný odkaz → "Odkaz už není platný. Zavolejte nám na [telefon] a pošleme vám nový."
- Již aktivovaný účet → "Účet už je aktivní. Chcete se přihlásit?" + odkaz

#### 1.2 Správa připomínek (po přihlášení)

**Přehled připomínek:**
- Karty s připomínkami, seřazené podle data (nejbližší první)
- Na každé kartě: typ + vztah, datum, cenový rozsah, countdown ("za 23 dní")
- Barevné rozlišení: blížící se (< 14 dní) zvýrazněné
- **Zobrazení limitu:** Progress bar "Využito 4 z 5 připomínek"

**Prázdný stav:**
- Ilustrace + "Zatím nemáte žádné připomínky"
- Velké tlačítko "Přidat první připomínku"

**Přidat novou připomínku** (pokud není vyčerpán limit):

| Pole | Popis | UX detail |
|------|-------|-----------|
| **Koho slavíte?** | Manželka / Matka / Otec / Dcera / Syn / Babička / Dědeček / Sestra / Bratr / Tchyně / Tchán / Partner/ka / Kamarád/ka / Kolega/yně / Jiné | Výběr s ikonkami, 2 sloupce na mobilu |
| **Co slavíte?** | Narozeniny / Svátek / Výročí svatby / Výročí vztahu / Den matek / Den otců / Valentýn / Jiné | Dynamicky se mění podle "Koho" (např. u Manželky nabídne i Výročí) |
| **Datum** | Den a měsíc | Dva selecty vedle sebe; tooltip: "Rok neukládáme — připomeneme vám to každý rok automaticky 🔁" |
| **Kdy připomenout?** | 3 / 5 / 7 / 10 / 14 pracovních dní předem | Výchozí: 5; vysvětlení: "Zavoláme vám X dní před, abyste měli čas" |
| **Rozpočet** | Do 500 Kč / 500–800 Kč / 800–1200 Kč / 1200–2000 Kč / Nad 2000 Kč / Poradíme při hovoru | "Poradíme při hovoru" s tooltipem: "Zavoláme a společně vybereme podle příležitosti" |
| **Poznámka** | Volný text (max 500 znaků) | Placeholder: "Např. má ráda tulipány, preferuji pastelové barvy, nemá ráda lilie..." |

**Po uložení:**
- Toast notifikace: "Připomínka uložena! Ozveme se vám [datum]."
- Návrat na přehled

**Editace / smazání:**
- Swipe na kartě (mobil) nebo ikony (desktop)
- Smazání s potvrzením: "Opravdu smazat? Tuto akci nelze vrátit."

#### 1.3 Přihlášení (hybridní model)

**Krok 1: Identifikace**
- Jedno pole: "Telefon nebo email"
- Tlačítko: "Pokračovat"

**Krok 2a: Má heslo**
- Zobrazí se pole pro heslo
- Link: "Zapomněli jste heslo?" → přesměruje na OTP

**Krok 2b: Nemá heslo (OTP)**
- Zobrazí se: "Poslali jsme vám 6místný kód na [email]. Zkontrolujte i složku spam."
- Pole pro kód (6 číslic, auto-focus na další pole)
- Countdown: "Poslat znovu za 60s" → pak tlačítko "Poslat kód znovu"
- Link: "Raději chci nastavit heslo" → po přihlášení přesměruje do profilu

**"Zapamatovat si mě":**
- Checkbox, výchozí zaškrtnutý
- Platnost: 30 dní

**Chybové stavy:**
- Špatné heslo: "Nesprávné heslo. Zkuste to znovu nebo použijte přihlášení kódem."
- Špatný OTP: "Nesprávný kód. Zkontrolujte email a zkuste to znovu."
- Příliš mnoho pokusů: "Příliš mnoho pokusů. Zkuste to za 15 minut."
- Neexistující účet: "Účet s tímto kontaktem neexistuje. Máte předplatné Připomněnky?"

#### 1.4 Předplatné

**Model:** Roční předplatné + sleva na květiny

**Varianty (editovatelné v adminu):**

| Varianta | Cena | Limit připomínek | Sleva |
|----------|------|------------------|-------|
| Early bird | 75 Kč/rok | 5 | 10% |
| Standard | 150 Kč/rok | 10 | 10% |
| *(další varianty lze přidat v nastavení)* |

**Důležité:** 
- Varianty lze přidávat/upravovat v adminu
- Změna ceny neovlivní existující předplatitele (cena se kopíruje při založení)
- Variantu lze deaktivovat (nenabízí se novým, existující doběhnou)

**Benefity předplatného:**
- Připomínky dle limitu varianty
- Osobní provolání před každou událostí
- **10% sleva na všechny kytice**

**Workflow platby (nový zákazník):**
1. Zákazník v obchodě projeví zájem
2. Sofie založí účet v adminu (telefon + email + varianta)
3. Zákazník zaplatí (hotově/kartou v obchodě NEBO převodem)
4. Pokud platí převodem: Sofie pošle email s QR kódem
5. Po připsání platby: systém automaticky spáruje (nebo Sofie ručně potvrdí)
6. Zákazník dostane email s aktivačním odkazem

**Workflow platby (obnova):**
- 30 dní před vypršením: automatický email s QR kódem pro platbu
- 14 dní před: druhá připomínka
- Po vypršení: účet "zmražen" (data zůstávají, připomínky se negenerují)
- Po zaplacení: okamžitá reaktivace

**Automatické párování plateb (AirBank):**
- Systém čte emaily s notifikacemi o platbách (IMAP)
- Páruje podle VS (formát: `RRCCC` = rok + pořadové číslo, např. `25001`)
- Kontroluje částku:
  - **Sedí:** automaticky aktivuje
  - **Nesedí:** upozorní Sofii ("Zákazník X zaplatil 200 Kč místo 150 Kč")
- Nespárované platby: seznam v adminu pro ruční přiřazení

#### 1.5 GDPR funkce
- Zobrazit všechna svá data
- Exportovat data (JSON/PDF)
- Smazat účet (s potvrzením)

### 2. Administrační část (pro Sofii)

#### 2.1 Dashboard

**Hlavní přehled "Co dělat dnes":**

| Widget | Popis | UX detail |
|--------|-------|-----------|
| 📞 **Dnes volat** | Počet připomínek k provolání | Velké číslo, kliknutím přejde na seznam; zelené = vše ok, oranžové = jsou tam opakované pokusy |
| ⏳ **Čeká na aktivaci** | Zákazníci co zaplatili, ale neaktivovali | Šedé, kliknutím seznam |
| 💳 **Nespárované platby** | Platby co systém nepřiřadil | ČERVENĚ pokud > 0, vyžaduje akci |
| 📅 **Tento týden** | Připomínky na příštích 7 dní | Pro plánování |
| ⚠️ **Expiruje brzy** | Předplatné do 30 dnů | Oranžově pokud > 0 |

**Prázdný stav (nic k řešení):**
- Velká ikona ✨ + "Dnes je klid! Všechno běží jak má."
- Pod tím statistiky: "Aktivních zákazníků: 47 | Připomínek celkem: 123 | Tento měsíc: 2 350 Kč"

**Statistiky (spodní část):**
- Počet aktivních zákazníků
- Počet připomínek celkem
- Příjmy z předplatného (tento měsíc / celkem)
- Úspěšnost provolání (% vyřízeno vs. nechce)

**FAB tlačítko (vždy viditelné, spodní roh):**
- ➕ "Nový zákazník" — velké, vždy dostupné, i při scrollování

#### 2.2 Založení nového zákazníka

**Cíl:** Sofie u pultu se zákazníkem — musí to být rychlé (< 30 sekund)

**Formulář (jeden krok):**

| Pole | Povinné | Výchozí | UX detail |
|------|---------|---------|-----------|
| Telefon | ✅ | — | Auto-formátování (+420...), validace |
| Email | ✅ | — | Validace, lowercase |
| Varianta | ✅ | Standard (nebo výchozí z nastavení) | Radio buttony s cenou: "Early bird — 75 Kč (5 připomínek)" / "Standard — 150 Kč (10 připomínek)" |
| Způsob platby | ✅ | Hotově | Radio: Hotově / Kartou / Převodem |

**Tlačítko:** "Uložit a odeslat" (jedno tlačítko, jedna akce)

**Co se stane po kliknutí:**

| Způsob platby | Akce systému | Co vidí Sofie |
|---------------|--------------|---------------|
| **Hotově / Kartou** | Označí jako zaplaceno, pošle aktivační email | Toast: "✅ Hotovo! Zákazníkovi jsme poslali email s aktivačním odkazem." |
| **Převodem** | Vygeneruje VS, pošle email s QR kódem | Toast: "✅ Zákazníkovi jsme poslali QR kód pro platbu. VS: 25001" |

**Validace:**
- Duplicitní telefon/email → "Zákazník s tímto kontaktem už existuje. Chcete zobrazit jeho profil?"
- Neplatný formát → inline chyba pod polem

**Klávesové zkratky (desktop):**
- Enter = Uložit
- Esc = Zavřít

#### 2.3 Seznam k provolání

**Záhlaví:**
- "Dnes volat: 5 zákazníků" + datum
- Filtr: Všechny / Nové / Opakované pokusy

**Karta zákazníka (jedna položka):**

```
┌─────────────────────────────────────────────────────────┐
│ 📞 +420 777 888 999              [VELKÉ KLIKACÍ TLAČÍTKO] │
│                                                         │
│ Jan Novák (nebo "Neznámé jméno" šedě)                  │
│ 🎂 Narozeniny — Manželka                    15. března │
│ 💰 800–1200 Kč                                         │
│                                                         │
│ 💬 "Má ráda tulipány, ne lilie"                        │
│                                                         │
│ 📊 Minule: 12.6.2024 — kytice 950 Kč                   │
│     Pokus: 1. (nebo "3. pokus ⚠️" červeně)             │
│                                                         │
│ ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐       │
│ │ ✅ OK   │ │ 📞 Nezv.│ │ ❌ Nechce│ │ ⏰ Jindy│       │
│ └─────────┘ └─────────┘ └─────────┘ └─────────┘       │
└─────────────────────────────────────────────────────────┘
```

**Informace na kartě:**
- Telefon (velký, klikatelný → volání)
- Jméno zákazníka (pokud vyplnil)
- Typ události + vztah oslavence + datum
- Cenový rozsah
- Poznámka zákazníka (pokud je)
- Historie: poslední objednávka (datum + částka)
- Počítadlo pokusů (1., 2., 3....)
- Preferovaný čas volání (pokud nastaveno): "Volat odpoledne"

**Akce (velké tlačítka, jedno kliknutí):**

| Akce | Co se stane | Následný krok |
|------|-------------|---------------|
| ✅ **Vyřízeno** | Otevře mini-modal | Volitelně: částka + poznámka, pak "Uložit" |
| 📞 **Nezvedá** | Přesune na další pracovní den | Toast: "Přesunuto na zítra" |
| ❌ **Nechce letos** | Označí jako odmítnuto | Toast: "OK, letos nevoláme" |
| ⏰ **Jindy** | Otevře date picker | Vybrat datum, "Uložit" |

**Logika opakovaných pokusů:**
- Po 1. "Nezvedá" → přesune na další pracovní den
- Po 3. "Nezvedá" → karta červeně zvýrazněná + návrh "Vzdát to letos?"
- Po 5. "Nezvedá" → automaticky označit jako nedostupný

**Prázdný stav:**
- "Dnes nikoho nevoláte 🎉 Užijte si klid!"
- Odkaz: "Zobrazit tento týden" / "Zobrazit všechny zákazníky"

**Swipe gesta (mobil):**
- Swipe doprava → Vyřízeno
- Swipe doleva → Nezvedá

#### 2.4 Správa zákazníků

**Seznam zákazníků:**
- Vyhledávání: telefon, email, jméno (real-time, už od 2 znaků)
- Filtry: Všichni / Aktivní / Čekají na aktivaci / Čekají na platbu / Vypršelí
- Řazení: Nejnovější / Abecedně / Podle expirace

**Karta zákazníka v seznamu:**
```
Jan Novák | +420 777 888 999
📧 jan@email.cz | 📅 Platí do: 15.3.2026
Připomínky: 4/5 | Status: ✅ Aktivní
```

**Detail zákazníka:**

| Sekce | Obsah |
|-------|-------|
| **Kontakty** | Telefon, email, jméno (editovatelné) |
| **Předplatné** | Varianta, platí od-do, stav, částka |
| **Připomínky** | Seznam všech připomínek (editovatelné) |
| **Historie volání** | Datum, výsledek, částka objednávky |
| **Interní poznámky** | Jen pro Sofii, zákazník nevidí |

**Akce v detailu:**
- ✏️ Upravit kontakty
- 📧 Znovu odeslat aktivační email
- 💳 Znovu odeslat QR kód pro platbu
- ➕ Přidat připomínku (za zákazníka)
- 🔄 Prodloužit předplatné ručně
- 🗑️ Smazat zákazníka (s potvrzením + GDPR info)

**Interní poznámky (GDPR-aware):**
- Upozornění: "⚠️ Poznámky mohou obsahovat osobní údaje. Na vyžádání zákazníka musí být poskytnuty."
- Strukturovaná pole (volitelné):
  - Preferované květiny: [text]
  - Obvyklý rozpočet: [select]
  - Preferovaný čas volání: Ráno / Odpoledne / Večer / Kdykoliv
- Volná poznámka: [textarea]

#### 2.5 Správa předplatného a plateb
- **Čekající na platbu:** Zákazníci, kterým byl odeslán QR kód
- **Nespárované platby:** 
  - Seznam plateb, které nepasují (špatný VS, špatná částka)
  - Možnost ručně přiřadit k zákazníkovi
  - Upozornění na přeplatky ("Zákazník X zaplatil 200 Kč místo 150 Kč — kontaktovat?")
- **Expiruje brzy:** Předplatné končící do 30 dnů
- **Vypršelé:** Neobnovená předplatné
- Přehled příjmů z předplatného (měsíc/rok)

#### 2.6 Nastavení
- Texty automatických emailů (aktivační, platební QR, připomínka události, expirace)
- Výchozí předstih připomínky
- Pracovní dny (pro výpočet předstihu)
- **Varianty předplatného:**
  - Seznam variant (tabulka)
  - Přidat novou variantu
  - Editovat existující (název, cena, limit připomínek, sleva, popis)
  - Aktivovat / deaktivovat variantu (deaktivovaná se nenabízí novým zákazníkům)
  - Nastavit výchozí variantu
  - **Poznámka:** Změna ceny/limitu neovlivní existující předplatitele
- **Banka:**
  - Číslo účtu pro QR kód
  - IMAP přístup pro čtení notifikací (email, heslo, server)
- Kontaktní údaje obchodu

### 3. Notifikační systém

#### 3.1 Pro obsluhu (Sofii)
- **Primární:** Zobrazení v adminu (Dashboard → Dnes volat)
- **Sekundární:** Denní souhrnný email ráno (volitelné)

#### 3.2 Pro zákazníka — vzorové emaily

**Aktivační email (po zaplacení):**
```
Předmět: Vítejte v Připomněnce! 🦌

Dobrý den!

Děkujeme, že jste se přidal/a k Připomněnce od Jelenů v zeleni.

Teď si nastavte, jaká data vám máme hlídat:

        [ NASTAVIT PŘIPOMÍNKY → ]
              (velké tlačítko)

Odkaz platí 30 dní. Pokud vyprší, ozvěte se nám 
a pošleme nový.

S pozdravem,
Vaše květinářství Jeleni v zeleni 🌷
Tel: 123 456 789
```

**QR kód pro platbu:**
```
Předmět: QR kód pro platbu Připomněnky 💳

Dobrý den!

Pro aktivaci služby Připomněnka prosím uhraďte:

        ┌─────────────────┐
        │                 │
        │    [QR KÓD]     │
        │                 │
        └─────────────────┘

Částka: 150 Kč
Účet: 123456789/0100
VS: 25001

Po připsání platby vám automaticky pošleme 
aktivační odkaz (obvykle do 24 hodin).

S pozdravem,
Jeleni v zeleni 🦌
```

**Připomínka události (X dní předem):**
```
Předmět: Blíží se narozeniny manželky! 🎂

Dobrý den, Honzo!

Za 5 dní, 15. března, má vaše manželka narozeniny.

Brzy vám zavoláme, abychom společně vybrali 
tu pravou kytici.

Nechcete čekat? Ozvěte se nám:
📞 123 456 789

Nezapomeňte: máte 10% slevu na všechny kytice!

Vaši Jeleni v zeleni 🦌
```

**Expirace předplatného (30 dní předem):**
```
Předmět: Vaše Připomněnka brzy vyprší ⏰

Dobrý den!

Vaše předplatné Připomněnky vyprší 15. dubna.

Chcete pokračovat? Stačí zaplatit:

        ┌─────────────────┐
        │                 │
        │    [QR KÓD]     │
        │                 │
        └─────────────────┘

Částka: 150 Kč
VS: 25001

Po zaplacení se předplatné automaticky prodlouží 
o další rok.

Díky, že jste s námi! 🦌
Jeleni v zeleni
```

**Expirace předplatného (14 dní předem — druhá připomínka):**
```
Předmět: Připomínka: předplatné vyprší za 14 dní

Dobrý den!

Ještě jsme nezaznamenali vaši platbu za prodloužení 
Připomněnky. Předplatné vyprší 15. dubna.

        [ ZAPLATIT → ]

Pokud nechcete pokračovat, nemusíte nic dělat. 
Vaše data zůstanou uložená pro případ, že si to 
rozmyslíte.

Jeleni v zeleni 🦌
```

#### 3.3 CRON úlohy
- **Každých 15 minut:** Čtení bankovních notifikací a párování plateb
- **Denně v 6:00:** Vygenerovat seznam k provolání na daný den
- **Denně v 6:00:** Odeslat automatické emaily zákazníkům
- **Denně v 7:00:** Odeslat souhrnný email Sofii (volitelné)
- **Denně v 8:00:** Odeslat připomínky expirace předplatného (30 dní / 14 dní před)

---

## GDPR požadavky

### Právní základ
- **Souhlas** (čl. 6 odst. 1 písm. a) GDPR) — explicitní checkbox při registraci

### Minimalizace dat
- Neukládat rok narození oslavenců
- Neukládat jména třetích osob (jen vztah: "manželka", ne "Jana")
- Poznámky jsou na zodpovědnost obsluhy (upozornění v UI)

### Povinné funkce
- **Právo na přístup:** Export všech dat zákazníka
- **Právo na výmaz:** Smazání účtu včetně všech dat
- **Právo na opravu:** Editace údajů v profilu
- **Informovanost:** Jasné podmínky zpracování při registraci

### Zabezpečení
- Hesla: `password_hash()` s `PASSWORD_DEFAULT`
- HTTPS povinné (zajistí hosting)
- Session: `httponly`, `secure`, `samesite=strict`
- CSRF tokeny na všech formulářích
- Prepared statements (PDO) — žádné SQL injection
- XSS ochrana: `htmlspecialchars()` na všech výstupech
- Rate limiting na přihlášení (max 5 pokusů / 15 min)

### Retence dat
- Aktivní účty: bez omezení
- Neaktivní účty (2 roky bez aktivity): upozornění emailem, po 30 dnech smazání
- Logy přístupů: 90 dní

---

## Technická specifikace

### Adresářová struktura

```
/pripomnenka/
├── public/                 # Veřejně přístupné (document root)
│   ├── index.php          # Front controller
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css
│   │   └── js/
│   │       └── app.js
│   └── .htaccess          # URL rewriting
├── src/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── CustomerController.php
│   │   ├── ReminderController.php
│   │   └── AdminController.php
│   ├── Models/
│   │   ├── Customer.php
│   │   ├── Reminder.php
│   │   ├── CallLog.php
│   │   └── Database.php
│   ├── Views/
│   │   ├── layouts/
│   │   │   ├── public.php
│   │   │   └── admin.php
│   │   ├── auth/
│   │   │   ├── login.php
│   │   │   └── register.php
│   │   ├── customer/
│   │   │   ├── dashboard.php
│   │   │   ├── reminders.php
│   │   │   └── profile.php
│   │   └── admin/
│   │       ├── dashboard.php
│   │       ├── call-list.php
│   │       ├── customers.php
│   │       └── settings.php
│   ├── Services/
│   │   ├── NotificationService.php
│   │   ├── EmailService.php
│   │   ├── PaymentService.php       # Generování QR, párování plateb
│   │   ├── BankMailParser.php       # Čtení IMAP notifikací z AirBank
│   │   └── WorkdayCalculator.php
│   └── Helpers/
│       ├── CSRF.php
│       ├── Validator.php
│       └── Session.php
├── config/
│   ├── config.php         # DB credentials, nastavení
│   └── routes.php         # Definice rout
├── cron/
│   ├── process-bank-emails.php     # Čtení a párování plateb
│   ├── generate-call-list.php
│   ├── send-customer-emails.php
│   ├── send-expiration-reminders.php
│   └── send-admin-summary.php
├── storage/
│   └── logs/              # Logy (mimo public)
├── database/
│   └── schema.sql         # Databázové schéma
└── README.md
```

### Databázové schéma

```sql
-- Zákazníci
CREATE TABLE customers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL UNIQUE,
    phone_hash VARCHAR(64) NOT NULL,  -- Pro rychlé vyhledávání
    email VARCHAR(255) NOT NULL,
    email_hash VARCHAR(64) NOT NULL,
    name VARCHAR(100) DEFAULT NULL,
    password_hash VARCHAR(255) DEFAULT NULL,  -- NULL = jen OTP přihlášení
    gdpr_consent_at DATETIME NOT NULL,
    gdpr_consent_text TEXT NOT NULL,  -- Verze textu, se kterým souhlasil
    last_login_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_phone_hash (phone_hash),
    INDEX idx_email_hash (email_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- Předplatné
CREATE TABLE subscriptions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    plan_id INT UNSIGNED NOT NULL,  -- Odkaz na variantu
    reminder_limit TINYINT UNSIGNED NOT NULL,  -- Kopie z plánu (pro případ změny cen)
    price DECIMAL(10,2) NOT NULL,  -- Očekávaná částka (kopie z plánu)
    price_paid DECIMAL(10,2) DEFAULT NULL,  -- Skutečně zaplacená částka
    variable_symbol VARCHAR(10) NOT NULL UNIQUE,  -- Formát: RRCCC (25001, 25002...)
    starts_at DATE DEFAULT NULL,  -- NULL dokud nezaplaceno
    expires_at DATE DEFAULT NULL,
    payment_method ENUM('cash', 'card', 'bank_transfer') NOT NULL,
    payment_status ENUM('pending', 'paid', 'mismatched') DEFAULT 'pending',
    payment_confirmed_at DATETIME DEFAULT NULL,
    payment_confirmed_by INT UNSIGNED DEFAULT NULL,  -- admin ID nebo NULL = automaticky
    payment_note VARCHAR(255) DEFAULT NULL,  -- Poznámka k platbě (např. přeplatek)
    activation_token VARCHAR(64) DEFAULT NULL,  -- Pro aktivační odkaz
    activation_token_expires_at DATETIME DEFAULT NULL,
    activated_at DATETIME DEFAULT NULL,
    status ENUM('awaiting_payment', 'awaiting_activation', 'active', 'expired', 'cancelled') DEFAULT 'awaiting_payment',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES subscription_plans(id),
    FOREIGN KEY (payment_confirmed_by) REFERENCES admins(id) ON SET NULL,
    INDEX idx_customer (customer_id),
    INDEX idx_status (status),
    INDEX idx_expires (expires_at),
    INDEX idx_vs (variable_symbol),
    INDEX idx_payment_status (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- Čítač pro variabilní symboly
CREATE TABLE vs_counter (
    year SMALLINT UNSIGNED PRIMARY KEY,
    last_number INT UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- Nespárované platby (z bankovních notifikací)
CREATE TABLE unmatched_payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    amount DECIMAL(10,2) NOT NULL,
    variable_symbol VARCHAR(20) DEFAULT NULL,
    sender_name VARCHAR(255) DEFAULT NULL,
    received_at DATETIME NOT NULL,
    raw_email_data TEXT DEFAULT NULL,  -- Celý text emailu pro debug
    matched_to_subscription_id INT UNSIGNED DEFAULT NULL,
    matched_at DATETIME DEFAULT NULL,
    matched_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (matched_to_subscription_id) REFERENCES subscriptions(id) ON SET NULL,
    FOREIGN KEY (matched_by) REFERENCES admins(id) ON SET NULL,
    INDEX idx_vs (variable_symbol),
    INDEX idx_unmatched (matched_to_subscription_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- Připomínky
CREATE TABLE reminders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    event_type ENUM('birthday', 'nameday', 'wedding_anniversary', 'relationship_anniversary', 'mothers_day', 'fathers_day', 'valentines', 'other') NOT NULL,
    recipient_relation ENUM('wife', 'husband', 'mother', 'father', 'daughter', 'son', 'grandmother', 'grandfather', 'sister', 'brother', 'mother_in_law', 'father_in_law', 'friend', 'colleague', 'other') NOT NULL,
    event_day TINYINT UNSIGNED NOT NULL,  -- 1-31
    event_month TINYINT UNSIGNED NOT NULL,  -- 1-12
    advance_days TINYINT UNSIGNED DEFAULT 5,  -- Předstih v pracovních dnech
    price_range ENUM('under_500', '500_800', '800_1200', '1200_2000', 'over_2000', 'to_discuss') DEFAULT 'to_discuss',
    customer_note TEXT DEFAULT NULL,  -- Poznámka od zákazníka
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_customer (customer_id),
    INDEX idx_date (event_month, event_day),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- Log provolání (pro historii a statistiky)
CREATE TABLE call_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reminder_id INT UNSIGNED NOT NULL,
    call_date DATE NOT NULL,
    status ENUM('completed', 'no_answer', 'declined', 'postponed') NOT NULL,
    order_amount DECIMAL(10,2) DEFAULT NULL,  -- Částka objednávky (volitelné)
    admin_note TEXT DEFAULT NULL,  -- Interní poznámka
    postponed_to DATE DEFAULT NULL,  -- Kam přesunuto (pokud postponed)
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reminder_id) REFERENCES reminders(id) ON DELETE CASCADE,
    INDEX idx_reminder (reminder_id),
    INDEX idx_date (call_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- Interní poznámky k zákazníkům (odděleno pro GDPR přehlednost)
CREATE TABLE customer_notes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    preferred_flowers TEXT DEFAULT NULL,
    typical_budget VARCHAR(50) DEFAULT NULL,
    preferred_call_time ENUM('morning', 'afternoon', 'evening', 'anytime') DEFAULT 'anytime',  -- Kdy volat
    general_note TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- Administrátoři
CREATE TABLE admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- Nastavení systému
CREATE TABLE settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- Varianty předplatného (editovatelné v adminu)
CREATE TABLE subscription_plans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,  -- "Early bird", "Standard", "Premium"...
    slug VARCHAR(50) NOT NULL UNIQUE,  -- "early_bird", "standard"
    price DECIMAL(10,2) NOT NULL,
    reminder_limit TINYINT UNSIGNED NOT NULL,  -- Počet připomínek
    discount_percent TINYINT UNSIGNED DEFAULT 10,  -- Sleva na květiny
    is_available BOOLEAN DEFAULT TRUE,  -- Lze aktuálně zakoupit?
    is_default BOOLEAN DEFAULT FALSE,  -- Výchozí při založení zákazníka
    sort_order TINYINT UNSIGNED DEFAULT 0,  -- Pořadí v seznamu
    description TEXT DEFAULT NULL,  -- Popis pro zákazníka
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_available (is_available),
    INDEX idx_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- Výchozí varianty
INSERT INTO subscription_plans (name, slug, price, reminder_limit, discount_percent, is_available, is_default, sort_order, description) VALUES
('Early bird', 'early_bird', 75.00, 5, 10, TRUE, FALSE, 1, 'Zvýhodněná cena pro první zákazníky. 5 připomínek, 10% sleva na kytice.'),
('Standard', 'standard', 150.00, 10, 10, TRUE, TRUE, 2, 'Plná verze služby. 10 připomínek, 10% sleva na kytice.');

-- Výchozí nastavení (bez hardcoded variant)
INSERT INTO settings (setting_key, setting_value) VALUES
('default_advance_days', '5'),
('workdays', '1,2,3,4,5'),  -- Po-Pá
('email_customer_reminder_subject', 'Blíží se důležité datum! 💐'),
('email_customer_reminder_template', 'Dobrý den{{#name}}, {{name}}{{/name}}!\n\nBlíží se {{event_type}} ({{recipient}}) dne {{date}}.\n\nBrzy vám zavoláme z květinářství Jeleni v zeleni.\n\nPokud nechcete čekat: {{shop_phone}}'),
('email_activation_subject', 'Vítejte v Připomněnce! Nastavte si své připomínky 💐'),
('email_payment_qr_subject', 'QR kód pro platbu předplatného Připomněnka'),
('email_expiration_subject', 'Vaše předplatné Připomněnka brzy vyprší'),
('shop_phone', '123456789'),
('shop_email', 'info@jelenivzeleni.cz'),
('bank_account', '123456789/0100'),
('bank_iban', 'CZ1234567890123456789012'),
('bank_imap_host', 'imap.airbank.cz'),
('bank_imap_email', ''),
('bank_imap_password', ''),
('activation_link_validity_days', '30');

-- Login pokusy (rate limiting)
CREATE TABLE login_attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL,  -- telefon nebo email
    ip_address VARCHAR(45) NOT NULL,
    attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_identifier (identifier),
    INDEX idx_ip (ip_address),
    INDEX idx_time (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- OTP kódy
CREATE TABLE otp_codes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    code VARCHAR(6) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_customer (customer_id),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- Fronta k provolání (generuje se CRONem)
CREATE TABLE call_queue (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reminder_id INT UNSIGNED NOT NULL,
    scheduled_date DATE NOT NULL,
    attempt_count TINYINT UNSIGNED DEFAULT 1,  -- Kolikátý pokus (1, 2, 3...)
    priority TINYINT UNSIGNED DEFAULT 0,  -- Vyšší = důležitější
    status ENUM('pending', 'completed', 'no_answer', 'declined', 'postponed', 'gave_up') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reminder_id) REFERENCES reminders(id) ON DELETE CASCADE,
    UNIQUE KEY unique_reminder_date (reminder_id, scheduled_date),
    INDEX idx_date (scheduled_date),
    INDEX idx_status (status),
    INDEX idx_priority (priority DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;
```

### URL struktura (routing)

```
# Veřejná část
GET  /                      → Úvodní stránka (info o službě)
GET  /registrace            → Registrační formulář
POST /registrace            → Zpracování registrace
GET  /prihlaseni            → Přihlašovací formulář
POST /prihlaseni            → Zpracování přihlášení
POST /odhlaseni             → Odhlášení
GET  /overeni/{token}       → Ověření OTP kódu

# Zákaznická sekce (vyžaduje přihlášení)
GET  /moje-pripominky       → Seznam připomínek
GET  /nova-pripominka       → Formulář nové připomínky
POST /nova-pripominka       → Uložení připomínky
GET  /pripominka/{id}       → Detail/editace připomínky
POST /pripominka/{id}       → Uložení změn
POST /pripominka/{id}/smazat → Smazání připomínky
GET  /profil                → Můj profil
POST /profil                → Uložení profilu
GET  /export-dat            → Export všech mých dat
POST /smazat-ucet           → Smazání účtu

# Administrace (vyžaduje admin přihlášení)
GET  /admin                 → Dashboard
GET  /admin/prihlaseni      → Admin login
POST /admin/prihlaseni      → Admin login zpracování
GET  /admin/dnes            → Seznam k provolání dnes
GET  /admin/tyden           → Přehled týdne
POST /admin/volani/{id}     → Záznam výsledku volání
GET  /admin/zakaznici       → Seznam zákazníků
GET  /admin/zakaznik/{id}   → Detail zákazníka
POST /admin/zakaznik/{id}   → Editace zákazníka
GET  /admin/novy-zakaznik   → Ruční přidání zákazníka
POST /admin/novy-zakaznik   → Uložení nového zákazníka
GET  /admin/predplatne      → Správa předplatného
POST /admin/predplatne/{id}/potvrdit → Potvrzení platby
GET  /admin/nastaveni       → Nastavení systému
POST /admin/nastaveni       → Uložení nastavení

# CRON endpointy (chráněné tokenem)
GET  /cron/generate-queue?token=XXX   → Generování fronty
GET  /cron/send-emails?token=XXX      → Odesílání emailů
GET  /cron/admin-summary?token=XXX    → Email pro Sofii
```

### Konfigurace

```php
<?php
// config/config.php

return [
    'db' => [
        'host' => 'localhost',
        'name' => 'pripomnenka',
        'user' => 'DOPLNIT',
        'pass' => 'DOPLNIT',
        'charset' => 'utf8mb4',
    ],
    
    'app' => [
        'name' => 'Připomněnka',
        'url' => 'https://pripomnenka.jelenivzeleni.cz',
        'timezone' => 'Europe/Prague',
        'locale' => 'cs_CZ',
    ],
    
    'security' => [
        'cron_token' => 'VYGENEROVAT_NAHODNY_TOKEN',
        'session_lifetime' => 86400 * 30,  // 30 dní
        'otp_lifetime' => 600,  // 10 minut
        'max_login_attempts' => 5,
        'lockout_duration' => 900,  // 15 minut
    ],
    
    'email' => [
        'from_address' => 'pripomnenka@jelenivzeleni.cz',
        'from_name' => 'Jeleni v zeleni',
        // Pro shared hosting použít mail() nebo SMTP
        'smtp' => [
            'host' => '',
            'port' => 587,
            'user' => '',
            'pass' => '',
        ],
    ],
];
```

---

## UI/UX požadavky

### Barevná paleta

| Účel | Barva | Hex |
|------|-------|-----|
| **Primární** | Modrá | `#3e6ea1` |
| **Sekundární** | Měděná | `#b87333` |
| **Text** | Tmavě hnědá | `#544a26` |
| **Pozadí** | Krémová | `#fbf8e7` |
| **Úspěch** | Zelená (Jeleni) | `#426027` |
| **Chyba** | Červená | `#c0392b` |
| **Varování** | Oranžová | `#d4853a` |

**Použití barev:**
- Primární (#3e6ea1): Hlavní tlačítka, odkazy, aktivní stavy
- Sekundární (#b87333): CTA tlačítka, akcenty, zvýraznění, FAB
- Text (#544a26): Veškerý text, nadpisy
- Pozadí (#fbf8e7): Hlavní pozadí, karty mohou být bílé (#ffffff)
- Úspěch (#426027): Toast "úspěch", potvrzení, ikona ✅
- Chyba (#c0392b): Chybové hlášky, validace, počítadlo nespárovaných plateb

### Referenční design — GitHub repozitář

**DŮLEŽITÉ:** Jako základ pro UI/UX použij existující aplikaci:
- **Repozitář:** `darkove-poukazy-php` (na GitHubu vlastníka)
- **Co převzít:**
  - Základní layout a strukturu stránek
  - Styl formulářů a tlačítek
  - Responzivní chování
  - Typografii a spacing
  - Strukturu CSS/komponent
- **Co změnit:**
  - Barevnou paletu (viz tabulka výše)
  - Specifické komponenty pro Připomněnku (karty připomínek, call list, atd.)

**Postup:**
1. Naklonuj/prohlédni repozitář `darkove-poukazy-php`
2. Převezmi základní strukturu a styly
3. Aplikuj barevnou paletu Připomněnky
4. Přidej specifické komponenty

### Design principy
- **Čistý, minimalistický** — žádné zbytečné prvky
- **Barvy:** Zelená (primární, brand), bílá, šedé odstíny; červená jen pro chyby/urgentní
- **Mobile-first** — Sofie používá hlavně telefon
- **Velké dotykové plochy** — min. 44×44px pro tlačítka
- **Okamžitá zpětná vazba** — loading stavy, toast notifikace

### Zákaznická část

**Aktivační wizard:**
- Progress bar nahoře (3 kroky)
- Jeden krok = jedna obrazovka
- Možnost vrátit se zpět
- Možnost přeskočit (kde to dává smysl)

**Přehled připomínek:**
- Karty, ne tabulka
- Nejbližší připomínka nahoře, zvýrazněná
- Countdown na každé kartě ("za 23 dní")
- Swipe pro editaci/smazání (mobil)

**Formulář připomínky:**
- Selecty vedle sebe kde to jde (Koho + Co)
- Datum: dva selecty (den + měsíc), ne date picker
- Výchozí hodnoty předvyplněné
- Validace inline (ne až po odeslání)

**Prázdné stavy:**
- Ilustrace + text + CTA tlačítko
- Nikdy prázdná bílá stránka

### Admin část

**Dashboard:**
- Widgety jako karty s velkými čísly
- Barvy signalizují stav (zelená OK, oranžová pozor, červená akce nutná)
- FAB tlačítko "+ Nový zákazník" vždy viditelné

**Seznam k provolání:**
- Telefon jako VELKÉ klikací tlačítko (celá šířka na mobilu)
- Akční tlačítka velká, vedle sebe
- Swipe gesta na mobilu
- Vizuální odlišení opakovaných pokusů

**Založení zákazníka:**
- Jeden formulář, jedna obrazovka
- Výchozí hodnoty = nejčastější scénář
- Po uložení: toast + automatický návrat

**Formuláře obecně:**
- Labels vždy nad polem (ne placeholder only)
- Chyby inline pod polem, červeně
- Úspěch = zelený toast vpravo nahoře
- Auto-save kde to dává smysl (poznámky)

### Responzivita

**Breakpointy:**
- Mobil: < 768px (primární pro admin)
- Tablet: 768–1024px
- Desktop: > 1024px

**Mobil specifika:**
- Hamburger menu
- Bottom navigation pro hlavní sekce
- Swipe gesta
- Sticky header s názvem sekce

### Notifikace a feedback

**Toast notifikace:**
- Vpravo nahoře
- Auto-hide po 3s
- Typy: success (zelená), error (červená), info (modrá), warning (oranžová)

**Loading stavy:**
- Skeleton loading pro seznamy
- Spinner pro akce
- Disabled tlačítko během odesílání

**Potvrzovací dialogy:**
- Jen pro destruktivní akce (smazání)
- Jasný text co se stane
- Červené tlačítko pro nebezpečnou akci

---

## Workflow: Typický scénář

### Nový zákazník (platba v obchodě)
1. Zákazník v obchodě projeví zájem o službu
2. Sofie v adminu klikne "Nový zákazník"
3. Zadá telefon, email, vybere variantu (Early bird / Standard)
4. Zákazník zaplatí hotově nebo kartou
5. Sofie označí "Zaplaceno"
6. Systém ihned pošle zákazníkovi aktivační email
7. Zákazník klikne na odkaz, nastaví si heslo a připomínky
8. Potvrdí v modalu → účet aktivní

### Nový zákazník (platba převodem)
1. Zákazník v obchodě projeví zájem, ale nechce platit hned
2. Sofie založí účet, vybere "Platba převodem"
3. Systém vygeneruje VS (např. `25001`) a pošle email s QR kódem
4. Zákazník zaplatí převodem
5. AirBank pošle notifikaci na `platby@jelenivzeleni.cz`
6. CRON přečte email, spáruje podle VS
7. **Částka sedí:** Automaticky se pošle aktivační email
8. **Částka nesedí:** Sofie vidí upozornění, rozhodne ručně

### Aktivace účtu (zákazník)
1. Zákazník obdrží aktivační email
2. Klikne na odkaz (platný 30 dní)
3. Nastaví si heslo (volitelné) a jméno
4. Přidá své připomínky (do limitu dle varianty)
5. Potvrdí v modalu, že vše sedí
6. Účet je aktivní

### Přidání připomínky (zákazník)
1. Zákazník se přihlásí (heslem nebo OTP)
2. Klikne "Nová připomínka"
3. Vybere typ (Narozeniny), vztah (Manželka), datum (15.3.)
4. Volitelně nastaví rozpočet a poznámku
5. Uloží (pokud má ještě volný limit)

### Provolání (Sofie)
1. Ráno otevře admin na mobilu
2. Vidí "Dnes volat: 5 zákazníků"
3. Klikne na telefon → zahájí hovor
4. Po hovoru klikne "Vyřízeno" a zapíše částku
5. Nebo "Nezvedá" → systém přesune na zítra

### Obnova předplatného
1. 30 dní před expirací: systém pošle email s QR kódem
2. Zákazník zaplatí převodem
3. Systém automaticky prodlouží o rok
4. Pokud nezaplatí: 14 dní před další připomínka
5. Po expiraci: účet zmražen (data zůstávají)

### Nespárovaná platba
1. Přijde platba se špatným VS nebo částkou
2. Sofie vidí na dashboardu "1 nespárovaná platba"
3. Otevře detail: 150 Kč, VS 25099, "Jan Novák"
4. Ručně přiřadí ke správnému zákazníkovi
5. Systém aktivuje/prodlouží předplatné

---

## Testovací data

Při prvním spuštění vytvořit:

**Admin účet:**
- Email: sofie@jelenivzeleni.cz
- Heslo: (nastavit při instalaci)

**Testovací zákazník:**
- Telefon: +420 777 888 999
- Email: test@example.com
- 2-3 připomínky v různých měsících

---

## Prioritizace vývoje

### Fáze 1: MVP (nutné pro spuštění)
- [ ] Základní struktura a routing
- [ ] Databáze a modely
- [ ] Admin: založení zákazníka (telefon, email, varianta, způsob platby)
- [ ] Generování VS a QR kódu pro platbu
- [ ] Aktivační email s unikátním odkazem
- [ ] Aktivační formulář: heslo + připomínky + potvrzovací modal
- [ ] Přihlášení zákazníka (hybridní: heslo nebo OTP)
- [ ] CRUD připomínek s kontrolou limitu
- [ ] Admin dashboard a seznam k provolání
- [ ] Ruční potvrzení platby v adminu
- [ ] GDPR: export dat, smazání účtu

### Fáze 2: Automatizace plateb
- [ ] IMAP čtení bankovních notifikací (AirBank)
- [ ] Automatické párování podle VS
- [ ] Kontrola částky, upozornění na nesrovnalosti
- [ ] Admin: seznam nespárovaných plateb, ruční přiřazení

### Fáze 3: Notifikace
- [ ] Automatické emaily zákazníkům (připomínky událostí)
- [ ] Emaily o expiraci předplatného (30 dní, 14 dní)
- [ ] Souhrnný email pro Sofii
- [ ] Šablony emailů v nastavení
- [ ] CRON pro generování fronty k provolání

### Fáze 4: Vylepšení
- [ ] Historie objednávek u zákazníka
- [ ] Statistiky v adminu (příjmy z předplatného, počet provolání, úspěšnost)
- [ ] Interní poznámky k zákazníkům (preferované květiny, čas volání)
- [ ] Early bird nastavení (datum/počet limit)
- [ ] Znovu odeslat aktivační email / QR kód

### Fáze 5: Budoucí rozšíření (mimo scope)
- SMS notifikace (napojení na SMS Operator API)
- Napojení na e-shop
- PWA pro admin (offline režim, push notifikace)

---

## Poznámky pro vývojáře

### Výpočet pracovních dní
Česká specifika:
- Pracovní dny: Po–Pá
- Státní svátky: 1.1., Velikonoce (pohyblivé!), 1.5., 8.5., 5.7., 6.7., 28.9., 28.10., 17.11., 24.12., 25.12., 26.12.
- Použít knihovnu nebo vlastní helper s výpočtem Velikonoc

### Email na shared hostingu
- Preferovat `mail()` funkci (nejspolehlivější na Webglobe)
- Alternativně SMTP přes externí službu
- Nastavit SPF/DKIM na doméně

### Bezpečnost
- Všechny SQL dotazy přes PDO prepared statements
- CSRF token na každém formuláři
- Session regenerate po přihlášení
- Escapovat všechny výstupy

### Lokalizace
- Vše v češtině
- České formáty dat (15. března, ne March 15)
- České řazení (háčky, čárky)

---

## Kontakt

**Projekt:** Připomněnka pro Jeleni v zeleni  
**Vlastník:** Honza  
**Uživatel systému:** Sofie (manželka)  
**Web květinářství:** jelenivzeleni.cz  
**Autor systému:** Dizen design
