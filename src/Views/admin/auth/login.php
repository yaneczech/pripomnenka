<div class="login-logo">
    <img src="<?= asset('img/ikona.svg') ?>" alt="">
    <span>Připomněnka</span>
</div>

<h1 class="login-title">Přihlášení</h1>

<form action="/admin/prihlaseni" method="post" data-validate>
    <?= \CSRF::field() ?>

    <div class="form-group">
        <label for="email" class="form-label">Email</label>
        <input type="email" id="email" name="email" class="form-input <?= isset($errors['email']) ? 'form-input--error' : '' ?>"
               value="<?= e(old('email')) ?>" required autofocus>
        <?php if (isset($errors['email'])): ?>
            <span class="form-error"><?= e($errors['email']) ?></span>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="password" class="form-label">Heslo</label>
        <input type="password" id="password" name="password" class="form-input <?= isset($errors['password']) ? 'form-input--error' : '' ?>" required>
        <?php if (isset($errors['password'])): ?>
            <span class="form-error"><?= e($errors['password']) ?></span>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn--primary btn--block">Přihlásit se</button>
</form>
