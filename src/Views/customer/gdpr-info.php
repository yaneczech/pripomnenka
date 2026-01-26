<div class="container" style="max-width: 800px;">

    <div class="page-header" style="margin-bottom: var(--spacing-xl);">
        <h1>Ochrana osobních údajů</h1>
        <p class="text-muted">Informace o zpracování vašich osobních údajů dle GDPR</p>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h2>1. Správce osobních údajů</h2>
            <p>
                <strong>Květinářství Jeleni v zeleni</strong><br>
                Adresa: [adresa provozovny]<br>
                Email: <?= e(\Setting::get('shop_email', 'info@jelenivzeleni.cz')) ?><br>
                Telefon: <?= e(\Setting::get('shop_phone', '123 456 789')) ?>
            </p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h2>2. Jaké údaje zpracováváme</h2>
            <ul>
                <li><strong>Identifikační údaje:</strong> jméno (pokud ho vyplníte)</li>
                <li><strong>Kontaktní údaje:</strong> telefonní číslo, emailová adresa</li>
                <li><strong>Údaje o připomínkách:</strong> typ události (narozeniny, výročí apod.), datum, vztah k oslavenci (manželka, matka apod.), cenový rozsah</li>
                <li><strong>Poznámky:</strong> vaše preference (oblíbené květiny, barvy apod.)</li>
            </ul>
            <p class="text-muted text-small mt-2">
                <strong>Neukládáme:</strong> rok narození oslavenců ani jejich jména — pouze vztah (např. "manželka").
            </p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h2>3. Účel zpracování</h2>
            <p>Vaše údaje zpracováváme za účelem:</p>
            <ul>
                <li>Poskytování služby Připomínka — upozornění na blížící se výročí a svátky</li>
                <li>Kontaktování s nabídkou květin před důležitými daty</li>
                <li>Správy vašeho předplatného</li>
            </ul>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h2>4. Právní základ</h2>
            <p>
                Vaše údaje zpracováváme na základě vašeho <strong>výslovného souhlasu</strong>
                (čl. 6 odst. 1 písm. a) GDPR), který jste udělili při registraci.
            </p>
            <p>
                Souhlas můžete kdykoliv odvolat smazáním účtu.
            </p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h2>5. Doba uchovávání</h2>
            <ul>
                <li><strong>Aktivní účty:</strong> po dobu trvání předplatného a 2 roky po jeho vypršení</li>
                <li><strong>Neaktivní účty:</strong> po 2 letech bez aktivity vás upozorníme emailem a po 30 dnech účet smažeme</li>
                <li><strong>Logy přístupů:</strong> 90 dní</li>
            </ul>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h2>6. Vaše práva</h2>
            <p>Máte právo:</p>
            <ul>
                <li><strong>Na přístup</strong> — můžete si stáhnout všechna svá data (<a href="/export-dat">Export dat</a>)</li>
                <li><strong>Na opravu</strong> — můžete upravit své údaje v <a href="/profil">profilu</a></li>
                <li><strong>Na výmaz</strong> — můžete <a href="/smazat-ucet">smazat svůj účet</a></li>
                <li><strong>Na přenositelnost</strong> — data si můžete stáhnout ve formátu JSON</li>
                <li><strong>Podat stížnost</strong> — u Úřadu pro ochranu osobních údajů (www.uoou.cz)</li>
            </ul>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h2>7. Zabezpečení</h2>
            <p>Vaše údaje chráníme pomocí:</p>
            <ul>
                <li>Šifrovaného připojení (HTTPS)</li>
                <li>Hashování hesel (bcrypt)</li>
                <li>Ochrany proti CSRF útokům</li>
                <li>Pravidelného zálohování</li>
            </ul>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h2>8. Sdílení údajů</h2>
            <p>
                Vaše údaje <strong>neprodáváme</strong> ani nepředáváme třetím stranám.
            </p>
            <p>
                Údaje mohou být sdíleny pouze:
            </p>
            <ul>
                <li>S poskytovatelem hostingu (technické zajištění služby)</li>
                <li>Na základě zákonné povinnosti (soudy, policie)</li>
            </ul>
        </div>
    </div>

    <div class="mt-4 text-center">
        <a href="/profil" class="btn btn--outline">← Zpět na profil</a>
    </div>

</div>
