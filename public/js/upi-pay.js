// Admin settlement: UPI QR pay modal.
// QR is valid for 5 minutes. "I've completed the payment" posts via AJAX; the success
// animation only plays after the server confirms the payment was recorded.
(function ($) {
    var QR_VALID_SECONDS = 5 * 60;
    var $modal = $('#payModal');
    if (!$modal.length) return;

    var form = document.getElementById('payForm');
    var qrEl = document.getElementById('qrCode');
    var ring = document.getElementById('qrRing');
    var amountInput = document.getElementById('payAmount');
    var state = { name: '', upi: '', pending: 0, timer: null, expiresAt: 0, recorded: false };
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function money(n) {
        return '₹' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function validAmount() {
        var v = parseFloat(amountInput.value);
        return !isNaN(v) && v > 0 && v <= state.pending + 0.0001;
    }

    function showError(msg) {
        $('#payError').text(msg || '').prop('hidden', !msg);
    }

    function upiQuery(amount) {
        return 'pa=' + encodeURIComponent(state.upi) +
            '&pn=' + encodeURIComponent(state.name) +
            '&am=' + Number(amount).toFixed(2) +
            '&cu=INR' +
            '&tr=RD' + Date.now() +
            '&tn=' + encodeURIComponent('Expense settlement - ' + state.name);
    }

    /* ---------- "Pay from this phone" app buttons ----------
       iOS has no app chooser for upi:// — it opens whichever app registered the scheme
       (often WhatsApp). So on iPhone we link straight to each app's own scheme.
       Android shows a chooser for upi://, so it also gets an "Any UPI app" button. */
    var isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) ||
        (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    var UPI_APPS = [
        { name: 'GPay', scheme: 'gpay://upi/pay?', color: '#1a73e8', icon: 'fab fa-google' },
        { name: 'PhonePe', scheme: 'phonepe://pay?', color: '#5f259f', icon: 'fas fa-mobile-alt' },
        { name: 'Paytm', scheme: 'paytmmp://pay?', color: '#00b9f1', icon: 'fas fa-wallet' },
        { name: 'BHIM', scheme: 'bhim://upi/pay?', color: '#f47b20', icon: 'fas fa-university' }
    ];

    function renderApps(query) {
        var grid = document.getElementById('upiAppsGrid');
        if (!grid) return;
        if (!query) { grid.innerHTML = ''; $('#upiApps').addClass('is-disabled'); return; }
        $('#upiApps').removeClass('is-disabled');
        var apps = isIOS ? UPI_APPS : [{ name: 'Any UPI app', scheme: 'upi://pay?', color: '#4f46e5', icon: 'fas fa-th' }].concat(UPI_APPS);
        grid.innerHTML = apps.map(function (a) {
            return '<a class="upi-app" href="' + a.scheme + query + '" style="--app:' + a.color + '">' +
                '<span class="upi-app-icon"><i class="' + a.icon + '"></i></span><span>' + a.name + '</span></a>';
        }).join('');
        $('#upiAppsNote').text(isIOS
            ? 'If nothing opens, that app isn\'t installed. Try another, or scan the QR from a different phone.'
            : 'Choose an app, or tap "Any UPI app" to pick from your installed apps.');
    }

    /* ---------- QR + 5 minute countdown ---------- */
    function stopTimer() {
        if (state.timer) clearInterval(state.timer);
        state.timer = null;
    }

    function expire() {
        stopTimer();
        qrEl.innerHTML = '';
        $('#qrFrame').addClass('is-expired');
        $('#qrExpired').prop('hidden', false);
        $('#qrTimer').text('0:00');
        renderApps(null);
    }

    function tick() {
        var left = Math.max(0, Math.round((state.expiresAt - Date.now()) / 1000));
        $('#qrTimer').text(Math.floor(left / 60) + ':' + String(left % 60).padStart(2, '0'));
        ring.style.strokeDashoffset = String(100 - (left / QR_VALID_SECONDS) * 100);
        $('#qrFrame').toggleClass('is-low', left <= 60);
        if (left <= 0) expire();
    }

    function generate() {
        stopTimer();
        qrEl.innerHTML = '';
        $('#qrFrame').removeClass('is-expired is-low');
        $('#qrExpired').prop('hidden', true);
        ring.style.strokeDashoffset = '0';

        if (!state.upi) return;
        if (!validAmount()) {
            qrEl.innerHTML = '<div class="qr-placeholder">Enter an amount up to ' + money(state.pending) + '</div>';
            renderApps(null);
            return;
        }
        if (typeof QRCode === 'undefined') {
            qrEl.innerHTML = '<div class="qr-placeholder">QR library failed to load. Check your internet connection.</div>';
            return;
        }

        var query = upiQuery(amountInput.value);
        new QRCode(qrEl, { text: 'upi://pay?' + query, width: 200, height: 200, colorDark: '#0f172a', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.M });
        renderApps(query);

        state.expiresAt = Date.now() + QR_VALID_SECONDS * 1000;
        tick();
        state.timer = setInterval(tick, 1000);
    }

    /* ---------- Open / reset ---------- */
    function resetView() {
        $('#payStep').prop('hidden', false);
        $('#paySuccess').prop('hidden', true).removeClass('is-in');
        $('#confetti').empty();
        $('#payConfirm').prop('disabled', false).removeClass('is-busy');
        showError('');
    }

    $(document).on('click', '[data-pay-open]', function () {
        var d = this.dataset;
        state.name = d.name;
        state.upi = d.upi || '';
        state.pending = parseFloat(d.amount);
        state.recorded = false;

        form.action = d.action;
        $('#payName').text(d.name);
        $('#payAvatar').text((d.name || '?').trim().charAt(0).toUpperCase());
        $('#payUpi').text(state.upi || 'UPI not added');
        $('#payUpiCopy').toggleClass('is-missing', !state.upi).prop('disabled', !state.upi);
        $('#payPending').text(money(state.pending));
        amountInput.value = state.pending.toFixed(2);
        amountInput.max = state.pending.toFixed(2);

        $('#qrSection').prop('hidden', !state.upi);
        $('#noUpiWarning').prop('hidden', !!state.upi);
        $('.pay-modal').toggleClass('no-upi', !state.upi);
        $('.pay-submit-text').html(state.upi
            ? '<i class="fas fa-check-circle mr-1"></i> I\'ve completed the payment'
            : '<i class="fas fa-check-circle mr-1"></i> Record payment');
        resetView();
        $modal.modal('show');
    });

    $modal.on('shown.bs.modal', generate);
    $modal.on('hidden.bs.modal', function () {
        stopTimer();
        qrEl.innerHTML = '';
        if (state.recorded) window.location.reload(); // refresh totals after a payment
    });

    var debounce;
    $(amountInput).on('input', function () {
        showError('');
        clearTimeout(debounce);
        debounce = setTimeout(generate, 400);
    });
    $('.pay-chip').on('click', function () {
        var f = parseFloat(this.dataset.fill);
        amountInput.value = (Math.floor(state.pending * f * 100) / 100).toFixed(2);
        showError('');
        generate();
    });
    $('#qrRegenerate').on('click', generate);

    $('#payUpiCopy').on('click', function () {
        var btn = this;
        if (!state.upi || !navigator.clipboard) return;
        navigator.clipboard.writeText(state.upi).then(function () {
            $(btn).addClass('is-copied');
            setTimeout(function () { $(btn).removeClass('is-copied'); }, 1400);
        }).catch(function () {});
    });

    /* ---------- Submit via AJAX, celebrate only on server success ---------- */
    $(form).on('submit', function (e) {
        e.preventDefault();
        if (!validAmount()) {
            showError('Enter an amount between ₹0.01 and ' + money(state.pending) + '.');
            amountInput.focus();
            return;
        }

        var full = Math.abs(parseFloat(amountInput.value) - state.pending) < 0.005;
        $('#payNote').val((full ? 'Full' : 'Partial') + ' settlement paid' + (state.upi ? ' via UPI QR' : ''));

        if (window.SFX) SFX.prime();
        var $btn = $('#payConfirm').prop('disabled', true).addClass('is-busy');
        showError('');

        fetch(form.action, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(form),
            credentials: 'same-origin'
        }).then(function (res) {
            return res.json().catch(function () { return {}; }).then(function (body) {
                if (!res.ok) {
                    var msg = body.message || 'Could not record the payment (error ' + res.status + ').';
                    if (body.errors) msg = Object.values(body.errors)[0][0];
                    if (res.status === 419) msg = 'Your session expired. Refresh the page and try again.';
                    throw new Error(msg);
                }
                return body;
            });
        }).then(function (body) {
            state.recorded = true;
            stopTimer();
            showSuccess(body);
            document.dispatchEvent(new CustomEvent('stats:refresh'));
        }).catch(function (err) {
            showError(err.message || 'Network error. Check your connection and try again.');
            $btn.prop('disabled', false).removeClass('is-busy');
        });
    });

    function showSuccess(body) {
        var p = body.payment || {};
        $('#successName').text(p.employee || state.name);
        $('#successDate').text(p.paid_at || '-');
        $('#successRef').text('#' + (p.id || '-'));
        $('#successRemaining').text(money(body.remaining || 0));

        $('#payStep').prop('hidden', true);
        $('#paySuccess').prop('hidden', false);
        // Force reflow so the entrance animation restarts each time
        void document.getElementById('paySuccess').offsetWidth;
        $('#paySuccess').addClass('is-in');
        countUp(document.getElementById('successAmount'), Number(p.amount || amountInput.value));
        if (!reduceMotion) burstConfetti();
        if (window.SFX) SFX.play('success');
        $('#successDone').trigger('focus');
    }

    function countUp(el, target) {
        if (reduceMotion) { el.textContent = money(target); return; }
        var start = performance.now(), dur = 900;
        (function step(now) {
            var t = Math.min(1, (now - start) / dur);
            var eased = 1 - Math.pow(1 - t, 3);
            el.textContent = money(target * eased);
            if (t < 1) requestAnimationFrame(step);
        })(start);
    }

    function burstConfetti() {
        var box = document.getElementById('confetti');
        var colors = ['#4f46e5', '#7c3aed', '#10b981', '#f59e0b', '#ec4899', '#0ea5e9'];
        box.innerHTML = '';
        for (var i = 0; i < 70; i++) {
            var s = document.createElement('i');
            var angle = Math.random() * Math.PI * 2;
            var dist = 90 + Math.random() * 170;
            s.style.setProperty('--x', Math.cos(angle) * dist + 'px');
            s.style.setProperty('--y', Math.sin(angle) * dist - 60 + 'px');
            s.style.setProperty('--r', (Math.random() * 720 - 360) + 'deg');
            s.style.background = colors[i % colors.length];
            s.style.animationDelay = (Math.random() * 0.15) + 's';
            if (i % 3 === 0) s.style.borderRadius = '50%';
            box.appendChild(s);
        }
    }

    $('#successDone').on('click', function () { $modal.modal('hide'); });
})(jQuery);
