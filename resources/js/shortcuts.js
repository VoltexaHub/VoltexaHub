/**
 * Global keyboard shortcuts.
 *
 *   /           focus the header search
 *   g h         go home
 *   g m         go to messages
 *   g n         go to notifications
 *   g b         go to bookmarks
 *   g p         go to your profile
 *   g s         go to settings
 *   ?           toggle the help overlay
 *   Esc         close overlays
 */

const isEditableTarget = (el) => {
    if (!el) return false;
    const tag = el.tagName;
    if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return true;
    if (el.isContentEditable) return true;
    // CodeMirror editor content sits inside divs with contenteditable
    if (el.closest && el.closest('.CodeMirror')) return true;
    return false;
};

const profileLink = document.querySelector('[data-shortcut-profile]');

const shortcuts = {
    'g h': () => window.location.assign('/'),
    'g m': () => window.location.assign('/messages'),
    'g n': () => window.location.assign('/notifications'),
    'g b': () => window.location.assign('/bookmarks'),
    'g s': () => window.location.assign('/profile'),
    'g p': () => {
        const url = profileLink?.getAttribute('href');
        if (url) window.location.assign(url);
    },
};

let comboPrefix = null;
let comboTimer = null;

function openHelp() {
    if (document.getElementById('vx-shortcut-help')) return;

    const overlay = document.createElement('div');
    overlay.id = 'vx-shortcut-help';
    overlay.style.cssText = `
        position: fixed; inset: 0; z-index: 1000;
        background: rgba(15,14,12,0.55);
        backdrop-filter: blur(4px);
        display: flex; align-items: center; justify-content: center;
        padding: 1rem;
        font-family: 'Inter Tight', system-ui, sans-serif;
    `;

    const rows = [
        ['/',         'Focus the search'],
        ['?',         'Toggle this help'],
        ['g h',       'Home'],
        ['g m',       'Messages'],
        ['g n',       'Notifications'],
        ['g b',       'Bookmarks'],
        ['g p',       'My profile'],
        ['g s',       'Settings'],
        ['Esc',       'Close overlays'],
    ];

    overlay.innerHTML = `
        <div style="
            max-width: 460px; width: 100%;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.18);
            overflow: hidden;
        ">
            <header style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; align-items: baseline; justify-content: space-between;">
                <div>
                    <p style="font-family:'JetBrains Mono',monospace;font-size:0.7rem;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-subtle);margin:0 0 0.25rem 0">Keyboard</p>
                    <h2 style="font-family:'Fraunces',serif;font-weight:600;font-size:1.5rem;color:var(--text);margin:0">Shortcuts</h2>
                </div>
                <button type="button" id="vx-shortcut-close" aria-label="Close"
                    style="background:none;border:0;cursor:pointer;color:var(--text-muted);font-size:1.5rem;line-height:1;padding:0 0.25rem">×</button>
            </header>
            <dl style="padding: 0.75rem 1.5rem 1.5rem; margin:0">
                ${rows.map(([k, v]) => `
                    <div style="display:flex;align-items:center;gap:1rem;padding:0.5rem 0;border-top:1px solid var(--border)">
                        <kbd style="
                            display:inline-block;
                            padding:0.15rem 0.5rem;
                            font-family:'JetBrains Mono',monospace;font-size:0.78rem;
                            background:var(--surface-mute);
                            border:1px solid var(--border);
                            border-radius:0.375rem;
                            color:var(--text);
                            min-width: 3.5rem;
                            text-align:center;
                        ">${k}</kbd>
                        <span style="color:var(--text-muted);font-size:0.9rem">${v}</span>
                    </div>
                `).join('')}
            </dl>
        </div>
    `;

    document.body.appendChild(overlay);

    const close = () => overlay.remove();
    overlay.addEventListener('click', (e) => { if (e.target === overlay) close(); });
    overlay.querySelector('#vx-shortcut-close').addEventListener('click', close);
}

function closeHelp() {
    const el = document.getElementById('vx-shortcut-help');
    if (el) el.remove();
}

function openMobileSearch() {
    const existing = document.querySelector('header form[action$="/search"] input[type="search"]');
    if (existing && existing.offsetParent !== null) {
        existing.focus();
        return;
    }
    // On mobile the inline search is hidden — drop a tiny overlay instead.
    const wrap = document.createElement('div');
    wrap.id = 'vx-mobile-search';
    wrap.style.cssText = `
        position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
        padding: 0.75rem 1rem;
        background: var(--surface);
        border-bottom: 1px solid var(--border);
        display: flex; gap: 0.5rem;
    `;
    wrap.innerHTML = `
        <form method="GET" action="/search" style="display:flex;flex:1;gap:0.5rem">
            <input name="q" type="search" placeholder="Search the hub…" autofocus
                   style="flex:1;padding:0.5rem 0.75rem;border:1px solid var(--border);border-radius:0.5rem;background:var(--surface);color:var(--text);font-family:inherit" />
            <button type="submit" style="padding:0.5rem 1rem;background:var(--accent);color:#fff;border:0;border-radius:0.5rem;font-family:inherit;font-weight:500">Go</button>
        </form>
        <button type="button" id="vx-mobile-search-close" aria-label="Close" style="background:none;border:0;cursor:pointer;color:var(--text-muted);font-size:1.5rem;padding:0 0.5rem">×</button>
    `;
    document.body.appendChild(wrap);
    wrap.querySelector('input').focus();
    wrap.querySelector('#vx-mobile-search-close').addEventListener('click', () => wrap.remove());
}

document.addEventListener('keydown', (e) => {
    if (e.metaKey || e.ctrlKey || e.altKey) return;

    // Close overlays on Escape regardless of focus.
    if (e.key === 'Escape') {
        closeHelp();
        document.getElementById('vx-mobile-search')?.remove();
        return;
    }

    if (isEditableTarget(e.target)) return;

    if (e.key === '/') {
        const input = document.querySelector('header form[action$="/search"] input[type="search"]');
        if (input && input.offsetParent !== null) {
            e.preventDefault();
            input.focus();
            input.select?.();
        } else {
            e.preventDefault();
            openMobileSearch();
        }
        return;
    }

    if (e.key === '?') {
        e.preventDefault();
        if (document.getElementById('vx-shortcut-help')) closeHelp();
        else openHelp();
        return;
    }

    if (comboPrefix === 'g') {
        const combo = 'g ' + e.key.toLowerCase();
        if (shortcuts[combo]) {
            e.preventDefault();
            shortcuts[combo]();
        }
        comboPrefix = null;
        clearTimeout(comboTimer);
        return;
    }

    if (e.key === 'g') {
        comboPrefix = 'g';
        clearTimeout(comboTimer);
        comboTimer = setTimeout(() => { comboPrefix = null; }, 1200);
    }
});
