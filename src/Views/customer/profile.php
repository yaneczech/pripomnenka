<div class="container" style="max-width: 700px;">

    <div class="page-header" style="margin-bottom: var(--spacing-xl);">
        <h1>Můj profil</h1>
    </div>

    <!-- Kontaktní údaje -->
    <div class="card mb-3">
        <div class="card-header">
            <h2 class="card-title">Kontaktní údaje</h2>
        </div>
        <div class="card-body">
            <form action="/profil" method="post" data-validate>
                <?= \CSRF::field() ?>

                <div class="form-group">
                    <label for="name" class="form-label">Jméno</label>
                    <input type="text" id="name" name="name" class="form-input"
                           value="<?= e($customer['name'] ?? '') ?>" placeholder="Jak vám máme říkat?">
                    <span class="form-hint">Toto jméno uvidíme my i vy v e-mailech.</span>
                </div>

                <div class="form-row form-row--2">
                    <div class="form-group">
                        <label class="form-label">Telefon</label>
                        <input type="tel" class="form-input" value="<?= e($customer['phone']) ?>" disabled>
                        <span class="form-hint">Pro změnu telefonu nás kontaktujte.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-input" value="<?= e($customer['email']) ?>" disabled>
                        <span class="form-hint">Pro změnu e-mailu nás kontaktujte.</span>
                    </div>
                </div>

                <button type="submit" class="btn btn--primary">Uložit změny</button>
            </form>
        </div>
    </div>

    <!-- Změna hesla -->
    <div class="card mb-3">
        <div class="card-header">
            <h2 class="card-title">
                <?= $customer['password_hash'] ? 'Změna hesla' : 'Nastavení hesla' ?>
            </h2>
        </div>
        <div class="card-body">
            <form action="/profil" method="post" data-validate>
                <?= \CSRF::field() ?>
                <input type="hidden" name="name" value="<?= e($customer['name'] ?? '') ?>">

                <?php if ($customer['password_hash']): ?>
                    <div class="form-group">
                        <label for="current_password" class="form-label">Aktuální heslo</label>
                        <input type="password" id="current_password" name="current_password" class="form-input">
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-3">
                        Zatím používáte přihlášení kódem z e-mailu. Můžete si nastavit heslo pro rychlejší přístup.
                    </p>
                <?php endif; ?>

                <div class="form-row form-row--2">
                    <div class="form-group">
                        <label for="new_password" class="form-label">Nové heslo</label>
                        <input type="password" id="new_password" name="new_password" class="form-input"
                               minlength="8" placeholder="Alespoň 8 znaků">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Potvrdit heslo</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input">
                    </div>
                </div>

                <button type="submit" class="btn btn--primary">
                    <?= $customer['password_hash'] ? 'Změnit heslo' : 'Nastavit heslo' ?>
                </button>
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
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: var(--spacing-md);">
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
                    <div>
                        <span class="text-small text-muted">Limit připomínek</span>
                        <div><?= $subscription['reminder_limit'] ?></div>
                    </div>
                </div>

                <?php if ($subscription['status'] === 'active'): ?>
                    <div class="mt-3" style="background: var(--color-success-light); padding: var(--spacing-md); border-radius: var(--radius-md);">
                        <strong>🎁 Vaše výhody:</strong>
                        <ul style="margin: var(--spacing-sm) 0 0; padding-left: var(--spacing-lg);">
                            <li>Připomínky důležitých dat</li>
                            <li>Osobní telefonát před každou událostí</li>
                            <li><strong>10% sleva</strong> na všechny kytice</li>
                        </ul>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-muted">Nemáte aktivní předplatné.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- GDPR a data -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Moje data (GDPR)</h2>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">
                Máte právo na přístup k vašim osobním údajům a jejich export.
                Můžete také požádat o smazání celého účtu.
            </p>

            <div style="display: flex; gap: var(--spacing-md); flex-wrap: wrap;">
                <a href="/export-dat?format=json" class="btn btn--outline">
                    📥 Stáhnout data (JSON)
                </a>
                <a href="/ochrana-udaju" class="btn btn--ghost">
                    📜 Informace o zpracování
                </a>
            </div>

            <hr style="margin: var(--spacing-xl) 0;">

            <div>
                <h3 class="text-error" style="margin-bottom: var(--spacing-sm);">Smazání účtu</h3>
                <p class="text-small text-muted mb-2">
                    Smazání účtu je nevratné. Všechna vaše data včetně připomínek budou odstraněna.
                </p>
                <a href="/smazat-ucet" class="btn btn--danger btn--small">Smazat účet</a>
            </div>
        </div>
    </div>

</div>
