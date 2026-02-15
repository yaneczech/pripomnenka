<?php
$setting = new \Models\Setting();
$shopName = $setting->get('shop_name_full', $setting->get('shop_name', 'Provozovatel'));
$shopAddress = $setting->get('shop_address', '');
$shopIco = $setting->get('shop_ico', '');
$shopOwner = $setting->get('shop_owner', '');
$shopEmail = $setting->get('shop_email', '');
$shopPhone = $setting->get('shop_phone', '');
$effectiveDate = $setting->get('terms_effective_date', date('Y-m-d'));
?>
<div class="container" style="max-width: 800px; padding: var(--spacing-2xl) var(--spacing-md);">

    <div style="text-align: center; margin-bottom: var(--spacing-2xl);">
        <h1 style="font-size: var(--font-size-2xl); margin-bottom: var(--spacing-sm);">Obchodní podmínky</h1>
        <p style="color: var(--color-text-light); font-size: var(--font-size-md);">
            služby Připomněnka
        </p>
    </div>

    <!-- 1. Úvodní ustanovení -->
    <div class="card" style="margin-bottom: var(--spacing-lg);">
        <div class="card-body">
            <h2 style="font-size: var(--font-size-lg); margin-bottom: var(--spacing-md); color: var(--color-primary);">
                1. Úvodní ustanovení
            </h2>
            <p style="line-height: 1.7; color: var(--color-text);">
                Tyto obchodní podmínky (dále jen „podmínky") upravují vzájemná práva a povinnosti
                mezi provozovatelem služby a zákazníkem.
            </p>
            <p style="line-height: 1.7; color: var(--color-text); margin-top: var(--spacing-sm);">
                <strong>Provozovatel:</strong><br>
                <?= e($shopName) ?><br>
                <?php if ($shopAddress): ?><?= e($shopAddress) ?><br><?php endif; ?>
                <?php if ($shopOwner): ?><?= e($shopOwner) ?><br><?php endif; ?>
                <?php if ($shopIco): ?>IČO: <?= e($shopIco) ?><br><?php endif; ?>
                <?php if ($shopEmail): ?>Email: <?= e($shopEmail) ?><br><?php endif; ?>
                <?php if ($shopPhone): ?>Tel: <?= e($shopPhone) ?><?php endif; ?>
            </p>
        </div>
    </div>

    <!-- 2. Popis služby -->
    <div class="card" style="margin-bottom: var(--spacing-lg);">
        <div class="card-body">
            <h2 style="font-size: var(--font-size-lg); margin-bottom: var(--spacing-md); color: var(--color-primary);">
                2. Popis služby
            </h2>
            <p style="line-height: 1.7; color: var(--color-text);">
                Služba Připomněnka (dále jen „služba") umožňuje zákazníkovi zadat důležitá data
                (narozeniny, výročí, svátky apod.), o kterých bude včas informován. Před blížícím se
                datem provozovatel telefonicky kontaktuje zákazníka s nabídkou květinového aranžmá.
            </p>
            <p style="line-height: 1.7; color: var(--color-text); margin-top: var(--spacing-sm);">
                Služba zahrnuje:
            </p>
            <ul style="line-height: 1.8; color: var(--color-text); padding-left: var(--spacing-lg);">
                <li style="margin-bottom: var(--spacing-xs);">Správu připomínek dle limitu zvoleného tarifu</li>
                <li style="margin-bottom: var(--spacing-xs);">Osobní telefonické kontaktování před každou událostí</li>
                <li>Slevu na květiny dle zvoleného tarifu</li>
            </ul>
        </div>
    </div>

    <!-- 3. Uzavření smlouvy -->
    <div class="card" style="margin-bottom: var(--spacing-lg);">
        <div class="card-body">
            <h2 style="font-size: var(--font-size-lg); margin-bottom: var(--spacing-md); color: var(--color-primary);">
                3. Uzavření smlouvy a aktivace
            </h2>
            <p style="line-height: 1.7; color: var(--color-text);">
                Smlouva o poskytování služby je uzavřena okamžikem, kdy zákazník při aktivaci účtu
                potvrdí souhlas s těmito podmínkami a se zpracováním osobních údajů.
            </p>
            <p style="line-height: 1.7; color: var(--color-text); margin-top: var(--spacing-sm);">
                Postup uzavření smlouvy:
            </p>
            <ol style="line-height: 1.8; color: var(--color-text); padding-left: var(--spacing-lg);">
                <li style="margin-bottom: var(--spacing-xs);">Zákazník projeví zájem o službu (v provozovně nebo prostřednictvím kontaktního formuláře)</li>
                <li style="margin-bottom: var(--spacing-xs);">Provozovatel založí zákazníkovi účet a odešle aktivační odkaz</li>
                <li style="margin-bottom: var(--spacing-xs);">Zákazník aktivuje účet, odsouhlasí podmínky a nastaví si připomínky</li>
                <li>Uhrazením předplatného a aktivací účtu vstupuje smlouva v platnost</li>
            </ol>
        </div>
    </div>

    <!-- 4. Předplatné a platby -->
    <div class="card" style="margin-bottom: var(--spacing-lg);">
        <div class="card-body">
            <h2 style="font-size: var(--font-size-lg); margin-bottom: var(--spacing-md); color: var(--color-primary);">
                4. Předplatné a platební podmínky
            </h2>
            <p style="line-height: 1.7; color: var(--color-text);">
                Služba je poskytována na základě ročního předplatného. Aktuální nabídka tarifů
                je uvedena na webových stránkách služby nebo přímo v provozovně.
            </p>
            <p style="line-height: 1.7; color: var(--color-text); margin-top: var(--spacing-sm);">
                <strong>Platba:</strong> Předplatné lze uhradit hotově, platební kartou v provozovně
                nebo bankovním převodem na základě zaslaného QR kódu.
            </p>
            <p style="line-height: 1.7; color: var(--color-text); margin-top: var(--spacing-sm);">
                <strong>Platnost:</strong> Předplatné platí 12 měsíců od data úhrady.
                Před vypršením platnosti bude zákazník upozorněn e-mailem.
            </p>
            <p style="line-height: 1.7; color: var(--color-text); margin-top: var(--spacing-sm);">
                <strong>Změna cen:</strong> Provozovatel si vyhrazuje právo měnit ceny tarifů.
                Změna ceny nemá vliv na již uhrazené předplatné.
            </p>
        </div>
    </div>

    <!-- 5. Práva a povinnosti -->
    <div class="card" style="margin-bottom: var(--spacing-lg);">
        <div class="card-body">
            <h2 style="font-size: var(--font-size-lg); margin-bottom: var(--spacing-md); color: var(--color-primary);">
                5. Práva a povinnosti zákazníka
            </h2>
            <p style="line-height: 1.7; color: var(--color-text); margin-bottom: var(--spacing-sm);">
                <strong>Zákazník má právo:</strong>
            </p>
            <ul style="line-height: 1.8; color: var(--color-text); padding-left: var(--spacing-lg); margin-bottom: var(--spacing-md);">
                <li style="margin-bottom: var(--spacing-xs);">Spravovat své připomínky v rámci limitu zvoleného tarifu</li>
                <li style="margin-bottom: var(--spacing-xs);">Měnit své kontaktní údaje a preference</li>
                <li style="margin-bottom: var(--spacing-xs);">Uplatnit slevu na nákup květin dle podmínek tarifu</li>
                <li style="margin-bottom: var(--spacing-xs);">Exportovat nebo smazat svá data (viz <a href="/ochrana-udaju" style="color: var(--color-primary);">Ochrana osobních údajů</a>)</li>
                <li>Odmítnout telefonické kontaktování u konkrétní připomínky</li>
            </ul>
            <p style="line-height: 1.7; color: var(--color-text); margin-bottom: var(--spacing-sm);">
                <strong>Zákazník je povinen:</strong>
            </p>
            <ul style="line-height: 1.8; color: var(--color-text); padding-left: var(--spacing-lg);">
                <li style="margin-bottom: var(--spacing-xs);">Uvádět pravdivé kontaktní údaje</li>
                <li style="margin-bottom: var(--spacing-xs);">Udržovat aktuální telefonní číslo a e-mailovou adresu</li>
                <li>Nesdílet přístupové údaje ke svému účtu s třetími osobami</li>
            </ul>
        </div>
    </div>

    <!-- 6. Práva a povinnosti provozovatele -->
    <div class="card" style="margin-bottom: var(--spacing-lg);">
        <div class="card-body">
            <h2 style="font-size: var(--font-size-lg); margin-bottom: var(--spacing-md); color: var(--color-primary);">
                6. Práva a povinnosti provozovatele
            </h2>
            <p style="line-height: 1.7; color: var(--color-text); margin-bottom: var(--spacing-sm);">
                <strong>Provozovatel se zavazuje:</strong>
            </p>
            <ul style="line-height: 1.8; color: var(--color-text); padding-left: var(--spacing-lg); margin-bottom: var(--spacing-md);">
                <li style="margin-bottom: var(--spacing-xs);">Včas kontaktovat zákazníka před blížícím se datem (v pracovních dnech)</li>
                <li style="margin-bottom: var(--spacing-xs);">Chránit osobní údaje zákazníka v souladu s GDPR</li>
                <li>Zajistit nepřetržitý provoz služby v rozumné míře</li>
            </ul>
            <p style="line-height: 1.7; color: var(--color-text); margin-bottom: var(--spacing-sm);">
                <strong>Provozovatel si vyhrazuje právo:</strong>
            </p>
            <ul style="line-height: 1.8; color: var(--color-text); padding-left: var(--spacing-lg);">
                <li style="margin-bottom: var(--spacing-xs);">Krátkodobě přerušit provoz služby z důvodu údržby</li>
                <li style="margin-bottom: var(--spacing-xs);">Změnit tyto podmínky (zákazník bude informován e-mailem)</li>
                <li>Zrušit účet zákazníka při závažném porušení podmínek</li>
            </ul>
        </div>
    </div>

    <!-- 7. Zrušení a ukončení -->
    <div class="card" style="margin-bottom: var(--spacing-lg);">
        <div class="card-body">
            <h2 style="font-size: var(--font-size-lg); margin-bottom: var(--spacing-md); color: var(--color-primary);">
                7. Ukončení služby
            </h2>
            <p style="line-height: 1.7; color: var(--color-text);">
                <strong>Ze strany zákazníka:</strong> Zákazník může kdykoliv smazat svůj účet.
                V takovém případě jsou všechna jeho data nevratně odstraněna. Uhrazené předplatné
                se nevrací.
            </p>
            <p style="line-height: 1.7; color: var(--color-text); margin-top: var(--spacing-sm);">
                <strong>Neobnovením předplatného:</strong> Pokud zákazník neuhradí předplatné
                po jeho vypršení, účet je pozastaven (data zůstávají uložena). Po opětovné úhradě
                je účet automaticky obnoven.
            </p>
            <p style="line-height: 1.7; color: var(--color-text); margin-top: var(--spacing-sm);">
                <strong>Neaktivní účty:</strong> Účty bez aktivity po dobu 2 let budou po předchozím
                upozornění e-mailem smazány (včetně všech dat).
            </p>
        </div>
    </div>

    <!-- 8. Odpovědnost -->
    <div class="card" style="margin-bottom: var(--spacing-lg);">
        <div class="card-body">
            <h2 style="font-size: var(--font-size-lg); margin-bottom: var(--spacing-md); color: var(--color-primary);">
                8. Omezení odpovědnosti
            </h2>
            <p style="line-height: 1.7; color: var(--color-text);">
                Provozovatel neodpovídá za škody vzniklé v důsledku:
            </p>
            <ul style="line-height: 1.8; color: var(--color-text); padding-left: var(--spacing-lg);">
                <li style="margin-bottom: var(--spacing-xs);">Uvedení nesprávných kontaktních údajů zákazníkem</li>
                <li style="margin-bottom: var(--spacing-xs);">Nedostupnosti zákazníka na uvedeném telefonním čísle</li>
                <li style="margin-bottom: var(--spacing-xs);">Technických výpadků mimo kontrolu provozovatele (výpadky serveru, internetu)</li>
                <li>Zásahů vyšší moci</li>
            </ul>
            <p style="line-height: 1.7; color: var(--color-text); margin-top: var(--spacing-sm);">
                Služba Připomněnka je informační a připomínací nástroj. Provozovatel nezaručuje
                uzavření objednávky květin ani dostupnost konkrétního sortimentu.
            </p>
        </div>
    </div>

    <!-- 9. Ochrana osobních údajů -->
    <div class="card" style="margin-bottom: var(--spacing-lg);">
        <div class="card-body">
            <h2 style="font-size: var(--font-size-lg); margin-bottom: var(--spacing-md); color: var(--color-primary);">
                9. Ochrana osobních údajů
            </h2>
            <p style="line-height: 1.7; color: var(--color-text);">
                Zpracování osobních údajů se řídí samostatným dokumentem
                <a href="/ochrana-udaju" style="color: var(--color-primary); font-weight: 600;">Ochrana osobních údajů (GDPR)</a>,
                který je nedílnou součástí těchto podmínek.
            </p>
        </div>
    </div>

    <!-- 10. Závěrečná ustanovení -->
    <div class="card" style="margin-bottom: var(--spacing-2xl);">
        <div class="card-body">
            <h2 style="font-size: var(--font-size-lg); margin-bottom: var(--spacing-md); color: var(--color-primary);">
                10. Závěrečná ustanovení
            </h2>
            <p style="line-height: 1.7; color: var(--color-text);">
                Tyto podmínky se řídí právním řádem České republiky. Případné spory budou řešeny
                mimosoudní cestou, případně příslušným soudem dle sídla provozovatele.
            </p>
            <p style="line-height: 1.7; color: var(--color-text); margin-top: var(--spacing-sm);">
                Provozovatel si vyhrazuje právo tyto podmínky jednostranně změnit. O změně bude
                zákazník informován e-mailem nejméně 14 dnů před nabytím účinnosti. Pokud zákazník
                se změnou nesouhlasí, má právo smlouvu ukončit smazáním účtu.
            </p>
        </div>
    </div>

    <!-- Navigace -->
    <div style="text-align: center;">
        <?php if (\Session::isLoggedIn()): ?>
            <a href="/profil" class="btn btn--outline">Zpět na profil</a>
        <?php else: ?>
            <a href="/" class="btn btn--outline">Zpět na úvodní stránku</a>
        <?php endif; ?>
        <a href="/ochrana-udaju" class="btn btn--ghost">Ochrana osobních údajů</a>
    </div>

    <!-- Footer info -->
    <div style="margin-top: var(--spacing-2xl); padding-top: var(--spacing-lg); border-top: 1px solid var(--color-border); text-align: center;">
        <p style="color: var(--color-text-muted); font-size: var(--font-size-sm);">
            Účinné od: <?= e(date('j. n. Y', strtotime($effectiveDate))) ?>
        </p>
    </div>

</div>
