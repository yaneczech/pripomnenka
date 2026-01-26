<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Export dat') ?> - Připomněnka</title>
    <style>
        @media print {
            @page {
                margin: 2cm;
            }
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #333;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }

        h1 {
            font-size: 24pt;
            margin-bottom: 10px;
            color: #3e6ea1;
        }

        h2 {
            font-size: 14pt;
            margin: 20px 0 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }

        h3 {
            font-size: 12pt;
            margin: 15px 0 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #f5f5f5;
            font-weight: bold;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #3e6ea1;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10pt;
            color: #666;
        }

        .no-print {
            margin-bottom: 20px;
            padding: 15px;
            background: #e3f2fd;
            border-radius: 5px;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="no-print">
    <strong>Tip:</strong> Pro uložení jako PDF použijte funkci tisku (Ctrl+P / Cmd+P) a vyberte "Uložit jako PDF".
    <br><br>
    <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">🖨️ Vytisknout / Uložit PDF</button>
    <a href="/profil" style="margin-left: 10px;">← Zpět na profil</a>
</div>

<?= $content ?>

<div class="footer">
    <p>Vygenerováno ze služby Připomněnka | Květinářství Jeleni v zeleni</p>
    <p><?= date('j. n. Y H:i') ?></p>
</div>

</body>
</html>
