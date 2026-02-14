<h1>Nový zákazník</h1>

<div class="card" style="max-width: 600px;">
    <div class="card-body">
        <form action="/admin/novy-zakaznik" method="post" data-validate id="quick-customer-form">
            <?= \CSRF::field() ?>

            <div class="form-group">
                <label for="phone" class="form-label form-label--required">Telefon</label>
                <input type="tel" id="phone" name="phone" class="form-input <?= isset($errors['phone']) ? 'form-input--error' : '' ?>"
                       value="<?= e(old('phone', '+420 ')) ?>" placeholder="+420 777 888 999" required autofocus>
                <?php if (isset($errors['phone'])): ?>
                    <span class="form-error"><?= e($errors['phone']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email" class="form-label form-label--required">E-mail</label>
                <input type="email" id="email" name="email" class="form-input <?= isset($errors['email']) ? 'form-input--error' : '' ?>"
                       value="<?= e(old('email')) ?>" placeholder="zakaznik@email.cz" required>
                <?php if (isset($errors['email'])): ?>
                    <span class="form-error"><?= e($errors['email']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label form-label--required">Varianta předplatného</label>
                <?php foreach ($plans as $plan): ?>
                    <div class="form-check">
                        <input type="radio" id="plan_<?= $plan['id'] ?>" name="plan_id" value="<?= $plan['id'] ?>"
                               class="form-check-input" <?= $plan['id'] == ($defaultPlanId ?? old('plan_id')) ? 'checked' : '' ?> required>
                        <label for="plan_<?= $plan['id'] ?>" class="form-check-label">
                            <strong><?= e($plan['name']) ?></strong> — <?= number_format($plan['price'], 0, ',', ' ') ?> Kč
                            <span class="text-muted">(<?= $plan['reminder_limit'] ?> připomínek)</span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="form-group">
                <label class="form-label form-label--required">Způsob platby</label>
                <div class="form-check">
                    <input type="radio" id="payment_cash" name="payment_method" value="cash"
                           class="form-check-input" <?= old('payment_method', 'cash') === 'cash' ? 'checked' : '' ?> required>
                    <label for="payment_cash" class="form-check-label">Hotově</label>
                </div>
                <div class="form-check">
                    <input type="radio" id="payment_card" name="payment_method" value="card"
                           class="form-check-input" <?= old('payment_method') === 'card' ? 'checked' : '' ?>>
                    <label for="payment_card" class="form-check-label">Kartou</label>
                </div>
                <div class="form-check">
                    <input type="radio" id="payment_transfer" name="payment_method" value="bank_transfer"
                           class="form-check-input" <?= old('payment_method') === 'bank_transfer' ? 'checked' : '' ?>>
                    <label for="payment_transfer" class="form-check-label">Převodem</label>
                </div>
                <div class="bank-transfer-info mt-2" style="display: <?= old('payment_method') === 'bank_transfer' ? 'block' : 'none' ?>;">
                    <p class="text-small text-muted">
                        Zákazníkovi bude odeslán e-mail s QR kódem pro platbu.
                        Po připsání platby mu automaticky pošleme aktivační odkaz.
                    </p>
                </div>
            </div>

            <div style="padding: var(--spacing-md); background: var(--color-background); border-radius: var(--radius-md); border-left: 3px solid var(--color-primary); margin-top: var(--spacing-lg);">
                <p style="margin: 0; color: var(--color-text-light); font-size: var(--font-size-sm);">
                    <strong>Souhlas s podmínkami:</strong>
                    Zákazník odsouhlasí <a href="/podminky" target="_blank">obchodní podmínky</a>
                    a <a href="/ochrana-udaju" target="_blank">zpracování osobních údajů</a>
                    při aktivaci účtu (krok 1 aktivačního průvodce).
                </p>
            </div>

            <div style="display: flex; gap: var(--spacing-md); margin-top: var(--spacing-xl);">
                <button type="submit" class="btn btn--primary">Uložit a odeslat</button>
                <a href="/admin/zakaznici" class="btn btn--ghost">Zrušit</a>
            </div>
        </form>
    </div>
</div>

<p class="text-small text-muted mt-3">
    Tip: Pro rychlejší zadávání můžete použít <kbd>Ctrl</kbd> + <kbd>Enter</kbd> pro uložení.
</p>
