<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title ?? 'Přihlášení') ?> | Připomněnka</title>

    <link rel="icon" type="image/svg+xml" href="<?= asset('img/ikona.svg') ?>">
    <link rel="stylesheet" href="https://use.typekit.net/kmq6mvy.css">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <style>
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-lg);
        }
        .login-box {
            background: var(--color-surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            padding: var(--spacing-2xl);
            width: 100%;
            max-width: 400px;
        }
        .login-logo {
            text-align: center;
            margin-bottom: var(--spacing-xl);
        }
        .login-logo img {
            height: 48px;
            margin: 0 auto var(--spacing-sm);
        }
        .login-logo span {
            display: block;
            font-size: var(--font-size-sm);
            color: var(--color-text-muted);
        }
        .login-title {
            text-align: center;
            margin-bottom: var(--spacing-xl);
        }
    </style>
</head>
<body class="layout-admin">
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

    <div class="login-page">
        <div class="login-box">
            <?= $content ?>
        </div>
    </div>

    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
