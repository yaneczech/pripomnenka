<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Připomněnka - nikdy nezapomeňte na důležitá data">
    <title><?= e($title ?? 'Připomněnka') ?> | Jeleni v zeleni</title>

    <link rel="icon" type="image/svg+xml" href="<?= asset('img/ikona.svg') ?>">
    <link rel="stylesheet" href="https://use.typekit.net/kmq6mvy.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="layout-public">
    <!-- Header -->
    <header class="header">
        <div class="container">
            <a href="/" class="logo">
                <img src="<?= asset('img/logo.svg') ?>" alt="Připomněnka" height="40">
            </a>

            <?php if (\Session::isLoggedIn()): ?>
                <nav class="nav">
                    <a href="/moje-pripominky" class="nav-link">Moje připomínky</a>
                    <a href="/profil" class="nav-link">Profil</a>
                    <form action="/odhlaseni" method="post" class="nav-form">
                        <?= \CSRF::field() ?>
                        <button type="submit" class="nav-link nav-link--button">Odhlásit</button>
                    </form>
                </nav>
            <?php else: ?>
                <nav class="nav">
                    <a href="/prihlaseni" class="nav-link">Přihlásit se</a>
                </nav>
            <?php endif; ?>

            <button class="nav-toggle" aria-label="Menu" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <!-- Flash zprávy -->
    <?php if (!empty($flash)): ?>
        <div class="flash-messages">
            <?php foreach ($flash as $type => $messages): ?>
                <?php foreach ($messages as $message): ?>
                    <div class="flash flash--<?= e($type) ?>">
                        <?= e($message) ?>
                        <button class="flash-close" aria-label="Zavřít">&times;</button>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Hlavní obsah -->
    <main class="main">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <img src="<?= asset('img/JvZ_logo.svg') ?>" alt="logo JvZ" style="height: 80px;">
                    
                </div>
                <div class="footer-contact">
                    <p style="font-weight: 600;">Jeleni v zeleni</p>
                    <p>Palackého 1308/32<br>58601 Jihlava</p>
                    <p>E-mail: <a href="mailto:mail@jelenivzeleni.cz">mail@jelenivzeleni.cz</a><br>
                    Telefon: <a href="tel:+420606493031">+420 606 493 031</a></p>
                    <a href="https://jelenivzeleni.cz">jelenivzeleni.cz</a>
                </div>
                <div class="footer-links">
                    <a href="/podminky">Obchodní podmínky</a>
                    <a href="/ochrana-udaju">Ochrana osobních údajů</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Jeleni v zeleni. Všechna práva vyhrazena.</p>
            </div>
        </div>
    </footer>

    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
