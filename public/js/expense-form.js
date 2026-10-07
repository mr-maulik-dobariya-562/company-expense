// Add Expense: submit via AJAX, show inline validation errors, then celebrate with a
// pop animation + sound before moving to the expense list.
(function () {
    var form = document.querySelector('form[data-ajax-expense]');
    if (!form || !window.fetch) return;

    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var submitBtn = form.querySelector('button:not([type]), button[type=submit]');

    function money(n) {
        return '₹' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function clearErrors() {
        form.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
        form.querySelectorAll('.invalid-feedback[data-ajax]').forEach(function (el) { el.remove(); });
    }

    function showErrors(errors) {
        Object.keys(errors).forEach(function (field) {
            var input = form.querySelector('[name="' + field + '"]');
            if (!input) return;
            input.classList.add('is-invalid');
            var fb = document.createElement('div');
            fb.className = 'invalid-feedback';
            fb.dataset.ajax = '1';
            fb.textContent = errors[field][0];
            input.insertAdjacentElement('afterend', fb);
        });
        var first = form.querySelector('.is-invalid');
        if (first) first.focus();
    }

    function setBusy(on) {
        if (!submitBtn) return;
        submitBtn.disabled = on;
        if (on) {
            submitBtn.dataset.label = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span>Saving…';
        } else if (submitBtn.dataset.label) {
            submitBtn.innerHTML = submitBtn.dataset.label;
        }
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearErrors();
        if (!form.checkValidity()) { form.reportValidity(); return; }
        if (window.SFX) SFX.prime();
        setBusy(true);

        fetch(form.action, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(form),
            credentials: 'same-origin'
        }).then(function (res) {
            return res.json().catch(function () { return {}; }).then(function (body) {
                if (res.status === 422 && body.errors) { showErrors(body.errors); throw null; }
                if (res.status === 419) throw new Error('Your session expired. Refresh the page and try again.');
                if (!res.ok) throw new Error(body.message || 'Could not save the expense (error ' + res.status + ').');
                return body;
            });
        }).then(celebrate).catch(function (err) {
            setBusy(false);
            if (err) alertError(err.message || 'Network error. Check your connection and try again.');
        });
    });

    function alertError(msg) {
        var box = document.createElement('div');
        box.className = 'alert alert-danger';
        box.setAttribute('role', 'alert');
        box.textContent = msg;
        form.insertAdjacentElement('beforebegin', box);
        setTimeout(function () { box.remove(); }, 6000);
    }

    function celebrate(body) {
        if (window.SFX) SFX.play('pop');
        document.dispatchEvent(new CustomEvent('stats:refresh'));
        var go = function () { window.location.assign(body.redirect); };
        if (reduce) { go(); return; }

        var e = body.expense || {};
        var pop = document.createElement('div');
        pop.className = 'expense-pop';
        pop.setAttribute('role', 'status');
        pop.innerHTML =
            '<div class="ep-card">' +
                '<div class="ep-burst" aria-hidden="true">' + new Array(12).join('<i></i>') + '</div>' +
                '<div class="ep-icon"><i class="fas fa-receipt"></i><span class="ep-badge"><i class="fas fa-check"></i></span></div>' +
                '<div class="ep-title">Expense added</div>' +
                '<div class="ep-amount"></div>' +
                '<div class="ep-name"></div>' +
            '</div>';
        pop.querySelector('.ep-amount').textContent = money(e.amount || 0);
        pop.querySelector('.ep-name').textContent = e.title || '';
        document.body.appendChild(pop);
        requestAnimationFrame(function () { pop.classList.add('show'); });

        setTimeout(function () { pop.classList.add('hide'); }, 1250);
        setTimeout(go, 1500);
    }
})();
