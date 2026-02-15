<div class="login-logo">
    <img src="<?= asset('img/ikona.svg') ?>" alt="">
    <span>Připomněnka</span>
</div>

<h1 class="login-title">Nové heslo</h1>

<form action="/admin/reset-hesla/<?= e($token) ?>" method="post" data-validate>
    <?= \CSRF::field() ?>

    <div class="form-group">
        <label for="password" class="form-label">Nové heslo</label>
        <input type="password" id="password" name="password" class="form-input <?= isset($errors['password']) ? 'form-input--error' : '' ?>"
               required autofocus minlength="8">
        <?php if (isset($errors['password'])): ?>
            <span class="form-error"><?= e($errors['password']) ?></span>
        <?php endif; ?>
        <span class="form-hint">Minimálně 8 znaků</span>
    </div>

    <div class="form-group">
        <label for="password_confirm" class="form-label">Potvrzení hesla</label>
        <input type="password" id="password_confirm" name="password_confirm" class="form-input <?= isset($errors['password_confirm']) ? 'form-input--error' : '' ?>"
               required minlength="8">
        <?php if (isset($errors['password_confirm'])): ?>
            <span class="form-error"><?= e($errors['password_confirm']) ?></span>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn--primary btn--block">Nastavit heslo</button>
</form>

<p style="text-align: center; margin-top: var(--spacing-lg);">
    <a href="/admin/prihlaseni" style="color: var(--color-primary);">Zpět na přihlášení</a>
</p>
