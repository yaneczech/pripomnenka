<div class="page-header">
    <h1>Náhled emailů</h1>
</div>

<div class="settings-nav">
    <a href="/admin/nastaveni" class="settings-nav-item">
        <i class="ri-settings-3-line"></i> Obecné
    </a>
    <a href="/admin/nastaveni/plany" class="settings-nav-item">
        <i class="ri-price-tag-3-line"></i> Tarify
    </a>
    <a href="/admin/nastaveni/emaily" class="settings-nav-item settings-nav-item--active">
        <i class="ri-mail-line"></i> Náhled emailů
    </a>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h2 class="card-title"><i class="ri-mail-line"></i> Zasílané emaily</h2>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">Zde můžete zobrazit náhledy všech emailů, které systém automaticky zasílá zákazníkům a administrátorům.</p>

        <div class="email-preview-list">
            <!-- Aktivační email -->
            <div class="email-preview-item">
                <div class="email-preview-header">
                    <div>
                        <h3 class="email-preview-title">
                            <i class="ri-user-add-line"></i> Aktivační email
                        </h3>
                        <p class="email-preview-desc">Posílá se zákazníkovi po zaplacení předplatného, obsahuje odkaz pro aktivaci účtu.</p>
                    </div>
                    <a href="/admin/nastaveni/emaily/nahled/activation" target="_blank" class="btn btn--primary">
                        <i class="ri-eye-line"></i> Zobrazit náhled
                    </a>
                </div>
            </div>

            <!-- QR kód pro platbu -->
            <div class="email-preview-item">
                <div class="email-preview-header">
                    <div>
                        <h3 class="email-preview-title">
                            <i class="ri-qr-code-line"></i> QR kód pro platbu
                        </h3>
                        <p class="email-preview-desc">Posílá se zákazníkovi, který zvolil platbu převodem, obsahuje QR kód a platební údaje.</p>
                    </div>
                    <a href="/admin/nastaveni/emaily/nahled/payment_qr" target="_blank" class="btn btn--primary">
                        <i class="ri-eye-line"></i> Zobrazit náhled
                    </a>
                </div>
            </div>

            <!-- Připomínka události -->
            <div class="email-preview-item">
                <div class="email-preview-header">
                    <div>
                        <h3 class="email-preview-title">
                            <i class="ri-calendar-event-line"></i> Připomínka události
                        </h3>
                        <p class="email-preview-desc">Automatický email zákazníkovi před důležitým datem (narozeniny, výročí apod.).</p>
                    </div>
                    <a href="/admin/nastaveni/emaily/nahled/event_reminder" target="_blank" class="btn btn--primary">
                        <i class="ri-eye-line"></i> Zobrazit náhled
                    </a>
                </div>
            </div>

            <!-- Expirace předplatného -->
            <div class="email-preview-item">
                <div class="email-preview-header">
                    <div>
                        <h3 class="email-preview-title">
                            <i class="ri-alarm-warning-line"></i> Upozornění na expiraci
                        </h3>
                        <p class="email-preview-desc">Posílá se 30 a 14 dní před vypršením předplatného, obsahuje QR kód pro obnovu.</p>
                    </div>
                    <a href="/admin/nastaveni/emaily/nahled/expiration_reminder" target="_blank" class="btn btn--primary">
                        <i class="ri-eye-line"></i> Zobrazit náhled
                    </a>
                </div>
            </div>

            <!-- OTP přihlašovací kód -->
            <div class="email-preview-item">
                <div class="email-preview-header">
                    <div>
                        <h3 class="email-preview-title">
                            <i class="ri-lock-password-line"></i> OTP přihlašovací kód
                        </h3>
                        <p class="email-preview-desc">Jednorázový 6místný kód pro přihlášení zákazníka bez hesla.</p>
                    </div>
                    <a href="/admin/nastaveni/emaily/nahled/otp" target="_blank" class="btn btn--primary">
                        <i class="ri-eye-line"></i> Zobrazit náhled
                    </a>
                </div>
            </div>

            <!-- Denní přehled pro administrátora -->
            <div class="email-preview-item">
                <div class="email-preview-header">
                    <div>
                        <h3 class="email-preview-title">
                            <i class="ri-dashboard-line"></i> Denní přehled pro administrátora
                        </h3>
                        <p class="email-preview-desc">Souhrnný email s přehledem úkolů, který se posílá každé ráno.</p>
                    </div>
                    <a href="/admin/nastaveni/emaily/nahled/admin_summary" target="_blank" class="btn btn--primary">
                        <i class="ri-eye-line"></i> Zobrazit náhled
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.email-preview-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.email-preview-item {
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    padding: var(--spacing-lg);
    background: var(--color-background);
    transition: all var(--transition-fast);
}

.email-preview-item:hover {
    box-shadow: var(--shadow-sm);
}

.email-preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: var(--spacing-lg);
}

.email-preview-title {
    margin: 0 0 var(--spacing-xs) 0;
    font-size: var(--font-size-lg);
    color: var(--color-text);
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.email-preview-title i {
    color: var(--color-primary);
}

.email-preview-desc {
    margin: 0;
    color: var(--color-text-muted);
    font-size: var(--font-size-sm);
}

@media (max-width: 768px) {
    .email-preview-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .email-preview-header .btn {
        width: 100%;
    }
}
</style>
