// Modul JS kecil untuk panel "Lainnya" pada bottom bar Admin (DESIGN-UPDATE §3 & §9):
// - Buka ke atas dengan aria-expanded
// - Tutup dengan Esc atau klik di luar
// - Kembalikan fokus ke tombol "Lainnya" saat ditutup
// - Perangkap fokus (focus trap) di dalam panel selama terbuka

function initBottomBarMorePanel() {
    const containers = document.querySelectorAll('[data-more-container]');
    if (!containers.length) return;

    containers.forEach((container) => {
        const toggleBtn = container.querySelector('[data-more-toggle]');
        const panel = container.querySelector('[data-more-panel]');
        if (!toggleBtn || !panel) return;

        const getFocusableItems = () => Array.from(
            panel.querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])')
        );

        const openPanel = () => {
            panel.removeAttribute('hidden');
            toggleBtn.setAttribute('aria-expanded', 'true');
            const items = getFocusableItems();
            if (items.length > 0) {
                items[0].focus();
            }
        };

        const closePanel = (restoreFocus = true) => {
            if (panel.hasAttribute('hidden')) return;
            panel.setAttribute('hidden', 'hidden');
            toggleBtn.setAttribute('aria-expanded', 'false');
            if (restoreFocus) {
                toggleBtn.focus();
            }
        };

        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = !panel.hasAttribute('hidden');
            if (isOpen) {
                closePanel(true);
            } else {
                openPanel();
            }
        });

        document.addEventListener('click', (e) => {
            if (!panel.hasAttribute('hidden') && !container.contains(e.target)) {
                closePanel(false);
            }
        });

        document.addEventListener('keydown', (e) => {
            if (panel.hasAttribute('hidden')) return;

            if (e.key === 'Escape') {
                e.preventDefault();
                closePanel(true);
                return;
            }

            if (e.key === 'Tab') {
                const items = getFocusableItems();
                if (items.length === 0) return;

                const first = items[0];
                const last = items[items.length - 1];

                if (e.shiftKey) {
                    if (document.activeElement === first || !panel.contains(document.activeElement)) {
                        e.preventDefault();
                        last.focus();
                    }
                } else if (document.activeElement === last || !panel.contains(document.activeElement)) {
                    e.preventDefault();
                    first.focus();
                }
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', initBottomBarMorePanel);
