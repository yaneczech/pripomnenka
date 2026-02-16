<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-xl); flex-wrap: wrap; gap: var(--spacing-md);">
    <div>
        <a href="/admin/zakaznici" class="text-small text-muted">← Zpět na seznam</a>
        <h1 style="margin: var(--spacing-sm) 0 0;"><?= e($customer['name'] ?: 'Zákazník') ?></h1>
    </div>
    <div style="display: flex; gap: var(--spacing-sm);">
        <?php if ($subscription && $subscription['status'] === 'awaiting_activation'): ?>
            <form action="/admin/zakaznik/<?= $customer['id'] ?>/email-aktivace" method="post" style="display: inline;">
                <?= \CSRF::field() ?>
                <button type="submit" class="btn btn--outline btn--small">Znovu poslat aktivaci</button>
            </form>
        <?php endif; ?>
        <?php if ($subscription && $subscription['status'] === 'awaiting_payment'): ?>
            <form action="/admin/zakaznik/<?= $customer['id'] ?>/email-qr" method="post" style="display: inline;">
                <?= \CSRF::field() ?>
                <button type="submit" class="btn btn--outline btn--small">Znovu poslat QR</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 400px), 1fr)); gap: var(--spacing-lg);">

    <!-- Levý sloupec -->
    <div>
        <!-- Kontaktní údaje -->
        <div class="card mb-3">
            <div class="card-header">
                <h2 class="card-title">Kontaktní údaje</h2>
            </div>
            <div class="card-body">
                <form action="/admin/zakaznik/<?= $customer['id'] ?>" method="post">
                    <?= \CSRF::field() ?>

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
                            <label for="email" class="form-label">E-mail</label>
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
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: var(--spacing-md);">
                        <div>
                            <span class="text-small text-muted">Varianta</span>
                            <div><strong><?= e($subscription['plan_name']) ?></strong></div>
                        </div>
                        <div>
                            <span class="text-small text-muted">Stav</span>
                            <div>
                                <?php
                                $isCustomerActive = (int) ($customer['is_active'] ?? 1) === 1;
                                if (!$isCustomerActive) {
                                    $statusClass = 'badge--warning';
                                    $statusText = 'Deaktivovaný';
                                } else {
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
                                }
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                            </div>
                        </div>
                        <div>
                            <span class="text-small text-muted">Cena</span>
                            <div><?= format_price($subscription['price']) ?></div>
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
                    <form action="/admin/zakaznik/<?= $customer['id'] ?>/zmenit-tarif" method="post" class="mt-3">
                        <?= \CSRF::field() ?>
                        <div class="form-group">
                            <label for="plan_id" class="form-label">Změnit tarif</label>
                            <select id="plan_id" name="plan_id" class="form-select" required>
                                <?php foreach ($plans as $plan): ?>
                                    <?php
                                    $label = $plan['name'] . ' — ' . number_format($plan['price'], 0, ',', ' ') . ' Kč (' . $plan['reminder_limit'] . ' připomínek)';
                                    if (!$plan['is_available']) {
                                        $label .= ' (neaktivní)';
                                    }
                                    ?>
                                    <option value="<?= $plan['id'] ?>" <?= (int) $plan['id'] === (int) $subscription['plan_id'] ? 'selected' : '' ?>>
                                        <?= e($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-hint">Aktuálně: <?= $reminderCount ?> / <?= $reminderLimit ?> připomínek.</div>
                        </div>
                        <button type="submit" class="btn btn--outline btn--small">Uložit tarif</button>
                    </form>
                    <?php if (in_array($subscription['status'], ['active', 'expired'])): ?>
                        <div class="mt-2">
                            <form action="/admin/zakaznik/<?= $customer['id'] ?>/prodlouzit" method="post" style="display: inline;"
                                  onsubmit="return confirm('Opravdu prodloužit předplatné o 1 rok?')">
                                <?= \CSRF::field() ?>
                                <button type="submit" class="btn btn--outline btn--small">Prodloužit o rok</button>
                            </form>
                        </div>
                    <?php endif; ?>
                    <?php if ($subscription['status'] === 'awaiting_payment'): ?>
                        <div class="mt-2">
                            <form action="/admin/predplatne/<?= $subscription['id'] ?>/potvrdit" method="post" style="display: inline;">
                                <?= \CSRF::field() ?>
                                <input type="hidden" name="price_paid" value="<?= $subscription['price'] ?>">
                                <button type="submit" class="btn btn--outline btn--small">Potvrdit platbu</button>
                            </form>
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
                    <?= \CSRF::field() ?>
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
                <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
                    <span class="text-small text-muted"><?= count($reminders) ?>/<?= $reminderLimit ?></span>
                    <?php if (count($reminders) < $reminderLimit): ?>
                        <button type="button" class="btn btn--primary btn--small" data-modal-open="addReminderModal">+ Přidat</button>
                    <?php endif; ?>
                </div>
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
                                    <?= format_date_long($reminder['event_day'], $reminder['event_month'], $reminder['event_type']) ?>
                                </div>
                            </div>
                            <div class="text-small text-muted">
                                <?= translate_price_range($reminder['price_range']) ?>
                                · Za <?= days_until($reminder['event_day'], $reminder['event_month'], $reminder['event_type']) ?> dní
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

<!-- Správa zákazníka -->
<div class="mt-4" style="border-top: 1px solid var(--color-border); padding-top: var(--spacing-xl);">
    <h3>Správa zákazníka</h3>

    <!-- Aktivace/Deaktivace -->
    <div class="mb-3">
        <form action="/admin/zakaznik/<?= $customer['id'] ?>/toggle-active" method="post" style="display: inline;">
            <?= \CSRF::field() ?>
            <?php if ($customer['is_active'] ?? true): ?>
                <button type="submit" class="btn btn--outline btn--small">
                    <i class="ri-user-unfollow-line"></i> Deaktivovat zákazníka
                </button>
                <span class="text-small text-muted" style="margin-left: var(--spacing-sm);">
                    Deaktivovaný zákazník se nebude zobrazovat v seznamu k provolání.
                </span>
            <?php else: ?>
                <button type="submit" class="btn btn--primary btn--small">
                    <i class="ri-user-follow-line"></i> Aktivovat zákazníka
                </button>
                <span class="badge badge--warning" style="margin-left: var(--spacing-sm);">Deaktivováno</span>
            <?php endif; ?>
        </form>
    </div>

    <!-- Smazání -->
    <div class="mt-3">
        <h4 class="text-error"><i class="ri-error-warning-line"></i> Nebezpečná zóna</h4>
        <p class="text-small text-muted">Smazání zákazníka je nevratné. Všechna data včetně připomínek budou odstraněna.</p>
        <button type="button" class="btn btn--danger btn--small" data-modal-open="deleteCustomerModal">
            <i class="ri-delete-bin-line"></i> Smazat zákazníka
        </button>
    </div>
</div>

<!-- Modal pro přidání připomínky -->
<div class="modal-overlay" id="addReminderModal">
    <div class="modal" style="max-width: 500px;">
        <div class="modal-header">
            <h3 class="modal-title">Přidat připomínku</h3>
            <button class="modal-close" data-modal-close>&times;</button>
        </div>
        <form action="/admin/zakaznik/<?= $customer['id'] ?>/pripominka" method="post">
            <?= \CSRF::field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Koho slavíte?</label>
                    <select name="recipient_relation" class="form-select" required>
                        <option value="">— vyberte —</option>
                        <?php foreach (\Models\Reminder::getRelations() as $val => $label): ?>
                            <option value="<?= $val ?>"><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Co slavíte?</label>
                    <select name="event_type" class="form-select" id="admin_event_type" required>
                        <option value="">— vyberte —</option>
                        <?php foreach (\Models\Reminder::getEventTypes() as $val => $label): ?>
                            <option value="<?= $val ?>"><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row form-row--2">
                    <div class="form-group">
                        <label class="form-label">Den</label>
                        <select name="event_day" class="form-select" id="admin_event_day" required>
                            <option value="">Den</option>
                            <?php for ($i = 1; $i <= 31; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?>.</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Měsíc</label>
                        <select name="event_month" class="form-select" id="admin_event_month" required>
                            <option value="">Měsíc</option>
                            <?php
                            $months = ['', 'Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen', 'Červenec', 'Srpen', 'Září', 'Říjen', 'Listopad', 'Prosinec'];
                            for ($i = 1; $i <= 12; $i++):
                            ?>
                                <option value="<?= $i ?>"><?= $months[$i] ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row form-row--2">
                    <div class="form-group">
                        <label class="form-label">Předstih</label>
                        <select name="advance_days" class="form-select">
                            <?php foreach (\Models\Reminder::getAdvanceDays() as $val => $label): ?>
                                <option value="<?= $val ?>" <?= $val === 5 ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Rozpočet</label>
                        <select name="price_range" class="form-select">
                            <?php foreach (\Models\Reminder::getPriceRanges() as $val => $label): ?>
                                <option value="<?= $val ?>" <?= $val === 'to_discuss' ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Poznámka</label>
                    <textarea name="customer_note" class="form-textarea" rows="2" maxlength="500"
                              placeholder="Např. má ráda tulipány..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--ghost" data-modal-close>Zrušit</button>
                <button type="submit" class="btn btn--primary">Přidat připomínku</button>
            </div>
        </form>
    </div>
</div>

<script>
// Auto-fill date for holidays
(function() {
    var eventType = document.getElementById('admin_event_type');
    var daySelect = document.getElementById('admin_event_day');
    var monthSelect = document.getElementById('admin_event_month');
    if (!eventType) return;

    <?php
    $mothersDay = get_holiday_date('mothers_day') ?? ['day' => 10, 'month' => 5];
    $fathersDay = get_holiday_date('fathers_day') ?? ['day' => 21, 'month' => 6];
    ?>
    var autoHolidays = {
        'valentines': { day: 14, month: 2 },
        'womens_day': { day: 8, month: 3 },
        'mothers_day': { day: <?= $mothersDay['day'] ?>, month: <?= $mothersDay['month'] ?> },
        'fathers_day': { day: <?= $fathersDay['day'] ?>, month: <?= $fathersDay['month'] ?> },
        'school_year_end': { day: 30, month: 6 }
    };

    eventType.addEventListener('change', function() {
        var h = autoHolidays[this.value];
        if (h) {
            daySelect.value = h.day;
            monthSelect.value = h.month;
        }
    });
})();
</script>

<!-- Modal pro potvrzení smazání -->
<div class="modal-overlay" id="deleteCustomerModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title"><i class="ri-error-warning-line"></i> Potvrdit smazání</h3>
            <button class="modal-close" data-modal-close>&times;</button>
        </div>
        <form action="/admin/zakaznik/<?= $customer['id'] ?>/smazat" method="post">
            <?= \CSRF::field() ?>
            <div class="modal-body">
                <p style="color: var(--color-error); font-weight: 600;">
                    Opravdu chcete smazat zákazníka <?= e($customer['name'] ?: $customer['phone']) ?>?
                </p>
                <p class="text-muted">
                    Tato akce je <strong>nevratná</strong>. Budou smazány:
                </p>
                <ul style="margin: var(--spacing-sm) 0; padding-left: var(--spacing-lg);">
                    <li>Všechny osobní údaje zákazníka</li>
                    <li>Všechny připomínky (<?= count($reminders) ?>)</li>
                    <li>Historie volání a objednávek</li>
                    <li>Předplatné a platební historie</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--ghost" data-modal-close>Zrušit</button>
                <button type="submit" class="btn btn--danger">
                    <i class="ri-delete-bin-line"></i> Ano, smazat zákazníka
                </button>
            </div>
        </form>
    </div>
</div>
