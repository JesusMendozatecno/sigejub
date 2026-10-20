(function() {
    var theme = localStorage.getItem('sigejub_theme') || 'light';
    if (theme === 'dark') {
        document.body.classList.add('dark-mode');
    }
})();
