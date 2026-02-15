/**
 * Připomněnka - Admin JavaScript
 */

(function() {
    'use strict';

    // ========================================
    // Admin Mobile Nav Toggle
    // ========================================
    var adminNavToggle = document.querySelector('.admin-nav-toggle');
    var adminNav = document.getElementById('admin-nav');

    if (adminNavToggle && adminNav) {
        adminNavToggle.addEventListener('click', function() {
            var isOpen = adminNav.classList.toggle('is-open');
            adminNavToggle.setAttribute('aria-expanded', isOpen);
        });

        // Close nav when clicking outside
        document.addEventListener('click', function(e) {
            if (adminNav.classList.contains('is-open') &&
                !adminNav.contains(e.target) &&
                !adminNavToggle.contains(e.target)) {
                adminNav.classList.remove('is-open');
                adminNavToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // ========================================
    // Modal Functionality
    // ========================================
    const modalOverlays = document.querySelectorAll('.modal-overlay');

    var lastFocusedElement = null;

    // Open modal with ARIA and focus trap
    document.querySelectorAll('[data-modal-open]').forEach(function(trigger) {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            var modalId = this.dataset.modalOpen;
            var modal = document.getElementById(modalId);
            if (modal) {
                openModal(modal);
            }
        });
    });

    function openModal(overlay) {
        lastFocusedElement = document.activeElement;
        overlay.classList.add('is-open');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        // Focus first focusable element
        var modal = overlay.querySelector('.modal');
        if (modal) {
            var focusable = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            if (focusable.length) {
                focusable[0].focus();
            }
        }
    }

    // Close modal
    modalOverlays.forEach(function(overlay) {
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.setAttribute('aria-hidden', 'true');

        // Close on overlay click
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                closeModal(overlay);
            }
        });

        // Close button
        var closeBtn = overlay.querySelector('.modal-close');
        if (closeBtn) {
            closeBtn.setAttribute('aria-label', 'Zavřít');
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

        // Focus trap
        overlay.addEventListener('keydown', function(e) {
            if (e.key !== 'Tab') return;

            var modal = overlay.querySelector('.modal');
            if (!modal) return;

            var focusable = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            if (!focusable.length) return;

            var first = focusable[0];
            var last = focusable[focusable.length - 1];

            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        });
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            var openModal = document.querySelector('.modal-overlay.is-open');
            if (openModal) {
                closeModal(openModal);
            }
        }
    });

    function closeModal(overlay) {
        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';

        // Restore focus
        if (lastFocusedElement) {
            lastFocusedElement.focus();
            lastFocusedElement = null;
        }
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
    // Swipe Gestures for Call Cards (mobile)
    // ========================================
    document.querySelectorAll('.call-card[data-swipe]').forEach(function(card) {
        var startX = 0;
        var startY = 0;
        var threshold = 80;

        card.addEventListener('touchstart', function(e) {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
        }, { passive: true });

        card.addEventListener('touchmove', function(e) {
            var deltaX = e.touches[0].clientX - startX;
            var deltaY = e.touches[0].clientY - startY;

            // Only horizontal swipes
            if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 20) {
                card.style.transform = 'translateX(' + deltaX + 'px)';
                card.style.opacity = Math.max(0.3, 1 - Math.abs(deltaX) / 300);
            }
        }, { passive: true });

        card.addEventListener('touchend', function(e) {
            var deltaX = e.changedTouches[0].clientX - startX;

            card.style.transition = 'all 0.3s ease';

            if (deltaX > threshold) {
                // Swipe right = Vyřízeno
                var completedBtn = card.querySelector('[data-action="completed"]');
                if (completedBtn) completedBtn.click();
            } else if (deltaX < -threshold) {
                // Swipe left = Nezvedá
                var noAnswerBtn = card.querySelector('[data-action="no_answer"]');
                if (noAnswerBtn) noAnswerBtn.click();
            } else {
                card.style.transform = '';
                card.style.opacity = '';
            }

            setTimeout(function() {
                card.style.transition = '';
            }, 300);
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
