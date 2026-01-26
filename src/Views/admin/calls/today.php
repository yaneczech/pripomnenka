<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-xl);">
    <div>
        <h1 style="margin: 0;">Dnes volat</h1>
        <p class="text-muted" style="margin: var(--spacing-xs) 0 0;">
            <?= $date->format('j. n. Y') ?> · <?= count($calls) ?> zákazníků
        </p>
    </div>
    <a href="/admin/tyden" class="btn btn--outline">Zobrazit týden</a>
</div>

<?php if (empty($calls)): ?>
    <div class="empty-state">
        <div class="empty-state-icon">🎉</div>
        <h2 class="empty-state-title">Dnes nikoho nevoláte!</h2>
        <p class="empty-state-text">Užijte si klid. Žádní zákazníci k provolání.</p>
        <a href="/admin/tyden" class="btn btn--outline">Zobrazit tento týden</a>
    </div>
<?php else: ?>
    <div style="display: flex; flex-direction: column; gap: var(--spacing-md);">
        <?php foreach ($calls as $call): ?>
            <div class="call-card <?= $call['attempt_count'] >= 3 ? 'call-card--urgent' : '' ?>">
                <!-- Telefon -->
                <a href="tel:<?= e($call['phone']) ?>" class="call-card-phone">
                    <?= e($call['phone']) ?>
                </a>

                <!-- Jméno -->
                <div class="call-card-name <?= empty($call['customer_name']) ? 'call-card-name--unknown' : '' ?>">
                    <?= e($call['customer_name'] ?: 'Neznámé jméno') ?>
                </div>

                <!-- Událost -->
                <div class="call-card-event">
                    <span><?= translate_event_type($call['event_type']) ?></span>
                    <span>—</span>
                    <span><?= translate_relation($call['recipient_relation']) ?></span>
                    <span>·</span>
                    <span><?= format_date_long($call['event_day'], $call['event_month']) ?></span>
                </div>

                <!-- Cenový rozsah -->
                <div class="text-small text-muted mb-2">
                    💰 <?= translate_price_range($call['price_range']) ?>
                </div>

                <!-- Poznámka zákazníka -->
                <?php if ($call['customer_note']): ?>
                    <div class="call-card-note">
                        💬 "<?= e($call['customer_note']) ?>"
                    </div>
                <?php endif; ?>

                <!-- Meta informace -->
                <div class="call-card-meta">
                    <?php if ($call['last_order_amount']): ?>
                        <span>📊 Minule: <?= number_format($call['last_order_amount'], 0, ',', ' ') ?> Kč (<?= format_date($call['last_order_date']) ?>)</span>
                    <?php endif; ?>
                    <span>
                        <?php if ($call['attempt_count'] > 1): ?>
                            ⚠️ <?= $call['attempt_count'] ?>. pokus
                        <?php else: ?>
                            1. pokus
                        <?php endif; ?>
                    </span>
                    <?php if ($call['preferred_call_time'] && $call['preferred_call_time'] !== 'anytime'): ?>
                        <span>
                            🕐 Volat <?= match($call['preferred_call_time']) {
                                'morning' => 'ráno',
                                'afternoon' => 'odpoledne',
                                'evening' => 'večer',
                                default => '',
                            } ?>
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Interní poznámka -->
                <?php if ($call['internal_note']): ?>
                    <div class="text-small text-muted mb-2">
                        📝 <?= e($call['internal_note']) ?>
                    </div>
                <?php endif; ?>

                <!-- Akční tlačítka -->
                <form action="/admin/volani/<?= $call['queue_id'] ?>" method="post" id="call-action-form-<?= $call['queue_id'] ?>">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="action" value="">

                    <div class="call-card-actions">
                        <button type="button" class="btn btn--primary call-action-btn" data-action="completed" data-call-id="<?= $call['queue_id'] ?>" data-modal-open="modal-completed-<?= $call['queue_id'] ?>">
                            ✅ Vyřízeno
                        </button>
                        <button type="submit" class="btn btn--outline call-action-btn" data-action="no_answer" data-call-id="<?= $call['queue_id'] ?>">
                            📞 Nezvedá
                        </button>
                        <button type="submit" class="btn btn--outline call-action-btn" data-action="declined" data-call-id="<?= $call['queue_id'] ?>">
                            ❌ Nechce
                        </button>
                        <button type="button" class="btn btn--outline call-action-btn" data-action="postponed" data-call-id="<?= $call['queue_id'] ?>" data-modal-open="modal-postponed-<?= $call['queue_id'] ?>">
                            ⏰ Jindy
                        </button>
                    </div>
                </form>

                <!-- Modal: Vyřízeno -->
                <div class="modal-overlay" id="modal-completed-<?= $call['queue_id'] ?>">
                    <div class="modal">
                        <div class="modal-header">
                            <h3 class="modal-title">Zaznamenat objednávku</h3>
                            <button class="modal-close" data-modal-close>&times;</button>
                        </div>
                        <form action="/admin/volani/<?= $call['queue_id'] ?>" method="post">
                            <?= CSRF::field() ?>
                            <input type="hidden" name="action" value="completed">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="order_amount_<?= $call['queue_id'] ?>" class="form-label">Částka objednávky (volitelné)</label>
                                    <input type="number" id="order_amount_<?= $call['queue_id'] ?>" name="order_amount" class="form-input" placeholder="Kč" step="1" min="0">
                                </div>
                                <div class="form-group">
                                    <label for="note_<?= $call['queue_id'] ?>" class="form-label">Poznámka (volitelné)</label>
                                    <textarea id="note_<?= $call['queue_id'] ?>" name="note" class="form-textarea" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn--ghost" data-modal-close>Zrušit</button>
                                <button type="submit" class="btn btn--primary">Uložit</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal: Odložit -->
                <div class="modal-overlay" id="modal-postponed-<?= $call['queue_id'] ?>">
                    <div class="modal">
                        <div class="modal-header">
                            <h3 class="modal-title">Odložit na jindy</h3>
                            <button class="modal-close" data-modal-close>&times;</button>
                        </div>
                        <form action="/admin/volani/<?= $call['queue_id'] ?>" method="post">
                            <?= CSRF::field() ?>
                            <input type="hidden" name="action" value="postponed">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="postponed_to_<?= $call['queue_id'] ?>" class="form-label">Nové datum</label>
                                    <input type="date" id="postponed_to_<?= $call['queue_id'] ?>" name="postponed_to" class="form-input" required
                                           min="<?= date('Y-m-d', strtotime('tomorrow')) ?>">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn--ghost" data-modal-close>Zrušit</button>
                                <button type="submit" class="btn btn--primary">Odložit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
