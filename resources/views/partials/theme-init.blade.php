<script>
    (() => {
        try {
            const savedTheme = localStorage.getItem('pos-lubricantes-theme');
            const isDark = savedTheme === 'dark'
                || (savedTheme !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);

            document.documentElement.classList.toggle('dark', isDark);
        } catch {
            document.documentElement.classList.toggle('dark', window.matchMedia('(prefers-color-scheme: dark)').matches);
        }
    })();
</script>
