<div class="container" style="max-width: 800px;">

    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-xl); flex-wrap: wrap; gap: var(--spacing-md);">
        <h1 style="margin: 0;">Moje připomínky</h1>
        <?php if ($canAdd): ?>
            <a href="/nova-pripominka" class="btn btn--secondary">+ Přidat připomínku</a>
        <?php endif; ?>
    </div>

    <!-- Progress bar limitu -->
    <?php if ($subscription): ?>
        <div class="card mb-3">
            <div class="card-body" style="padding: var(--spacing-md) var(--spacing-lg);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-sm);">
                    <span>Využito <?= $reminderCount ?> z <?= $reminderLimit ?> připomínek</span>
                    <span class="text-small text-muted"><?= e($subscription['plan_name']) ?></span>
                </div>
                <div style="background: var(--color-border); border-radius: var(--radius-full); height: 8px; overflow: hidden;">
                    <div style="background: var(--color-primary); height: 100%; width: <?= min(100, ($reminderCount / $reminderLimit) * 100) ?>%;"></div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (empty($reminders)): ?>
        <!-- Prázdný stav -->
        <div class="empty-state">
            <div class="empty-state-icon">📅</div>
            <h2 class="empty-state-title">Zatím nemáte žádné připomínky</h2>
            <p class="empty-state-text">
                Přidejte si důležitá data a my vám je připomeneme.
            </p>
            <?php if ($canAdd): ?>
                <a href="/nova-pripominka" class="btn btn--secondary btn--large">Přidat první připomínku</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Seznam připomínek -->
        <div style="display: flex; flex-direction: column; gap: var(--spacing-md);">
            <?php foreach ($reminders as $index => $reminder): ?>
                <?php
                $daysUntil = days_until($reminder['event_day'], $reminder['event_month'], $reminder['event_type']);
                $isNear = $daysUntil <= 14;
                ?>
                <div class="card <?= $isNear ? 'card--highlight' : '' ?>" style="<?= $isNear ? 'border-color: var(--color-primary); border-width: 2px;' : '' ?>">
                    <div class="card-body">
                        <div style="display: grid; grid-template-columns: 1fr auto; gap: var(--spacing-md); margin-bottom: var(--spacing-md);">
                            <div>
                                <!-- Typ a vztah -->
                                <h3 style="margin: 0 0 var(--spacing-xs);">
                                    <?= translate_event_type($reminder['event_type']) ?>
                                    <span class="text-muted">—</span>
                                    <?= translate_relation($reminder['recipient_relation']) ?>
                                </h3>

                                <!-- Datum -->
                                <div class="text-muted mb-2">
                                    <?= format_date_long($reminder['event_day'], $reminder['event_month'], $reminder['event_type']) ?>
                                </div>

                                <!-- Meta -->
                                <div class="text-small text-muted">
                                    <?= translate_price_range($reminder['price_range']) ?>
                                    · Připomeneme <?= $reminder['advance_days'] ?> dní předem
                                </div>

                                <!-- Poznámka -->
                                <?php if ($reminder['customer_note']): ?>
                                    <div class="text-small" style="margin-top: var(--spacing-sm); padding: var(--spacing-sm); background: var(--color-background); border-radius: var(--radius-sm);">
                                        <?= e($reminder['customer_note']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Countdown -->
                            <div style="text-align: center; min-width: 80px;">
                                <div style="font-size: var(--font-size-3xl); font-weight: 700; color: <?= $isNear ? 'var(--color-primary)' : 'var(--color-text-muted)' ?>; line-height: 1;">
                                    <?= $daysUntil ?>
                                </div>
                                <div class="text-small text-muted" style="margin-top: var(--spacing-xs);">
                                    <?= $daysUntil === 1 ? 'den' : ($daysUntil < 5 ? 'dny' : 'dní') ?>
                                </div>
                            </div>
                        </div>

                        <!-- Akce tlačítka -->
                        <div style="display: flex; gap: var(--spacing-sm); padding-top: var(--spacing-sm); border-top: 1px solid var(--color-border);">
                            <a href="/pripominka/<?= $reminder['id'] ?>" class="btn btn--outline btn--small" style="flex: 1; max-width: 200px;">
                                <i class="ri-edit-line"></i> Upravit
                            </a>
                            <form action="/pripominka/<?= $reminder['id'] ?>/smazat" method="post" style="flex: 1; max-width: 200px;"
                                  onsubmit="return confirm('Opravdu smazat tuto připomínku?');">
                                <?= \CSRF::field() ?>
                                <button type="submit" class="btn btn--outline btn--small btn--delete-hover" style="width: 100%; color: var(--color-error); border-color: var(--color-error);">
                                    <i class="ri-delete-bin-line"></i> Smazat
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($canAdd): ?>
            <div class="text-center mt-4">
                <a href="/nova-pripominka" class="btn btn--outline">+ Přidat další připomínku</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Info o slevě -->
    <?php if ($subscription && $subscription['discount_percent']): ?>
        <div class="card mt-4" style="background: var(--color-success-light); border-color: var(--color-success);">
            <div class="card-body text-center">
                <strong>🎁 Máte <?= $subscription['discount_percent'] ?>% slevu na všechny kytice!</strong>
                <br>
                <span class="text-small">Stačí při objednávce zmínit, že jste členem Připomněnky.</span>
            </div>
        </div>
    <?php endif; ?>

</div>
