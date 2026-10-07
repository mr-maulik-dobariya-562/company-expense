// Light / dark theme toggle with animated switch. Default: light.
(function () {
    var root = document.documentElement;
    var meta = document.querySelector('meta[name="theme-color"]');

    function apply(theme) {
        root.setAttribute('data-theme', theme);
        if (meta) meta.setAttribute('content', theme === 'dark' ? '#0b1020' : '#4f46e5');
        document.querySelectorAll('[data-theme-toggle]').forEach(function (b) {
            b.setAttribute('aria-pressed', theme === 'dark');
            b.setAttribute('title', theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
        });
        try { localStorage.setItem('theme', theme); } catch (e) {}
    }

    function toggle(e) {
        var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (reduce) { apply(next); return; }

        if (!document.startViewTransition) {
            root.classList.add('theme-animating');
            apply(next);
            setTimeout(function () { root.classList.remove('theme-animating'); }, 500);
            return;
        }

        // Circular reveal expanding from the clicked button
        var rect = e.currentTarget.getBoundingClientRect();
        var x = rect.left + rect.width / 2, y = rect.top + rect.height / 2;
        var r = Math.hypot(Math.max(x, innerWidth - x), Math.max(y, innerHeight - y));
        root.classList.add('theme-vt');
        var vt = document.startViewTransition(function () { apply(next); });
        vt.finished.finally(function () { root.classList.remove('theme-vt'); });
        vt.ready.then(function () {
            root.animate(
                { clipPath: ['circle(0px at ' + x + 'px ' + y + 'px)', 'circle(' + r + 'px at ' + x + 'px ' + y + 'px)'] },
                { duration: 550, easing: 'cubic-bezier(.4, 0, .2, 1)', pseudoElement: '::view-transition-new(root)' }
            );
        });
    }

    apply(root.getAttribute('data-theme') || 'light');
    document.querySelectorAll('[data-theme-toggle]').forEach(function (b) { b.addEventListener('click', toggle); });
})();
