// Toast: muncul saat ada flash message dari server atau lewat window.rfToast(pesan, jenis).
const KINDS = {
    ok: { label: 'Berhasil', icon: 'check-circle' },
    error: { label: 'Perlu diperbaiki', icon: 'alert-circle' },
    info: { label: 'Info', icon: 'info' },
};

const AUTOHIDE_MS = 6000;

function dismiss(toast) {
    toast.classList.add('is-leaving');
    window.setTimeout(() => toast.remove(), 140);
}

function arm(toast) {
    toast.querySelector('[data-toast-close]')?.addEventListener('click', () => dismiss(toast));
    window.setTimeout(() => dismiss(toast), AUTOHIDE_MS);
}

window.rfToast = (message, kind = 'info') => {
    const container = document.querySelector('.rf-toasts');
    if (!container) return;

    const meta = KINDS[kind] ?? KINDS.info;
    const toast = document.createElement('div');
    toast.className = `rf-alert rf-alert--${kind} rf-toast`;
    toast.setAttribute('role', kind === 'error' ? 'alert' : 'status');

    const body = document.createElement('div');
    body.className = 'rf-alert__body';
    const strong = document.createElement('span');
    strong.className = 'rf-alert__kind';
    strong.textContent = meta.label;
    const text = document.createElement('span');
    text.textContent = message; // textContent: aman dari HTML
    body.append(strong, text);

    const close = document.createElement('button');
    close.type = 'button';
    close.className = 'rf-btn rf-btn--tertiary rf-btn--icon';
    close.setAttribute('aria-label', 'Tutup pemberitahuan');
    close.setAttribute('data-toast-close', '');
    close.textContent = '\u00d7';

    toast.append(body, close);
    container.append(toast);
    arm(toast);
};

document.querySelectorAll('.rf-toast').forEach(arm);