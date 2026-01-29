<div class="page-header">
    <h1>E-mailové šablony</h1>
    <p class="text-muted">Upravte předměty e-mailů a zobrazte jejich náhledy</p>
</div>

<div class="settings-nav">
    <a href="/admin/nastaveni" class="settings-nav-item">
        <i class="ri-settings-3-line"></i> Obecné
    </a>
    <a href="/admin/nastaveni/plany" class="settings-nav-item">
        <i class="ri-price-tag-3-line"></i> Tarify
    </a>
    <a href="/admin/nastaveni/emaily" class="settings-nav-item settings-nav-item--active">
        <i class="ri-mail-line"></i> E-maily
    </a>
</div>

<form action="/admin/nastaveni" method="post">
    <?= \CSRF::field() ?>

    <div class="email-templates-grid">
        <!-- Aktivační e-mail -->
        <div class="email-template-card card">
            <div class="card-body">
                <div class="email-template-header">
                    <div>
                        <h3 class="email-template-title">
                            <i class="ri-user-add-line"></i> Aktivační e-mail
                        </h3>
                        <p class="email-template-desc">Posílá se zákazníkovi po zaplacení předplatného, obsahuje odkaz pro aktivaci účtu.</p>
                    </div>
                    <a href="/admin/nastaveni/emaily/nahled/activation" target="_blank" class="btn btn--outline btn--small">
                        <i class="ri-eye-line"></i> Náhled
                    </a>
                </div>

                <div class="form-group" style="margin-top: var(--spacing-md); margin-bottom: 0;">
                    <label for="email_activation_subject" class="form-label">Předmět e-mailu</label>
                    <input type="text" id="email_activation_subject" name="email_activation_subject" class="form-input"
                           value="<?= e($settings['email_activation_subject'] ?? '') ?>" placeholder="Vítejte v Připomněnce! 🌷">
                </div>
            </div>
        </div>

        <!-- QR kód pro platbu -->
        <div class="email-template-card card">
            <div class="card-body">
                <div class="email-template-header">
                    <div>
                        <h3 class="email-template-title">
                            <i class="ri-qr-code-line"></i> QR kód pro platbu
                        </h3>
                        <p class="email-template-desc">Posílá se zákazníkovi, který zvolil platbu převodem, obsahuje QR kód a platební údaje.</p>
                    </div>
                    <a href="/admin/nastaveni/emaily/nahled/payment_qr" target="_blank" class="btn btn--outline btn--small">
                        <i class="ri-eye-line"></i> Náhled
                    </a>
                </div>

                <div class="form-group" style="margin-top: var(--spacing-md); margin-bottom: 0;">
                    <label for="email_payment_qr_subject" class="form-label">Předmět e-mailu</label>
                    <input type="text" id="email_payment_qr_subject" name="email_payment_qr_subject" class="form-input"
                           value="<?= e($settings['email_payment_qr_subject'] ?? '') ?>" placeholder="QR kód pro platbu Připomněnka 💳">
                </div>
            </div>
        </div>

        <!-- Připomínka události -->
        <div class="email-template-card card">
            <div class="card-body">
                <div class="email-template-header">
                    <div>
                        <h3 class="email-template-title">
                            <i class="ri-calendar-event-line"></i> Připomínka události
                        </h3>
                        <p class="email-template-desc">Automatický e-mail zákazníkovi před důležitým datem (narozeniny, výročí apod.).</p>
                    </div>
                    <a href="/admin/nastaveni/emaily/nahled/event_reminder" target="_blank" class="btn btn--outline btn--small">
                        <i class="ri-eye-line"></i> Náhled
                    </a>
                </div>

                <div class="form-group" style="margin-top: var(--spacing-md); margin-bottom: 0;">
                    <label for="email_customer_reminder_subject" class="form-label">Předmět e-mailu</label>
                    <input type="text" id="email_customer_reminder_subject" name="email_customer_reminder_subject" class="form-input"
                           value="<?= e($settings['email_customer_reminder_subject'] ?? '') ?>" placeholder="Blíží se důležité datum! 💐">
                    <span class="form-hint">Proměnné: {{event_type}}, {{recipient}}</span>
                </div>
            </div>
        </div>

        <!-- Upozornění na expiraci -->
        <div class="email-template-card card">
            <div class="card-body">
                <div class="email-template-header">
                    <div>
                        <h3 class="email-template-title">
                            <i class="ri-alarm-warning-line"></i> Upozornění na expiraci
                        </h3>
                        <p class="email-template-desc">Posílá se 30 a 14 dní před vypršením předplatného, obsahuje QR kód pro obnovu.</p>
                    </div>
                    <a href="/admin/nastaveni/emaily/nahled/expiration_reminder" target="_blank" class="btn btn--outline btn--small">
                        <i class="ri-eye-line"></i> Náhled
                    </a>
                </div>

                <div class="form-group" style="margin-top: var(--spacing-md); margin-bottom: 0;">
                    <label for="email_expiration_subject" class="form-label">Předmět e-mailu</label>
                    <input type="text" id="email_expiration_subject" name="email_expiration_subject" class="form-input"
                           value="<?= e($settings['email_expiration_subject'] ?? '') ?>" placeholder="Vaše předplatné Připomněnka brzy vyprší ⏰">
                </div>
            </div>
        </div>

        <!-- OTP přihlašovací kód -->
        <div class="email-template-card card">
            <div class="card-body">
                <div class="email-template-header">
                    <div>
                        <h3 class="email-template-title">
                            <i class="ri-lock-password-line"></i> OTP přihlašovací kód
                        </h3>
                        <p class="email-template-desc">Jednorázový 6místný kód pro přihlášení zákazníka bez hesla.</p>
                    </div>
                    <a href="/admin/nastaveni/emaily/nahled/otp" target="_blank" class="btn btn--outline btn--small">
                        <i class="ri-eye-line"></i> Náhled
                    </a>
                </div>

                <p class="text-small text-muted" style="margin: var(--spacing-md) 0 0 0;">
                    <i class="ri-information-line"></i> Tento e-mail má pevný předmět a nelze ho upravit.
                </p>
            </div>
        </div>

        <!-- Denní přehled pro administrátora -->
        <div class="email-template-card card">
            <div class="card-body">
                <div class="email-template-header">
                    <div>
                        <h3 class="email-template-title">
                            <i class="ri-dashboard-line"></i> Denní přehled pro administrátora
                        </h3>
                        <p class="email-template-desc">Souhrnný e-mail s přehledem úkolů, který se posílá každé ráno.</p>
                    </div>
                    <a href="/admin/nastaveni/emaily/nahled/admin_summary" target="_blank" class="btn btn--outline btn--small">
                        <i class="ri-eye-line"></i> Náhled
                    </a>
                </div>

                <p class="text-small text-muted" style="margin: var(--spacing-md) 0 0 0;">
                    <i class="ri-information-line"></i> Tento e-mail má pevný předmět a nelze ho upravit.
                </p>
            </div>
        </div>
    </div>

    <div class="form-actions" style="margin-top: var(--spacing-xl);">
        <button type="submit" class="btn btn--primary btn--large">
            <i class="ri-save-line"></i> Uložit změny
        </button>
    </div>
</form>

<style>
.email-templates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 400px), 1fr));
    gap: var(--spacing-lg);
}

.email-template-card {
    height: 100%;
}

.email-template-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: var(--spacing-md);
}

.email-template-title {
    margin: 0 0 var(--spacing-xs) 0;
    font-size: var(--font-size-lg);
    color: var(--color-text);
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.email-template-title i {
    color: var(--color-primary);
    font-size: var(--font-size-xl);
}

.email-template-desc {
    margin: 0;
    color: var(--color-text-muted);
    font-size: var(--font-size-sm);
    line-height: 1.5;
}

@media (max-width: 768px) {
    .email-templates-grid {
        grid-template-columns: 1fr;
    }

    .email-template-header {
        flex-direction: column;
    }

    .email-template-header .btn {
        width: 100%;
    }
}
</style>
