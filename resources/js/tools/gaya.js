// Modul 3: Simulasi & Kalkulator Gaya Newton (F = m·a, Berat w = m·g, Resultan Gaya) — dari legacy/page/rumus-gaya.

export function computeFMA({ f, m, a }) {
    const blanks = [Number.isNaN(f), Number.isNaN(m), Number.isNaN(a)].filter(Boolean).length;
    if (blanks !== 1) {
        return { error: 'Kosongkan tepat 1 nilai yang ingin dicari.' };
    }

    let rF = f;
    let rM = m;
    let rA = a;
    if (Number.isNaN(f)) rF = m * a;
    else if (Number.isNaN(m)) rM = f / a;
    else rA = f / m;

    return {
        f: rF,
        m: rM,
        a: rA,
        fText: `${rF.toFixed(3)} N`,
        mText: `${rM.toFixed(3)} kg`,
        aText: `${rA.toFixed(3)} m/s²`,
    };
}

export function computeBerat({ m, g = 9.8 }) {
    const gravity = Number.isNaN(g) || g === 0 ? 9.8 : g;
    if (Number.isNaN(m)) {
        return { error: 'Masukkan nilai massa terlebih dahulu.' };
    }
    const w = m * gravity;
    return {
        m,
        g: gravity,
        w,
        wText: `${w.toFixed(3)} N`,
    };
}

export function computeResultanGaya({ f1, f2, m }) {
    if (Number.isNaN(f1) || Number.isNaN(f2)) {
        return { error: 'Masukkan nilai F₁ dan F₂.' };
    }
    const fRes = f1 + f2;
    const aCalc = !Number.isNaN(m) && m > 0 ? fRes / m : null;

    return {
        f1,
        f2,
        fRes,
        a: aCalc,
        fResText: `${fRes.toFixed(3)} N`,
        aText: aCalc !== null ? `${aCalc.toFixed(3)} m/s²` : null,
    };
}

export function mountGayaTool(container) {
    container.innerHTML = `
        <div class="rf-tool" data-tool-gaya>
            <div class="rf-tool__block">
                <div class="rf-tool__head">
                    <h3 class="h4 mb-0">Kalkulator Hukum Newton</h3>
                    <div class="rf-chips">
                        <button type="button" class="rf-chip is-active" data-g-tab="fma" aria-pressed="true">F = m · a</button>
                        <button type="button" class="rf-chip" data-g-tab="berat" aria-pressed="false">Berat (w = m · g)</button>
                        <button type="button" class="rf-chip" data-g-tab="res" aria-pressed="false">Resultan Gaya</button>
                    </div>
                </div>

                <div data-g-panel="fma">
                    <p class="rf-muted rf-small">Kosongkan tepat 1 nilai yang ingin dicari.</p>
                    <div class="rf-tool__grid-3">
                        <div class="rf-field">
                            <label class="rf-label" for="rf-fma-f">Gaya F (N)</label>
                            <input class="rf-input rf-num" id="rf-fma-f" type="number" step="any" data-fma-f>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-fma-m">Massa m (kg)</label>
                            <input class="rf-input rf-num" id="rf-fma-m" type="number" step="any" data-fma-m>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-fma-a">Percepatan a (m/s²)</label>
                            <input class="rf-input rf-num" id="rf-fma-a" type="number" step="any" data-fma-a>
                        </div>
                    </div>
                    <div class="rf-tool__actions mt-3">
                        <button type="button" class="rf-btn rf-btn--secondary" data-fma-calc>Hitung Hukum II Newton</button>
                    </div>
                    <div class="rf-sheet mt-4">
                        <h4 class="rf-sheet__title">Hasil di Lembar Rumus</h4>
                        <div class="rf-sheet__paper rf-num" data-fma-res>Kosongkan satu nilai, lalu klik Hitung.</div>
                    </div>
                </div>

                <div data-g-panel="berat" hidden>
                    <div class="rf-tool__grid-2">
                        <div class="rf-field">
                            <label class="rf-label" for="rf-berat-m">Massa m (kg)</label>
                            <input class="rf-input rf-num" id="rf-berat-m" type="number" step="any" data-berat-m>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-berat-g">Gravitasi g (m/s²)</label>
                            <input class="rf-input rf-num" id="rf-berat-g" type="number" step="any" value="9.8" data-berat-g>
                        </div>
                    </div>
                    <div class="rf-tool__actions mt-3">
                        <button type="button" class="rf-btn rf-btn--secondary" data-berat-calc>Hitung Berat</button>
                    </div>
                    <div class="rf-sheet mt-4">
                        <h4 class="rf-sheet__title">Hasil di Lembar Rumus</h4>
                        <div class="rf-sheet__paper rf-num" data-berat-res>Masukkan massa benda, lalu klik Hitung Berat.</div>
                    </div>
                </div>

                <div data-g-panel="res" hidden>
                    <div class="rf-tool__grid-3">
                        <div class="rf-field">
                            <label class="rf-label" for="rf-res-f1">Gaya F₁ (N)</label>
                            <input class="rf-input rf-num" id="rf-res-f1" type="number" step="any" data-res-f1>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-res-f2">Gaya F₂ (N, negatif bila berlawanan)</label>
                            <input class="rf-input rf-num" id="rf-res-f2" type="number" step="any" data-res-f2>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-res-m">Massa m (opsional, kg)</label>
                            <input class="rf-input rf-num" id="rf-res-m" type="number" step="any" data-res-m>
                        </div>
                    </div>
                    <div class="rf-tool__actions mt-3">
                        <button type="button" class="rf-btn rf-btn--secondary" data-res-calc>Hitung Resultan</button>
                    </div>
                    <div class="rf-sheet mt-4">
                        <h4 class="rf-sheet__title">Hasil di Lembar Rumus</h4>
                        <div class="rf-sheet__paper rf-num" data-res-res>Masukkan F₁ dan F₂, lalu klik Hitung Resultan.</div>
                    </div>
                </div>
            </div>
        </div>
    `;

    container.querySelectorAll('[data-g-tab]').forEach((tab) => {
        tab.addEventListener('click', () => {
            const key = tab.getAttribute('data-g-tab');
            container.querySelectorAll('[data-g-tab]').forEach((other) => {
                const active = other === tab;
                other.classList.toggle('is-active', active);
                other.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            container.querySelectorAll('[data-g-panel]').forEach((p) => {
                if (p.getAttribute('data-g-panel') === key) p.removeAttribute('hidden');
                else p.setAttribute('hidden', 'hidden');
            });
        });
    });

    container.querySelector('[data-fma-calc]')?.addEventListener('click', () => {
        const res = computeFMA({
            f: parseFloat(container.querySelector('[data-fma-f]')?.value || ''),
            m: parseFloat(container.querySelector('[data-fma-m]')?.value || ''),
            a: parseFloat(container.querySelector('[data-fma-a]')?.value || ''),
        });
        const box = container.querySelector('[data-fma-res]');
        if (!box) return;
        if (res.error) {
            box.innerHTML = `<span class="rf-error">${res.error}</span>`;
            return;
        }
        box.innerHTML = `
            <div class="rf-calc-outcomes">
                <div><span>Gaya (F)</span><strong>${res.fText}</strong></div>
                <div><span>Massa (m)</span><strong>${res.mText}</strong></div>
                <div><span>Percepatan (a)</span><strong>${res.aText}</strong></div>
            </div>
        `;
    });

    container.querySelector('[data-berat-calc]')?.addEventListener('click', () => {
        const res = computeBerat({
            m: parseFloat(container.querySelector('[data-berat-m]')?.value || ''),
            g: parseFloat(container.querySelector('[data-berat-g]')?.value || '9.8'),
        });
        const box = container.querySelector('[data-berat-res]');
        if (!box) return;
        if (res.error) {
            box.innerHTML = `<span class="rf-error">${res.error}</span>`;
            return;
        }
        box.innerHTML = `
            <div class="rf-calc-outcomes">
                <div><span>Berat (w)</span><strong>${res.wText}</strong></div>
            </div>
        `;
    });

    container.querySelector('[data-res-calc]')?.addEventListener('click', () => {
        const res = computeResultanGaya({
            f1: parseFloat(container.querySelector('[data-res-f1]')?.value || ''),
            f2: parseFloat(container.querySelector('[data-res-f2]')?.value || ''),
            m: parseFloat(container.querySelector('[data-res-m]')?.value || ''),
        });
        const box = container.querySelector('[data-res-res]');
        if (!box) return;
        if (res.error) {
            box.innerHTML = `<span class="rf-error">${res.error}</span>`;
            return;
        }
        box.innerHTML = `
            <div class="rf-calc-outcomes">
                <div><span>Resultan (ΣF)</span><strong>${res.fResText}</strong></div>
                ${res.aText ? `<div><span>Percepatan (a)</span><strong>${res.aText}</strong></div>` : ''}
            </div>
        `;
    });
}
