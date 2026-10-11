const themeStorageKey = 'pos-lubricantes-theme';
const themeToggleSelector = '[data-theme-toggle]';

function setTheme(theme, persist = false) {
    const isDark = theme === 'dark';

    document.documentElement.classList.toggle('dark', isDark);
    document.querySelectorAll(themeToggleSelector).forEach((button) => {
        button.setAttribute('aria-pressed', String(isDark));
    });

    if (persist) {
        try {
            window.localStorage.setItem(themeStorageKey, theme);
        } catch {
            // Keep the selected theme for this page even when storage is unavailable.
        }
    }
}

document.querySelectorAll(themeToggleSelector).forEach((button) => {
    button.addEventListener('click', () => {
        const nextTheme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';

        setTheme(nextTheme, true);
    });
});

try {
    const savedTheme = window.localStorage.getItem(themeStorageKey);

    if (savedTheme === 'light' || savedTheme === 'dark') {
        setTheme(savedTheme);
    } else {
        setTheme(window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    }
} catch {
    setTheme(window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
}
