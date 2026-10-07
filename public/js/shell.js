// App shell behaviour: collapsible sidebar, dock sliding indicator, "More" bottom sheet.
(function () {
    var root = document.documentElement;

    /* ---------- Sidebar collapse (remembered per browser) ---------- */
    var sidebar = document.getElementById('sidebar');

    function setCollapsed(on) {
        root.classList.toggle('sb-collapsed', on);
        try { localStorage.setItem('sidebar', on ? 'collapsed' : 'expanded'); } catch (e) {}
        if (on) {
            // The pointer is still over the sidebar right after clicking collapse; don't let
            // the hover-expand reopen it until the pointer has left once.
            root.classList.add('sb-hover-lock');
            if (document.activeElement && sidebar && sidebar.contains(document.activeElement)) document.activeElement.blur();
        }
    }
    if (sidebar) {
        sidebar.addEventListener('mouseleave', function () { root.classList.remove('sb-hover-lock'); });
    }
    document.querySelectorAll('[data-sidebar-toggle]').forEach(function (b) {
        b.addEventListener('click', function () { setCollapsed(!root.classList.contains('sb-collapsed')); });
    });
    document.querySelectorAll('[data-sidebar-expand]').forEach(function (b) {
        b.addEventListener('click', function () {
            if (root.classList.contains('sb-collapsed')) setCollapsed(false);
        });
    });
    // Re-enable transitions after first paint
    requestAnimationFrame(function () { requestAnimationFrame(function () { root.classList.remove('no-anim'); }); });

    /* ---------- Header money pills (AJAX) ---------- */
    var statsBox = document.getElementById('headerStats');

    function money(n) {
        return '₹' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function esc(s) {
        return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; });
    }

    function loadStats() {
        if (!statsBox) return;
        statsBox.setAttribute('aria-busy', 'true');
        statsBox.querySelectorAll('.hstat').forEach(function (p) { p.classList.add('is-refreshing'); });

        fetch(statsBox.dataset.url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin', cache: 'no-store' })
            .then(function (res) { if (!res.ok) throw new Error(res.status); return res.json(); })
            .then(function (data) {
                statsBox.innerHTML = (data.stats || []).map(function (s) {
                    return '<span class="hstat tone-' + esc(s.tone) + '" title="' + esc(s.label) + '">' +
                        '<span class="hstat-icon"><i class="' + esc(s.icon) + '"></i></span>' +
                        '<span class="hstat-body"><span class="hstat-label">' + esc(s.label) + '</span>' +
                        '<span class="hstat-value">' + money(s.value) + '</span></span></span>';
                }).join('');
                statsBox.setAttribute('aria-busy', 'false');
            })
            .catch(function () {
                statsBox.innerHTML = '<button type="button" class="hstat hstat-error" title="Could not load balances. Tap to retry."><span class="hstat-icon"><i class="fas fa-redo"></i></span><span class="hstat-body"><span class="hstat-label">Balance</span><span class="hstat-value">Retry</span></span></button>';
                statsBox.setAttribute('aria-busy', 'false');
                statsBox.querySelector('button').addEventListener('click', loadStats);
            });
    }
    loadStats();
    // Other scripts (e.g. after a payment) can ask for a refresh
    document.addEventListener('stats:refresh', loadStats);

    /* ---------- Dock sliding indicator ---------- */
    var dock = document.getElementById('dock');
    var indicator = document.getElementById('dockIndicator');

    function moveIndicator(item, animate) {
        if (!dock || !indicator || !item) return;
        if (!animate) indicator.style.transition = 'none';
        indicator.style.width = item.offsetWidth + 'px';
        indicator.style.transform = 'translateX(' + item.offsetLeft + 'px)';
        indicator.classList.add('ready');
        if (!animate) { void indicator.offsetWidth; indicator.style.transition = ''; }
    }

    if (dock) {
        var current = dock.querySelector('.dock-item.active');
        if (current) moveIndicator(current, false);
        window.addEventListener('resize', function () {
            moveIndicator(dock.querySelector('.dock-item.active'), false);
        });
        // Slide to the tapped tab before the page navigates
        dock.querySelectorAll('a.dock-item').forEach(function (a) {
            a.addEventListener('click', function () {
                dock.querySelectorAll('.dock-item').forEach(function (i) { i.classList.remove('active'); });
                a.classList.add('active');
                moveIndicator(a, true);
            });
        });
    }

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Tapping the raised centre button: the tab pill fades away
    var center = dock && dock.querySelector('.dock-center');
    if (center && indicator) {
        center.addEventListener('click', function () {
            dock.querySelectorAll('.dock-item').forEach(function (i) { i.classList.remove('active'); });
            indicator.classList.remove('ready');
        });
    }

    /* ---------- Logout: play the exit animation, then submit ---------- */
    document.querySelectorAll('form[data-logout]').forEach(function (f) {
        f.addEventListener('submit', function (e) {
            if (f.dataset.leaving) return;
            e.preventDefault();
            f.dataset.leaving = '1';
            if (window.SFX) SFX.play('lock');
            if (!reduceMotion) root.classList.add('is-leaving');
            // The lock sound is ~1.5s; submit when it ends so it isn't cut off
            setTimeout(function () { f.submit(); }, 1500);
        });
    });

    /* ---------- Stat numbers count up on page load ---------- */
    if (!reduceMotion) {
        document.querySelectorAll('.stat-value').forEach(function (el, i) {
            var text = el.textContent.trim();
            var m = /^(₹?)([\d,]+(?:\.\d+)?)$/.exec(text);
            if (!m) return;
            var target = parseFloat(m[2].replace(/,/g, ''));
            var decimals = (m[2].split('.')[1] || '').length;
            if (!isFinite(target) || target === 0) return;
            var fmt = function (n) {
                return m[1] + n.toLocaleString('en-IN', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
            };
            el.textContent = fmt(0);
            var start = null, dur = 900, delay = 120 + i * 50;
            setTimeout(function () {
                requestAnimationFrame(function step(now) {
                    if (start === null) start = now;
                    var t = Math.min(1, (now - start) / dur);
                    el.textContent = fmt(target * (1 - Math.pow(1 - t, 3)));
                    if (t < 1) requestAnimationFrame(step); else el.textContent = text;
                });
            }, delay);
        });
    }

    /* ---------- "More" bottom sheet ---------- */
    var sheet = document.getElementById('moreSheet');
    var backdrop = document.getElementById('sheetBackdrop');
    var opener = document.querySelector('[data-sheet-open]');
    if (!sheet || !backdrop) return;

    function openSheet() {
        backdrop.hidden = false;
        void backdrop.offsetWidth;
        backdrop.classList.add('show');
        sheet.style.transform = '';
        sheet.classList.add('open');
        sheet.setAttribute('aria-hidden', 'false');
        root.classList.add('sheet-open');
        if (opener) opener.setAttribute('aria-expanded', 'true');
        var first = sheet.querySelector('.sheet-item');
        if (first) setTimeout(function () { first.focus({ preventScroll: true }); }, 200);
    }
    function closeSheet() {
        sheet.classList.remove('open', 'dragging');
        sheet.style.transform = '';
        sheet.setAttribute('aria-hidden', 'true');
        backdrop.classList.remove('show');
        root.classList.remove('sheet-open');
        if (opener) { opener.setAttribute('aria-expanded', 'false'); opener.focus({ preventScroll: true }); }
        setTimeout(function () { if (!sheet.classList.contains('open')) backdrop.hidden = true; }, 300);
    }

    if (opener) opener.addEventListener('click', openSheet);
    document.querySelectorAll('[data-sheet-close]').forEach(function (el) { el.addEventListener('click', closeSheet); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sheet.classList.contains('open')) closeSheet();
    });

    // Drag the grabber down to dismiss
    var grabber = sheet.querySelector('[data-sheet-drag]');
    var startY = null, dy = 0;
    if (grabber) {
        grabber.addEventListener('pointerdown', function (e) {
            startY = e.clientY; dy = 0;
            sheet.classList.add('dragging');
            grabber.setPointerCapture(e.pointerId);
        });
        grabber.addEventListener('pointermove', function (e) {
            if (startY === null) return;
            dy = Math.max(0, e.clientY - startY);
            sheet.style.transform = 'translateY(' + dy + 'px)';
        });
        var end = function () {
            if (startY === null) return;
            startY = null;
            sheet.classList.remove('dragging');
            if (dy > 90) closeSheet(); else sheet.style.transform = '';
        };
        grabber.addEventListener('pointerup', end);
        grabber.addEventListener('pointercancel', end);
    }
})();
