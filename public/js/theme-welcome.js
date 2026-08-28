(function() {
    var theme = localStorage.getItem('sigejub_theme') || 'light';
    if (theme === 'dark') {
        document.body.classList.add('dark-mode');
    } else if (theme === 'modern') {
        document.body.classList.add('theme-modern');
    }
})();