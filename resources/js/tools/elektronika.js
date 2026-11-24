// Modul 6: Simulasi & Kalkulator Elektronika Dasar (Hukum Ohm & Resistor Pengganti Seri/Paralel) — dari legacy/page/rumus-elektronik.

export function computeOhmElektronika({ v, i, r }) {
    const blanks = [Number.isNaN(v), Number.isNaN(i), Number.isNaN(r)].filter(Boolean).length;
    if (blanks !== 1) {
        return { error: 'Kosongkan tepat 1 nilai yang ingin dicari.' };
    }

    let rV = v;
    let rI = i;
    let rR = r;
    if (Number.isNaN(v)) rV = i * r;
    else if (Number.isNaN(i)) rI = v / r;
    else rR = v / i;

    return {
        v: rV,
        i: rI,
        r: rR,
        vText: `${rV.toFixed(2)} V`,
        iText: `${rI.toFixed(2)} A`,
        rText: `${rR.toFixed(2)} Ω`,
    };
}

export function computeResistorPengganti({ r1, r2 }) {
    if (Number.isNaN(r1) || Number.isNaN(r2)) {
        return { error: 'Masukkan nilai R1 dan R2 dengan benar.' };
    }

    const rs = r1 + r2;
    const rp = (r1 * r2) / (r1 + r2);

    return {
        rs,
        rp,
        rsText: `${rs.toFixed(2)} Ω`,
        rpText: `${rp.toFixed(2)} Ω`,
    };
}

export function mountElektronikaTool(container) {
    container.innerHTML = `
        <div class="rf-tool" data-tool-elektronika>
            <div class="rf-tool__block">
                <div class="rf-tool__head">
                    <h3 class="h4 mb-0">Kalkulator Elektronika Dasar</h3>
                    <div class="rf-chips" role="group" aria-label="Mode kalkulator elektronika">
                        <button type="button" class="rf-chip is-active" data-el-tab="res" aria-pressed="true"><svg class="rf-icon rf-chip__check" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 6 9 17l-5-5"/></svg> Resistor Seri & Paralel</button>
                        <button type="button" class="rf-chip" data-el-tab="ohm" aria-pressed="false"><svg class="rf-icon rf-chip__check" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 6 9 17l-5-5"/></svg> Hukum Ohm (V = I · R)</button>
                    </div>
                </div>

                <div data-el-panel="res">
                    <div class="rf-tool__grid-2">
                        <div class="rf-field">
                            <label class="rf-label" for="rf-el-r1">Resistor 1 / R₁ (Ω)</label>
                            <input class="rf-input rf-num" id="rf-el-r1" type="number" step="any" data-el-r1>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-el-r2">Resistor 2 / R₂ (Ω)</label>
                            <input class="rf-input rf-num" id="rf-el-r2" type="number" step="any" data-el-r2>
                        </div>
                    </div>
                    <div class="rf-tool__actions mt-3">
                        <button type="button" class="rf-btn rf-btn--secondary" data-el-res-calc>Hitung R Pengganti</button>
                    </div>
                    <div class="rf-sheet mt-4">
                        <h4 class="rf-sheet__title">Hasil di Lembar Rumus</h4>
                        <div class="rf-sheet__paper rf-num" data-el-res-out>Masukkan R₁ dan R₂, lalu klik Hitung R Pengganti.</div>
                    </div>
                </div>

                <div data-el-panel="ohm" hidden>
                    <div class="rf-tool__grid-3">
                        <div class="rf-field">
                            <label class="rf-label" for="rf-el-v">Tegangan V (Volt)</label>
                            <input class="rf-input rf-num" id="rf-el-v" type="number" step="any" data-el-v>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-el-i">Arus I (Ampere)</label>
                            <input class="rf-input rf-num" id="rf-el-i" type="number" step="any" data-el-i>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-el-r">Hambatan R (Ω)</label>
                            <input class="rf-input rf-num" id="rf-el-r" type="number" step="any" data-el-r>
                        </div>
                    </div>
                    <div class="rf-tool__actions mt-3">
                        <button type="button" class="rf-btn rf-btn--secondary" data-el-ohm-calc>Hitung Ohm</button>
                    </div>
                    <div class="rf-sheet mt-4">
                        <h4 class="rf-sheet__title">Hasil di Lembar Rumus</h4>
                        <div class="rf-sheet__paper rf-num" data-el-ohm-out>Kosongkan satu nilai, lalu klik Hitung Ohm.</div>
                    </div>
                </div>
            </div>
        </div>
    `;

    container.querySelectorAll('[data-el-tab]').forEach((tab) => {
        tab.addEventListener('click', () => {
            const key = tab.getAttribute('data-el-tab');
            container.querySelectorAll('[data-el-tab]').forEach((other) => {
                const active = other === tab;
                other.classList.toggle('is-active', active);
                other.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            container.querySelectorAll('[data-el-panel]').forEach((p) => {
                if (p.getAttribute('data-el-panel') === key) p.removeAttribute('hidden');
                else p.setAttribute('hidden', 'hidden');
            });
        });
    });

    container.querySelector('[data-el-res-calc]')?.addEventListener('click', () => {
        const res = computeResistorPengganti({
            r1: parseFloat(container.querySelector('[data-el-r1]')?.value || ''),
            r2: parseFloat(container.querySelector('[data-el-r2]')?.value || ''),
        });
        const box = container.querySelector('[data-el-res-out]');
        if (!box) return;
        if (res.error) {
            box.innerHTML = `<span class="rf-error">${res.error}</span>`;
            return;
        }
        box.innerHTML = `<div class="rf-calc-outcomes"><div><span>Jika Diseri (Rs)</span><strong>${res.rsText}</strong></div><div><span>Jika Diparalel (Rp)</span><strong>${res.rpText}</strong></div></div>`;
    });

    container.querySelector('[data-el-ohm-calc]')?.addEventListener('click', () => {
        const res = computeOhmElektronika({
            v: parseFloat(container.querySelector('[data-el-v]')?.value || ''),
            i: parseFloat(container.querySelector('[data-el-i]')?.value || ''),
            r: parseFloat(container.querySelector('[data-el-r]')?.value || ''),
        });
        const box = container.querySelector('[data-el-ohm-out]');
        if (!box) return;
        if (res.error) {
            box.innerHTML = `<span class="rf-error">${res.error}</span>`;
            return;
        }
        box.innerHTML = `<div class="rf-calc-outcomes"><div><span>Tegangan (V)</span><strong>${res.vText}</strong></div><div><span>Arus (I)</span><strong>${res.iText}</strong></div><div><span>Hambatan (R)</span><strong>${res.rText}</strong></div></div>`;
    });
}
