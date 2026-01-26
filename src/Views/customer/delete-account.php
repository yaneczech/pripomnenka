<div class="container" style="max-width: 500px; padding: var(--spacing-2xl) var(--spacing-md);">

    <div class="page-header" style="margin-bottom: var(--spacing-xl);">
        <a href="/profil" class="text-small text-muted"><i class="ri-arrow-left-line"></i> Zpět na profil</a>
        <h1 style="margin: var(--spacing-sm) 0 0; color: var(--color-error);">Smazání účtu</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <div style="text-align: center; margin-bottom: var(--spacing-xl);">
                <div style="font-size: 4rem; color: var(--color-warning);"><i class="ri-alert-line"></i></div>
                <h2 style="color: var(--color-error);">Tato akce je nevratná</h2>
            </div>

            <div style="background: var(--color-warning-light); padding: var(--spacing-md); border-radius: var(--radius-md); margin-bottom: var(--spacing-lg);">
                <p style="margin: 0;"><strong>Smazáním účtu přijdete o:</strong></p>
                <ul style="margin: var(--spacing-sm) 0 0; padding-left: var(--spacing-lg);">
                    <li>Všechny uložené připomínky</li>
                    <li>Historii objednávek</li>
                    <li>Slevu 10% na květiny</li>
                </ul>
            </div>

            <div style="background: var(--color-error-light); padding: var(--spacing-md); border-radius: var(--radius-md); margin-bottom: var(--spacing-xl); border: 1px solid var(--color-error);">
                <p style="margin: 0; color: var(--color-error);">
                    <strong><i class="ri-error-warning-line"></i> Upozornění k předplatnému:</strong>
                </p>
                <p style="margin: var(--spacing-sm) 0 0; color: var(--color-text);">
                    Pokud máte aktivní předplatné, jeho <strong>zbývající doba nebude refundována</strong>.
                    Smazáním účtu se vzdáváte nároku na vrácení zaplacené částky.
                </p>
            </div>

            <form action="/smazat-ucet" method="post">
                <?= \CSRF::field() ?>

                <div class="form-group">
                    <label for="confirmation" class="form-label form-label--required">
                        Pro potvrzení napište: <strong>SMAZAT ÚČET</strong>
                    </label>
                    <input type="text" id="confirmation" name="confirmation" class="form-input"
                           placeholder="SMAZAT ÚČET" required autocomplete="off">
                </div>

                <div style="display: flex; gap: var(--spacing-md); margin-top: var(--spacing-xl);">
                    <button type="submit" class="btn btn--danger">
                        <i class="ri-delete-bin-line"></i> Ano, smazat účet
                    </button>
                    <a href="/profil" class="btn btn--ghost">Zrušit</a>
                </div>
            </form>
        </div>
    </div>

    <p class="text-small text-muted text-center mt-3">
        Pokud máte dotazy ohledně smazání, kontaktujte nás na
        <a href="mailto:<?= e($config['app']['shop_email'] ?? 'info@jelenivzeleni.cz') ?>">
            <?= e($config['app']['shop_email'] ?? 'info@jelenivzeleni.cz') ?>
        </a>
    </p>

</div>
