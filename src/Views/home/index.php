<?php
$setting = new \Models\Setting();
$shopPhone = $setting->get('shop_phone', '');
$shopName = $setting->get('shop_name_full', $setting->get('shop_name', 'Jeleni v zeleni'));
?>

<!-- Hero sekce -->
<section style="text-align: center; padding: var(--spacing-2xl) var(--spacing-md) var(--spacing-xl);">
    <div class="container" style="max-width: 800px;">
        <img src="<?= asset('img/logo.svg') ?>" alt="Připomněnka" style="max-width: 280px; margin: 0 auto var(--spacing-xl) auto;">

        <h1 style="font-size: 2.5rem; margin-bottom: var(--spacing-md); line-height: 1.2;">
            Nikdy nezapomeňte<br>na důležitá data
        </h1>

        <p style="font-size: var(--font-size-lg); color: var(--color-text-light); margin-bottom: var(--spacing-xl); max-width: 600px; margin-left: auto; margin-right: auto; line-height: 1.7;">
            Narozeniny, výročí, svátky — my si je pamatujeme za vás.
            Včas vám zavoláme a společně vybereme tu pravou kytici.
        </p>

        <div style="display: flex; gap: var(--spacing-md); justify-content: center; flex-wrap: wrap;">
            <a href="/prihlaseni" class="btn btn--primary btn--large">
                <i class="ri-login-box-line"></i> Přihlásit se
            </a>
            <a href="#jak-to-funguje" class="btn btn--outline btn--large">
                Jak to funguje?
            </a>
        </div>
    </div>
</section>

<!-- Jak to funguje -->
<section id="jak-to-funguje" style="padding: var(--spacing-2xl) var(--spacing-md); background: var(--color-surface);">
    <div class="container" style="max-width: 900px;">
        <h2 style="text-align: center; font-size: var(--font-size-2xl); margin-bottom: var(--spacing-2xl);">
            Jak to funguje?
        </h2>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--spacing-lg); text-align: center;"
             class="landing-steps">
            <div>
                <div style="width: 64px; height: 64px; border-radius: var(--radius-full); background: var(--color-primary); color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--spacing-md); font-size: 1.5rem;">
                    <i class="ri-store-2-line"></i>
                </div>
                <h3 style="font-size: var(--font-size-base); margin-bottom: var(--spacing-xs);">1. Zaregistrujte se</h3>
                <p style="font-size: var(--font-size-sm); color: var(--color-text-light); margin: 0;">
                    Navštivte nás v květinářství nebo se zaregistrujte online.
                </p>
            </div>

            <div>
                <div style="width: 64px; height: 64px; border-radius: var(--radius-full); background: var(--color-primary); color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--spacing-md); font-size: 1.5rem;">
                    <i class="ri-calendar-event-line"></i>
                </div>
                <h3 style="font-size: var(--font-size-base); margin-bottom: var(--spacing-xs);">2. Zadejte data</h3>
                <p style="font-size: var(--font-size-sm); color: var(--color-text-light); margin: 0;">
                    Narozeniny, výročí, svátky — cokoliv, co nechcete zapomenout.
                </p>
            </div>

            <div>
                <div style="width: 64px; height: 64px; border-radius: var(--radius-full); background: var(--color-primary); color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--spacing-md); font-size: 1.5rem;">
                    <i class="ri-phone-line"></i>
                </div>
                <h3 style="font-size: var(--font-size-base); margin-bottom: var(--spacing-xs);">3. Zavoláme vám</h3>
                <p style="font-size: var(--font-size-sm); color: var(--color-text-light); margin: 0;">
                    5 pracovních dní předem se ozveme a poradíme s výběrem.
                </p>
            </div>

            <div>
                <div style="width: 64px; height: 64px; border-radius: var(--radius-full); background: var(--color-secondary); color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--spacing-md); font-size: 1.5rem;">
                    <i class="ri-gift-line"></i>
                </div>
                <h3 style="font-size: var(--font-size-base); margin-bottom: var(--spacing-xs);">4. Potěšíte blízké</h3>
                <p style="font-size: var(--font-size-sm); color: var(--color-text-light); margin: 0;">
                    Kytice je připravená, nikdo nezapomněl. Radost zaručena.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Co získáte -->
<section style="padding: var(--spacing-2xl) var(--spacing-md);">
    <div class="container" style="max-width: 900px;">
        <h2 style="text-align: center; font-size: var(--font-size-2xl); margin-bottom: var(--spacing-2xl);">
            Co získáte?
        </h2>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--spacing-lg);"
             class="landing-features">

            <div class="card" style="text-align: center;">
                <div class="card-body">
                    <div style="font-size: 2rem; color: var(--color-primary); margin-bottom: var(--spacing-sm);">
                        <i class="ri-alarm-line"></i>
                    </div>
                    <h3 style="font-size: var(--font-size-lg); margin-bottom: var(--spacing-sm);">Automatické připomínky</h3>
                    <p style="font-size: var(--font-size-sm); color: var(--color-text-light); margin: 0;">
                        Systém hlídá všechna vaše důležitá data. Narozeniny, výročí, Valentýn, Den matek — nic vám neuteče.
                    </p>
                </div>
            </div>

            <div class="card" style="text-align: center;">
                <div class="card-body">
                    <div style="font-size: 2rem; color: var(--color-primary); margin-bottom: var(--spacing-sm);">
                        <i class="ri-user-heart-line"></i>
                    </div>
                    <h3 style="font-size: var(--font-size-lg); margin-bottom: var(--spacing-sm);">Osobní přístup</h3>
                    <p style="font-size: var(--font-size-sm); color: var(--color-text-light); margin: 0;">
                        Žádný automat — zavoláme vám osobně a společně vybereme kytici podle příležitosti i rozpočtu.
                    </p>
                </div>
            </div>

            <div class="card" style="text-align: center;">
                <div class="card-body">
                    <div style="font-size: 2rem; color: var(--color-secondary); margin-bottom: var(--spacing-sm);">
                        <i class="ri-percent-line"></i>
                    </div>
                    <h3 style="font-size: var(--font-size-lg); margin-bottom: var(--spacing-sm);">Sleva na kytice</h3>
                    <p style="font-size: var(--font-size-sm); color: var(--color-text-light); margin: 0;">
                        Jako člen Připomněnky získáte trvalou slevu na všechny květinové aranžmá v naší nabídce.
                    </p>
                </div>
            </div>

            <div class="card" style="text-align: center;">
                <div class="card-body">
                    <div style="font-size: 2rem; color: var(--color-success); margin-bottom: var(--spacing-sm);">
                        <i class="ri-repeat-line"></i>
                    </div>
                    <h3 style="font-size: var(--font-size-lg); margin-bottom: var(--spacing-sm);">Rok za rokem</h3>
                    <p style="font-size: var(--font-size-sm); color: var(--color-text-light); margin: 0;">
                        Stačí nastavit jednou. Připomínky se opakují každý rok automaticky, dokud je nepotřebujete.
                    </p>
                </div>
            </div>

            <div class="card" style="text-align: center;">
                <div class="card-body">
                    <div style="font-size: 2rem; color: var(--color-success); margin-bottom: var(--spacing-sm);">
                        <i class="ri-smartphone-line"></i>
                    </div>
                    <h3 style="font-size: var(--font-size-lg); margin-bottom: var(--spacing-sm);">Jednoduché ovládání</h3>
                    <p style="font-size: var(--font-size-sm); color: var(--color-text-light); margin: 0;">
                        Přihlaste se z telefonu i počítače. Připomínky přidáte nebo upravíte za pár sekund.
                    </p>
                </div>
            </div>

            <div class="card" style="text-align: center;">
                <div class="card-body">
                    <div style="font-size: 2rem; color: var(--color-primary); margin-bottom: var(--spacing-sm);">
                        <i class="ri-mail-send-line"></i>
                    </div>
                    <h3 style="font-size: var(--font-size-lg); margin-bottom: var(--spacing-sm);">E-mailové upozornění</h3>
                    <p style="font-size: var(--font-size-sm); color: var(--color-text-light); margin: 0;">
                        Kromě telefonátu dostanete i e-mail, abyste měli jistotu, že nic nezmeškáte.
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Bezpečnost & důvěra -->
<section style="padding: var(--spacing-2xl) var(--spacing-md); background: var(--color-surface);">
    <div class="container" style="max-width: 800px;">
        <h2 style="text-align: center; font-size: var(--font-size-2xl); margin-bottom: var(--spacing-md);">
            Vaše data jsou v bezpečí
        </h2>
        <p style="text-align: center; color: var(--color-text-light); margin-bottom: var(--spacing-2xl); max-width: 600px; margin-left: auto; margin-right: auto;">
            Bezpečnost vašich osobních údajů bereme vážně.
            Dodržujeme všechny požadavky evropského nařízení GDPR.
        </p>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: var(--spacing-lg);"
             class="landing-security">

            <div style="display: flex; gap: var(--spacing-md); align-items: flex-start;">
                <div style="flex-shrink: 0; width: 40px; height: 40px; border-radius: var(--radius-md); background: var(--color-success-light); color: var(--color-success); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="ri-lock-line"></i>
                </div>
                <div>
                    <h3 style="font-size: var(--font-size-base); margin-bottom: var(--spacing-xs);">Šifrované spojení</h3>
                    <p style="font-size: var(--font-size-sm); color: var(--color-text-light); margin: 0;">
                        Veškerá komunikace probíhá přes zabezpečený protokol HTTPS.
                    </p>
                </div>
            </div>

            <div style="display: flex; gap: var(--spacing-md); align-items: flex-start;">
                <div style="flex-shrink: 0; width: 40px; height: 40px; border-radius: var(--radius-md); background: var(--color-success-light); color: var(--color-success); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="ri-shield-check-line"></i>
                </div>
                <div>
                    <h3 style="font-size: var(--font-size-base); margin-bottom: var(--spacing-xs);">Hesla bezpečně uložená</h3>
                    <p style="font-size: var(--font-size-sm); color: var(--color-text-light); margin: 0;">
                        Hesla ukládáme pouze v zašifrované podobě (bcrypt). Nikdo je nemůže přečíst.
                    </p>
                </div>
            </div>

            <div style="display: flex; gap: var(--spacing-md); align-items: flex-start;">
                <div style="flex-shrink: 0; width: 40px; height: 40px; border-radius: var(--radius-md); background: var(--color-success-light); color: var(--color-success); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="ri-eye-off-line"></i>
                </div>
                <div>
                    <h3 style="font-size: var(--font-size-base); margin-bottom: var(--spacing-xs);">Minimální sběr dat</h3>
                    <p style="font-size: var(--font-size-sm); color: var(--color-text-light); margin: 0;">
                        Neukládáme rok narození ani jména oslavenců — jen to, co je potřeba.
                    </p>
                </div>
            </div>

            <div style="display: flex; gap: var(--spacing-md); align-items: flex-start;">
                <div style="flex-shrink: 0; width: 40px; height: 40px; border-radius: var(--radius-md); background: var(--color-success-light); color: var(--color-success); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    <i class="ri-delete-bin-line"></i>
                </div>
                <div>
                    <h3 style="font-size: var(--font-size-base); margin-bottom: var(--spacing-xs);">Plná kontrola nad daty</h3>
                    <p style="font-size: var(--font-size-sm); color: var(--color-text-light); margin: 0;">
                        Kdykoliv si data exportujete nebo účet úplně smažete. Žádné háčky.
                    </p>
                </div>
            </div>

        </div>

        <div style="text-align: center; margin-top: var(--spacing-xl);">
            <a href="/ochrana-udaju" style="color: var(--color-primary); font-size: var(--font-size-sm);">
                <i class="ri-file-text-line"></i> Kompletní informace o ochraně osobních údajů
            </a>
        </div>
    </div>
</section>

<!-- Cenové varianty -->
<section style="padding: var(--spacing-2xl) var(--spacing-md);">
    <div class="container" style="max-width: 700px;">
        <h2 style="text-align: center; font-size: var(--font-size-2xl); margin-bottom: var(--spacing-md);">
            Jednoduchý ceník
        </h2>
        <p style="text-align: center; color: var(--color-text-light); margin-bottom: var(--spacing-2xl);">
            Roční předplatné, žádné skryté poplatky.
        </p>

        <?php
        $planModel = new \Models\SubscriptionPlan();
        $plans = $planModel->getAvailable();
        ?>

        <div style="display: grid; grid-template-columns: repeat(<?= count($plans) ?>, 1fr); gap: var(--spacing-lg);"
             class="landing-pricing">
            <?php foreach ($plans as $plan): ?>
                <div class="card" style="text-align: center; <?= $plan['is_default'] ? 'border-color: var(--color-primary); box-shadow: var(--shadow-md);' : '' ?>">
                    <?php if ($plan['is_default']): ?>
                        <div style="background: var(--color-primary); color: white; padding: var(--spacing-xs) var(--spacing-md); font-size: var(--font-size-sm); font-weight: 600; border-radius: var(--radius-md) var(--radius-md) 0 0;">
                            Nejoblíbenější
                        </div>
                    <?php endif; ?>
                    <div class="card-body" style="padding: var(--spacing-xl);">
                        <h3 style="font-size: var(--font-size-xl); margin-bottom: var(--spacing-sm);"><?= e($plan['name']) ?></h3>
                        <div style="font-size: var(--font-size-3xl); font-weight: 700; color: var(--color-primary); margin-bottom: var(--spacing-xs);">
                            <?= number_format($plan['price'], 0, ',', ' ') ?> Kč
                        </div>
                        <p style="font-size: var(--font-size-sm); color: var(--color-text-muted); margin-bottom: var(--spacing-lg);">za rok</p>

                        <ul style="text-align: left; list-style: none; padding: 0; margin-bottom: var(--spacing-lg);">
                            <li style="padding: var(--spacing-xs) 0; display: flex; align-items: center; gap: var(--spacing-sm);">
                                <i class="ri-check-line" style="color: var(--color-success); font-size: 1.1rem;"></i>
                                Až <?= $plan['reminder_limit'] ?> připomínek
                            </li>
                            <li style="padding: var(--spacing-xs) 0; display: flex; align-items: center; gap: var(--spacing-sm);">
                                <i class="ri-check-line" style="color: var(--color-success); font-size: 1.1rem;"></i>
                                Osobní telefonát před událostí
                            </li>
                            <li style="padding: var(--spacing-xs) 0; display: flex; align-items: center; gap: var(--spacing-sm);">
                                <i class="ri-check-line" style="color: var(--color-success); font-size: 1.1rem;"></i>
                                E-mailové upozornění
                            </li>
                            <?php if ($plan['discount_percent'] > 0): ?>
                            <li style="padding: var(--spacing-xs) 0; display: flex; align-items: center; gap: var(--spacing-sm);">
                                <i class="ri-check-line" style="color: var(--color-success); font-size: 1.1rem;"></i>
                                <strong><?= $plan['discount_percent'] ?>% sleva</strong> na kytice
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <p style="text-align: center; font-size: var(--font-size-sm); color: var(--color-text-muted); margin-top: var(--spacing-lg);">
            Předplatné si aktivujete přímo v naší provozovně. Platba hotově, kartou nebo převodem.
        </p>
    </div>
</section>

<!-- CTA závěrečný -->
<section style="padding: var(--spacing-2xl) var(--spacing-md); background: var(--color-surface); text-align: center;">
    <div class="container" style="max-width: 600px;">
        <div style="font-size: 3rem; margin-bottom: var(--spacing-md);">
            <i class="ri-hearts-line" style="color: var(--color-secondary);"></i>
        </div>
        <h2 style="font-size: var(--font-size-2xl); margin-bottom: var(--spacing-md);">
            Už nikdy nezapomenete
        </h2>
        <p style="color: var(--color-text-light); margin-bottom: var(--spacing-xl); line-height: 1.7;">
            Stavte se za námi v květinářství <?= e($shopName) ?>.
            Rádi vám vše vysvětlíme a Připomněnku rovnou aktivujeme.
        </p>

        <div style="display: flex; gap: var(--spacing-md); justify-content: center; flex-wrap: wrap; margin-bottom: var(--spacing-xl);">
            <?php if ($shopPhone): ?>
                <a href="tel:<?= e(str_replace(' ', '', $shopPhone)) ?>" class="btn btn--secondary btn--large">
                    <i class="ri-phone-line"></i> <?= e($shopPhone) ?>
                </a>
            <?php endif; ?>
            <a href="/prihlaseni" class="btn btn--primary btn--large">
                <i class="ri-login-box-line"></i> Přihlásit se
            </a>
        </div>
    </div>
</section>

<!-- Responzivní styly pro landing page -->
<style>
@media (max-width: 768px) {
    .landing-steps {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    .landing-features {
        grid-template-columns: 1fr !important;
    }
    .landing-security {
        grid-template-columns: 1fr !important;
    }
    .landing-pricing {
        grid-template-columns: 1fr !important;
        max-width: 400px;
        margin: 0 auto;
    }
}
@media (max-width: 480px) {
    .landing-steps {
        grid-template-columns: 1fr !important;
    }
}
</style>
