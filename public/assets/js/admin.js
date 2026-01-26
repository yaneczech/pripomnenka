/**
 * Připomněnka - Admin JavaScript
 */

(function() {
    'use strict';

    // ========================================
    // Modal Functionality
    // ========================================
    const modalOverlays = document.querySelectorAll('.modal-overlay');

    // Open modal
    document.querySelectorAll('[data-modal-open]').forEach(function(trigger) {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const modalId = this.dataset.modalOpen;
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('is-open');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    // Close modal
    modalOverlays.forEach(function(overlay) {
        // Close on overlay click
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                closeModal(overlay);
            }
        });

        // Close button
        const closeBtn = overlay.querySelector('.modal-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                closeModal(overlay);
            });
        }

        // Close on data-modal-close
        overlay.querySelectorAll('[data-modal-close]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                closeModal(overlay);
            });
        });
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal-overlay.is-open');
            if (openModal) {
                closeModal(openModal);
            }
        }
    });

    function closeModal(overlay) {
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    // ========================================
    // Call Log Quick Actions
    // ========================================
    document.querySelectorAll('.call-action-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();

            const action = this.dataset.action;
            const callId = this.dataset.callId;
            const form = document.getElementById('call-action-form-' + callId);

            if (!form) return;

            // Set action value
            const actionInput = form.querySelector('input[name="action"]');
            if (actionInput) {
                actionInput.value = action;
            }

            // For "completed" action, show modal for order amount
            if (action === 'completed') {
                const modal = document.getElementById('modal-completed-' + callId);
                if (modal) {
                    modal.classList.add('is-open');
                    document.body.style.overflow = 'hidden';
                    return;
                }
            }

            // For "postponed" action, show date picker modal
            if (action === 'postponed') {
                const modal = document.getElementById('modal-postponed-' + callId);
                if (modal) {
                    modal.classList.add('is-open');
                    document.body.style.overflow = 'hidden';
                    return;
                }
            }

            // Submit form for other actions
            form.submit();
        });
    });

    // ========================================
    // Customer Search
    // ========================================
    const searchInput = document.getElementById('customer-search');
    const customerList = document.querySelector('.customer-list');

    if (searchInput && customerList) {
        let debounceTimer;

        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value.trim();

            if (query.length < 2) {
                // Show all customers
                customerList.querySelectorAll('.customer-row').forEach(function(row) {
                    row.style.display = '';
                });
                return;
            }

            debounceTimer = setTimeout(function() {
                const queryLower = query.toLowerCase();

                customerList.querySelectorAll('.customer-row').forEach(function(row) {
                    const name = (row.dataset.name || '').toLowerCase();
                    const phone = (row.dataset.phone || '').toLowerCase();
                    const email = (row.dataset.email || '').toLowerCase();

                    if (name.includes(queryLower) || phone.includes(queryLower) || email.includes(queryLower)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }, 300);
        });
    }

    // ========================================
    // Quick Customer Form
    // ========================================
    const quickCustomerForm = document.getElementById('quick-customer-form');

    if (quickCustomerForm) {
        // Payment method toggle
        const paymentMethodInputs = quickCustomerForm.querySelectorAll('input[name="payment_method"]');

        paymentMethodInputs.forEach(function(input) {
            input.addEventListener('change', function() {
                const bankTransferInfo = quickCustomerForm.querySelector('.bank-transfer-info');
                if (bankTransferInfo) {
                    bankTransferInfo.style.display = this.value === 'bank_transfer' ? 'block' : 'none';
                }
            });
        });

        // Keyboard shortcuts
        quickCustomerForm.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + Enter to submit
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                quickCustomerForm.submit();
            }

            // Escape to close (if in modal)
            if (e.key === 'Escape') {
                const modal = quickCustomerForm.closest('.modal-overlay');
                if (modal) {
                    closeModal(modal);
                }
            }
        });
    }

    // ========================================
    // Unmatched Payments Assignment
    // ========================================
    document.querySelectorAll('.match-payment-form').forEach(function(form) {
        const customerSelect = form.querySelector('select[name="customer_id"]');
        const submitBtn = form.querySelector('button[type="submit"]');

        if (customerSelect && submitBtn) {
            customerSelect.addEventListener('change', function() {
                submitBtn.disabled = !this.value;
            });
        }
    });

    // ========================================
    // Settings Form Auto-save
    // ========================================
    const settingsForm = document.getElementById('settings-form');

    if (settingsForm && settingsForm.dataset.autosave) {
        let saveTimer;
        const saveIndicator = document.createElement('span');
        saveIndicator.className = 'text-muted text-small';
        saveIndicator.style.marginLeft = '1rem';
        settingsForm.querySelector('h1, .form-title')?.appendChild(saveIndicator);

        settingsForm.querySelectorAll('input, select, textarea').forEach(function(field) {
            field.addEventListener('change', function() {
                saveIndicator.textContent = 'Ukládám...';

                clearTimeout(saveTimer);
                saveTimer = setTimeout(function() {
                    // Auto-save via AJAX
                    const formData = new FormData(settingsForm);

                    fetch(settingsForm.action, {
                        method: 'POST',
                        body: formData
                    })
                    .then(function(response) {
                        if (response.ok) {
                            saveIndicator.textContent = 'Uloženo';
                            setTimeout(function() {
                                saveIndicator.textContent = '';
                            }, 2000);
                        } else {
                            saveIndicator.textContent = 'Chyba při ukládání';
                            saveIndicator.style.color = 'var(--color-error)';
                        }
                    })
                    .catch(function() {
                        saveIndicator.textContent = 'Chyba při ukládání';
                        saveIndicator.style.color = 'var(--color-error)';
                    });
                }, 500);
            });
        });
    }

    // ========================================
    // Dashboard Widget Click
    // ========================================
    document.querySelectorAll('.widget[data-href]').forEach(function(widget) {
        widget.style.cursor = 'pointer';
        widget.addEventListener('click', function() {
            window.location.href = this.dataset.href;
        });
    });

    // ========================================
    // Subscription Plan Editor
    // ========================================
    const planEditor = document.getElementById('plan-editor');

    if (planEditor) {
        // Add new plan
        const addPlanBtn = planEditor.querySelector('.add-plan-btn');
        const planTemplate = planEditor.querySelector('#plan-template');

        if (addPlanBtn && planTemplate) {
            addPlanBtn.addEventListener('click', function() {
                const newPlan = planTemplate.content.cloneNode(true);
                const planList = planEditor.querySelector('.plan-list');

                // Set unique index
                const index = planList.children.length;
                newPlan.querySelectorAll('[name]').forEach(function(input) {
                    input.name = input.name.replace('[0]', '[' + index + ']');
                });

                planList.appendChild(newPlan);
            });
        }

        // Remove plan
        planEditor.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-plan-btn')) {
                e.preventDefault();
                const planItem = e.target.closest('.plan-item');
                if (planItem && confirm('Opravdu chcete odstranit tuto variantu?')) {
                    planItem.remove();
                }
            }
        });
    }

})();
