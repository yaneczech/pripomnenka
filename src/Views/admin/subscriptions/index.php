<div class="page-header">
    <h1>Předplatné</h1>
</div>

<!-- Filtry -->
<div class="filter-tabs">
    <a href="/admin/predplatne" class="filter-tab <?= $filter === 'all' ? 'filter-tab--active' : '' ?>">
        Všechny
    </a>
    <a href="/admin/predplatne?filter=pending" class="filter-tab <?= $filter === 'pending' ? 'filter-tab--active' : '' ?>">
        <i class="ri-time-line"></i>
        Čeká na platbu
        <?php if ($stats['pending'] > 0): ?>
            <span class="badge"><?= $stats['pending'] ?></span>
        <?php endif; ?>
    </a>
    <a href="/admin/predplatne?filter=unmatched" class="filter-tab <?= $filter === 'unmatched' ? 'filter-tab--active' : '' ?>">
        <i class="ri-error-warning-line"></i>
        Nespárované
        <?php if ($stats['unmatched'] > 0): ?>
            <span class="badge badge--error"><?= $stats['unmatched'] ?></span>
        <?php endif; ?>
    </a>
    <a href="/admin/predplatne?filter=expiring" class="filter-tab <?= $filter === 'expiring' ? 'filter-tab--active' : '' ?>">
        <i class="ri-alarm-warning-line"></i>
        Expiruje
        <?php if ($stats['expiring'] > 0): ?>
            <span class="badge badge--warning"><?= $stats['expiring'] ?></span>
        <?php endif; ?>
    </a>
    <a href="/admin/predplatne?filter=expired" class="filter-tab <?= $filter === 'expired' ? 'filter-tab--active' : '' ?>">
        <i class="ri-close-circle-line"></i>
        Vypršelé
        <?php if ($stats['expired'] > 0): ?>
            <span class="badge"><?= $stats['expired'] ?></span>
        <?php endif; ?>
    </a>
</div>

<?php if (empty($subscriptions)): ?>
    <div class="empty-state">
        <div class="empty-state-icon"><i class="ri-file-list-3-line"></i></div>
        <h2 class="empty-state-title">Žádné záznamy</h2>
        <p class="empty-state-text">V této kategorii nejsou žádná předplatná.</p>
    </div>
<?php else: ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Zákazník</th>
                        <th>Tarif</th>
                        <th>Částka</th>
                        <th>VS</th>
                        <th>Stav</th>
                        <th>Platnost</th>
                        <th>Akce</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subscriptions as $sub): ?>
                        <tr>
                            <td data-label="Zákazník">
                                <?php if (!empty($sub['customer'])): ?>
                                    <a href="/admin/zakaznik/<?= $sub['customer_id'] ?>">
                                        <?= e($sub['customer']['name'] ?: $sub['customer']['email']) ?>
                                    </a>
                                    <div class="text-small text-muted"><?= e($sub['customer']['phone']) ?></div>
                                <?php else: ?>
                                    <span class="text-muted">Zákazník #<?= $sub['customer_id'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Tarif"><?= e($sub['plan_name'] ?? 'Neznámý') ?></td>
                            <td data-label="Částka"><?= format_price($sub['price']) ?></td>
                            <td data-label="VS"><code><?= e($sub['variable_symbol']) ?></code></td>
                            <td data-label="Stav">
                                <?php
                                $statusClass = match ($sub['status']) {
                                    'active' => 'badge--success',
                                    'awaiting_payment' => 'badge--warning',
                                    'awaiting_activation' => 'badge--info',
                                    'expired' => 'badge--error',
                                    default => '',
                                };
                                $statusText = match ($sub['status']) {
                                    'active' => 'Aktivní',
                                    'awaiting_payment' => 'Čeká na platbu',
                                    'awaiting_activation' => 'Čeká na aktivaci',
                                    'expired' => 'Vypršelo',
                                    'cancelled' => 'Zrušeno',
                                    default => $sub['status'],
                                };
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                            </td>
                            <td data-label="Platnost">
                                <?php if ($sub['expires_at']): ?>
                                    <?= format_date($sub['expires_at']) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Akce">
                                <?php if ($sub['status'] === 'awaiting_payment'): ?>
                                    <button type="button" class="btn btn--small btn--primary" onclick="confirmPayment(<?= $sub['id'] ?>, <?= $sub['price'] ?>)">
                                        <i class="ri-check-line"></i> Potvrdit
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- Modal pro potvrzení platby -->
<div id="confirmPaymentModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="closeModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Potvrdit platbu</h3>
            <button type="button" class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="confirmPaymentForm" method="post">
            <?= \CSRF::field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label for="price_paid" class="form-label">Zaplacená částka (Kč)</label>
                    <input type="number" id="price_paid" name="price_paid" class="form-input" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="note" class="form-label">Poznámka (nepovinné)</label>
                    <input type="text" id="note" name="note" class="form-input" placeholder="Např. přeplatek, platba v hotovosti...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--ghost" onclick="closeModal()">Zrušit</button>
                <button type="submit" class="btn btn--primary">Potvrdit platbu</button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmPayment(id, expectedPrice) {
    document.getElementById('confirmPaymentForm').action = '/admin/predplatne/' + id + '/potvrdit';
    document.getElementById('price_paid').value = expectedPrice;
    document.getElementById('confirmPaymentModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('confirmPaymentModal').style.display = 'none';
}
</script>
