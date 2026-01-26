<h1>Dashboard</h1>

<?php if ($todayCallCount === 0 && $unmatchedPaymentsCount === 0 && $awaitingActivationCount === 0): ?>
    <!-- Prazdny stav -->
    <div class="empty-state">
        <div class="empty-state-icon"><i class="ri-sparkling-2-fill"></i></div>
        <h2 class="empty-state-title">Dnes je klid!</h2>
        <p class="empty-state-text">Vsechno bezi jak ma. Zadne ukoly k vyrizeni.</p>
    </div>
<?php endif; ?>

<!-- Widgety -->
<div class="dashboard-widgets">
    <!-- Dnes volat -->
    <a href="/admin/dnes" class="widget <?= $todayCallCount > 0 ? ($hasRepeatedAttempts ? 'widget--warning' : 'widget--primary') : '' ?>" data-href="/admin/dnes">
        <div class="widget-icon"><i class="ri-phone-line"></i></div>
        <div class="widget-value"><?= $todayCallCount ?></div>
        <div class="widget-label">Dnes volat</div>
    </a>

    <!-- Ceka na aktivaci -->
    <a href="/admin/zakaznici?filter=awaiting_activation" class="widget" data-href="/admin/zakaznici?filter=awaiting_activation">
        <div class="widget-icon"><i class="ri-hourglass-line"></i></div>
        <div class="widget-value"><?= $awaitingActivationCount ?></div>
        <div class="widget-label">Ceka na aktivaci</div>
    </a>

    <!-- Nesparovane platby -->
    <a href="/admin/predplatne?filter=unmatched" class="widget <?= $unmatchedPaymentsCount > 0 ? 'widget--error' : '' ?>" data-href="/admin/predplatne?filter=unmatched">
        <div class="widget-icon"><i class="ri-bank-card-line"></i></div>
        <div class="widget-value"><?= $unmatchedPaymentsCount ?></div>
        <div class="widget-label">Nesparovane platby</div>
    </a>

    <!-- Tento tyden -->
    <a href="/admin/tyden" class="widget" data-href="/admin/tyden">
        <div class="widget-icon"><i class="ri-calendar-line"></i></div>
        <div class="widget-value"><?= $thisWeekCount ?></div>
        <div class="widget-label">Tento tyden</div>
    </a>

    <!-- Expiruje brzy -->
    <a href="/admin/predplatne?filter=expiring" class="widget <?= $expiringSoonCount > 0 ? 'widget--warning' : '' ?>" data-href="/admin/predplatne?filter=expiring">
        <div class="widget-icon"><i class="ri-alarm-warning-line"></i></div>
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
                <div class="stat-label">Aktivnich zakazniku</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= $stats['reminders_total'] ?></div>
                <div class="stat-label">Pripominek celkem</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= number_format($stats['revenue_this_month'], 0, ',', ' ') ?> Kc</div>
                <div class="stat-label">Tento mesic</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= number_format($stats['revenue_total'], 0, ',', ' ') ?> Kc</div>
                <div class="stat-label">Celkem</div>
            </div>
        </div>
    </div>
</div>
