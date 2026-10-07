{{-- Set theme, sidebar state and post-login entrance before first paint to avoid a flash. Default theme is light. --}}
<script>
(function () {
    var d = document.documentElement, t = 'light', s = '';
    try {
        t = localStorage.getItem('theme') === 'dark' ? 'dark' : 'light';
        s = localStorage.getItem('sidebar');
        if (sessionStorage.getItem('justLoggedIn')) {
            sessionStorage.removeItem('justLoggedIn');
            d.classList.add('enter-from-login');
        }
    } catch (e) {}
    d.setAttribute('data-theme', t);
    d.classList.add('no-anim');
    if (s === 'collapsed') d.classList.add('sb-collapsed');
})();
</script>
