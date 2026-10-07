// Login page: AJAX sign-in with an iPhone-style unlock animation before entering the app.
(function () {
    var root = document.documentElement;
    var form = document.getElementById('loginForm');
    var btn = document.getElementById('loginBtn');
    var errorBox = document.getElementById('loginError');
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Entrance: elements fall into place
    requestAnimationFrame(function () { root.classList.add('login-in'); });

    document.getElementById('togglePassword').addEventListener('click', function () {
        var input = document.getElementById('password');
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        this.querySelector('span').className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
    });

    function showError(msg) {
        errorBox.textContent = msg;
        errorBox.hidden = false;
        var card = document.querySelector('.login-card-body');
        card.classList.remove('shake');
        void card.offsetWidth;
        card.classList.add('shake');
        btn.disabled = false;
        btn.classList.remove('is-busy');
    }

    if (!window.fetch || !window.FormData) return; // plain form post fallback

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!form.reportValidity()) return;

        errorBox.hidden = true;
        btn.disabled = true;
        btn.classList.add('is-busy');

        fetch(form.action, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(form),
            credentials: 'same-origin'
        }).then(function (res) {
            return res.json().catch(function () { return {}; }).then(function (body) {
                if (res.status === 419) { window.location.reload(); throw null; }
                if (!res.ok) {
                    var msg = body.message || 'Login failed. Please try again.';
                    if (body.errors) msg = Object.values(body.errors)[0][0];
                    throw new Error(msg);
                }
                return body;
            });
        }).then(function (body) {
            unlock(body);
        }).catch(function (err) {
            if (err) showError(err.message || 'Network error. Check your connection and try again.');
        });
    });

    function unlock(body) {
        var go = function () {
            try { sessionStorage.setItem('justLoggedIn', '1'); } catch (e) {}
            window.location.assign(body.redirect);
        };
        // The unlock sound is ~1.5s; navigate when it ends so it isn't cut off.
        if (window.SFX) SFX.play('unlock');
        if (!reduce) root.classList.add('is-unlocking'); // lock opens, card zooms toward the viewer
        setTimeout(go, 1500);
    }
})();
