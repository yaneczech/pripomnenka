<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-xl);">
    <h1 style="margin: 0;">Zákazníci</h1>
    <a href="/admin/novy-zakaznik" class="btn btn--secondary">+ Nový zákazník</a>
</div>

<!-- Vyhledávání -->
<div class="card mb-3">
    <div class="card-body">
        <form action="/admin/zakaznici" method="get">
            <div class="form-inline">
                <div class="form-group" style="flex: 1; margin: 0;">
                    <input type="text" name="q" id="customer-search" class="form-input"
                           placeholder="Hledat podle jména, telefonu nebo emailu..."
                           value="<?= e($search) ?>">
                </div>
                <button type="submit" class="btn btn--primary">Hledat</button>
                <?php if ($search): ?>
                    <a href="/admin/zakaznici" class="btn btn--ghost">Zrušit</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Filtry -->
<div class="mb-3" style="display: flex; gap: var(--spacing-sm); flex-wrap: wrap;">
    <a href="/admin/zakaznici" class="btn <?= $filter === 'all' ? 'btn--primary' : 'btn--outline' ?> btn--small">
        Všichni (<?= $counts['all'] ?>)
    </a>
    <a href="/admin/zakaznici?filter=active" class="btn <?= $filter === 'active' ? 'btn--primary' : 'btn--outline' ?> btn--small">
        Aktivní (<?= $counts['active'] ?>)
    </a>
    <a href="/admin/zakaznici?filter=awaiting_activation" class="btn <?= $filter === 'awaiting_activation' ? 'btn--primary' : 'btn--outline' ?> btn--small">
        Čeká na aktivaci (<?= $counts['awaiting_activation'] ?>)
    </a>
    <a href="/admin/zakaznici?filter=awaiting_payment" class="btn <?= $filter === 'awaiting_payment' ? 'btn--primary' : 'btn--outline' ?> btn--small">
        Čeká na platbu (<?= $counts['awaiting_payment'] ?>)
    </a>
    <a href="/admin/zakaznici?filter=expired" class="btn <?= $filter === 'expired' ? 'btn--primary' : 'btn--outline' ?> btn--small">
        Vypršelí (<?= $counts['expired'] ?>)
    </a>
</div>

<!-- Seznam zákazníků -->
<?php if (empty($customers)): ?>
    <div class="empty-state">
        <div class="empty-state-icon">👥</div>
        <h2 class="empty-state-title">Žádní zákazníci</h2>
        <p class="empty-state-text">
            <?php if ($search): ?>
                Pro hledaný výraz "<?= e($search) ?>" nebyli nalezeni žádní zákazníci.
            <?php else: ?>
                Zatím nemáte žádné zákazníky.
            <?php endif; ?>
        </p>
        <a href="/admin/novy-zakaznik" class="btn btn--secondary">Přidat prvního zákazníka</a>
    </div>
<?php else: ?>
    <div class="customer-list">
        <?php foreach ($customers as $customer): ?>
            <a href="/admin/zakaznik/<?= $customer['id'] ?>" class="customer-row"
               data-name="<?= e($customer['name'] ?? '') ?>"
               data-phone="<?= e($customer['phone']) ?>"
               data-email="<?= e($customer['email']) ?>">
                <div class="customer-info">
                    <div class="customer-name">
                        <?= e($customer['name'] ?: 'Neznámé jméno') ?>
                    </div>
                    <div class="customer-contact">
                        <?= e($customer['phone']) ?> · <?= e($customer['email']) ?>
                    </div>
                </div>
                <div class="customer-status">
                    <?php
                    $status = $customer['subscription_status'] ?? 'none';
                    $statusClass = match($status) {
                        'active' => 'badge--success',
                        'awaiting_activation' => 'badge--info',
                        'awaiting_payment' => 'badge--warning',
                        'expired' => 'badge--error',
                        default => 'badge--muted',
                    };
                    $statusText = match($status) {
                        'active' => 'Aktivní',
                        'awaiting_activation' => 'Čeká na aktivaci',
                        'awaiting_payment' => 'Čeká na platbu',
                        'expired' => 'Vypršelo',
                        default => 'Bez předplatného',
                    };
                    ?>
                    <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
