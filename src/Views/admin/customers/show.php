<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-xl); flex-wrap: wrap; gap: var(--spacing-md);">
    <div>
        <a href="/admin/zakaznici" class="text-small text-muted">← Zpět na seznam</a>
        <h1 style="margin: var(--spacing-sm) 0 0;"><?= e($customer['name'] ?: 'Zákazník') ?></h1>
    </div>
    <div style="display: flex; gap: var(--spacing-sm);">
        <?php if ($subscription && $subscription['status'] === 'awaiting_activation'): ?>
            <form action="/admin/zakaznik/<?= $customer['id'] ?>/email-aktivace" method="post" style="display: inline;">
                <?= CSRF::field() ?>
                <button type="submit" class="btn btn--outline btn--small">Znovu poslat aktivaci</button>
            </form>
        <?php endif; ?>
        <?php if ($subscription && $subscription['status'] === 'awaiting_payment'): ?>
            <form action="/admin/zakaznik/<?= $customer['id'] ?>/email-qr" method="post" style="display: inline;">
                <?= CSRF::field() ?>
                <button type="submit" class="btn btn--outline btn--small">Znovu poslat QR</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-lg);">

    <!-- Levý sloupec -->
    <div>
        <!-- Kontaktní údaje -->
        <div class="card mb-3">
            <div class="card-header">
                <h2 class="card-title">Kontaktní údaje</h2>
            </div>
            <div class="card-body">
                <form action="/admin/zakaznik/<?= $customer['id'] ?>" method="post">
                    <?= CSRF::field() ?>

                    <div class="form-group">
                        <label for="name" class="form-label">Jméno</label>
                        <input type="text" id="name" name="name" class="form-input"
                               value="<?= e($customer['name'] ?? '') ?>" placeholder="Jak zákazníkovi říkat">
                    </div>

                    <div class="form-row form-row--2">
                        <div class="form-group">
                            <label for="phone" class="form-label">Telefon</label>
                            <input type="tel" id="phone" name="phone" class="form-input"
                                   value="<?= e($customer['phone']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="form-input"
                                   value="<?= e($customer['email']) ?>" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn--primary btn--small">Uložit změny</button>
                </form>
            </div>
        </div>

        <!-- Předplatné -->
        <div class="card mb-3">
            <div class="card-header">
                <h2 class="card-title">Předplatné</h2>
            </div>
            <div class="card-body">
                <?php if ($subscription): ?>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md);">
                        <div>
                            <span class="text-small text-muted">Varianta</span>
                            <div><strong><?= e($subscription['plan_name']) ?></strong></div>
                        </div>
                        <div>
                            <span class="text-small text-muted">Stav</span>
                            <div>
                                <?php
                                $statusClass = match($subscription['status']) {
                                    'active' => 'badge--success',
                                    'awaiting_activation' => 'badge--info',
                                    'awaiting_payment' => 'badge--warning',
                                    'expired' => 'badge--error',
                                    default => 'badge--muted',
                                };
                                $statusText = match($subscription['status']) {
                                    'active' => 'Aktivní',
                                    'awaiting_activation' => 'Čeká na aktivaci',
                                    'awaiting_payment' => 'Čeká na platbu',
                                    'expired' => 'Vypršelo',
                                    default => $subscription['status'],
                                };
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                            </div>
                        </div>
                        <div>
                            <span class="text-small text-muted">Cena</span>
                            <div><?= number_format($subscription['price'], 0, ',', ' ') ?> Kč</div>
                        </div>
                        <div>
                            <span class="text-small text-muted">VS</span>
                            <div><?= e($subscription['variable_symbol']) ?></div>
                        </div>
                        <?php if ($subscription['starts_at']): ?>
                            <div>
                                <span class="text-small text-muted">Platí od</span>
                                <div><?= format_date($subscription['starts_at']) ?></div>
                            </div>
                            <div>
                                <span class="text-small text-muted">Platí do</span>
                                <div><?= format_date($subscription['expires_at']) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($subscription['payment_note']): ?>
                        <div class="mt-2 text-small text-warning">
                            <?= e($subscription['payment_note']) ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted">Zákazník nemá žádné předplatné.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Interní poznámky -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Interní poznámky</h2>
            </div>
            <div class="card-body">
                <form action="/admin/zakaznik/<?= $customer['id'] ?>" method="post">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="name" value="<?= e($customer['name'] ?? '') ?>">
                    <input type="hidden" name="phone" value="<?= e($customer['phone']) ?>">
                    <input type="hidden" name="email" value="<?= e($customer['email']) ?>">

                    <div class="form-group">
                        <label for="preferred_flowers" class="form-label">Preferované květiny</label>
                        <input type="text" id="preferred_flowers" name="preferred_flowers" class="form-input"
                               value="<?= e($notes['preferred_flowers'] ?? '') ?>" placeholder="Např. tulipány, růže...">
                    </div>

                    <div class="form-row form-row--2">
                        <div class="form-group">
                            <label for="typical_budget" class="form-label">Obvyklý rozpočet</label>
                            <input type="text" id="typical_budget" name="typical_budget" class="form-input"
                                   value="<?= e($notes['typical_budget'] ?? '') ?>" placeholder="Např. 800-1200 Kč">
                        </div>
                        <div class="form-group">
                            <label for="preferred_call_time" class="form-label">Kdy volat</label>
                            <select id="preferred_call_time" name="preferred_call_time" class="form-select">
                                <option value="anytime" <?= ($notes['preferred_call_time'] ?? '') === 'anytime' ? 'selected' : '' ?>>Kdykoliv</option>
                                <option value="morning" <?= ($notes['preferred_call_time'] ?? '') === 'morning' ? 'selected' : '' ?>>Ráno</option>
                                <option value="afternoon" <?= ($notes['preferred_call_time'] ?? '') === 'afternoon' ? 'selected' : '' ?>>Odpoledne</option>
                                <option value="evening" <?= ($notes['preferred_call_time'] ?? '') === 'evening' ? 'selected' : '' ?>>Večer</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="general_note" class="form-label">Poznámka</label>
                        <textarea id="general_note" name="general_note" class="form-textarea" rows="3"
                                  placeholder="Cokoliv důležitého..."><?= e($notes['general_note'] ?? '') ?></textarea>
                        <span class="form-hint">Poznámky mohou obsahovat osobní údaje. Na vyžádání zákazníka musí být poskytnuty.</span>
                    </div>

                    <button type="submit" class="btn btn--primary btn--small">Uložit poznámky</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Pravý sloupec -->
    <div>
        <!-- Připomínky -->
        <div class="card mb-3">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h2 class="card-title" style="margin: 0;">Připomínky</h2>
                <span class="text-small text-muted"><?= count($reminders) ?>/<?= $reminderLimit ?></span>
            </div>
            <div class="card-body">
                <?php if (empty($reminders)): ?>
                    <p class="text-muted">Zákazník nemá žádné připomínky.</p>
                <?php else: ?>
                    <?php foreach ($reminders as $reminder): ?>
                        <div style="padding: var(--spacing-sm) 0; border-bottom: 1px solid var(--color-border);">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong><?= translate_event_type($reminder['event_type']) ?></strong>
                                    — <?= translate_relation($reminder['recipient_relation']) ?>
                                </div>
                                <div class="text-small">
                                    <?= format_date_long($reminder['event_day'], $reminder['event_month']) ?>
                                </div>
                            </div>
                            <div class="text-small text-muted">
                                <?= translate_price_range($reminder['price_range']) ?>
                                · Za <?= days_until($reminder['event_day'], $reminder['event_month']) ?> dní
                            </div>
                            <?php if ($reminder['customer_note']): ?>
                                <div class="text-small" style="margin-top: var(--spacing-xs); color: var(--color-text-light);">
                                    "<?= e($reminder['customer_note']) ?>"
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Historie volání -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Historie volání</h2>
            </div>
            <div class="card-body">
                <?php if (empty($callHistory)): ?>
                    <p class="text-muted">Zatím žádná historie.</p>
                <?php else: ?>
                    <?php foreach ($callHistory as $call): ?>
                        <div style="padding: var(--spacing-sm) 0; border-bottom: 1px solid var(--color-border);">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <?= format_date($call['call_date']) ?>
                                    — <?= translate_event_type($call['event_type']) ?>
                                </div>
                                <div>
                                    <?php
                                    $callStatusClass = match($call['status']) {
                                        'completed' => 'badge--success',
                                        'no_answer' => 'badge--warning',
                                        'declined' => 'badge--error',
                                        'postponed' => 'badge--info',
                                        default => 'badge--muted',
                                    };
                                    $callStatusText = match($call['status']) {
                                        'completed' => 'Vyřízeno',
                                        'no_answer' => 'Nezvedá',
                                        'declined' => 'Nechce',
                                        'postponed' => 'Odloženo',
                                        default => $call['status'],
                                    };
                                    ?>
                                    <span class="badge <?= $callStatusClass ?>"><?= $callStatusText ?></span>
                                </div>
                            </div>
                            <?php if ($call['order_amount']): ?>
                                <div class="text-small text-success">
                                    Objednávka: <?= number_format($call['order_amount'], 0, ',', ' ') ?> Kč
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<!-- Smazání zákazníka -->
<div class="mt-4" style="border-top: 1px solid var(--color-border); padding-top: var(--spacing-xl);">
    <h3 class="text-error">Nebezpečná zóna</h3>
    <p class="text-small text-muted">Smazání zákazníka je nevratné. Všechna data včetně připomínek budou odstraněna.</p>
    <form action="/admin/zakaznik/<?= $customer['id'] ?>/smazat" method="post"
          onsubmit="return confirm('Opravdu chcete smazat tohoto zákazníka? Tuto akci nelze vrátit.');">
        <?= CSRF::field() ?>
        <button type="submit" class="btn btn--danger btn--small">Smazat zákazníka</button>
    </form>
</div>
