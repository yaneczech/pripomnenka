<div class="container" style="max-width: 600px;">

    <div class="page-header" style="margin-bottom: var(--spacing-xl);">
        <a href="/moje-pripominky" class="text-small text-muted">← Zpět na seznam</a>
        <h1 style="margin: var(--spacing-sm) 0 0;">Upravit připomínku</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="/pripominka/<?= $reminder['id'] ?>" method="post" data-validate>
                <?= \CSRF::field() ?>

                <!-- Koho slavíte? -->
                <div class="form-group">
                    <label class="form-label form-label--required">Koho slavíte?</label>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: var(--spacing-sm);">
                        <?php foreach ($relations as $value => $label): ?>
                            <div class="form-check">
                                <input type="radio" id="relation_<?= $value ?>" name="recipient_relation" value="<?= $value ?>"
                                       class="form-check-input" <?= (old('recipient_relation') ?: $reminder['recipient_relation']) === $value ? 'checked' : '' ?> required>
                                <label for="relation_<?= $value ?>" class="form-check-label"><?= e($label) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (isset($errors['recipient_relation'])): ?>
                        <span class="form-error"><?= e($errors['recipient_relation']) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Co slavíte? -->
                <div class="form-group">
                    <label class="form-label form-label--required">Co slavíte?</label>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: var(--spacing-sm);">
                        <?php foreach ($eventTypes as $value => $label): ?>
                            <div class="form-check">
                                <input type="radio" id="event_<?= $value ?>" name="event_type" value="<?= $value ?>"
                                       class="form-check-input" <?= (old('event_type') ?: $reminder['event_type']) === $value ? 'checked' : '' ?> required>
                                <label for="event_<?= $value ?>" class="form-check-label"><?= e($label) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (isset($errors['event_type'])): ?>
                        <span class="form-error"><?= e($errors['event_type']) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Datum -->
                <div class="form-group">
                    <label class="form-label form-label--required">Datum</label>
                    <div class="form-row form-row--2">
                        <div>
                            <select name="event_day" class="form-select <?= isset($errors['event_day']) ? 'form-input--error' : '' ?>" required>
                                <option value="">Den</option>
                                <?php for ($i = 1; $i <= 31; $i++): ?>
                                    <option value="<?= $i ?>" <?= (int) (old('event_day') ?: $reminder['event_day']) === $i ? 'selected' : '' ?>><?= $i ?>.</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div>
                            <select name="event_month" class="form-select <?= isset($errors['event_month']) ? 'form-input--error' : '' ?>" required>
                                <option value="">Měsíc</option>
                                <?php
                                $months = ['', 'Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen', 'Červenec', 'Srpen', 'Září', 'Říjen', 'Listopad', 'Prosinec'];
                                for ($i = 1; $i <= 12; $i++):
                                ?>
                                    <option value="<?= $i ?>" <?= (int) (old('event_month') ?: $reminder['event_month']) === $i ? 'selected' : '' ?>><?= $months[$i] ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <span class="form-hint">Rok neukládáme — připomeneme vám to každý rok automaticky 🔁</span>
                    <?php if (isset($errors['event_day'])): ?>
                        <span class="form-error"><?= e($errors['event_day']) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Kdy připomenout -->
                <div class="form-group">
                    <label for="advance_days" class="form-label">Kdy připomenout?</label>
                    <select name="advance_days" id="advance_days" class="form-select">
                        <?php foreach ($advanceDays as $value => $label): ?>
                            <option value="<?= $value ?>" <?= (int) (old('advance_days') ?: $reminder['advance_days']) === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="form-hint">Zavoláme vám tolik dní předem, abyste měli čas na objednávku.</span>
                </div>

                <!-- Rozpočet -->
                <div class="form-group">
                    <label class="form-label">Rozpočet</label>
                    <select name="price_range" class="form-select">
                        <?php foreach ($priceRanges as $value => $label): ?>
                            <option value="<?= $value ?>" <?= (old('price_range') ?: $reminder['price_range']) === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Poznámka -->
                <div class="form-group">
                    <label for="customer_note" class="form-label">Poznámka</label>
                    <textarea name="customer_note" id="customer_note" class="form-textarea" rows="3"
                              placeholder="Např. má ráda tulipány, preferuji pastelové barvy, nemá ráda lilie..."
                              data-maxlength="500"><?= e(old('customer_note') ?: $reminder['customer_note']) ?></textarea>
                </div>

                <div style="display: flex; gap: var(--spacing-md); margin-top: var(--spacing-xl);">
                    <button type="submit" class="btn btn--primary">Uložit změny</button>
                    <a href="/moje-pripominky" class="btn btn--ghost">Zrušit</a>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
// Automatické doplnění data pro specifické svátky
(function() {
    const eventTypeInputs = document.querySelectorAll('input[name="event_type"]');
    const daySelect = document.querySelector('select[name="event_day"]');
    const monthSelect = document.querySelector('select[name="event_month"]');

    // Svátky s automatickým datem (vypočítáno pro aktuální rok)
    const autoHolidays = {
        'valentines': { day: 14, month: 2 },
        'womens_day': { day: 8, month: 3 },
        'mothers_day': { day: <?= get_holiday_date('mothers_day')['day'] ?>, month: <?= get_holiday_date('mothers_day')['month'] ?> },
        'fathers_day': { day: <?= get_holiday_date('fathers_day')['day'] ?>, month: <?= get_holiday_date('fathers_day')['month'] ?> },
        'school_year_end': { day: 30, month: 6 }
    };

    eventTypeInputs.forEach(input => {
        input.addEventListener('change', function() {
            const eventType = this.value;

            if (autoHolidays[eventType]) {
                daySelect.value = autoHolidays[eventType].day;
                monthSelect.value = autoHolidays[eventType].month;
                daySelect.disabled = true;
                monthSelect.disabled = true;
            } else {
                daySelect.disabled = false;
                monthSelect.disabled = false;
            }
        });
    });

    // Při načtení stránky zkontrolovat aktuálně vybraný event_type
    const checkedEventType = document.querySelector('input[name="event_type"]:checked');
    if (checkedEventType && autoHolidays[checkedEventType.value]) {
        daySelect.disabled = true;
        monthSelect.disabled = true;
    }
})();
</script>
