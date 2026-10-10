// Donation page: details -> save donor -> gateway checkout (Razorpay or Cashfree) -> verify on server -> thank-you page.
// Only the first name and an amount are required; optional fields are checked only when filled in.
(function () {
    'use strict';

    var form = document.getElementById('donateForm');
    if (!form) return;

    var BASE = (document.querySelector('meta[name="base-url"]') || { content: '' }).content;
    var CSRF = (document.querySelector('meta[name="csrf-token"]') || { content: '' }).content;
    var COST_PER_CHILD_YEAR = parseInt(form.getAttribute('data-cost') || '60000', 10);
    var MIN = parseInt(form.getAttribute('data-min') || '1', 10);
    var MAX = parseInt(form.getAttribute('data-max') || '1000000', 10);
    var towards = window.DONATE_TOWARDS || {};

    var amountField = form.querySelector('[name="amount"]');      // hidden: what is sent to the server
    var customInput = document.getElementById('d-amount');         // visible only for "Custom Amount"
    var customWrap = document.getElementById('customAmountWrap');
    var cards = form.querySelectorAll('.amount-card');
    var label = form.querySelector('.donate-submit-label');
    var button = form.querySelector('.donate-submit');
    var errorBox = form.querySelector('.donate-error');
    var towardsSelect = form.querySelector('[name="towards"]');
    var preview = document.getElementById('projectPreview');
    var sum = {
        amount: document.getElementById('sumAmount'),
        project: document.getElementById('sumProject'),
        name: document.getElementById('sumName'),
        impact: document.getElementById('sumImpact')
    };

    function inr(n) { return '₹' + Number(n).toLocaleString('en-IN'); }
    // Whole rupees only; anything else (empty, 0, negative, decimals, text) counts as no amount.
    function amount() { return /^\d+$/.test(amountField.value) ? parseInt(amountField.value, 10) : 0; }

    function post(path, data) {
        var body = new URLSearchParams(data);
        body.append('_token', CSRF);
        return fetch(BASE + path, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'fetch' },
            body: body,
            credentials: 'same-origin'
        }).then(function (r) {
            return r.json().catch(function () { return { ok: false, error: 'Unexpected response from the server. Please try again.' }; });
        });
    }

    // ------------------------------------------------ live summary
    function refresh() {
        var a = amount();
        label.textContent = a > 0 ? 'Donate ' + inr(a) : 'Donate';
        if (sum.amount) sum.amount.textContent = inr(a);

        var name = [form.first_name.value.trim(), form.last_name.value.trim()].join(' ').trim();
        if (sum.name) sum.name.textContent = name || '—';

        var t = towards[towardsSelect.value];
        if (sum.project) sum.project.textContent = t ? t.title : 'General Donation';
        if (preview) {
            preview.hidden = !(t && (t.summary || t.image));
            if (t) {
                var img = preview.querySelector('img');
                img.hidden = !t.image;
                if (t.image) img.src = t.image;
                preview.querySelector('.preview-icon').hidden = !!t.image;
                preview.querySelector('strong').textContent = t.title;
                preview.querySelector('p').textContent = t.summary || '';
            }
        }

        if (sum.impact) {
            var days = Math.round(a / (COST_PER_CHILD_YEAR / 365));
            var text = '';
            if (days >= 365) text = 'Covers about <b>' + (Math.round(days / 36.5) / 10) + ' year' + (days >= 730 ? 's' : '') + '</b> of food, shelter and education for one child.';
            else if (days >= 30) text = 'Covers about <b>' + Math.round(days / 30) + ' month' + (days >= 60 ? 's' : '') + '</b> of care for one child.';
            else if (days >= 1) text = 'Covers about <b>' + days + ' day' + (days > 1 ? 's' : '') + '</b> of care for one child.';
            sum.impact.innerHTML = text ? '<i class="bi bi-stars"></i> ' + text : '';
        }
    }

    // ------------------------------------------------ amount cards
    function selectCard(card) {
        cards.forEach(function (c) {
            var on = c === card;
            c.classList.toggle('is-selected', on);
            c.setAttribute('aria-checked', on ? 'true' : 'false');
        });
        var custom = card.dataset.amount === 'custom';
        customWrap.hidden = !custom;
        if (custom) {
            amountField.value = customInput.value.trim();
            customInput.focus();
        } else {
            amountField.value = card.dataset.amount;
        }
        clearFieldError('amount');
        refresh();
    }

    cards.forEach(function (card) {
        card.addEventListener('click', function () { selectCard(card); });
    });
    customInput.addEventListener('input', function () {
        amountField.value = customInput.value.trim();
        clearFieldError('amount');
        refresh();
    });

    form.addEventListener('input', function (e) {
        if (e.target.name) clearFieldError(e.target.name);
        refresh();
    });
    towardsSelect.addEventListener('change', refresh);
    form.phone.addEventListener('input', function () { this.value = this.value.replace(/\D/g, '').slice(0, 10); });
    refresh();

    // ------------------------------------------------ validation messages
    function fieldFor(name) {
        return name === 'amount' ? (customWrap.hidden ? form.querySelector('.amount-cards') : customInput) : form.querySelector('[name="' + name + '"]');
    }

    function setFieldError(name, msg) {
        var box = form.querySelector('[data-error-for="' + name + '"]');
        if (box) box.textContent = msg;
        var field = fieldFor(name);
        if (field) field.classList.add('is-invalid');
    }

    function clearFieldError(name) {
        var box = form.querySelector('[data-error-for="' + name + '"]');
        if (box) box.textContent = '';
        var field = fieldFor(name);
        if (field) field.classList.remove('is-invalid');
    }

    function showErrors(errors) {
        var first = null;
        Object.keys(errors).forEach(function (name) {
            setFieldError(name, errors[name]);
            first = first || fieldFor(name);
        });
        if (first) {
            first.scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (first.focus) first.focus({ preventScroll: true });
        }
    }

    function showError(msg) {
        errorBox.textContent = msg;
        errorBox.classList.remove('d-none');
        errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function busy(on, text) {
        button.disabled = on;
        if (on) label.textContent = text || 'Please wait…';
        else refresh();
    }

    function validate(data) {
        var errors = {};
        var a = amount();
        if (!data.first_name.trim()) errors.first_name = 'Please enter your first name.';
        if (data.phone && !/^[6-9]\d{9}$/.test(data.phone)) errors.phone = 'Please enter a valid 10-digit mobile number, or leave it empty.';
        if (data.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email.trim())) errors.email = 'Please enter a valid email address, or leave it empty.';
        if (data.pan && !/^[A-Za-z]{5}[0-9]{4}[A-Za-z]$/.test(data.pan.trim())) errors.pan = 'PAN should look like ABCDE1234F, or leave it empty.';
        if (!amountField.value.trim()) errors.amount = 'Please choose an amount, or enter your own.';
        else if (!/^\d+$/.test(amountField.value.trim())) errors.amount = 'Please enter the amount in whole rupees, using numbers only.';
        else if (a < MIN) errors.amount = 'The minimum donation is ' + inr(MIN) + '.';
        else if (a > MAX) errors.amount = 'For donations above ' + inr(MAX) + ', please contact us or use a bank transfer.';
        return errors;
    }

    // ------------------------------------------------ submit
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        errorBox.classList.add('d-none');

        var data = Object.fromEntries(new FormData(form).entries());
        var errors = validate(data);
        if (Object.keys(errors).length) return showErrors(errors);

        busy(true, 'Saving your details…');
        post('/donate/order', data).then(function (res) {
            if (!res.ok) {
                busy(false);
                if (res.saved && window.swal) return swal('Details saved', res.error, 'info');
                if (res.errors) return showErrors(res.errors);
                return showError(res.error || 'Something went wrong. Please try again.');
            }
            if (res.gateway === 'cashfree') openCashfree(res);
            else openRazorpay(res);
        }).catch(function () {
            busy(false);
            showError('Could not reach the server. Please check your internet connection and try again.');
        });
    });

    function goThanks(ref, check) {
        window.location.href = BASE + '/donate/thank-you?ref=' + encodeURIComponent(ref) + (check ? '&check=1' : '');
    }

    var LOAD_ERROR = 'The payment window could not load. Please check your connection or turn off any ad-blocker, then try again.';

    function openRazorpay(order) {
        if (typeof Razorpay === 'undefined') {
            busy(false);
            return showError(LOAD_ERROR);
        }
        busy(true, 'Waiting for payment…');
        var rzp = new Razorpay({
            key: order.key,
            order_id: order.order_id,
            amount: order.amount,
            currency: 'INR',
            name: order.org_name,
            description: order.description,
            image: order.logo,
            prefill: order.prefill,
            notes: { donation_ref: order.ref },
            theme: { color: '#ff6b00' },
            handler: function (resp) {
                busy(true, 'Confirming your payment…');
                post('/donate/verify', {
                    ref: order.ref,
                    razorpay_order_id: resp.razorpay_order_id,
                    razorpay_payment_id: resp.razorpay_payment_id,
                    razorpay_signature: resp.razorpay_signature
                }).then(function (v) { goThanks(order.ref, !v.ok); })
                  .catch(function () { goThanks(order.ref, true); });
            },
            modal: {
                ondismiss: function () {
                    busy(false);
                    if (paymentFailed) {
                        // Already recorded as failed (e.g. bank declined) in the handler below.
                        return showError('The payment did not go through and no money was taken. Your details are saved — please try again.');
                    }
                    post('/donate/failed', { ref: order.ref, type: 'cancelled' });
                    showError('Payment cancelled — no money was taken. Your details are saved; you can try again whenever you are ready.');
                }
            }
        });
        var paymentFailed = false;
        rzp.on('payment.failed', function (resp) {
            paymentFailed = true;
            post('/donate/failed', {
                ref: order.ref,
                reason: (resp.error && (resp.error.description || resp.error.reason)) || 'Payment failed'
            });
        });
        rzp.open();
    }

    function openCashfree(order) {
        if (typeof Cashfree === 'undefined') {
            busy(false);
            return showError(LOAD_ERROR);
        }
        busy(true, 'Waiting for payment…');
        var cashfree = Cashfree({ mode: order.mode });
        cashfree.checkout({ paymentSessionId: order.payment_session_id, redirectTarget: '_modal' }).then(function (result) {
            if (result && result.redirect) return; // Cashfree is taking the donor to their bank's page
            // Whatever the popup says, the server asks Cashfree for the real status.
            busy(true, 'Confirming your payment…');
            post('/donate/verify', { ref: order.ref }).then(function (v) {
                if (v.ok || (result && result.paymentDetails)) return goThanks(order.ref, !v.ok);
                busy(false);
                showError(v.status === 'failed'
                    ? 'The payment did not go through and no money was taken. Your details are saved — please try again.'
                    : 'Payment cancelled — no money was taken. Your details are saved; you can try again whenever you are ready.');
            }).catch(function () { goThanks(order.ref, true); });
        });
    }
})();
