/**
 * Připomněnka - Hlavní JavaScript
 */

(function() {
    'use strict';

    // ========================================
    // Mobile Navigation Toggle
    // ========================================
    const navToggle = document.querySelector('.nav-toggle');
    const nav = document.querySelector('.nav');

    if (navToggle && nav) {
        navToggle.addEventListener('click', function() {
            const isOpen = nav.classList.toggle('is-open');
            navToggle.setAttribute('aria-expanded', isOpen);
        });

        // Close nav when clicking outside
        document.addEventListener('click', function(e) {
            if (!nav.contains(e.target) && !navToggle.contains(e.target)) {
                nav.classList.remove('is-open');
                navToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // ========================================
    // Flash Messages Auto-dismiss
    // ========================================
    const flashMessages = document.querySelectorAll('.flash');

    flashMessages.forEach(function(flash) {
        // Auto dismiss after 5 seconds
        setTimeout(function() {
            dismissFlash(flash);
        }, 5000);

        // Close button
        const closeBtn = flash.querySelector('.flash-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                dismissFlash(flash);
            });
        }
    });

    function dismissFlash(flash) {
        flash.style.opacity = '0';
        flash.style.transform = 'translateX(100%)';
        setTimeout(function() {
            flash.remove();
        }, 300);
    }

    // ========================================
    // Form Validation
    // ========================================
    const forms = document.querySelectorAll('form[data-validate]');

    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');

            requiredFields.forEach(function(field) {
                const error = field.parentNode.querySelector('.form-error');

                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('form-input--error');
                    if (error) error.textContent = 'Toto pole je povinné.';
                } else {
                    field.classList.remove('form-input--error');
                    if (error) error.textContent = '';
                }
            });

            // Email validation
            const emailFields = form.querySelectorAll('input[type="email"]');
            emailFields.forEach(function(field) {
                if (field.value && !isValidEmail(field.value)) {
                    isValid = false;
                    field.classList.add('form-input--error');
                    const error = field.parentNode.querySelector('.form-error');
                    if (error) error.textContent = 'Neplatný formát emailu.';
                }
            });

            // Phone validation
            const phoneFields = form.querySelectorAll('input[type="tel"]');
            phoneFields.forEach(function(field) {
                if (field.value && !isValidPhone(field.value)) {
                    isValid = false;
                    field.classList.add('form-input--error');
                    const error = field.parentNode.querySelector('.form-error');
                    if (error) error.textContent = 'Neplatný formát telefonu.';
                }
            });

            if (!isValid) {
                e.preventDefault();
                // Scroll to first error
                const firstError = form.querySelector('.form-input--error');
                if (firstError) {
                    firstError.focus();
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });

        // Clear error on input
        form.querySelectorAll('.form-input, .form-select, .form-textarea').forEach(function(field) {
            field.addEventListener('input', function() {
                this.classList.remove('form-input--error');
                const error = this.parentNode.querySelector('.form-error');
                if (error) error.textContent = '';
            });
        });
    });

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function isValidPhone(phone) {
        const cleaned = phone.replace(/[\s\-()]/g, '');
        return /^(\+420|00420)?[1-9]\d{8}$/.test(cleaned);
    }

    // ========================================
    // Phone Number Formatting
    // ========================================
    const phoneInputs = document.querySelectorAll('input[type="tel"]');

    phoneInputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            let value = this.value.replace(/[\s\-()]/g, '');

            // Add +420 prefix if missing
            if (value && !/^\+/.test(value)) {
                if (/^00420/.test(value)) {
                    value = '+' + value.substring(2);
                } else if (/^420/.test(value)) {
                    value = '+' + value;
                } else if (/^\d{9}$/.test(value)) {
                    value = '+420' + value;
                }
            }

            // Format: +420 XXX XXX XXX
            if (/^\+420\d{9}$/.test(value)) {
                value = value.replace(/(\+420)(\d{3})(\d{3})(\d{3})/, '$1 $2 $3 $4');
            }

            this.value = value;
        });
    });

    // ========================================
    // Confirm Delete Actions
    // ========================================
    document.querySelectorAll('[data-confirm]').forEach(function(element) {
        element.addEventListener('click', function(e) {
            const message = this.dataset.confirm || 'Opravdu chcete provést tuto akci?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // ========================================
    // Auto-resize Textareas
    // ========================================
    document.querySelectorAll('textarea[data-autoresize]').forEach(function(textarea) {
        function resize() {
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
        }

        textarea.addEventListener('input', resize);
        resize();
    });

    // ========================================
    // Character Counter
    // ========================================
    document.querySelectorAll('[data-maxlength]').forEach(function(field) {
        const max = parseInt(field.dataset.maxlength, 10);
        const counter = document.createElement('span');
        counter.className = 'form-hint';
        counter.style.textAlign = 'right';
        field.parentNode.appendChild(counter);

        function update() {
            const remaining = max - field.value.length;
            counter.textContent = remaining + ' znaků zbývá';
            counter.style.color = remaining < 20 ? 'var(--color-warning)' : '';
        }

        field.addEventListener('input', update);
        update();
    });

})();
