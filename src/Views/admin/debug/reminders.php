<div class="container" style="max-width: 1200px;">

    <div class="page-header">
        <h1>🐛 Debug: Připomínky a Call Queue</h1>
        <p style="color: var(--color-text-light);">
            Diagnostika, proč se připomínky nezobrazují v seznamu k volání
        </p>
    </div>

    <div class="card" style="margin-bottom: var(--spacing-lg); background: #fff3cd; border-left: 4px solid #ffc107;">
        <div class="card-body">
            <h3 style="margin-top: 0; color: #856404;">ℹ️ Jak funguje systém připomínek</h3>
            <p style="margin-bottom: var(--spacing-sm); line-height: 1.6;">
                Připomínky se <strong>NEVOLAJÍ v den události</strong>, ale <strong><?= e($default_advance_days) ?> pracovních dní PŘEDEM</strong>
                (nebo dle individuálního nastavení připomínky).
            </p>
            <p style="margin-bottom: 0; line-height: 1.6;">
                <strong>Příklad:</strong> Pokud je událost 30. ledna a předstih je 5 pracovních dní,
                volání bude <strong>23. ledna</strong> (5 pracovních dní předem).
            </p>
        </div>
    </div>

    <div class="card" style="margin-bottom: var(--spacing-lg);">
        <div class="card-body">
            <h3>⚙️ Nastavení systému</h3>
            <ul style="list-style: none; padding: 0;">
                <li><strong>Dnes:</strong> <?= e($today) ?> (<?= e(strftime('%A', strtotime($today))) ?>)</li>
                <li><strong>Výchozí předstih:</strong> <?= e($default_advance_days) ?> pracovních dní</li>
                <li><strong>Pracovní dny:</strong> <?= e(implode(', ', array_map(fn($d) => ['Po','Út','St','Čt','Pá','So','Ne'][$d-1], $workdays))) ?></li>
            </ul>
        </div>
    </div>

    <h2 style="margin-bottom: var(--spacing-md);">📋 Přehled všech připomínek</h2>

    <?php if (empty($reminders)): ?>
        <div class="card">
            <div class="card-body" style="text-align: center; padding: var(--spacing-2xl);">
                <p style="color: var(--color-text-light); font-size: var(--font-size-lg);">
                    Žádné připomínky v systému
                </p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($reminders as $data): ?>
            <?php
            $r = $data['reminder'];
            $eventDateStr = $r['event_day'] . '. ' . ['ledna','února','března','dubna','května','června','července','srpna','září','října','listopadu','prosince'][$r['event_month']-1];
            ?>
            <div class="card" style="margin-bottom: var(--spacing-md); border-left: 4px solid <?= $data['should_be_in_queue'] ? '#28a745' : '#dc3545' ?>;">
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: var(--spacing-md);">
                        <div>
                            <h3 style="margin: 0 0 var(--spacing-xs) 0;">
                                <?= e($r['customer_name'] ?: 'Bez jména') ?>
                            </h3>
                            <p style="margin: 0; color: var(--color-text-light);">
                                <?= e($r['phone']) ?> • <?= e($r['email']) ?>
                            </p>
                        </div>
                        <div style="text-align: right;">
                            <span style="display: inline-block; padding: var(--spacing-xs) var(--spacing-sm); background: <?= $r['customer_active'] ? '#d4edda' : '#f8d7da' ?>; color: <?= $r['customer_active'] ? '#155724' : '#721c24' ?>; border-radius: var(--radius-sm); font-size: var(--font-size-sm); font-weight: 600;">
                                <?= $r['customer_active'] ? '✓ Aktivní' : '✗ Neaktivní' ?>
                            </span>
                        </div>
                    </div>

                    <div style="background: var(--color-surface); padding: var(--spacing-md); border-radius: var(--radius-sm); margin-bottom: var(--spacing-md);">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="padding: var(--spacing-xs) 0; width: 180px;"><strong>Událost:</strong></td>
                                <td style="padding: var(--spacing-xs) 0;">
                                    <?= e(ucfirst($r['event_type'])) ?> — <?= e(ucfirst($r['recipient_relation'])) ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: var(--spacing-xs) 0;"><strong>Datum události:</strong></td>
                                <td style="padding: var(--spacing-xs) 0; font-size: var(--font-size-lg); font-weight: 600;">
                                    <?= e($eventDateStr) ?>
                                    <?php
                                    $eventDateThisYear = mktime(0, 0, 0, $r['event_month'], $r['event_day'], date('Y'));
                                    if ($eventDateThisYear < time()) {
                                        $eventDateThisYear = mktime(0, 0, 0, $r['event_month'], $r['event_day'], date('Y') + 1);
                                    }
                                    $daysUntil = (int) ((mktime(0,0,0,$r['event_month'],$r['event_day'],date('Y')) - time()) / 86400);
                                    ?>
                                    <span style="color: var(--color-text-light); font-size: var(--font-size-sm); font-weight: normal;">
                                        (za <?= $daysUntil ?> dní)
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: var(--spacing-xs) 0;"><strong>Předstih volání:</strong></td>
                                <td style="padding: var(--spacing-xs) 0;">
                                    <?= e($data['advance_days']) ?> pracovních dní
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: var(--spacing-xs) 0;"><strong>Vypočítané datum volání:</strong></td>
                                <td style="padding: var(--spacing-xs) 0; font-size: var(--font-size-lg); font-weight: 600; color: var(--color-primary);">
                                    <?= e($data['call_date']) ?>
                                    <?php
                                    $callDaysUntil = (int) ((strtotime($data['call_date']) - time()) / 86400);
                                    if ($callDaysUntil < 0) {
                                        echo '<span style="color: var(--color-error); font-size: var(--font-size-sm); font-weight: normal;">(bylo před ' . abs($callDaysUntil) . ' dny)</span>';
                                    } elseif ($callDaysUntil == 0) {
                                        echo '<span style="color: var(--color-success); font-size: var(--font-size-sm); font-weight: normal;">(DNES)</span>';
                                    } else {
                                        echo '<span style="color: var(--color-text-light); font-size: var(--font-size-sm); font-weight: normal;">(za ' . $callDaysUntil . ' dní)</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div style="background: var(--color-surface); padding: var(--spacing-md); border-radius: var(--radius-sm); margin-bottom: var(--spacing-md);">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="padding: var(--spacing-xs) 0; width: 180px;"><strong>Předplatné:</strong></td>
                                <td style="padding: var(--spacing-xs) 0;">
                                    <?php if ($r['subscription_id']): ?>
                                        <span style="display: inline-block; padding: 2px 8px; background: <?= $r['subscription_status'] === 'active' ? '#d4edda' : '#fff3cd' ?>; color: <?= $r['subscription_status'] === 'active' ? '#155724' : '#856404' ?>; border-radius: 3px; font-size: 0.875rem;">
                                            <?= e($r['subscription_status']) ?>
                                        </span>
                                        <?php if ($r['expires_at']): ?>
                                            <span style="color: var(--color-text-light); margin-left: var(--spacing-sm);">
                                                (platí do <?= e(date('j. n. Y', strtotime($r['expires_at']))) ?>)
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color: var(--color-error);">Nemá předplatné</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: var(--spacing-xs) 0;"><strong>Připomínka aktivní:</strong></td>
                                <td style="padding: var(--spacing-xs) 0;">
                                    <?= $r['reminder_active'] ? '<span style="color: var(--color-success);">✓ Ano</span>' : '<span style="color: var(--color-error);">✗ Ne</span>' ?>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div style="padding: var(--spacing-md); background: <?= $data['should_be_in_queue'] ? '#d4edda' : '#f8d7da' ?>; border-radius: var(--radius-sm); border-left: 4px solid <?= $data['should_be_in_queue'] ? '#28a745' : '#dc3545' ?>;">
                        <strong style="font-size: var(--font-size-md);">
                            <?= e($data['reason']) ?>
                        </strong>

                        <?php if ($data['queue_entry']): ?>
                            <div style="margin-top: var(--spacing-sm); padding-top: var(--spacing-sm); border-top: 1px solid rgba(0,0,0,0.1);">
                                <small style="color: var(--color-text-light);">
                                    Call Queue ID: <?= e($data['queue_entry']['id']) ?> |
                                    Pokus: <?= e($data['queue_entry']['attempt_count']) ?> |
                                    Priorita: <?= e($data['queue_entry']['priority']) ?>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="margin-top: var(--spacing-md); text-align: right;">
                        <a href="/admin/zakaznik/<?= e($r['customer_id']) ?>" class="btn btn--outline btn--small">
                            Detail zákazníka →
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div style="margin-top: var(--spacing-2xl); padding: var(--spacing-lg); background: var(--color-surface); border-radius: var(--radius-md);">
        <h3 style="margin-top: 0;">🔧 Akce</h3>
        <div style="display: flex; gap: var(--spacing-md); flex-wrap: wrap;">
            <a href="/admin" class="btn btn--outline">← Zpět na dashboard</a>
            <a href="/admin/zakaznici" class="btn btn--outline">Zobrazit zákazníky</a>
            <form action="https://pripomnenka.jelenivzeleni.cz/cron-generate-queue.php?token=f8A3kN7vQ1mZx9T2cR5pL0hYw6JdS4uB" method="get" target="_blank" style="margin: 0;">
                <button type="submit" class="btn btn--primary">
                    ▶️ Spustit CRON ručně
                </button>
            </form>
        </div>
    </div>

</div>
