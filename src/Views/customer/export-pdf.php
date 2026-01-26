<div class="header">
    <h1>Export osobních údajů</h1>
    <p>Připomněnka — Květinářství Jeleni v zeleni</p>
    <p><small>Vygenerováno: <?= e($exportDate) ?></small></p>
</div>

<h2>Osobní údaje</h2>
<table>
    <tr>
        <th>Jméno</th>
        <td><?= e($data['customer']['name'] ?? 'Neuvedeno') ?></td>
    </tr>
    <tr>
        <th>Telefon</th>
        <td><?= e($data['customer']['phone']) ?></td>
    </tr>
    <tr>
        <th>Email</th>
        <td><?= e($data['customer']['email']) ?></td>
    </tr>
    <tr>
        <th>Účet vytvořen</th>
        <td><?= format_date($data['customer']['created_at']) ?></td>
    </tr>
    <tr>
        <th>GDPR souhlas udělen</th>
        <td><?= format_date($data['customer']['gdpr_consent_at']) ?></td>
    </tr>
</table>

<?php if (!empty($data['subscription'])): ?>
<h2>Předplatné</h2>
<table>
    <tr>
        <th>Varianta</th>
        <td><?= e($data['subscription']['plan_name'] ?? 'N/A') ?></td>
    </tr>
    <tr>
        <th>Stav</th>
        <td><?= e($data['subscription']['status']) ?></td>
    </tr>
    <tr>
        <th>Platí od</th>
        <td><?= $data['subscription']['starts_at'] ? format_date($data['subscription']['starts_at']) : 'N/A' ?></td>
    </tr>
    <tr>
        <th>Platí do</th>
        <td><?= $data['subscription']['expires_at'] ? format_date($data['subscription']['expires_at']) : 'N/A' ?></td>
    </tr>
    <tr>
        <th>Cena</th>
        <td><?= number_format($data['subscription']['price'], 0, ',', ' ') ?> Kč</td>
    </tr>
</table>
<?php endif; ?>

<?php if (!empty($data['reminders'])): ?>
<h2>Připomínky (<?= count($data['reminders']) ?>)</h2>
<table>
    <thead>
        <tr>
            <th>Typ události</th>
            <th>Koho</th>
            <th>Datum</th>
            <th>Rozpočet</th>
            <th>Poznámka</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data['reminders'] as $reminder): ?>
        <tr>
            <td><?= translate_event_type($reminder['event_type']) ?></td>
            <td><?= translate_relation($reminder['recipient_relation']) ?></td>
            <td><?= $reminder['event_day'] ?>. <?= $reminder['event_month'] ?>.</td>
            <td><?= translate_price_range($reminder['price_range']) ?></td>
            <td><?= e($reminder['customer_note'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php if (!empty($data['call_history'])): ?>
<h2>Historie volání</h2>
<table>
    <thead>
        <tr>
            <th>Datum</th>
            <th>Výsledek</th>
            <th>Částka objednávky</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data['call_history'] as $call): ?>
        <tr>
            <td><?= format_date($call['call_date']) ?></td>
            <td>
                <?php
                echo match($call['status']) {
                    'completed' => 'Vyřízeno',
                    'no_answer' => 'Nezvedá',
                    'declined' => 'Nechce',
                    'postponed' => 'Odloženo',
                    default => $call['status'],
                };
                ?>
            </td>
            <td>
                <?php if ($call['order_amount']): ?>
                    <?= number_format($call['order_amount'], 0, ',', ' ') ?> Kč
                <?php else: ?>
                    —
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<h2>Text souhlasu GDPR</h2>
<div style="background: #f5f5f5; padding: 15px; margin: 10px 0; font-size: 10pt;">
    <?= nl2br(e($data['customer']['gdpr_consent_text'])) ?>
</div>

<h2>Vaše práva</h2>
<p>Máte právo na:</p>
<ul>
    <li><strong>Přístup k údajům</strong> — tento dokument</li>
    <li><strong>Opravu údajů</strong> — v profilu na webu</li>
    <li><strong>Výmaz údajů</strong> — smazání účtu na webu</li>
    <li><strong>Přenositelnost</strong> — export ve formátu JSON</li>
    <li><strong>Stížnost</strong> — u ÚOOÚ (www.uoou.cz)</li>
</ul>

<h2>Kontakt na správce</h2>
<p>
    Květinářství Jeleni v zeleni<br>
    Email: <?= e(Setting::get('shop_email', 'info@jelenivzeleni.cz')) ?><br>
    Telefon: <?= e(Setting::get('shop_phone', '123 456 789')) ?>
</p>
