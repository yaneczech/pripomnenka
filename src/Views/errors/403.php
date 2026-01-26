<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přístup odepřen | Připomněnka</title>
    <link rel="icon" type="image/svg+xml" href="/assets/img/ikona.svg">
    <link rel="stylesheet" href="https://use.typekit.net/kmq6mvy.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'skolar-latin', Georgia, 'Times New Roman', serif;
            background: #fbf8e7;
            color: #544a26;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
        }
        .error-page { max-width: 500px; }
        .error-code {
            font-size: 6rem;
            font-weight: 700;
            color: #c0392b;
            line-height: 1;
            margin-bottom: 1rem;
        }
        .error-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        .error-message {
            margin-bottom: 2rem;
            opacity: 0.8;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #3e6ea1;
            color: white;
            text-decoration: none;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn:hover { background: #2d5a8a; }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-code">403</div>
        <h1 class="error-title">Přístup odepřen</h1>
        <p class="error-message">Nemáte oprávnění k zobrazení této stránky.</p>
        <a href="/" class="btn">Zpět na úvodní stránku</a>
    </div>
</body>
</html>
