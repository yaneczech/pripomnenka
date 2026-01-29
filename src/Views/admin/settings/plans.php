<div class="page-header">
    <h1>Tarify předplatného</h1>
</div>

<div class="settings-nav">
    <a href="/admin/nastaveni" class="settings-nav-item">
        <i class="ri-settings-3-line"></i> Obecné
    </a>
    <a href="/admin/nastaveni/plany" class="settings-nav-item settings-nav-item--active">
        <i class="ri-price-tag-3-line"></i> Tarify
    </a>
    <a href="/admin/nastaveni/emaily" class="settings-nav-item">
        <i class="ri-mail-line"></i> Náhled emailů
    </a>
</div>

<!-- Seznam tarifů -->
<div class="card mb-3">
    <div class="card-header">
        <h2 class="card-title">Aktuální tarify</h2>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Název</th>
                    <th>Cena</th>
                    <th>Připomínek</th>
                    <th>Sleva</th>
                    <th>Stav</th>
                    <th>Akce</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($plans as $plan): ?>
                    <tr>
                        <td>
                            <strong><?= e($plan['name']) ?></strong>
                            <?php if ($plan['is_default']): ?>
                                <span class="badge badge--primary">Výchozí</span>
                            <?php endif; ?>
                            <?php if ($plan['description']): ?>
                                <div class="text-small text-muted"><?= e($plan['description']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= number_format($plan['price'], 0, ',', ' ') ?> Kč/rok</td>
                        <td><?= $plan['reminder_limit'] ?></td>
                        <td><?= $plan['discount_percent'] ?>%</td>
                        <td>
                            <?php if ($plan['is_available']): ?>
                                <span class="badge badge--success">Aktivní</span>
                            <?php else: ?>
                                <span class="badge badge--muted">Neaktivní</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn--small btn--ghost" onclick="editPlan(<?= htmlspecialchars(json_encode($plan)) ?>)">
                                    <i class="ri-edit-line"></i>
                                </button>
                                <form action="/admin/nastaveni/plany" method="post" style="display: inline;">
                                    <?= \CSRF::field() ?>
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                                    <button type="submit" class="btn btn--small btn--ghost" title="<?= $plan['is_available'] ? 'Deaktivovat' : 'Aktivovat' ?>">
                                        <i class="ri-<?= $plan['is_available'] ? 'eye-off-line' : 'eye-line' ?>"></i>
                                    </button>
                                </form>
                                <?php if (!$plan['is_default']): ?>
                                    <form action="/admin/nastaveni/plany" method="post" style="display: inline;">
                                        <?= \CSRF::field() ?>
                                        <input type="hidden" name="action" value="set_default">
                                        <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                                        <button type="submit" class="btn btn--small btn--ghost" title="Nastavit jako výchozí">
                                            <i class="ri-star-line"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Přidat nový tarif -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><i class="ri-add-line"></i> Přidat nový tarif</h2>
    </div>
    <div class="card-body">
        <form action="/admin/nastaveni/plany" method="post">
            <?= \CSRF::field() ?>
            <input type="hidden" name="action" value="add">

            <div class="form-row form-row--2">
                <div class="form-group">
                    <label for="name" class="form-label form-label--required">Název tarifu</label>
                    <input type="text" id="name" name="name" class="form-input" required placeholder="Např. Premium">
                </div>
                <div class="form-group">
                    <label for="price" class="form-label form-label--required">Cena (Kč/rok)</label>
                    <input type="number" id="price" name="price" class="form-input" required min="1" step="1">
                </div>
            </div>

            <div class="form-row form-row--2">
                <div class="form-group">
                    <label for="reminder_limit" class="form-label">Limit připomínek</label>
                    <input type="number" id="reminder_limit" name="reminder_limit" class="form-input" value="5" min="1" max="100">
                </div>
                <div class="form-group">
                    <label for="discount_percent" class="form-label">Sleva na kytice (%)</label>
                    <input type="number" id="discount_percent" name="discount_percent" class="form-input" value="10" min="0" max="100">
                </div>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Popis</label>
                <textarea id="description" name="description" class="form-textarea" rows="2" placeholder="Krátký popis tarifu pro zákazníky..."></textarea>
            </div>

            <button type="submit" class="btn btn--primary">
                <i class="ri-add-line"></i> Přidat tarif
            </button>
        </form>
    </div>
</div>

<!-- Modal pro editaci -->
<div id="editPlanModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="closeModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Upravit tarif</h3>
            <button type="button" class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="editPlanForm" action="/admin/nastaveni/plany" method="post">
            <?= \CSRF::field() ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="plan_id" id="edit_plan_id">

            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_name" class="form-label">Název</label>
                    <input type="text" id="edit_name" name="name" class="form-input" required>
                </div>
                <div class="form-row form-row--2">
                    <div class="form-group">
                        <label for="edit_price" class="form-label">Cena</label>
                        <input type="number" id="edit_price" name="price" class="form-input" required min="1">
                    </div>
                    <div class="form-group">
                        <label for="edit_reminder_limit" class="form-label">Limit připomínek</label>
                        <input type="number" id="edit_reminder_limit" name="reminder_limit" class="form-input" min="1">
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_discount_percent" class="form-label">Sleva (%)</label>
                    <input type="number" id="edit_discount_percent" name="discount_percent" class="form-input" min="0" max="100">
                </div>
                <div class="form-group">
                    <label for="edit_description" class="form-label">Popis</label>
                    <textarea id="edit_description" name="description" class="form-textarea" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--ghost" onclick="closeModal()">Zrušit</button>
                <button type="submit" class="btn btn--primary">Uložit</button>
            </div>
        </form>
    </div>
</div>

<script>
function editPlan(plan) {
    document.getElementById('edit_plan_id').value = plan.id;
    document.getElementById('edit_name').value = plan.name;
    document.getElementById('edit_price').value = plan.price;
    document.getElementById('edit_reminder_limit').value = plan.reminder_limit;
    document.getElementById('edit_discount_percent').value = plan.discount_percent;
    document.getElementById('edit_description').value = plan.description || '';
    document.getElementById('editPlanModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('editPlanModal').style.display = 'none';
}
</script>
