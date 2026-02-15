<div class="container" style="max-width: 400px; padding: var(--spacing-2xl) var(--spacing-md);">

    <div style="text-align: center; margin-bottom: var(--spacing-xl);">
        <img src="<?= asset('img/logo.svg') ?>" alt="Připomněnka" style="max-width: 200px; margin: 0 auto var(--spacing-md) auto;">
        <h1 style="font-size: var(--font-size-2xl);">Přihlášení</h1>
    </div>

    <div class="card">
        <div class="card-body">

            <?php if ($step === 'identifier'): ?>
                <!-- Krok 1: Zadání telefonu nebo emailu -->
                <form action="/prihlaseni" method="post" data-validate>
                    <?= \CSRF::field() ?>
                    <input type="hidden" name="step" value="identifier">

                    <div class="form-group">
                        <label for="identifier" class="form-label">Telefon nebo e-mail</label>
                        <input type="text" id="identifier" name="identifier"
                               class="form-input <?= isset($errors['identifier']) ? 'form-input--error' : '' ?>"
                               placeholder="+420 777 888 999 nebo vas@email.cz"
                               value="<?= e(old('identifier')) ?>" required autofocus>
                        <?php if (isset($errors['identifier'])): ?>
                            <span class="form-error"><?= e($errors['identifier']) ?></span>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn--primary btn--block">Pokračovat</button>
                </form>

            <?php elseif ($step === 'password'): ?>
                <!-- Krok 2a: Zadání hesla -->
                <p class="text-center text-muted mb-3">
                    Přihlášení jako<br>
                    <strong><?= e($identifier) ?></strong>
                    <?php if (!empty($customerName)): ?>
                        <br><?= e($customerName) ?>
                    <?php endif; ?>
                </p>

                <form action="/prihlaseni" method="post" data-validate>
                    <?= \CSRF::field() ?>
                    <input type="hidden" name="step" value="password">

                    <div class="form-group">
                        <label for="password" class="form-label">Heslo</label>
                        <input type="password" id="password" name="password"
                               class="form-input <?= isset($errors['password']) ? 'form-input--error' : '' ?>"
                               required autofocus>
                        <?php if (isset($errors['password'])): ?>
                            <span class="form-error"><?= e($errors['password']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" id="remember" name="remember" value="1" class="form-check-input" checked>
                        <label for="remember" class="form-check-label">Zapamatovat si mě</label>
                    </div>

                    <button type="submit" class="btn btn--primary btn--block">Přihlásit se</button>
                </form>

                <div class="text-center mt-3">
                    <a href="/prihlaseni/otp" class="text-small">Zapomněli jste heslo? Přihlaste se kódem</a>
                </div>

                <div class="text-center mt-2">
                    <a href="/prihlaseni" class="text-small text-muted">← Zpět</a>
                </div>

            <?php elseif ($step === 'otp'): ?>
                <!-- Krok 2b: Zadání OTP kódu -->
                <?php if (!empty($customerName)): ?>
                    <p class="text-center text-muted mb-2">
                        Přihlášení jako<br>
                        <strong><?= e($customerName) ?></strong>
                    </p>
                <?php endif; ?>

                <p class="text-center text-muted mb-3">
                    Poslali jsme vám 6místný kód na e-mail:<br>
                    <strong><?= e($identifier) ?></strong>
                </p>

                <?php if (!empty($debugOtp)): ?>
                    <div class="flash flash--info mb-3" style="position: static;">
                        <strong>🔧 Debug režim:</strong> Váš kód je <strong style="font-size: 1.3em; user-select: all;"><?= e($debugOtp) ?></strong>
                    </div>
                <?php endif; ?>

                <?php if (isset($emailSent) && !$emailSent): ?>
                    <div class="flash flash--error mb-3" style="position: static;">
                        ⚠️ Nepodařilo se odeslat e-mail. Zkuste to znovu nebo kontaktujte podporu.
                    </div>
                <?php endif; ?>

                <form action="/prihlaseni" method="post" data-validate>
                    <?= \CSRF::field() ?>
                    <input type="hidden" name="step" value="otp">

                    <div class="form-group">
                        <label for="code" class="form-label">Ověřovací kód</label>
                        <input type="text" id="code" name="code" inputmode="numeric" pattern="[0-9]{6}"
                               class="form-input <?= isset($errors['code']) ? 'form-input--error' : '' ?>"
                               placeholder="000000" maxlength="6"
                               style="text-align: center; font-size: var(--font-size-2xl); letter-spacing: 0.5em;"
                               required autofocus autocomplete="one-time-code">
                        <?php if (isset($errors['code'])): ?>
                            <span class="form-error"><?= e($errors['code']) ?></span>
                        <?php endif; ?>
                        <span class="form-hint">Zkontrolujte i složku spam.</span>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" id="remember_otp" name="remember" value="1" class="form-check-input" checked>
                        <label for="remember_otp" class="form-check-label">Zapamatovat si mě</label>
                    </div>

                    <button type="submit" class="btn btn--primary btn--block">Ověřit</button>
                </form>

                <div class="text-center mt-3" id="otp-resend">
                    <?php if (!empty($canResend)): ?>
                        <form action="/prihlaseni/znovu-poslat" method="post" style="display: inline;">
                            <?= \CSRF::field() ?>
                            <button type="submit" class="btn btn--ghost btn--small">Poslat kód znovu</button>
                        </form>
                    <?php else: ?>
                        <span class="text-muted text-small" id="otp-countdown">Poslat znovu za <strong id="otp-timer">60</strong>s</span>
                        <form action="/prihlaseni/znovu-poslat" method="post" style="display: none;" id="otp-resend-form">
                            <?= \CSRF::field() ?>
                            <button type="submit" class="btn btn--ghost btn--small">Poslat kód znovu</button>
                        </form>
                        <script>
                        (function() {
                            var seconds = 60;
                            var timer = document.getElementById('otp-timer');
                            var countdown = document.getElementById('otp-countdown');
                            var form = document.getElementById('otp-resend-form');
                            var interval = setInterval(function() {
                                seconds--;
                                timer.textContent = seconds;
                                if (seconds <= 0) {
                                    clearInterval(interval);
                                    countdown.style.display = 'none';
                                    form.style.display = 'inline';
                                }
                            }, 1000);
                        })();
                        </script>
                    <?php endif; ?>
                </div>

                <div class="text-center mt-2">
                    <a href="/prihlaseni" class="text-small text-muted">← Zpět</a>
                </div>

            <?php endif; ?>

        </div>
    </div>

</div>
