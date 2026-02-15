<div class="login-logo">
    <img src="<?= asset('img/ikona.svg') ?>" alt="">
    <span>Připomněnka</span>
</div>

<h1 class="login-title">Zapomenuté heslo</h1>

<p style="text-align: center; color: var(--color-text-muted); margin-bottom: var(--spacing-lg);">
    Zadejte svůj email a pošleme vám odkaz pro nastavení nového hesla.
</p>

<form action="/admin/zapomnene-heslo" method="post" data-validate>
    <?= \CSRF::field() ?>

    <div class="form-group">
        <label for="email" class="form-label">E-mail</label>
        <input type="email" id="email" name="email" class="form-input <?= isset($errors['email']) ? 'form-input--error' : '' ?>"
               value="<?= e(old('email')) ?>" required autofocus>
        <?php if (isset($errors['email'])): ?>
            <span class="form-error"><?= e($errors['email']) ?></span>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn--primary btn--block">Odeslat odkaz</button>
</form>

<p style="text-align: center; margin-top: var(--spacing-lg);">
    <a href="/admin/prihlaseni" style="color: var(--color-primary);">Zpět na přihlášení</a>
</p>
