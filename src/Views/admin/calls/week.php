<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-xl);">
    <h1 style="margin: 0;">Tento týden</h1>
    <a href="/admin/dnes" class="btn btn--primary">Dnes volat</a>
</div>

<div style="display: flex; flex-direction: column; gap: var(--spacing-md);">
    <?php foreach ($days as $day): ?>
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h2 class="card-title" style="margin: 0;">
                    <?php
                    $dayNames = ['', 'Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota', 'Neděle'];
                    echo $dayNames[(int) $day['date']->format('N')];
                    ?>
                    <span class="text-muted" style="font-weight: normal;"><?= $day['date']->format('j. n.') ?></span>
                    <?php if ($day['isToday']): ?>
                        <span class="badge badge--primary" style="margin-left: var(--spacing-sm);">Dnes</span>
                    <?php endif; ?>
                </h2>
                <span class="<?= $day['count'] > 0 ? 'text-primary' : 'text-muted' ?>" style="font-size: var(--font-size-xl); font-weight: 600;">
                    <?= $day['count'] ?>
                </span>
            </div>

            <?php if ($day['count'] > 0): ?>
                <div class="card-body" style="padding: var(--spacing-sm) var(--spacing-lg);">
                    <?php foreach ($day['calls'] as $call): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--spacing-sm) 0; border-bottom: 1px solid var(--color-border);">
                            <div>
                                <strong><?= e($call['customer_name'] ?: $call['phone']) ?></strong>
                                <span class="text-muted">·</span>
                                <span><?= translate_event_type($call['event_type']) ?></span>
                                <span class="text-muted">—</span>
                                <span><?= translate_relation($call['recipient_relation']) ?></span>
                            </div>
                            <div class="text-small text-muted">
                                <?= format_date_long($call['event_day'], $call['event_month'], $call['event_type']) ?>
                                <?php if ($call['attempt_count'] > 1): ?>
                                    <span class="badge badge--warning" style="margin-left: var(--spacing-sm);"><?= $call['attempt_count'] ?>. pokus</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
