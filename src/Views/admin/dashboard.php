<h1>Dashboard</h1>

<?php if ($todayCallCount === 0 && $unmatchedPaymentsCount === 0 && $awaitingActivationCount === 0): ?>
    <!-- Prázdný stav -->
    <div class="empty-state">
        <div class="empty-state-icon">✨</div>
        <h2 class="empty-state-title">Dnes je klid!</h2>
        <p class="empty-state-text">Všechno běží jak má. Žádné úkoly k vyřízení.</p>
    </div>
<?php endif; ?>

<!-- Widgety -->
<div class="dashboard-widgets">
    <!-- Dnes volat -->
    <a href="/admin/dnes" class="widget <?= $todayCallCount > 0 ? ($hasRepeatedAttempts ? 'widget--warning' : 'widget--primary') : '' ?>" data-href="/admin/dnes">
        <div class="widget-icon">📞</div>
        <div class="widget-value"><?= $todayCallCount ?></div>
        <div class="widget-label">Dnes volat</div>
    </a>

    <!-- Čeká na aktivaci -->
    <a href="/admin/zakaznici?filter=awaiting_activation" class="widget" data-href="/admin/zakaznici?filter=awaiting_activation">
        <div class="widget-icon">⏳</div>
        <div class="widget-value"><?= $awaitingActivationCount ?></div>
        <div class="widget-label">Čeká na aktivaci</div>
    </a>

    <!-- Nespárované platby -->
    <a href="/admin/platby" class="widget <?= $unmatchedPaymentsCount > 0 ? 'widget--error' : '' ?>" data-href="/admin/platby">
        <div class="widget-icon">💳</div>
        <div class="widget-value"><?= $unmatchedPaymentsCount ?></div>
        <div class="widget-label">Nespárované platby</div>
    </a>

    <!-- Tento týden -->
    <a href="/admin/tyden" class="widget" data-href="/admin/tyden">
        <div class="widget-icon">📅</div>
        <div class="widget-value"><?= $thisWeekCount ?></div>
        <div class="widget-label">Tento týden</div>
    </a>

    <!-- Expiruje brzy -->
    <a href="/admin/predplatne?filter=expiring" class="widget <?= $expiringSoonCount > 0 ? 'widget--warning' : '' ?>" data-href="/admin/predplatne?filter=expiring">
        <div class="widget-icon">⚠️</div>
        <div class="widget-value"><?= $expiringSoonCount ?></div>
        <div class="widget-label">Expiruje brzy</div>
    </a>
</div>

<!-- Statistiky -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Statistiky</h2>
    </div>
    <div class="card-body">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-value"><?= $stats['customers_active'] ?></div>
                <div class="stat-label">Aktivních zákazníků</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= $stats['reminders_total'] ?></div>
                <div class="stat-label">Připomínek celkem</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= number_format($stats['revenue_this_month'], 0, ',', ' ') ?> Kč</div>
                <div class="stat-label">Tento měsíc</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= number_format($stats['revenue_total'], 0, ',', ' ') ?> Kč</div>
                <div class="stat-label">Celkem</div>
            </div>
        </div>
    </div>
</div>
