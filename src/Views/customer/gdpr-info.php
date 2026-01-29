<?php
$setting = new \Models\Setting();
?>
<div class="container" style="max-width: 800px; padding: var(--spacing-2xl) var(--spacing-md);">

    <div style="text-align: center; margin-bottom: var(--spacing-2xl);">
        <h1 style="font-size: var(--font-size-2xl); margin-bottom: var(--spacing-sm);">Ochrana osobních údajů</h1>
        <p style="color: var(--color-text-light); font-size: var(--font-size-md);">
            Informace o zpracování osobních údajů dle GDPR
        </p>
    </div>

    <!-- 1. Správce -->
    <div class="card" style="margin-bottom: var(--spacing-lg);">
        <div class="card-body">
            <h2 style="font-size: var(--font-size-lg); margin-bottom: var(--spacing-md); color: var(--color-primary);">
                1. Správce osobních údajů
            </h2>
            <p style="line-height: 1.7; color: var(--color-text);">
                <strong>Jeleni v zeleni</strong><br>
                Palackého 1308/32, 586 01 Jihlava<br>
                Sofie Janečková<br>
                IČO: 14111250<br>
                <br>
                <strong>Kontakt:</strong><br>
                Email: <?= e($setting->get('shop_email', 'info@jelenivzeleni.cz')) ?><br>
                Telefon: <?= e($setting->get('shop_phone', '775 900 551')) ?>
            </p>
        </div>
    </div>

    <!-- 2. Jaké údaje -->
    <div class="card" style="margin-bottom: var(--spacing-lg);">
        <div class="card-body">
            <h2 style="font-size: var(--font-size-lg); margin-bottom: var(--spacing-md); color: var(--color-primary);">
                2. Jaké osobní údaje zpracováváme
            </h2>
            <ul style="line-height: 1.8; color: var(--color-text); padding-left: var(--spacing-lg);">
                <li style="margin-bottom: var(--spacing-xs);">
                    <strong>Identifikační údaje:</strong> vaše jméno (dobrovolné)
                </li>
                <li style="margin-bottom: var(--spacing-xs);">
                    <strong>Kontaktní údaje:</strong> telefonní číslo, emailová adresa
                </li>
                <li style="margin-bottom: var(--spacing-xs);">
                    <strong>Údaje o připomínkách:</strong> typ události (narozeniny, výročí), datum, vztah k oslavenci (manželka, matka apod.), cenový rozsah
                </li>
                <li style="margin-bottom: var(--spacing-xs);">
                    <strong>Vaše poznámky:</strong> preference ohledně květin, barev a stylů
                </li>
                <li style="margin-bottom: var(--spacing-xs);">
                    <strong>Technické údaje:</strong> IP adresa při přihlášení (uchováváno 90 dní)
                </li>
            </ul>
            <div style="margin-top: var(--spacing-md); padding: var(--spacing-md); background: var(--color-surface); border-left: 3px solid var(--color-success); border-radius: var(--radius-sm);">
                <p style="margin: 0; color: var(--color-text-light); font-size: var(--font-size-sm);">
                    <strong>🔒 Co NEUKLÁDÁME:</strong> rok narození oslavenců, jejich jména ani další citlivé údaje.
                    Ukládáme pouze vztah (např. "manželka"), nikoliv identitu třetích osob.
                </p>
            </div>
        </div>
    </div>

    <!-- 3. Účel -->
    <div class="card" style="margin-bottom: var(--spacing-lg);">
        <div class="card-body">
            <h2 style="font-size: var(--font-size-lg); margin-bottom: var(--spacing-md); color: var(--color-primary);">
                3. Účel zpracování
            </h2>
            <p style="line-height: 1.7; color: var(--color-text); margin-bottom: var(--spacing-sm);">
                Vaše údaje zpracováváme výhradně za účelem:
            </p>
            <ul style="line-height: 1.8; color: var(--color-text); padding-left: var(--spacing-lg);">
                <li style="margin-bottom: var(--spacing-xs);">Poskytování služby Připomněnka — upozornění na blížící se výročí</li>
                <li style="margin-bottom: var(--spacing-xs);">Telefonického kontaktování s nabídkou květinových aranžmá</li>
                <li style="margin-bottom: var(--spacing-xs);">Správy vašeho předplatného a plateb</li>
                <li>Vedení statistik a zlepšování kvality služby</li>
            </ul>
        </div>
    </div>

    <!-- 4. Právní základ -->
    <div class="card" style="margin-bottom: var(--spacing-lg);">
        <div class="card-body">
            <h2 style="font-size: var(--font-size-lg); margin-bottom: var(--spacing-md); color: var(--color-primary);">
                4. Právní základ zpracování
            </h2>
            <p style="line-height: 1.7; color: var(--color-text);">
                Vaše osobní údaje zpracováváme na základě vašeho <strong>výslovného souhlasu</strong>
                (článek 6 odst. 1 písm. a) nařízení GDPR), který jste poskytli při aktivaci účtu.
            </p>
            <p style="line-height: 1.7; color: var(--color-text); margin-top: var(--spacing-sm);">
                <strong>Odvolání souhlasu:</strong> Souhlas můžete kdykoliv odvolat smazáním účtu.
                Odvolání nemá vliv na zákonnost zpracování založeného na souhlasu před jeho odvoláním.
            </p>
        </div>
    </div>

    <!-- 5. Doba uchovávání -->
    <div class="card" style="margin-bottom: var(--spacing-lg);">
        <div class="card-body">
            <h2 style="font-size: var(--font-size-lg); margin-bottom: var(--spacing-md); color: var(--color-primary);">
                5. Doba uchovávání údajů
            </h2>
            <ul style="line-height: 1.8; color: var(--color-text); padding-left: var(--spacing-lg);">
                <li style="margin-bottom: var(--spacing-xs);">
                    <strong>Aktivní účty:</strong> po dobu trvání předplatného bez časového omezení
                </li>
                <li style="margin-bottom: var(--spacing-xs);">
                    <strong>Neaktivní účty:</strong> po 2 letech bez aktivity vás upozorníme emailem, po dalších 30 dnech účet automaticky smažeme
                </li>
                <li style="margin-bottom: var(--spacing-xs);">
                    <strong>Historie volání:</strong> uchováváme 2 roky pro statistické účely
                </li>
                <li style="margin-bottom: var(--spacing-xs);">
                    <strong>Logy přístupu:</strong> automaticky mažeme po 90 dnech
                </li>
            </ul>
        </div>
    </div>

    <!-- 6. Vaše práva -->
    <div class="card" style="margin-bottom: var(--spacing-lg);">
        <div class="card-body">
            <h2 style="font-size: var(--font-size-lg); margin-bottom: var(--spacing-md); color: var(--color-primary);">
                6. Vaše práva
            </h2>
            <p style="line-height: 1.7; color: var(--color-text); margin-bottom: var(--spacing-md);">
                V souvislosti se zpracováním vašich osobních údajů máte následující práva:
            </p>
            <ul style="line-height: 1.8; color: var(--color-text); padding-left: var(--spacing-lg);">
                <li style="margin-bottom: var(--spacing-sm);">
                    <strong>Právo na přístup k údajům</strong> — můžete si kdykoliv stáhnout všechna svá data
                    <?php if (\Session::isLoggedIn()): ?>
                        <br><a href="/export-dat" style="color: var(--color-primary);">→ Export dat</a>
                    <?php endif; ?>
                </li>
                <li style="margin-bottom: var(--spacing-sm);">
                    <strong>Právo na opravu</strong> — můžete upravit své kontaktní údaje a poznámky
                    <?php if (\Session::isLoggedIn()): ?>
                        <br><a href="/profil" style="color: var(--color-primary);">→ Můj profil</a>
                    <?php endif; ?>
                </li>
                <li style="margin-bottom: var(--spacing-sm);">
                    <strong>Právo na výmaz ("právo být zapomenut")</strong> — můžete kdykoliv smazat svůj účet včetně všech dat
                    <?php if (\Session::isLoggedIn()): ?>
                        <br><a href="/profil" style="color: var(--color-primary);">→ Smazat účet</a>
                    <?php endif; ?>
                </li>
                <li style="margin-bottom: var(--spacing-sm);">
                    <strong>Právo na přenositelnost</strong> — data si můžete exportovat ve strojově čitelném formátu (JSON)
                </li>
                <li style="margin-bottom: var(--spacing-sm);">
                    <strong>Právo podat stížnost</strong> — u dozorového orgánu:<br>
                    Úřad pro ochranu osobních údajů<br>
                    Pplk. Sochora 27, 170 00 Praha 7<br>
                    Web: <a href="https://www.uoou.cz" target="_blank" rel="noopener" style="color: var(--color-primary);">www.uoou.cz</a>
                </li>
            </ul>
        </div>
    </div>

    <!-- 7. Zabezpečení -->
    <div class="card" style="margin-bottom: var(--spacing-lg);">
        <div class="card-body">
            <h2 style="font-size: var(--font-size-lg); margin-bottom: var(--spacing-md); color: var(--color-primary);">
                7. Zabezpečení osobních údajů
            </h2>
            <p style="line-height: 1.7; color: var(--color-text); margin-bottom: var(--spacing-sm);">
                Vaše údaje chráníme pomocí moderních technologií:
            </p>
            <ul style="line-height: 1.8; color: var(--color-text); padding-left: var(--spacing-lg);">
                <li style="margin-bottom: var(--spacing-xs);">Šifrované připojení (HTTPS) pro veškerou komunikaci</li>
                <li style="margin-bottom: var(--spacing-xs);">Hashování hesel pomocí algoritmu bcrypt</li>
                <li style="margin-bottom: var(--spacing-xs);">Ochrana proti CSRF útokům a SQL injection</li>
                <li style="margin-bottom: var(--spacing-xs);">Pravidelné bezpečnostní zálohy databáze</li>
                <li style="margin-bottom: var(--spacing-xs);">Logování přístupů a bezpečnostních událostí</li>
                <li>Omezení počtu pokusů o přihlášení (ochrana proti hrubé síle)</li>
            </ul>
        </div>
    </div>

    <!-- 8. Sdílení údajů -->
    <div class="card" style="margin-bottom: var(--spacing-lg);">
        <div class="card-body">
            <h2 style="font-size: var(--font-size-lg); margin-bottom: var(--spacing-md); color: var(--color-primary);">
                8. Předávání osobních údajů třetím stranám
            </h2>
            <div style="padding: var(--spacing-md); background: var(--color-surface); border-left: 3px solid var(--color-success); border-radius: var(--radius-sm); margin-bottom: var(--spacing-md);">
                <p style="margin: 0; color: var(--color-text); font-weight: 600;">
                    ✓ Vaše osobní údaje <strong>neprodáváme</strong>, nepronajímáme ani jinak nepředáváme třetím stranám pro marketingové účely.
                </p>
            </div>
            <p style="line-height: 1.7; color: var(--color-text); margin-bottom: var(--spacing-sm);">
                Údaje mohou být sdíleny pouze v následujících případech:
            </p>
            <ul style="line-height: 1.8; color: var(--color-text); padding-left: var(--spacing-lg);">
                <li style="margin-bottom: var(--spacing-xs);">
                    <strong>Poskytovatel hostingu</strong> (Webglobe.cz) — technické zajištění provozu aplikace
                </li>
                <li style="margin-bottom: var(--spacing-xs);">
                    <strong>Zákonná povinnost</strong> — na základě právního předpisu nebo soudního/úředního rozhodnutí
                </li>
            </ul>
        </div>
    </div>

    <!-- 9. Kontakt -->
    <div class="card" style="margin-bottom: var(--spacing-2xl);">
        <div class="card-body">
            <h2 style="font-size: var(--font-size-lg); margin-bottom: var(--spacing-md); color: var(--color-primary);">
                9. Kontakt pro otázky k GDPR
            </h2>
            <p style="line-height: 1.7; color: var(--color-text);">
                Máte-li jakékoliv dotazy ohledně zpracování vašich osobních údajů nebo chcete
                uplatnit svá práva, kontaktujte nás:
            </p>
            <p style="line-height: 1.7; color: var(--color-text); margin-top: var(--spacing-md);">
                Email: <a href="mailto:<?= e($setting->get('shop_email', 'info@jelenivzeleni.cz')) ?>" style="color: var(--color-primary); font-weight: 600;"><?= e($setting->get('shop_email', 'info@jelenivzeleni.cz')) ?></a><br>
                Telefon: <a href="tel:<?= e(str_replace(' ', '', $setting->get('shop_phone', '775900551'))) ?>" style="color: var(--color-primary); font-weight: 600;"><?= e($setting->get('shop_phone', '775 900 551')) ?></a>
            </p>
            <p style="line-height: 1.7; color: var(--color-text-light); font-size: var(--font-size-sm); margin-top: var(--spacing-md); margin-bottom: 0;">
                Odpovíme vám do 30 dnů od obdržení žádosti.
            </p>
        </div>
    </div>

    <!-- Navigace -->
    <div style="text-align: center;">
        <?php if (\Session::isLoggedIn()): ?>
            <a href="/profil" class="btn btn--outline">← Zpět na profil</a>
        <?php else: ?>
            <a href="/" class="btn btn--outline">← Zpět na úvodní stránku</a>
        <?php endif; ?>
    </div>

    <!-- Footer info -->
    <div style="margin-top: var(--spacing-2xl); padding-top: var(--spacing-lg); border-top: 1px solid var(--color-border); text-align: center;">
        <p style="color: var(--color-text-muted); font-size: var(--font-size-sm);">
            Poslední aktualizace: 29. ledna 2026
        </p>
    </div>

</div>
