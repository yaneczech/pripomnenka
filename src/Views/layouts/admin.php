<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title ?? 'Admin') ?> | Připomněnka</title>

    <link rel="icon" type="image/svg+xml" href="<?= asset('img/ikona.svg') ?>">
    <link rel="stylesheet" href="https://use.typekit.net/kmq6mvy.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="layout-admin">
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="container">
            <a href="/admin" class="admin-logo">
                <img src="<?= asset('img/ikona.svg') ?>" alt="" height="32">
                <span>Připomněnka</span>
            </a>

            <div class="admin-user">
                <span class="admin-user-name"><?= e(\Session::getAdminName() ?? 'Admin') ?></span>
                <form action="/admin/odhlaseni" method="post" class="admin-logout-form">
                    <?= \CSRF::field() ?>
                    <button type="submit" class="btn btn--small btn--outline">Odhlásit</button>
                </form>
            </div>
        </div>
    </header>

    <!-- Admin Navigation -->
    <nav class="admin-nav">
        <div class="container">
            <ul class="admin-nav-list">
                <li><a href="/admin" class="admin-nav-link <?= ($_SERVER['REQUEST_URI'] === '/admin') ? 'is-active' : '' ?>">Dashboard</a></li>
                <li><a href="/admin/dnes" class="admin-nav-link <?= str_starts_with($_SERVER['REQUEST_URI'], '/admin/dnes') ? 'is-active' : '' ?>">Dnes volat</a></li>
                <li><a href="/admin/zakaznici" class="admin-nav-link <?= str_starts_with($_SERVER['REQUEST_URI'], '/admin/zakazni') ? 'is-active' : '' ?>">Zákazníci</a></li>
                <li><a href="/admin/predplatne" class="admin-nav-link <?= str_starts_with($_SERVER['REQUEST_URI'], '/admin/predplatne') ? 'is-active' : '' ?>">Předplatné</a></li>
                <li><a href="/admin/nastaveni" class="admin-nav-link <?= str_starts_with($_SERVER['REQUEST_URI'], '/admin/nastaveni') ? 'is-active' : '' ?>">Nastavení</a></li>
            </ul>
        </div>
    </nav>

    <!-- Flash zprávy -->
    <?php if (!empty($flash)): ?>
        <div class="container">
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
        </div>
    <?php endif; ?>

    <!-- Hlavní obsah -->
    <main class="admin-main">
        <div class="container">
            <?= $content ?>
        </div>
    </main>

    <!-- FAB - Nový zákazník -->
    <a href="/admin/novy-zakaznik" class="fab" title="Nový zákazník">
        <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
        </svg>
    </a>

    <!-- Admin Footer -->
    <footer class="admin-footer">
        <div class="container">
            <p>Připomněnka pro Jeleni v zeleni &copy; <?= date('Y') ?></p>
        </div>
    </footer>

    <script src="<?= asset('js/app.js') ?>"></script>
    <script src="<?= asset('js/admin.js') ?>"></script>
</body>
</html>
