<div class="page-header">
    <h1>Nespárované platby</h1>
</div>

<?php if (empty($payments)): ?>
    <div class="empty-state">
        <div class="empty-state-icon"><i class="ri-check-double-line"></i></div>
        <h2>Vše spárováno</h2>
        <p>Žádné nespárované platby k vyřízení.</p>
        <a href="/admin" class="btn btn--primary">Zpět na dashboard</a>
    </div>
<?php else: ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Částka</th>
                        <th>VS</th>
                        <th>Odesílatel</th>
                        <th>Akce</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?= e(date('j. n. Y H:i', strtotime($payment['received_at']))) ?></td>
                            <td><strong><?= number_format((float) $payment['amount'], 0, ',', ' ') ?> Kč</strong></td>
                            <td><?= e($payment['variable_symbol'] ?? '—') ?></td>
                            <td><?= e($payment['sender_name'] ?? '—') ?></td>
                            <td>
                                <form action="/admin/platby/<?= $payment['id'] ?>/priradit" method="post" class="form-inline">
                                    <?= \CSRF::field() ?>
                                    <select name="subscription_id" class="form-input form-input--small" required>
                                        <option value="">Vyberte předplatné...</option>
                                        <?php foreach ($pendingSubscriptions as $sub): ?>
                                            <option value="<?= $sub['id'] ?>">
                                                VS <?= e($sub['variable_symbol'] ?? '') ?> — <?= e($sub['customer_name'] ?? $sub['phone'] ?? '') ?> (<?= format_price($sub['price']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn--small btn--primary">Přiřadit</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
