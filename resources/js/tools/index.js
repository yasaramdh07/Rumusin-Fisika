// Pemuat alat interaktif (simulasi & kalkulator) per kategori materi + kalkulator mini di hero landing.
import { computeGLBB, mountGerakTool } from './gerak';
import { mountListrikTool } from './listrik';
import { mountGayaTool } from './gaya';
import { mountEnergiTool } from './energi';
import { mountArchimedesTool } from './archimedes';
import { mountElektronikaTool } from './elektronika';

const TOOL_MOUNTS = {
    gerak: mountGerakTool,
    listrik: mountListrikTool,
    'gaya-newton': mountGayaTool,
    energi: mountEnergiTool,
    archimedes: mountArchimedesTool,
    'elektronika-dasar': mountElektronikaTool,
};

function initLessonTools() {
    document.querySelectorAll('[data-physics-tool]').forEach((node) => {
        const key = node.getAttribute('data-physics-tool') || '';
        const mountFn = TOOL_MOUNTS[key];
        if (typeof mountFn === 'function') {
            mountFn(node);
        }
    });

    initHeroMiniCalculator();
}

function initHeroMiniCalculator() {
    const box = document.querySelector('[data-hero-mini-calc]');
    if (!box) return;

    const v0In = box.querySelector('[data-hero-v0]');
    const aIn = box.querySelector('[data-hero-a]');
    const tIn = box.querySelector('[data-hero-t]');
    const vtOut = box.querySelector('[data-hero-vt]');
    const sOut = box.querySelector('[data-hero-s]');

    const update = () => {
        const v0 = parseFloat(v0In?.value || '0');
        const a = parseFloat(aIn?.value || '0');
        const t = parseFloat(tIn?.value || '0');
        const res = computeGLBB({
            v0: Number.isNaN(v0) ? 0 : v0,
            a: Number.isNaN(a) ? 0 : a,
            t: Number.isNaN(t) ? 0 : t,
            sIn: NaN,
        });

        if (!res.error) {
            if (vtOut) vtOut.textContent = `${res.vFinal.toFixed(1)} m/s`;
            if (sOut) sOut.textContent = `${res.sFinal.toFixed(1)} m`;
        }
    };

    [v0In, aIn, tIn].forEach((input) => input?.addEventListener('input', update));
    update();
}

document.addEventListener('DOMContentLoaded', initLessonTools);
