<div class="container" style="max-width: 600px; padding: var(--spacing-xl) var(--spacing-md);">

    <!-- Progress bar -->
    <div style="display: flex; gap: var(--spacing-sm); margin-bottom: var(--spacing-2xl);">
        <?php for ($i = 1; $i <= 3; $i++): ?>
            <div style="flex: 1; height: 4px; border-radius: 2px; background: <?= $i <= $step ? 'var(--color-primary)' : 'var(--color-border)' ?>;"></div>
        <?php endfor; ?>
    </div>

    <div class="text-center mb-4">
        <img src="<?= asset('img/logo.svg') ?>" alt="Připomněnka" style="max-width: 200px;">
    </div>

    <?php if ($step === 1): ?>
        <!-- Krok 1: Představení -->
        <div class="card">
            <div class="card-body">
                <h1 style="text-align: center; margin-bottom: var(--spacing-lg);">Nejdřív se představte</h1>

                <form action="/aktivace/<?= e($token) ?>" method="post">
                    <?= \CSRF::field() ?>
                    <input type="hidden" name="step" value="1">

                    <div class="form-group">
                        <label for="name" class="form-label">Jak vám máme říkat?</label>
                        <input type="text" id="name" name="name" class="form-input"
                               value="<?= e(old('name') ?: $customer['name']) ?>"
                               placeholder="Vaše jméno (volitelné)">
                        <span class="form-hint">Použijeme ho při telefonátech a v e-mailech.</span>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Heslo (volitelné)</label>
                        <input type="password" id="password" name="password" class="form-input <?= isset($errors['password']) ? 'form-input--error' : '' ?>"
                               placeholder="Minimálně 8 znaků">
                        <?php if (isset($errors['password'])): ?>
                            <span class="form-error"><?= e($errors['password']) ?></span>
                        <?php else: ?>
                            <span class="form-hint">Pokud nenastavíte, pošleme vám při každém přihlášení kód na e-mail.</span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" id="gdpr_consent" name="gdpr_consent" value="1"
                                   class="form-check-input" <?= old('gdpr_consent') ? 'checked' : '' ?> required>
                            <label for="gdpr_consent" class="form-check-label">
                                Souhlasím s <a href="/podminky" target="_blank">obchodními podmínkami</a>
                                a se <a href="/ochrana-udaju" target="_blank">zpracováním osobních údajů</a>.
                            </label>
                        </div>
                        <?php if (isset($errors['gdpr_consent'])): ?>
                            <span class="form-error"><?= e($errors['gdpr_consent']) ?></span>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn--primary btn--block">Pokračovat</button>
                </form>
            </div>
        </div>

    <?php elseif ($step === 2): ?>
        <!-- Krok 2: Připomínky -->
        <?php
        $reminders = (new \Models\Reminder())->getByCustomer($subscription['customer_id']);
        $remainingCount = $subscription['reminder_limit'] - count($reminders);
        ?>

        <div class="card mb-3">
            <div class="card-body">
                <h1 style="text-align: center; margin-bottom: var(--spacing-md);">Jaká data vám máme hlídat?</h1>

                <!-- Progress limitu -->
                <div style="margin-bottom: var(--spacing-lg);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: var(--spacing-xs);">
                        <span>Můžete přidat ještě <?= $remainingCount ?> připomínek</span>
                        <span class="text-muted"><?= count($reminders) ?>/<?= $subscription['reminder_limit'] ?></span>
                    </div>
                    <div style="background: var(--color-border); border-radius: var(--radius-full); height: 6px;">
                        <div style="background: var(--color-primary); height: 100%; width: <?= (count($reminders) / $subscription['reminder_limit']) * 100 ?>%; border-radius: var(--radius-full);"></div>
                    </div>
                </div>

                <!-- Existující připomínky -->
                <?php if (!empty($reminders)): ?>
                    <div class="mb-3">
                        <?php foreach ($reminders as $reminder): ?>
                            <div style="display: flex; justify-content: space-between; padding: var(--spacing-sm) 0; border-bottom: 1px solid var(--color-border);">
                                <div>
                                    <strong><?= translate_event_type($reminder['event_type']) ?></strong>
                                    — <?= translate_relation($reminder['recipient_relation']) ?>
                                </div>
                                <div class="text-muted"><?= format_date_long($reminder['event_day'], $reminder['event_month']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Formulář pro přidání -->
                <?php if ($remainingCount > 0): ?>
                    <form action="/aktivace/<?= e($token) ?>" method="post">
                        <?= \CSRF::field() ?>
                        <input type="hidden" name="step" value="2">
                        <input type="hidden" name="action" value="add_reminder">

                        <div class="form-row form-row--2">
                            <div class="form-group">
                                <label class="form-label">Koho?</label>
                                <select name="recipient_relation" class="form-select" required>
                                    <option value="">Vyberte...</option>
                                    <?php foreach (\Models\Reminder::getRelations() as $value => $label): ?>
                                        <option value="<?= $value ?>"><?= e($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Co?</label>
                                <select name="event_type" class="form-select" required>
                                    <option value="">Vyberte...</option>
                                    <?php foreach (\Models\Reminder::getEventTypes() as $value => $label): ?>
                                        <option value="<?= $value ?>"><?= e($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row form-row--2">
                            <div class="form-group">
                                <label class="form-label">Den</label>
                                <select name="event_day" class="form-select" required>
                                    <option value="">Den</option>
                                    <?php for ($i = 1; $i <= 31; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?>.</option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Měsíc</label>
                                <select name="event_month" class="form-select" required>
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

                        <button type="submit" class="btn btn--outline btn--block">+ Přidat připomínku</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tlačítka pro pokračování -->
        <form action="/aktivace/<?= e($token) ?>" method="post">
            <?= \CSRF::field() ?>
            <input type="hidden" name="step" value="2">
            <input type="hidden" name="action" value="continue">

            <div style="display: flex; gap: var(--spacing-md);">
                <button type="submit" class="btn btn--primary" style="flex: 1;">Pokračovat</button>
                <?php if (empty($reminders)): ?>
                    <button type="submit" name="action" value="skip" class="btn btn--ghost">Přidat později</button>
                <?php endif; ?>
            </div>
        </form>

        <?php
        // Svátky s automatickým datem
        $mothersDay = get_holiday_date('mothers_day') ?? ['day' => 10, 'month' => 5];
        $fathersDay = get_holiday_date('fathers_day') ?? ['day' => 21, 'month' => 6];
        ?>

        <style>
        .select-locked {
            opacity: 0.6;
            pointer-events: none;
            background-color: #f5f5f5;
            cursor: not-allowed;
        }
        </style>

        <script>
        (function() {
            var eventTypeSelect = document.querySelector('select[name="event_type"]');
            var daySelect = document.querySelector('select[name="event_day"]');
            var monthSelect = document.querySelector('select[name="event_month"]');

            if (!eventTypeSelect || !daySelect || !monthSelect) return;

            var autoHolidays = {
                'valentines': { day: 14, month: 2 },
                'womens_day': { day: 8, month: 3 },
                'mothers_day': { day: <?= $mothersDay['day'] ?? 10 ?>, month: <?= $mothersDay['month'] ?? 5 ?> },
                'fathers_day': { day: <?= $fathersDay['day'] ?? 21 ?>, month: <?= $fathersDay['month'] ?? 6 ?> },
                'school_year_end': { day: 30, month: 6 }
            };

            function updateDateFields(eventType) {
                if (autoHolidays[eventType]) {
                    daySelect.value = autoHolidays[eventType].day;
                    monthSelect.value = autoHolidays[eventType].month;
                    daySelect.classList.add('select-locked');
                    monthSelect.classList.add('select-locked');
                    daySelect.setAttribute('tabindex', '-1');
                    monthSelect.setAttribute('tabindex', '-1');
                } else {
                    daySelect.classList.remove('select-locked');
                    monthSelect.classList.remove('select-locked');
                    daySelect.removeAttribute('tabindex');
                    monthSelect.removeAttribute('tabindex');
                }
            }

            eventTypeSelect.addEventListener('change', function() {
                updateDateFields(this.value);
            });

            if (eventTypeSelect.value) {
                updateDateFields(eventTypeSelect.value);
            }
        })();
        </script>

    <?php elseif ($step === 3): ?>
        <!-- Krok 3: Hotovo -->
        <?php $reminders = (new \Models\Reminder())->getByCustomerSorted($subscription['customer_id']); ?>

        <div class="card">
            <div class="card-body text-center">
                <div style="font-size: 4rem; margin-bottom: var(--spacing-md);">🎉</div>
                <h1 style="margin-bottom: var(--spacing-lg);">Hotovo!</h1>

                <?php if (!empty($reminders)): ?>
                    <p class="text-muted mb-3">Připomeneme vám:</p>
                    <div class="mb-4" style="text-align: left;">
                        <?php foreach ($reminders as $reminder): ?>
                            <div style="display: flex; justify-content: space-between; padding: var(--spacing-sm) var(--spacing-md); background: var(--color-background); border-radius: var(--radius-md); margin-bottom: var(--spacing-sm);">
                                <div>
                                    <strong><?= format_date_long($reminder['event_day'], $reminder['event_month'], $reminder['event_type']) ?></strong>
                                    — <?= translate_event_type($reminder['event_type']) ?> (<?= translate_relation($reminder['recipient_relation']) ?>)
                                </div>
                                <div class="text-muted">za <?= days_until($reminder['event_day'], $reminder['event_month'], $reminder['event_type']) ?> dní</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($subscription['discount_percent']): ?>
                    <div style="background: var(--color-success-light); padding: var(--spacing-md); border-radius: var(--radius-md); margin-bottom: var(--spacing-xl);">
                        <strong>🎁 Nezapomeňte:</strong> Máte <?= $subscription['discount_percent'] ?>% slevu na všechny kytice!
                    </div>
                <?php endif; ?>

                <form action="/aktivace/<?= e($token) ?>" method="post">
                    <?= \CSRF::field() ?>
                    <input type="hidden" name="step" value="3">
                    <button type="submit" class="btn btn--primary btn--large">Přejít do profilu</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

</div>
