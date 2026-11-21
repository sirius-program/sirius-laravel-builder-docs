import '../../vendor/sirius/ui/dist/sirius.js';

const media = window.matchMedia('(prefers-color-scheme: dark)');
let selected = null;
try { selected = localStorage.getItem('sirius-builder-theme'); } catch {}
function applyTheme(dark) {
    document.documentElement.classList.toggle('dark', dark);
    document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
    document.querySelectorAll('[data-theme-toggle]').forEach(button => {
        button.disabled = false;
        button.setAttribute('aria-disabled', 'false');
        button.setAttribute('aria-pressed', String(dark));
        button.setAttribute('title', dark ? 'Switch to light theme' : 'Switch to dark theme');
    });
}
applyTheme(selected === 'dark' || (selected !== 'light' && media.matches));
document.addEventListener('click', event => {
    if (!(event.target instanceof Element) || !event.target.closest('[data-theme-toggle]')) return;
    const dark = !document.documentElement.classList.contains('dark');
    selected = dark ? 'dark' : 'light';
    try { localStorage.setItem('sirius-builder-theme', selected); } catch {}
    applyTheme(dark);
});
media.addEventListener('change', event => {
    if (selected !== 'light' && selected !== 'dark') applyTheme(event.matches);
});

const copyResetTimers = new Map();
document.addEventListener('click', async event => {
    const button = event.target instanceof Element ? event.target.closest('[data-copy-code]') : null;
    if (!button || button.disabled) return;
    const block = button.closest('[data-docs-code]');
    const code = block.querySelector('code');
    const label = button.querySelector('[data-copy-label]');
    const status = block.querySelector('[data-copy-status]');
    clearTimeout(copyResetTimers.get(button));
    button.disabled = true;
    status.textContent = '';
    try {
        if (!navigator.clipboard?.writeText) throw new Error('Clipboard unavailable');
        await navigator.clipboard.writeText(code.textContent);
        label.textContent = 'Copied';
        status.textContent = 'Code copied to clipboard.';
    } catch {
        const range = document.createRange();
        range.selectNodeContents(code);
        const selection = window.getSelection();
        selection?.removeAllRanges();
        selection?.addRange(range);
        label.textContent = 'Select & copy';
        status.textContent = 'Clipboard unavailable. Code selected; copy it manually.';
    } finally {
        if (!button.isConnected) return;
        button.disabled = false;
        copyResetTimers.set(button, setTimeout(() => {
            label.textContent = 'Copy';
            status.textContent = '';
            copyResetTimers.delete(button);
        }, 2500));
    }
});

function initializePage() {
    applyTheme(selected === 'dark' || (selected !== 'light' && media.matches));
    document.querySelectorAll('[data-copy-code]').forEach(button => {
        button.disabled = false;
        button.setAttribute('aria-disabled', 'false');
        button.querySelector('[data-copy-label]').textContent = 'Copy';
        button.closest('[data-docs-code]').querySelector('[data-copy-status]').textContent = '';
    });
}

document.addEventListener('livewire:navigating', () => {
    for (const timer of copyResetTimers.values()) clearTimeout(timer);
    copyResetTimers.clear();
});
document.addEventListener('livewire:navigated', initializePage);
initializePage();
