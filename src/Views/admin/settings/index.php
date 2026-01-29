<div class="page-header">
    <h1>Nastavení</h1>
</div>

<div class="settings-nav">
    <a href="/admin/nastaveni" class="settings-nav-item settings-nav-item--active">
        <i class="ri-settings-3-line"></i> Obecné
    </a>
    <a href="/admin/nastaveni/plany" class="settings-nav-item">
        <i class="ri-price-tag-3-line"></i> Tarify
    </a>
    <a href="/admin/nastaveni/emaily" class="settings-nav-item">
        <i class="ri-mail-line"></i> E-maily
    </a>
</div>

<form action="/admin/nastaveni" method="post">
    <?= \CSRF::field() ?>

    <!-- Kontaktní údaje -->
    <div class="card mb-3">
        <div class="card-header">
            <h2 class="card-title"><i class="ri-store-2-line"></i> Kontaktní údaje</h2>
        </div>
        <div class="card-body">
            <div class="form-row form-row--2">
                <div class="form-group">
                    <label for="shop_phone" class="form-label">Telefon</label>
                    <input type="tel" id="shop_phone" name="shop_phone" class="form-input"
                           value="<?= e($settings['shop_phone'] ?? '') ?>" placeholder="+420 123 456 789">
                </div>
                <div class="form-group">
                    <label for="shop_email" class="form-label">Email</label>
                    <input type="email" id="shop_email" name="shop_email" class="form-input"
                           value="<?= e($settings['shop_email'] ?? '') ?>" placeholder="info@jelenivzeleni.cz">
                </div>
            </div>
        </div>
    </div>

    <!-- Bankovní údaje -->
    <div class="card mb-3">
        <div class="card-header">
            <h2 class="card-title"><i class="ri-bank-line"></i> Bankovní údaje</h2>
        </div>
        <div class="card-body">
            <div class="form-row form-row--2">
                <div class="form-group">
                    <label for="bank_account" class="form-label">Číslo účtu</label>
                    <input type="text" id="bank_account" name="bank_account" class="form-input"
                           value="<?= e($settings['bank_account'] ?? '') ?>" placeholder="123456789/0100">
                </div>
                <div class="form-group">
                    <label for="bank_iban" class="form-label">IBAN</label>
                    <input type="text" id="bank_iban" name="bank_iban" class="form-input"
                           value="<?= e($settings['bank_iban'] ?? '') ?>" placeholder="CZ...">
                </div>
            </div>

            <hr class="my-3">
            <h3 class="text-small mb-2"><i class="ri-mail-settings-line"></i> IMAP pro automatické párování plateb</h3>

            <div class="form-row form-row--3">
                <div class="form-group">
                    <label for="bank_imap_host" class="form-label">IMAP server</label>
                    <input type="text" id="bank_imap_host" name="bank_imap_host" class="form-input"
                           value="<?= e($settings['bank_imap_host'] ?? '') ?>" placeholder="imap.airbank.cz">
                </div>
                <div class="form-group">
                    <label for="bank_imap_email" class="form-label">Email</label>
                    <input type="email" id="bank_imap_email" name="bank_imap_email" class="form-input"
                           value="<?= e($settings['bank_imap_email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="bank_imap_password" class="form-label">Heslo</label>
                    <input type="password" id="bank_imap_password" name="bank_imap_password" class="form-input"
                           placeholder="<?= !empty($settings['bank_imap_password']) ? '********' : '' ?>">
                    <span class="form-hint">Nechte prázdné pro zachování aktuálního</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Výchozí nastavení -->
    <div class="card mb-3">
        <div class="card-header">
            <h2 class="card-title"><i class="ri-timer-line"></i> Výchozí nastavení</h2>
        </div>
        <div class="card-body">
            <div class="form-row form-row--2">
                <div class="form-group">
                    <label for="default_advance_days" class="form-label">Výchozí předstih připomínky (dny)</label>
                    <select id="default_advance_days" name="default_advance_days" class="form-select">
                        <?php foreach ([3, 5, 7, 10, 14] as $days): ?>
                            <option value="<?= $days ?>" <?= ($settings['default_advance_days'] ?? 5) == $days ? 'selected' : '' ?>>
                                <?= $days ?> pracovních dnů
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="activation_link_validity_days" class="form-label">Platnost aktivačního odkazu (dny)</label>
                    <input type="number" id="activation_link_validity_days" name="activation_link_validity_days"
                           class="form-input" min="1" max="90"
                           value="<?= e($settings['activation_link_validity_days'] ?? '30') ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn--primary btn--large">
            <i class="ri-save-line"></i> Uložit nastavení
        </button>
    </div>
</form>
